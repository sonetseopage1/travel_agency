<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\Tour;
use App\Models\TourPricingTier;
use App\Services\TourPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

/**
 * Public booking.
 *
 * The form collects the party the simple way: a number of adults, a couple
 * checkbox that switches to the couple rate, and - only when the customer ticks
 * "we have children" - a count and an age per child. Everything priced below
 * goes through TourPricingService so the shown total is the charged total.
 */
class BookingController extends Controller
{
    public function __construct(
        private readonly TourPricingService $pricing,
    ) {}

    public function create(Request $request): View
    {
        $tour = Tour::with('pricingTiers')->find($request->query('tour_id', 1));

        if (! $tour) {
            $tour = (object) [
                'id' => $request->query('tour_id', 1),
                'title' => 'কক্সবাজার ৩ দিন ২ রাত সমুদ্র ভ্রমণ',
                'slug' => 'coxs-bazar',
                'location' => 'কক্সবাজার',
                'image' => 'https://images.unsplash.com/photo-1609947017136-9daf32a5eb16?auto=format&fit=crop&w=400&q=80',
                'price_per_person' => 6500,
                'duration_days' => 3,
                'total_seats' => 30,
                'current_booked' => 22,
                'transport_type' => 'AC Bus',
                'departure_date' => now()->addDays(15),
            ];
        }

        $tiers = $tour->bookableTiers();

        // Fall back to a synthetic single tier when a tour has none configured,
        // so the form always has something to price against.
        if ($tiers->isEmpty() && isset($tour->price_per_person)) {
            $tiers = collect([new TourPricingTier([
                'type' => 'single',
                'label' => 'Single',
                'price_per_adult' => (float) $tour->price_per_person,
                'min_adults' => 1,
                'max_adults' => 30,
                'infant_age_max' => 3,
                'child_age_max' => 8,
                'child_price_percent' => 50,
                'capacity_per_cabin' => 4,
                'included_cabin_count' => 1,
                'extra_cabin_fee' => 0,
                'discount_type' => 'none',
                'discount_value' => 0,
                'cabins_total' => null,
            ])]);
        }

        $couple = (bool) old('is_couple', false);
        $adults = max(1, (int) old('guest_count', $couple ? 2 : 1));
        $childAges = $this->restoreChildAges();

        $tier = $this->pickTier($tour, $tiers, $couple, $adults, $adults);
        $quote = $tier ? $this->pricing->quote($tier, $adults, $childAges) : null;

        return view('frontend.bookings.create', [
            'tour' => $tour,
            'tiers' => $tiers,
            'selectedTier' => $tier,
            'isCouple' => $couple,
            'adults' => $adults,
            'childAges' => $childAges,
            'quote' => $quote,
            'pricingPayload' => $this->pricing->toBrowserPayload($tiers),
            // Shared explicitly so the view never depends on the session
            // middleware having run, which is not true for every caller.
            'errors' => $request->session()->get('errors') ?? new ViewErrorBag,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'guest_count' => 'required|integer|min:1|max:100',
            'is_couple' => 'nullable|boolean',
            'has_children' => 'nullable|boolean',
            'child_count' => 'nullable|integer|min:0|max:50',
            'child_ages' => 'nullable|array',
            'promo_code' => 'nullable|string|max:50',
            'special_notes' => 'nullable|string',
        ]);

        $tour = Tour::with('pricingTiers')->findOrFail($validated['tour_id']);

        $isCouple = $request->boolean('is_couple');
        $hasChildren = $request->boolean('has_children');
        $adults = max(1, (int) $validated['guest_count']);

        try {
            $childAges = $hasChildren
                ? $this->pricing->normalizeChildAges($validated['child_ages'] ?? [])
                : [];
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        // The couple checkbox is the only thing that selects the couple rate.
        // Otherwise the tier follows the shape of the party: children or a
        // larger group take the widest tier that fits. This keys off the ages
        // actually entered, not the checkbox, so a ticked box with every age
        // left blank still books as the party it really is.
        $tier = $isCouple
            ? $this->pricing->resolveTier($tour, 'couple')
            : $this->pricing->suggestTier($tour, $adults, $childAges !== []);

        if (! $tier) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'এই ট্যুরের জন্য কোনো বুকিং সুবিধা সক্রিয় নেই।');
        }

        $quote = $this->pricing->quote($tier, $adults, $childAges);

