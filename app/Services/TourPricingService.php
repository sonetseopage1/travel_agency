<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\Tour;
use App\Models\TourPricingTier;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * The only place tour prices are calculated.
 *
 * Every caller - the booking form, the admin panel, the promo endpoint - goes
 * through here, so the number a customer is shown cannot drift from the number
 * they are charged. The browser mirrors this logic for live feedback, but the
 * server value is always the one that is stored.
 *
 * The party is described the way the form collects it: a number of adults, plus
 * the ages of any children. Adults are counted because they all pay one rate;
 * only children need an age, because the age decides their rate.
 *
 * Money order of operations, which the client must reproduce exactly:
 *
 *   1. Adults pay the tier's adult rate; each child pays the rate for its age.
 *   2. Those are summed into the guest subtotal.
 *   3. The tier discount applies to the guest subtotal only, never to cabins.
 *   4. Cabins beyond the included count are charged at the extra cabin fee.
 *   5. A promo code applies last, to the discounted guests plus the cabins.
 */
class TourPricingService
{
    /**
     * Build a price quote for a party.
     *
     * @param  int  $adults  number of paying adults
     * @param  array<int, int|null>  $childAges  age of each child, in order
     * @return array<string, mixed>
     */
    public function quote(TourPricingTier $tier, int $adults, array $childAges = []): array
    {
        $adults = max(0, $adults);
        $adultRate = (float) $tier->price_per_adult;

        $children = [];
        $childSubtotal = 0.0;
        $infantCount = 0;
        $childCount = 0;

        foreach (array_values($childAges) as $index => $age) {
            $age = ($age === '' || $age === null) ? null : (int) $age;
            $type = $tier->guestTypeForAge($age);
            $unit = $tier->unitPriceForAge($age);

            $children[] = [
                'sort_order' => $index,
                'age' => $age,
                // Stored as a child regardless of band: a child old enough to
                // pay the adult rate is still a child on the booking, it just
                // gets folded into the adult count for pricing.
                'type' => $type === 'infant' ? 'infant' : 'child',
                'unit_price' => $unit,
                'line_total' => $unit,
            ];

            $childSubtotal += $unit;

            // An age old enough to pay the adult rate is booked as an adult, so
            // the adult headcount stays the number of people actually paying.
            if ($type === 'infant') {
                $infantCount++;
            } elseif ($type === 'child') {
                $childCount++;
            } else {
                $adults++;
            }
        }

        $adultSubtotal = round($adults * $adultRate, 2);
        $guestSubtotal = round($adultSubtotal + $childSubtotal, 2);

        $tierDiscount = $this->tierDiscountFor($tier, $guestSubtotal);

        // Infants travel free but still need a seat and a cabin, so they count
        // toward the headcount used for inventory.
        $totalGuests = $adults + $childCount + $infantCount;

        $cabins = $this->cabinsNeeded($tier, $totalGuests);
        $extraCabins = max(0, $cabins - (int) $tier->included_cabin_count);
        $extraCabinAmount = round($extraCabins * (float) $tier->extra_cabin_fee, 2);

        // A promo is applied by the caller against this figure, because only the
        // caller knows which promo was entered.
        $discountableSubtotal = round(max(0, $guestSubtotal - $tierDiscount), 2);

        return [
            'children' => $children,
            'adult_count' => $adults,
            'child_count' => $childCount,
            'infant_count' => $infantCount,
            'guest_count' => $totalGuests,
            'adult_subtotal' => $adultSubtotal,
            'child_subtotal' => round($childSubtotal, 2),
            'guest_subtotal' => $guestSubtotal,
            'tier_discount_amount' => $tierDiscount,
            'cabin_count' => $cabins,
            'extra_cabin_count' => $extraCabins,
            'extra_cabin_amount' => $extraCabinAmount,
            'discountable_subtotal' => $discountableSubtotal,
            'subtotal' => round($discountableSubtotal + $extraCabinAmount, 2),
        ];
    }

