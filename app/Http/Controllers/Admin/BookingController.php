<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientSeatsException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('tour');

        if ($search = $request->input('search')) {
            $query->where('customer_name', 'like', "%{$search}%")
                ->orWhere('customer_email', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $bookings = $query->latest()->paginate(20);

        $confirmedCount = Booking::where('status', 'confirmed')->count();
        $pendingCount = Booking::where('status', 'pending')->count();
        $cancelledCount = Booking::where('status', 'cancelled')->count();

        return view('admin.bookings.index', compact('bookings', 'confirmedCount', 'pendingCount', 'cancelledCount'));
    }

    public function create(): View
    {
        $tours = Tour::where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'price_per_person', 'max_slots', 'current_booked', 'status']);

        return view('admin.bookings.create', [
            'tours' => $tours,
            'promoCodes' => PromoCode::orderBy('code')->get(),
        ]);
    }

    /**
     * Create a booking on behalf of a customer (walk-in, phone or counter sale).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|integer|exists:tours,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'guest_count' => 'required|integer|min:1',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'payment_status' => 'required|in:unpaid,partial,paid',
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

        $tour = Tour::findOrFail($validated['tour_id']);

        $subtotal = round((float) $tour->price_per_person * $validated['guest_count'], 2);
        $discount = 0.0;
        $promoCode = null;

        if ($request->filled('promo_code')) {
            $promoCode = PromoCode::whereRaw('UPPER(code) = ?', [strtoupper($request->input('promo_code'))])->first();

            if (! $promoCode) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'প্রোমো কোডটি সঠিক নয়।');
            }

            $reason = $promoCode->rejectionReason((int) $tour->id);

            if ($reason !== null) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $reason);
            }

            $discount = $promoCode->discountFor($subtotal);
        }

        try {
            $booking = DB::transaction(function () use ($validated, $tour, $subtotal, $discount, $promoCode) {
                // Lock the tour row so concurrent bookings cannot oversell the seats.
                $locked = Tour::whereKey($tour->getKey())->lockForUpdate()->firstOrFail();
                $available = ($locked->max_slots ?? 0) - ($locked->current_booked ?? 0);

                if ($available < $validated['guest_count']) {
                    throw new InsufficientSeatsException($available);
                }

                $booking = Booking::create([
                    'tour_id' => $tour->id,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'] ?? null,
                    'customer_phone' => $validated['customer_phone'],
                    'guest_count' => $validated['guest_count'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'promo_code' => $promoCode?->code,
                    'promo_code_id' => $promoCode?->id,
                    'total_price' => round($subtotal - $discount, 2),
                    'status' => $validated['status'],
                    'payment_status' => $validated['payment_status'],
                    'payment_method' => $validated['payment_method'] ?? null,
                    'transaction_id' => $validated['transaction_id'] ?? null,
                    'special_notes' => $validated['special_notes'] ?? null,
                ]);

                $locked->increment('current_booked', $validated['guest_count']);

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

    public function show($id)
    {
        $booking = Booking::with('tour')->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $booking = Booking::findOrFail($id);
        $booking->status = $request->input('status');
        $booking->save();

        return back()->with('success', 'Booking status updated!');
    }
}
