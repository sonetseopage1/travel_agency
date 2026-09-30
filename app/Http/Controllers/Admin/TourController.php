<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $query = Tour::query();

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('destination', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $tours = $query->latest()->paginate(20);

        return view('admin.tours.index', compact('tours'));
    }

    public function create()
    {
        return view('admin.tours.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_title' => 'nullable|string|max:255',
            'category' => ['required', 'string', Rule::in(Tour::CATEGORIES)],
            'destination' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:3000',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'days' => 'nullable|integer|min:1',
            'nights' => 'nullable|integer|min:0',
            'departure_location' => 'nullable|string|max:255',
            'meeting_point' => 'nullable|string|max:255',
            'price_per_person' => 'nullable|numeric|min:0',
            'max_slots' => 'nullable|integer|min:1',
            'current_booked' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|url|max:2048',
            'status' => 'nullable|in:draft,published,unpublished,completed',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $tour = new Tour;
        $tour->fill($validated);

        $title = $request->input('title');
        $slugBase = $request->input('slug') ?: Str::slug($title);
        $slug = $slugBase;
        $counter = 1;
        while (Tour::where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$counter++;
        }
        $tour->slug = $slug;

        $tour->status = $this->input($request, 'status', 'draft');
        $tour->is_featured = $request->boolean('is_featured', false);
        $tour->price_per_person = $this->input($request, 'price_per_person', $this->input($request, 'price_per_person_sidebar', 0));
        $tour->max_slots = $this->input($request, 'max_slots', $this->input($request, 'max_slots_sidebar', 30));
        $tour->current_booked = $this->input($request, 'current_booked', $this->input($request, 'current_booked_sidebar', 0));

        $tour->includes = json_encode($request->input('includes', []) ?: []);
        $tour->excludes = json_encode($request->input('excludes', []) ?: []);
        $tour->important_info = json_encode($request->input('important_info', []) ?: []);
        $tour->faqs = json_encode($request->input('faqs', []) ?: []);
        $tour->features = json_encode($request->input('features', []) ?: []);
        $tour->gallery = json_encode($request->input('gallery_urls', []) ?: []);

        $itinerary = $request->input('itinerary', []);
        $formattedItinerary = [];
        if (is_array($itinerary)) {
            foreach ($itinerary as $day) {
                $dayData = [
                    'day_title' => $day['day_title'] ?? '',
                    'activities' => [],
                ];
                if (isset($day['activities']) && is_array($day['activities'])) {
                    foreach ($day['activities'] as $act) {
                        $dayData['activities'][] = [
                            'time' => $act['time'] ?? '',
                            'icon' => $act['icon'] ?? '',
                            'title' => $act['title'] ?? '',
                            'location' => $act['location'] ?? '',
                            'description' => $act['description'] ?? '',
                        ];
                    }
                }
                $formattedItinerary[] = $dayData;
            }
        }
        $tour->itinerary = json_encode($formattedItinerary);

        $tour->save();

        return redirect()->route('admin.tours.index')->with('success', 'Tour created successfully!');
    }

    public function edit(Tour $tour)
    {
        return view('admin.tours.edit', compact('tour'));
    }

    public function update(Request $request, Tour $tour)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'short_title' => 'nullable|string|max:255',
            'category' => ['required', 'string', Rule::in(Tour::CATEGORIES)],
            'destination' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:3000',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'days' => 'nullable|integer|min:1',
            'nights' => 'nullable|integer|min:0',
            'departure_location' => 'nullable|string|max:255',
            'meeting_point' => 'nullable|string|max:255',
            'price_per_person' => 'nullable|numeric|min:0',
            'max_slots' => 'nullable|integer|min:1',
            'current_booked' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|url|max:2048',
            'status' => 'nullable|in:draft,published,unpublished,completed',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $tour->fill($validated);

        if ($request->input('title') !== $tour->getOriginal('title') || ! $tour->slug) {
            $slugBase = Str::slug($request->input('title'));
            $slug = $slugBase;
            $counter = 1;
            while (Tour::where('slug', $slug)->where('id', '!=', $tour->id)->exists()) {
                $slug = $slugBase.'-'.$counter++;
            }
            $tour->slug = $slug;
        }

        $tour->status = $this->input($request, 'status', $tour->status ?? 'draft');
        $tour->is_featured = $request->boolean('is_featured', false);
        $tour->price_per_person = $this->input($request, 'price_per_person', $this->input($request, 'price_per_person_sb', $tour->price_per_person ?? 0));
        $tour->max_slots = $this->input($request, 'max_slots', $this->input($request, 'max_slots_sb', $tour->max_slots ?? 30));
        $tour->current_booked = $this->input($request, 'current_booked', $this->input($request, 'current_booked_sb', $tour->current_booked ?? 0));

        $tour->includes = json_encode($request->input('includes', []) ?: []);
        $tour->excludes = json_encode($request->input('excludes', []) ?: []);
        $tour->important_info = json_encode($request->input('important_info', []) ?: []);
        $tour->faqs = json_encode($request->input('faqs', []) ?: []);
        $tour->features = json_encode($request->input('features', []) ?: []);
        $tour->gallery = json_encode($request->input('gallery_urls', []) ?: []);

        $itinerary = $request->input('itinerary', []);
        $formattedItinerary = [];
        if (is_array($itinerary)) {
            foreach ($itinerary as $day) {
                $dayData = [
                    'day_title' => $day['day_title'] ?? '',
                    'activities' => [],
                ];
                if (isset($day['activities']) && is_array($day['activities'])) {
                    foreach ($day['activities'] as $act) {
                        $dayData['activities'][] = [
                            'time' => $act['time'] ?? '',
                            'icon' => $act['icon'] ?? '',
                            'title' => $act['title'] ?? '',
                            'location' => $act['location'] ?? '',
                            'description' => $act['description'] ?? '',
                        ];
                    }
                }
                $formattedItinerary[] = $dayData;
            }
        }
        $tour->itinerary = json_encode($formattedItinerary);

        $tour->save();

        return back()->with('success', 'Tour updated successfully!');
    }

    public function destroy(Tour $tour)
    {
        $tour->delete();

        return redirect()->route('admin.tours.index')->with('success', 'Tour deleted successfully!');
    }

    /**
     * Read a form value, falling back when the field is absent or blank.
     *
     * `Request::input()` returns the submitted value even when it is null or an
     * empty string, which would write NULL into NOT NULL columns.
     */
    private function input(Request $request, string $key, mixed $fallback): mixed
    {
        return $request->filled($key) ? $request->input($key) : $fallback;
    }
}
