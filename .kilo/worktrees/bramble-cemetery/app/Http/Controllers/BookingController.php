<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create(Request $request)
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tour_id' => 'required|exists:tours,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'guest_count' => 'required|integer|min:1',
            'special_notes' => 'nullable|string',
        ]);

        $tour = null;

        if (class_exists(Tour::class)) {
            $tour = Tour::findOrFail($validated['tour_id']);

            $availableSlots = ($tour->total_seats ?? 30) - ($tour->current_booked ?? 0);

            if ($availableSlots < $validated['guest_count']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'দুঃখিত! পর্যাপ্ত সিট নেই। ফাকা আছে মাত্র '.$availableSlots.'টি।');
            }
        }

        $totalPrice = ($tour->price_per_person ?? 6500) * $validated['guest_count'];

        $bookingData = array_merge($validated, [
            'total_price' => $totalPrice,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $booking = null;

        if (class_exists(Booking::class)) {
            $booking = Booking::create($bookingData);

            if ($tour) {
                $tour->increment('current_booked', $validated['guest_count']);
            }
        }

        if (! $booking) {
            $booking = (object) [
                'id' => 'BT-'.rand(10000, 99999),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'guest_count' => $validated['guest_count'],
                'total_price' => $totalPrice,
                'tour' => $tour ?? (object) ['title' => 'কক্সবাজার সমুদ্র ভ্রমণ'],
            ];
        }

        return redirect()->route('bookings.success', $booking->id ?? 1)
            ->with('success', 'আপনার বুকিং সফল হয়েছে! শীঘ্রই আমরা আপনার সাথে যোগাযোগ করব।');
    }

    public function success($id)
    {
        $booking = null;

        if (class_exists(Booking::class)) {
            $booking = Booking::with('tour')->findOrFail($id);
        }

        if (! $booking) {
            $booking = (object) [
                'id' => $id,
                'customer_name' => 'গ্রাহক',
                'customer_phone' => '01XXXXXXXXX',
                'guest_count' => 2,
                'total_price' => 13000,
                'tour' => (object) ['title' => 'কক্সবাজার সমুদ্র ভ্রমণ'],
            ];
        }

        return view('frontend.bookings.success', compact('booking'));
    }
}
