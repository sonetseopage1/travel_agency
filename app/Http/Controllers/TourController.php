<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $query = null;
        $tours = collect([]);

        // Years the month filter can offer, taken from real departures plus a
        // little either side, so the list is never empty on a thin dataset.
        $currentYear = (int) now()->year;
        $availableYears = Tour::whereNotNull('departure_date')
            ->distinct()
            ->orderByDesc('departure_date')
            ->pluck('departure_date')
            ->map(fn ($date) => (int) Carbon::parse($date)->year)
            ->unique()
            ->sortDesc()
            ->values();

        $availableYears = $availableYears
            ->merge([$currentYear, $currentYear + 1])
            ->filter(fn ($year) => $year >= $currentYear - 1)
            ->unique()
            ->sortDesc()
            ->values();

        if (class_exists(Tour::class)) {
            $query = Tour::where('status', 'published');

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('destination')) {
                $query->where('departure_location', 'like', '%'.$request->destination.'%');
            }

            if ($request->filled('price_max')) {
                $query->where('price_per_person', '<=', $request->price_max);
            }

            // Travel window. Both ends are optional: a tour departing on or
            // after date_from and returning on or before date_to. An open end
            // means the customer did not care about that side.
            //
            // A tour that returns after date_to is excluded even if it departs
            // inside the window, because the customer needs the whole trip to
            // fit. A tour with no dates at all is excluded from any dated
            // search, since it cannot be confirmed to fit.
            if ($request->filled('date_from') || $request->filled('date_to')) {
                $query->whereNotNull('departure_date');

                if ($request->filled('date_from')) {
                    $query->whereDate('departure_date', '>=', $request->date_from);
                }

                if ($request->filled('date_to')) {
                    // A missing return date means the tour has no fixed end, so
                    // it cannot be ruled out by an upper bound.
                    $query->where(function ($q) use ($request) {
                        $q->whereNull('return_date')
                            ->orWhereDate('return_date', '<=', $request->date_to);
                    });
                }
            }

            // A month on its own means "any year", so the year is only applied
            // when one was actually chosen.
            if ($request->filled('month')) {
                $query->whereNotNull('departure_date')
                    ->whereMonth('departure_date', (int) $request->month);

                if ($request->filled('year')) {
                    $query->whereYear('departure_date', (int) $request->year);
                }
            }

            $tours = $query->orderBy('departure_date', 'asc')->paginate(9)->withQueryString();
        }

        if ($tours->isEmpty()) {
            $tours = collect([
                (object) [
                    'id' => 1,
                    'title' => 'কক্সবাজার সমুদ্র ভ্রমণ',
                    'slug' => 'coxs-bazar',
                    'location' => 'কক্সবাজার',
                    'image' => 'https://images.unsplash.com/photo-1609947017136-9daf32a5eb16?auto=format&fit=crop&w=900&q=80',
                    'price_per_person' => 6500,
                    'duration_days' => 3,
                    'total_seats' => 30,
                    'current_booked' => 22,
                    'transport_type' => 'বাস',
                    'transport_icon' => '🚌',
                    'is_international' => false,
                ],
                (object) [
                    'id' => 2,
                    'title' => 'সাজেক ভ্যালি অ্যাডভেঞ্চার',
                    'slug' => 'sajek-valley',
                    'location' => 'রাঙ্গামাটি',
                    'image' => 'https://images.unsplash.com/photo-1596895111956-bf1cf0599ce5?auto=format&fit=crop&w=900&q=80',
                    'price_per_person' => 5990,
                    'duration_days' => 3,
                    'total_seats' => 30,
                    'current_booked' => 18,
                    'transport_type' => 'জিপ',
                    'transport_icon' => '🚙',
                    'is_international' => false,
                ],
                (object) [
                    'id' => 3,
                    'title' => 'মালয়েশিয়া ট্যুর',
                    'slug' => 'malaysia-tour',
                    'location' => 'Kuala Lumpur',
                    'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                    'price_per_person' => 54900,
                    'duration_days' => 5,
                    'total_seats' => 20,
                    'current_booked' => 15,
                    'transport_type' => 'Flight',
                    'transport_icon' => '✈️',
                    'is_international' => true,
                ],
                (object) [
                    'id' => 4,
                    'title' => 'সুন্দরবন অ্যাডভেঞ্চার',
                    'slug' => 'sundarban',
                    'location' => 'খুলনা',
                    'image' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=900&q=80',
                    'price_per_person' => 4500,
                    'duration_days' => 2,
                    'total_seats' => 25,
                    'current_booked' => 10,
                    'transport_type' => 'লঞ্চ',
                    'transport_icon' => '🚢',
                    'is_international' => false,
                ],
                (object) [
                    'id' => 5,
                    'title' => 'বান্দরবন ট্যুর',
                    'slug' => 'bandarban',
                    'location' => 'বান্দরবান',
                    'image' => 'https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&w=900&q=80',
                    'price_per_person' => 5200,
                    'duration_days' => 3,
                    'total_seats' => 28,
                    'current_booked' => 24,
                    'transport_type' => 'জিপ',
                    'transport_icon' => '🚙',
                    'is_international' => false,
                ],
                (object) [
                    'id' => 6,
                    'title' => 'থাইল্যান্ড এস্কেপ',
                    'slug' => 'thailand-escape',
                    'location' => 'Bangkok',
                    'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                    'price_per_person' => 62000,
                    'duration_days' => 5,
                    'total_seats' => 18,
                    'current_booked' => 8,
                    'transport_type' => 'Flight',
                    'transport_icon' => '✈️',
                    'is_international' => true,
                ],
            ]);
        }

        return view('frontend.tours.index', compact('tours', 'availableYears', 'currentYear'));
    }

    public function show($slug)
    {
        $tour = null;
        $reviews = collect([]);
        $relatedTours = collect([]);

        if (class_exists(Tour::class)) {
            $tour = Tour::where('slug', $slug)
                ->where('status', 'published')
                ->firstOrFail();

            if (method_exists($tour, 'reviews')) {
                $reviews = $tour->reviews()->where('is_approved', true)->get();
            }

            $relatedTours = Tour::where('category', $tour->category ?? 'Adventure Tour')
                ->where('id', '!=', $tour->id)
                ->where('status', 'published')
                ->limit(3)
                ->get();
        }

        if (! $tour) {
            $tour = (object) [
                'id' => 1,
                'title' => 'কক্সবাজার ৩ দিন ২ রাত সমুদ্র ভ্রমণ',
                'slug' => 'coxs-bazar',
                'location' => 'কক্সবাজার',
                'image' => 'https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=1400&q=85',
                'price_per_person' => 6500,
                'duration_days' => 3,
                'duration_nights' => 2,
                'total_seats' => 30,
                'current_booked' => 22,
                'transport_type' => 'AC Bus',
                'transport_icon' => '🚌',
                'is_international' => false,
                'rating' => '4.9',
                'review_count' => 128,
                'category' => 'Adventure Tour',
                'description' => 'বাংলাদেশের সবচেয়ে জনপ্রিয় সমুদ্র সৈকত কক্সবাজার ঘুরে দেখুন আমাদের পরিকল্পিত ৩ দিন ২ রাতের premium group tour-এর মাধ্যমে। Hotel, transport, meals এবং sightseeing package-এর মধ্যে অন্তর্ভুক্ত।',
                'departure_date' => now()->addDays(15),
                'gallery_images' => [
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=700&q=80',
                    'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=700&q=80',
                    'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=700&q=80',
                    'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=700&q=80',
                ],
                'itinerary' => [
                    [
                        'day_title' => 'Day 01 — ঢাকা → কক্সবাজার',
                        'badge' => 'Travel Day',
                        'activities' => [
                            ['icon' => '🚌', 'time' => '06:30 AM', 'title' => 'গাবতলী থেকে যাত্রা', 'description' => 'নির্ধারিত meeting point-এ সবাইকে উপস্থিত থাকতে হবে।'],
                            ['icon' => '🍛', 'time' => '01:30 PM', 'title' => 'Lunch Break', 'description' => 'পথে নির্ধারিত restaurant-এ দুপুরের খাবার।'],
                            ['icon' => '🏨', 'time' => '04:00 PM', 'title' => 'Hotel Check-in', 'description' => 'Hotel check-in এবং fresh-up।'],
                            ['icon' => '🌊', 'time' => '05:00 PM', 'title' => 'Laboni Beach', 'description' => 'সূর্যাস্ত উপভোগ ও beach activities।'],
                        ],
                    ],
                    [
                        'day_title' => 'Day 02 — Sea & Adventure',
                        'badge' => null,
                        'activities' => [
                            ['icon' => '🍳', 'time' => '07:00 AM', 'title' => 'Breakfast', 'description' => ''],
                            ['icon' => '🚤', 'time' => '08:00 AM', 'title' => 'Marine Drive', 'description' => 'Marine Drive road trip এবং scenic photography।'],
                            ['icon' => '🏝️', 'time' => '11:00 AM', 'title' => 'Inani Beach', 'description' => ''],
                            ['icon' => '🌅', 'time' => '05:00 PM', 'title' => 'Sunset Experience', 'description' => ''],
                        ],
                    ],
                    [
                        'day_title' => 'Day 03 — Shopping & Return',
                        'badge' => null,
                        'activities' => [
                            ['icon' => '🛍️', 'time' => '10:00 AM', 'title' => 'Local Shopping', 'description' => ''],
                            ['icon' => '🚌', 'time' => '02:00 PM', 'title' => 'ঢাকার উদ্দেশ্যে যাত্রা', 'description' => ''],
                        ],
                    ],
                ],
                'includes' => [
                    'ঢাকা-কক্সবাজার AC বাস',
                    '২ রাত hotel accommodation',
                    'Daily breakfast',
                    'Sightseeing transport',
                    'Tour guide',
                    'Basic travel assistance',
                ],
                'excludes' => [
                    'Personal shopping',
                    'Lunch & dinner',
                    'Personal expenses',
                    'Optional activities',
                    'Anything not mentioned above',
                ],
                'important_info' => [
                    'যাত্রার কমপক্ষে ৩০ মিনিট আগে উপস্থিত হতে হবে।',
                    'Booking confirm হওয়ার পর cancellation policy প্রযোজ্য হবে।',
                    'আবহাওয়ার কারণে itinerary পরিবর্তিত হতে পারে।',
                    'শিশুদের জন্য package price আলাদা হতে পারে।',
                ],
                'faqs' => [
                    ['q' => 'Booking করার পর কীভাবে confirmation পাব?', 'a' => 'Payment successful হওয়ার পর আপনার registered mobile/email-এ booking confirmation পাঠানো হবে।'],
                    ['q' => 'Seat availability কি real-time?', 'a' => 'হ্যাঁ। Confirmed booking অনুযায়ী available seat সংখ্যা automatically update হবে।'],
                    ['q' => 'Cancellation policy কী?', 'a' => 'Tour-এর departure date অনুযায়ী cancellation এবং refund policy প্রযোজ্য হবে।'],
                ],
            ];

            $reviews = collect([
                (object) ['name' => 'রাকিব হাসান', 'comment' => 'পুরো trip-টা খুব সুন্দর ছিল। Guide এবং transport দুটোই ভালো ছিল।'],
                (object) ['name' => 'সাদিয়া রহমান', 'comment' => 'Itinerary আগে থেকেই clear থাকায় family নিয়ে trip plan করা অনেক সহজ হয়েছে।'],
            ]);
        }

        return view('frontend.tours.show', compact('tour', 'reviews', 'relatedTours'));
    }
}
