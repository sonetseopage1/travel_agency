<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Tour;

class DashboardController extends Controller
{
    public function index()
    {
        $tours = Tour::all();
        $bookings = Booking::all();
        $reviews = Review::all();

        $customers = Booking::distinct('customer_email')->count('customer_email');
        $revenue = Booking::where('status', 'confirmed')->sum('total_price');

        $upcomingTours = Tour::where('status', 'published')
            ->where('departure_date', '>=', now())
            ->orderBy('departure_date')
            ->limit(5)
            ->get();

        if ($upcomingTours->isEmpty()) {
            $upcomingTours = Tour::orderBy('departure_date')->limit(5)->get();
        }

        $recentBookings = Booking::with('tour')
            ->latest()
            ->limit(10)
            ->get();

        $topTours = Tour::orderByDesc('current_booked')
            ->limit(4)
            ->get();

        $confirmedCount = Booking::where('status', 'confirmed')->count();
        $pendingCount = Booking::where('status', 'pending')->count();
        $cancelledCount = Booking::where('status', 'cancelled')->count();
        $completedCount = Booking::where('status', 'completed')->count();

        return view('admin.dashboard', compact(
            'tours',
            'bookings',
            'reviews',
            'customers',
            'revenue',
            'upcomingTours',
            'recentBookings',
            'topTours',
            'confirmedCount',
            'pendingCount',
            'cancelledCount',
            'completedCount'
        ));
    }
}
