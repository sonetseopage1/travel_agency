<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\PromoCode;
use App\Models\Tour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(Request $request): View
    {
        $tour = null;
        $tourId = $request->query('tour_id', 1);

        if (class_exists(Tour::class)) {
            $tour = Tour::find($tourId);
        }

        if (! $tour) {
            $tour = (object) [
                'id' => $tourId,
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

        return view('frontend.bookings.create', compact('tour'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'guest_count' => 'required|integer|min:1',
            'promo_code' => 'nullable|string|max:50',
            'special_notes' => 'nullable|string',
        ]);

        $tour = Tour::findOrFail($validated['tour_id']);

        $availableSlots = ($tour->total_seats ?? 30) - ($tour->current_booked ?? 0);

        if ($availableSlots < $validated['guest_count']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'দুঃখিত! পর্যাপ্ত সিট নেই। ফাকা আছে মাত্র '.$availableSlots.'টি।');
        }

        $unitPrice = (float) $tour->price_per_person;
        $subtotal = round($unitPrice * $validated['guest_count'], 2);
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

        $totalPrice = round($subtotal - $discount, 2);

        $booking = Booking::create(array_merge($validated, [
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'promo_code' => $promoCode?->code,
            'promo_code_id' => $promoCode?->id,
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]));

        if ($promoCode) {
            $promoCode->increment('used_count');
        }

        $tour->increment('current_booked', $validated['guest_count']);

        return redirect()->route('bookings.success', $booking->id)
            ->with('success', 'আপনার বুকিং সফল হয়েছে! শীঘ্রই আমরা আপনার সাথে যোগাযোগ করব।');
    }

    /**
     * Check a promo code as the user types it and report the resulting discount.
     */
    public function validatePromo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'promo_code' => 'required|string|max:50',
            'tour_id' => 'required|integer',
            'guest_count' => 'required|integer|min:1',
        ]);

        $promoCode = PromoCode::whereRaw('UPPER(code) = ?', [strtoupper($validated['promo_code'])])->first();

        if (! $promoCode) {
            return response()->json(['valid' => false, 'message' => 'প্রোমো কোডটি সঠিক নয়।'], 422);
        }

        $tour = Tour::find($validated['tour_id']);
        $unitPrice = (float) ($tour?->price_per_person ?? 0);
        $subtotal = round($unitPrice * $validated['guest_count'], 2);

        $reason = $promoCode->rejectionReason($tour?->id);

        if ($reason !== null) {
            return response()->json(['valid' => false, 'message' => $reason], 422);
        }

        $discount = $promoCode->discountFor($subtotal);

        return response()->json([
            'valid' => true,
            'code' => $promoCode->code,
            'message' => 'প্রোমো কোড প্রয়োগ হয়েছে — '.$promoCode->describeDiscount(),
            'discount' => $discount,
            'subtotal' => $subtotal,
            'total' => round($subtotal - $discount, 2),
            // Sent so the client can recompute the discount when guest count changes.
            'discount_type' => $promoCode->discount_type,
            'discount_value' => (float) $promoCode->discount_value,
            'max_discount' => $promoCode->max_discount !== null ? (float) $promoCode->max_discount : null,
        ]);
    }

    public function success($id): View
    {
        $booking = null;

        if (class_exists(Booking::class)) {
            $booking = Booking::with(['tour', 'promoCode'])->findOrFail($id);
        }

        if (! $booking) {
            $booking = (object) [
                'id' => $id,
                'customer_name' => 'গ্রাহক',
                'customer_phone' => '01XXXXXXXXX',
                'guest_count' => 2,
                'subtotal' => 13000,
                'discount_amount' => 0,
                'promo_code' => null,
                'total_price' => 13000,
                'tour' => (object) ['title' => 'কক্সবাজার সমুদ্র ভ্রমণ'],
            ];
        }

        return view('frontend.bookings.success', compact('booking'));
    }
}
