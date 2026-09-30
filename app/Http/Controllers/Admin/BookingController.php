<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

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
