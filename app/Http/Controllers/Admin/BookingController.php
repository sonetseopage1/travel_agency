<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientCabinsException;
use App\Exceptions\InsufficientSeatsException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\Tour;
use App\Services\TourPricingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly TourPricingService $pricing,
    ) {}

    public function index(Request $request)
    {
        $query = Booking::with('tour');

        // Grouped so the status filter below narrows the search results too.
        // Un-grouped, SQL binds AND tighter than OR and the status filter is
        // silently ignored.
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhereHas('tour', fn (Builder $t) => $t->where('title', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($payment = $request->input('payment_status')) {
            $query->where('payment_status', $payment);
        }

        if ($tourId = $request->input('tour_id')) {
            $query->where('tour_id', $tourId);
        }

        // withQueryString keeps the active filters when moving to page 2+.
        $bookings = $query->latest()->paginate(20)->withQueryString();

        $confirmedCount = Booking::where('status', 'confirmed')->count();
        $pendingCount = Booking::where('status', 'pending')->count();
        $cancelledCount = Booking::where('status', 'cancelled')->count();

        // Counted separately from $bookings->total(), which is the filtered
        // count while a filter is active.
        $totalCount = Booking::count();

        $tours = Tour::orderBy('title')->get(['id', 'title']);

        return view('admin.bookings.index', compact(
            'bookings',
            'confirmedCount',
            'pendingCount',
            'cancelledCount',
            'totalCount',
            'tours',
        ));
    }

    public function create(Request $request): View
    {
        $tours = Tour::with('pricingTiers')
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'price_per_person', 'max_slots', 'current_booked', 'status']);

        return view('admin.bookings.create', [
            'tours' => $tours,
            'promoCodes' => PromoCode::orderBy('code')->get(),
            // Shared explicitly so the view never depends on the session
            // middleware having run.
            'errors' => $request->session()->get('errors') ?? new ViewErrorBag,
        ]);
    }

    /**
     * Create a booking on behalf of a customer (walk-in, phone or counter sale).
     *
     * The party is collected the same way the public form collects it: an adult
     * count, an optional couple flag, and optional child ages.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer|exists:tours,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'guest_count' => 'required|integer|min:1|max:100',
            'is_couple' => 'nullable|boolean',
            'has_children' => 'nullable|boolean',
            'child_ages' => 'nullable|array',
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'payment_status' => ['required', Rule::in(Booking::PAYMENT_STATUSES)],
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'promo_code' => 'nullable|string|max:50',
            'special_notes' => 'nullable|string|max:2000',
        ], [
            'tour_id.required' => 'ট্যুর নির্বাচন করুন।',
            'tour_id.exists' => 'নির্বাচিত ট্যুরটি পাওয়া যায়নি।',
            'customer_name.required' => 'গ্রাহকের পুরো নাম লিখুন।',
            'customer_email.required' => 'গ্রাহকের ইমেইল ঠিকানা দিন।',
            'customer_email.email' => 'সঠিক ইমেইল ঠিকানা দিন।',
            'customer_phone.required' => 'মোবাইল নম্বর লিখুন।',
            'guest_count.required' => 'যাত্রীর সংখ্যা লিখুন।',
            'guest_count.min' => 'অন্তত ১ জন যাত্রী লাগবে।',
            'status.required' => 'বুকিং স্ট্যাটাস নির্বাচন করুন।',
            'status.in' => 'বুকিং স্ট্যাটাসটি সঠিক নয়।',
            'payment_status.required' => 'পেমেন্ট স্ট্যাটাস নির্বাচন করুন।',
            'payment_status.in' => 'পেমেন্ট স্ট্যাটাসটি সঠিক নয়।',
        ]);

        $tour = Tour::with('pricingTiers')->findOrFail($validated['tour_id']);

        $adults = max(1, (int) $validated['guest_count']);
        $isCouple = $request->boolean('is_couple');
        $hasChildren = $request->boolean('has_children');

        try {
            $childAges = $hasChildren
                ? $this->pricing->normalizeChildAges($validated['child_ages'] ?? [])
                : [];
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $tier = $isCouple
            ? $this->pricing->resolveTier($tour, 'couple')
            : $this->pricing->suggestTier($tour, $adults, $childAges !== []);

        if (! $tier) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'এই ট্যুরের জন্য কোনো বুকিং সুবিধা সক্রিয় নেই।');
        }

        $quote = $this->pricing->quote($tier, $adults, $childAges);

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

            $reason = $promoCode->rejectionReason((int) $tour->id);

            if ($reason !== null) {
                return redirect()->back()->withInput()->with('error', $reason);
            }
        }

        $quote = $this->pricing->applyPromo($quote, $promoCode);

        // A booking created already cancelled must not hold inventory.
        $occupying = $validated['status'] !== 'cancelled';

        try {
            $booking = DB::transaction(function () use ($validated, $tour, $tier, $quote, $promoCode, $occupying) {
                // Lock the tour row so concurrent bookings cannot oversell the seats.
                $locked = Tour::whereKey($tour->getKey())->lockForUpdate()->firstOrFail();
                $available = ($locked->max_slots ?? 0) - ($locked->current_booked ?? 0);

                if ($available < $quote['guest_count']) {
                    throw new InsufficientSeatsException($available);
                }

                $booking = Booking::create([
                    'tour_id' => $tour->id,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'customer_phone' => $validated['customer_phone'],
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
                    'discount_amount' => $quote['discount_amount'],
                    'promo_code' => $promoCode?->code,
                    'promo_code_id' => $promoCode?->id,
                    'total_price' => $quote['total_price'],
                    'status' => $validated['status'],
                    'payment_status' => $validated['payment_status'],
                    'payment_method' => $validated['payment_method'] ?? null,
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'special_notes' => $validated['special_notes'] ?? null,
                ]);

                foreach ($quote['children'] as $child) {
                    $booking->guests()->create($child);
                }

                if ($occupying) {
                    $locked->increment('current_booked', $quote['guest_count']);
                    $this->pricing->reserveCabins($tier, $quote['cabin_count']);
                }

                if ($promoCode) {
                    $promoCode->increment('used_count');
                }

                return $booking;
            });
        } catch (InsufficientSeatsException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'দুঃখিত! এই ট্যুরে পর্যাপ্ত সিট নেই। ফাকা আছে মাত্র '.$e->available.'টি।');
        }

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'ম্যানুয়াল বুকিং সফলভাবে তৈরি হয়েছে।');
    }

    public function show(Request $request, $id)
    {
        $booking = Booking::with(['tour', 'guests', 'pricingTier'])->findOrFail($id);

        return view('admin.bookings.show', [
            'booking' => $booking,
            // Shared explicitly so the view never depends on the session
            // middleware having run.
            'errors' => $request->session()->get('errors') ?? new ViewErrorBag,
        ]);
    }

    /**
     * Update a booking's status and/or payment details.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'payment_status' => ['required', Rule::in(Booking::PAYMENT_STATUSES)],
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
        ], [
            'status.required' => 'বুকিং স্ট্যাটাস নির্বাচন করুন।',
            'status.in' => 'বুকিং স্ট্যাটাসটি সঠিক নয়।',
            'payment_status.required' => 'পেমেন্ট স্ট্যাটাস নির্বাচন করুন।',
            'payment_status.in' => 'পেমেন্ট স্ট্যাটাসটি সঠিক নয়।',
        ]);

        $booking = Booking::with('tour')->findOrFail($id);
        $newStatus = $validated['status'];
        $wasCancelled = $booking->status === 'cancelled';
        $willCancel = $newStatus === 'cancelled';

        try {
            DB::transaction(function () use ($booking, $validated, $newStatus, $wasCancelled, $willCancel) {
                $lockedTour = Tour::whereKey($booking->tour_id)->lockForUpdate()->first();

                // Cancelling frees the seats and cabins; un-cancelling takes them back.
                if ($wasCancelled && ! $willCancel && $lockedTour) {
                    $available = ($lockedTour->max_slots ?? 0) - ($lockedTour->current_booked ?? 0);

                    if ($available < $booking->guest_count) {
                        throw new InsufficientSeatsException($available);
                    }

                    $tier = $booking->pricingTier()->first();

                    if ($tier && ! $tier->hasCabinAvailability((int) $booking->cabin_count)) {
                        throw new InsufficientCabinsException;
                    }

                    $lockedTour->increment('current_booked', $booking->guest_count);
                    $this->pricing->reserveCabins($tier, (int) $booking->cabin_count);
                } elseif (! $wasCancelled && $willCancel && $lockedTour) {
                    $lockedTour->decrement('current_booked', $booking->guest_count);
                    $this->pricing->releaseCabins($booking->pricingTier()->first(), (int) $booking->cabin_count);
                }

                $booking->status = $newStatus;
                $booking->payment_status = $validated['payment_status'];
                $booking->payment_method = $validated['payment_method'] ?? $booking->payment_method;
                $booking->transaction_id = $validated['transaction_id'] ?? $booking->transaction_id;
                $booking->save();
            });
        } catch (InsufficientSeatsException $e) {
            return redirect()->back()
                ->with('error', 'বুকিং পুনরায় চালু করা যায়নি — ট্যুরে পর্যাপ্ত সিট নেই। ফাকা আছে মাত্র '.$e->available.'টি।');
        } catch (InsufficientCabinsException) {
            return redirect()->back()
                ->with('error', 'বুকিং পুনরায় চালু করা যায়নি — প্রয়োজনীয় সংখ্যক কেবিন ফাঁকা নেই।');
        }

        return back()->with('success', 'বুকিং ও পেমেন্ট তথ্য আপডেট হয়েছে।');
    }
}
