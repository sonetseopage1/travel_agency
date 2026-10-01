<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\ContactMessage;
use App\Models\Destination;
use App\Models\GalleryPhoto;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $tours = collect([]);
        $destinations = collect([]);
        $reviews = collect([]);
        $galleryPhotos = collect([]);
        $blogPosts = collect([]);

        if (class_exists(Tour::class)) {
            $tours = Tour::where('status', 'published')
                ->latest()
                ->orderBy('is_featured', 'desc')
                ->orderBy('departure_date', 'asc')
                ->limit(8)
                ->get();
        }

        if (class_exists(Destination::class)) {
            $destinations = Destination::where('is_active', true)
                ->latest()
                ->limit(6)
                ->get();
        }

        if (class_exists(Review::class)) {
            $reviews = Review::where('is_approved', true)
                ->latest()
                ->limit(3)
                ->get();
        }

        if (class_exists(GalleryPhoto::class)) {
            $galleryPhotos = GalleryPhoto::active()
                ->ordered()
                ->limit(8)
                ->get();
        }

        if (class_exists(BlogPost::class)) {
            $blogPosts = BlogPost::published()
                ->ordered()
                ->limit(3)
                ->get();
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
                    'status' => 'published',
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
                    'status' => 'published',
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
                    'status' => 'published',
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
                    'status' => 'published',
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
                    'status' => 'published',
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
                    'status' => 'published',
                ],
            ]);
        }

        if ($destinations->isEmpty()) {
            $destinations = collect([
                (object) ['id' => 1, 'name' => 'কক্সবাজার', 'category' => 'সমুদ্র • বাংলাদেশ', 'image' => 'https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=600&q=80'],
                (object) ['id' => 2, 'name' => 'সাজেক', 'category' => 'পাহাড় • বাংলাদেশ', 'image' => 'https://images.unsplash.com/photo-1570789210967-2cac24afeb00?auto=format&fit=crop&w=600&q=80'],
                (object) ['id' => 3, 'name' => 'সুন্দরবন', 'category' => 'প্রকৃতি • বাংলাদেশ', 'image' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=600&q=80'],
                (object) ['id' => 4, 'name' => 'Bali', 'category' => 'International', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80'],
                (object) ['id' => 5, 'name' => 'রাঙ্গামাটি', 'category' => 'পাহাড় • বাংলাদেশ', 'image' => 'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=600&q=80'],
                (object) ['id' => 6, 'name' => 'সিলেট', 'category' => 'প্রকৃতি • বাংলাদেশ', 'image' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=600&q=80'],
            ]);
        }

        if ($reviews->isEmpty()) {
            $reviews = collect([
                (object) ['name' => 'রাকিব হাসান', 'comment' => 'পুরো trip-টা খুব সুন্দরভাবে organized ছিল। সময়মতো transport, hotel এবং guide—সবকিছু খুব ভালো ছিল।', 'tour_name' => "Cox's Bazar Traveller"],
                (object) ['name' => 'সাদিয়া রহমান', 'comment' => 'সবচেয়ে ভালো লেগেছে live seat availability। Booking করার আগে কতগুলো seat আছে সেটা পরিষ্কারভাবে দেখতে পেরেছি।', 'tour_name' => 'Sajek Traveller'],
                (object) ['name' => 'মাহমুদুল ইসলাম', 'comment' => 'Family নিয়ে Malaysia trip করেছি। পুরো itinerary আগে থেকেই clear ছিল, তাই trip planning অনেক সহজ হয়েছে।', 'tour_name' => 'Malaysia Traveller'],
            ]);
        }

        return view('frontend.home', array_merge(compact(
            'tours',
            'destinations',
            'reviews',
            'galleryPhotos',
            'blogPosts',
        ), [
            // The contact form on this page reads it, and the view must not
            // depend on the session middleware having run.
            'errors' => $request->session()->get('errors') ?? new ViewErrorBag,
        ]));
    }

    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
        ]);

        if (class_exists(ContactMessage::class)) {
            ContactMessage::create($validated);
        }

        return redirect()->route('home')
            ->withFragment('contact')
            ->with('success', 'ধন্যবাদ! আপনার বার্তা আমরা পেয়েছি। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।');
    }
}