        // Children above the child age pay in full, so make sure the party still
        // contains a paying adult rather than only free infants.
        if (($quote['adult_count'] ?? 0) < 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'অন্তত একজন প্রাপ্তবয়স্ক যাত্রী থাকতে হবে।');
        }

        $blockReason = $this->pricing->blockReason($tour, $tier, $quote);

        if ($blockReason !== null) {
            return redirect()->back()->withInput()->with('error', $blockReason);
        }

        $promoCode = null;

        if ($request->filled('promo_code')) {
            $promoCode = PromoCode::whereRaw('UPPER(code) = ?', [strtoupper($request->input('promo_code'))])->first();

            if (! $promoCode) {
                return redirect()->back()->withInput()->with('error', 'প্রোমো কোডটি সঠিক নয়।');
            }

            $reason = $promoCode->rejectionReason($tour->id);

            if ($reason !== null) {
                return redirect()->back()->withInput()->with('error', $reason);
            }
        }

        $quote = $this->pricing->applyPromo($quote, $promoCode);

        $booking = DB::transaction(fn () => $this->persistBooking($tour, $tier, $quote, [
            'customer_name' => $validated['customer_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'special_notes' => $validated['special_notes'] ?? null,
        ]));

        if ($promoCode) {
            $promoCode->increment('used_count');
        }

        return redirect()->route('bookings.success', $booking->id)
            ->with('success', 'আপনার বুকিং সফল হয়েছে! শীঘ্রই আমরা আপনার সাথে যোগাযোগ করব।');
    }

    /**
     * Create the booking, its child lines and the inventory it holds, so a
     * failure part-way cannot leave cabins reserved without a booking.
     */
    private function persistBooking(Tour $tour, TourPricingTier $tier, array $quote, array $customer): Booking
    {
        return DB::transaction(function () use ($tour, $tier, $quote, $customer) {
            $booking = Booking::create([
                ...$customer,
                'tour_id' => $tour->id,
                'guest_count' => $quote['guest_count'],
                'pricing_tier_id' => $tier->id,
                'pricing_tier_type' => $tier->type,
                'pricing_tier_label' => $tier->label,
                'adult_rate' => (float) $tier->price_per_adult,
                'adult_count' => $quote['adult_count'],
                'child_count' => $quote['child_count'],
                'infant_count' => $quote['infant_count'],
                'cabin_count' => $quote['cabin_count'],
                'extra_cabin_amount' => $quote['extra_cabin_amount'],
                'tier_discount_amount' => $quote['tier_discount_amount'],
                'subtotal' => $quote['subtotal'],
                'discount_amount' => $quote['discount_amount'] ?? 0,
                'promo_code' => $quote['promo_code'] ?? null,
                'promo_code_id' => $quote['promo_code_id'] ?? null,
                'total_price' => $quote['total_price'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($quote['children'] as $child) {
                $booking->guests()->create($child);
            }

            $tour->increment('current_booked', $quote['guest_count']);
            $this->pricing->reserveCabins($tier, $quote['cabin_count']);

            return $booking;
        });
    }

    /**
     * Re-price the booking as the party changes, so the promo figure stays
     * honest. Mirrors the client calculation exactly.
     */
    public function validatePromo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'promo_code' => 'required|string|max:50',
            'tour_id' => 'required|integer',
            'guest_count' => 'required|integer|min:1|max:100',
            'is_couple' => 'nullable|boolean',
            'has_children' => 'nullable|boolean',
            'child_ages' => 'nullable|array',
        ]);

        $promoCode = PromoCode::whereRaw('UPPER(code) = ?', [strtoupper($validated['promo_code'])])->first();

        if (! $promoCode) {
            return response()->json(['valid' => false, 'message' => 'প্রোমো কোডটি সঠিক নয়।'], 422);
        }

        $tour = Tour::with('pricingTiers')->find($validated['tour_id']);

        if (! $tour) {
            return response()->json(['valid' => false, 'message' => 'ট্যুরটি পাওয়া যায়নি।'], 422);
        }

        $reason = $promoCode->rejectionReason($tour->id);

        if ($reason !== null) {
            return response()->json(['valid' => false, 'message' => $reason], 422);
        }

        $adults = max(1, (int) $validated['guest_count']);
        $isCouple = $request->boolean('is_couple');
        $hasChildren = $request->boolean('has_children');

        try {
            $childAges = $hasChildren
                ? $this->pricing->normalizeChildAges($validated['child_ages'] ?? [])
                : [];
        } catch (\InvalidArgumentException $e) {
            return response()->json(['valid' => false, 'message' => $e->getMessage()], 422);
        }

        $tier = $isCouple
            ? $this->pricing->resolveTier($tour, 'couple')
            : $this->pricing->suggestTier($tour, $adults, $childAges !== []);

        if (! $tier) {
            return response()->json(['valid' => false, 'message' => 'এই ট্যুরের জন্য কোনো সুবিধা সক্রিয় নেই।'], 422);
        }

        $quote = $this->pricing->applyPromo($this->pricing->quote($tier, $adults, $childAges), $promoCode);

        return response()->json([
            'valid' => true,
            'code' => $promoCode->code,
            'message' => 'প্রোমো কোড প্রয়োগ হয়েছে — '.$promoCode->describeDiscount(),
            'discount' => $quote['discount_amount'],
            'subtotal' => $quote['subtotal'],
            'total' => $quote['total_price'],
            'discount_type' => $promoCode->discount_type,
            'discount_value' => (float) $promoCode->discount_value,
            'max_discount' => $promoCode->max_discount !== null ? (float) $promoCode->max_discount : null,
        ]);
    }

    /**
     * Restore the child ages after a validation failure, so the customer does
     * not have to retype them.
     *
     * @return array<int, int>
     */
    private function restoreChildAges(): array
    {
        $old = old('child_ages');

        if (! is_array($old) || $old === []) {
            return [];
        }

        $ages = [];

        foreach ($old as $value) {
            $value = is_array($value) ? ($value['age'] ?? null) : $value;

            if ($value !== null && trim((string) $value) !== '') {
                $ages[] = (int) $value;
            }
        }

        return $ages;
    }

    /**
     * Pick the tier a freshly loaded form should show, preferring an exact
     * couple match when the box is ticked.
     */
    private function pickTier($tour, $tiers, bool $isCouple, int $adults, int $quoteAdults): ?TourPricingTier
    {
        if ($isCouple) {
            $couple = $tiers->first(fn (TourPricingTier $tier) => $tier->type === 'couple');

            if ($couple) {
                return $couple;
            }
        }

        $old = old('pricing_tier_type');

        if (! empty($old)) {
            $match = $tiers->first(fn (TourPricingTier $tier) => $tier->type === $old);

            if ($match) {
                return $match;
            }
        }

        if ($tour instanceof Tour) {
            return $this->pricing->suggestTier($tour, $quoteAdults) ?? $tiers->first();
        }

        return $tiers->first();
    }

    public function success($id): View
    {
        $booking = Booking::with(['tour', 'promoCode', 'guests'])->findOrFail($id);

        return view('frontend.bookings.success', compact('booking'));
    }
}