    /**
     * Apply a promo on top of a quote and return the finished money.
     */
    public function applyPromo(array $quote, ?PromoCode $promo): array
    {
        $base = (float) $quote['discountable_subtotal'] + (float) $quote['extra_cabin_amount'];
        $promoDiscount = $promo ? $promo->discountFor($base) : 0.0;

        $quote['promo_code_id'] = $promo?->id;
        $quote['promo_code'] = $promo?->code;
        $quote['discount_amount'] = $promoDiscount;
        $quote['total_price'] = round(max(0, $base - $promoDiscount), 2);

        return $quote;
    }

    /**
     * Operator discount on a tier, applied to the guest subtotal only.
     *
     * Capped at the subtotal so a fixed discount can never go negative, which
     * matches how PromoCode::discountFor() behaves.
     */
    public function tierDiscountFor(TourPricingTier $tier, float $guestSubtotal): float
    {
        if ($guestSubtotal <= 0 || $tier->discount_type === 'none') {
            return 0.0;
        }

        $discount = $tier->discount_type === 'percent'
            ? $guestSubtotal * ((float) $tier->discount_value / 100)
            : (float) $tier->discount_value;

        return round(max(0, min($discount, $guestSubtotal)), 2);
    }

    /**
     * Cabins a headcount needs. Every guest occupies a place, including free
     * infants, so a family cannot squeeze into fewer cabins than it fills.
     */
    public function cabinsNeeded(TourPricingTier $tier, int $guestCount): int
    {
        if ($guestCount <= 0) {
            return 0;
        }

        $capacity = max(1, (int) $tier->capacity_per_cabin);

        return (int) ceil($guestCount / $capacity);
    }

    /**
     * The tier a booking should use, or null when nothing fits.
     */
    public function resolveTier(Tour $tour, ?string $type): ?TourPricingTier
    {
        $tiers = $tour->bookableTiers();

        if ($tiers->isEmpty()) {
            return null;
        }

        if ($type === null || $type === '') {
            return $tiers->first();
        }

        return $tiers->first(fn (TourPricingTier $tier) => $tier->type === $type);
    }

    /**
     * Suggest the tier a party of $adults should book.
     *
     * The couple rate is never chosen automatically: ticking the "we are a
     * couple" box is the only thing that selects it, so a party of two that is
     * not a couple does not silently get the couple price.
     *
     * For everything else the tier follows the shape of the party. A party
     * travelling with children, or too large for the tighter options, takes the
     * widest tier that fits; otherwise the tightest tier that fits wins, so one
     * adult is a Single booking even when the Family rate happens to be lower.
     */
    public function suggestTier(Tour $tour, int $adults, bool $preferWidest = false): ?TourPricingTier
    {
        $adults = max(1, $adults);

        $fitting = $tour->bookableTiers()
            ->filter(fn (TourPricingTier $tier) => $tier->type !== 'couple')
            ->filter(fn (TourPricingTier $tier) => $adults >= $tier->min_adults && $adults <= $tier->max_adults);

        $chosen = $preferWidest
            // Widest first, so a family lands on Family rather than Single.
            ? $fitting->sortBy([
                fn (TourPricingTier $tier) => -(int) $tier->max_adults,
                fn (TourPricingTier $tier) => (float) $tier->price_per_adult,
            ])->first()
            : $fitting->sortBy([
                fn (TourPricingTier $tier) => (int) $tier->max_adults,
                fn (TourPricingTier $tier) => (float) $tier->price_per_adult,
            ])->first();

        if ($chosen) {
            return $chosen;
        }

        // Nothing fits exactly, so fall back to whatever can actually take them.
        return $tour->bookableTiers()
            ->filter(fn (TourPricingTier $tier) => $adults <= $tier->max_adults)
            ->sortByDesc(fn (TourPricingTier $tier) => (int) $tier->max_adults)
            ->first();
    }

    /**
     * Why this booking cannot proceed, or null when it can.
     *
     * Checks in the order a customer would care about: the adult headcount,
     * then seats, then cabins.
     */
    public function blockReason(Tour $tour, TourPricingTier $tier, array $quote): ?string
    {
        $adults = (int) $quote['adult_count'];

        if ($adults < (int) $tier->min_adults || $adults > (int) $tier->max_adults) {
            return sprintf(
                'এই সুবিধায় সর্বনিম্ন %d এবং সর্বোচ্চ %d জন প্রাপ্তবয়স্ক যাত্রী থাকতে হবে।',
                $tier->min_adults,
                $tier->max_adults
            );
        }

        if ($tour->available_slots < (int) $quote['guest_count']) {
            return 'এই ট্যুরের জন্য পর্যাপ্ত আসন নেই।';
        }

        if (! $tier->hasCabinAvailability((int) $quote['cabin_count'])) {
            return 'প্রয়োজনীয় সংখ্যক কেবিন এখন নেই।';
        }

        return null;
    }

    /**
     * Hold cabin inventory for a booking.
     */
    public function reserveCabins(TourPricingTier $tier, int $cabins): void
    {
        if ($tier->cabins_total === null || $cabins <= 0) {
            return;
        }

        $tier->increment('cabins_booked', $cabins);
    }

    /**
     * Return cabin inventory when a booking stops occupying it.
     */
    public function releaseCabins(TourPricingTier $tier, int $cabins): void
    {
        if ($tier->cabins_total === null || $cabins <= 0) {
            return;
        }

        $tier->decrement('cabins_booked', max(0, $cabins));
    }

    /**
     * Move cabin inventory between two states of a booking, so cancelling
     * releases and un-cancelling re-reserves without double counting.
     *
     * @return bool whether anything changed
     */
    public function syncCabinsForStatus(Booking $booking, ?string $from, ?string $to): bool
    {
        if ($booking->pricing_tier_id === null) {
            return false;
        }

        $wasOccupying = $from !== null && $from !== 'cancelled';
        $willOccupy = $to !== 'cancelled';

        if ($wasOccupying === $willOccupy) {
            return false;
        }

        $tier = $booking->pricingTier()->first();

        if ($tier === null) {
            return false;
        }

        $cabins = max(0, (int) $booking->cabin_count);

        $willOccupy
            ? $this->reserveCabins($tier, $cabins)
            : $this->releaseCabins($tier, $cabins);

        return true;
    }

    /**
     * Clean and validate the child ages posted by a form.
     *
     * Blank entries are dropped so an unused age box does not become a guest.
     *
     * @return array<int, int>
     */
    public function normalizeChildAges(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $ages = [];

        foreach ($raw as $value) {
            $value = is_array($value) ? ($value['age'] ?? null) : $value;

            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            if (! is_numeric($value)) {
                throw new InvalidArgumentException('শিশুর বয়স সংখ্যা হিসেবে দিন।');
            }

            $age = (int) $value;

            if ($age < 0 || $age > TourPricingTier::MAX_AGE) {
                throw new InvalidArgumentException(
                    'শিশুর বয়স ০ থেকে '.TourPricingTier::MAX_AGE.' এর মধ্যে হতে হবে।'
                );
            }

            $ages[] = $age;
        }

        return $ages;
    }

    /**
     * Tier data shaped for the browser, so the client can price live without a
     * round trip. Mirrors quote() exactly.
     *
     * @param  Collection<int, TourPricingTier>  $tiers
     * @return array<string, mixed>
     */
    public function toBrowserPayload($tiers): array
    {
        return [
            'tiers' => collect($tiers)
                ->map(fn (TourPricingTier $tier) => $this->tierPayload($tier))
                ->values()
                ->all(),
        ];
    }

    /**
     * One tier in the shape the browser needs to reproduce the server maths.
     *
     * @return array<string, mixed>
     */
    public function tierPayload(TourPricingTier $tier): array
    {
        return [
            'type' => $tier->type,
            'label' => $tier->display_name,
            'price_per_adult' => (float) $tier->price_per_adult,
            'min_adults' => (int) $tier->min_adults,
            'max_adults' => (int) $tier->max_adults,
            'infant_age_max' => (int) $tier->infant_age_max,
            'child_age_max' => (int) $tier->child_age_max,
            'child_price_percent' => (float) $tier->child_price_percent,
            'capacity_per_cabin' => (int) $tier->capacity_per_cabin,
            'included_cabin_count' => (int) $tier->included_cabin_count,
            'extra_cabin_fee' => (float) $tier->extra_cabin_fee,
            'discount_type' => $tier->discount_type,
            'discount_value' => (float) $tier->discount_value,
            'cabins_available' => $tier->cabins_available,
        ];
    }
}
