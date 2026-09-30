<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Services\TourImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TourController extends Controller
{
    public function __construct(private readonly TourImageService $images) {}

    /**
     * Validation rules for the image inputs shared by store() and update().
     */
    private function imageRules(): array
    {
        return [
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_cover' => ['nullable', 'boolean'],
            'gallery_files' => ['nullable', 'array', 'max:'.TourImageService::MAX_GALLERY_IMAGES],
            'gallery_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'existing_gallery' => ['nullable', 'array'],
            'existing_gallery.*' => ['nullable', 'string', 'max:255'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function imageMessages(): array
    {
        return [
            'cover_image.image' => 'কভার ইমেজ অবশ্যই একটি ইমেজ ফাইল হতে হবে।',
            'cover_image.mimes' => 'কভার ইমেজ JPG, PNG বা WEBP ফরম্যাটে হতে হবে।',
            'cover_image.max' => 'কভার ইমেজ সর্বোচ্চ ৪ মেগাবাইট হতে পারবে।',
            'gallery_files.max' => 'গ্যালারিতে সর্বোচ্চ '.TourImageService::MAX_GALLERY_IMAGES.'টি ইমেজ দেওয়া যাবে।',
            'gallery_files.*.image' => 'গ্যালারির প্রতিটি ফাইল অবশ্যই একটি ইমেজ হতে হবে।',
            'gallery_files.*.mimes' => 'গ্যালারির ইমেজগুলো JPG, PNG বা WEBP ফরম্যাটে হতে হবে।',
            'gallery_files.*.max' => 'প্রতিটি ইমেজ সর্বোচ্চ ৪ মেগাবাইট হতে পারবে।',
        ];
    }

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

    /**
     * Image inputs are handled explicitly, so they must never reach fill() —
     * mass assigning an UploadedFile would put an object into a string column.
     */
    private function withoutImageInputs(array $validated): array
    {
        unset(
            $validated['cover_image'],
            $validated['remove_cover'],
            $validated['gallery_files'],
            $validated['existing_gallery'],
            $validated['remove_gallery'],
        );

        return $validated;
    }

    public function store(Request $request): RedirectResponse
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
            'status' => 'nullable|in:draft,published,unpublished,completed',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            ...$this->imageRules(),
        ], $this->imageMessages());

        $tour = new Tour;
        $tour->fill($this->withoutImageInputs($validated));

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

        $tour->includes = $this->listInput($request, 'includes');
        $tour->excludes = $this->listInput($request, 'excludes');
        $tour->important_info = $this->listInput($request, 'important_info');
        $tour->faqs = $this->listInput($request, 'faqs');
        $tour->features = $this->listInput($request, 'features');

        if ($request->hasFile('cover_image')) {
            $tour->cover_image = $this->images->store($request->file('cover_image'), 'cover');
        }

        $tour->gallery = $this->images->storeMany(
            (array) $request->file('gallery_files', [])
        );

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
        $tour->itinerary = $formattedItinerary;

        $tour->save();

        return redirect()->route('admin.tours.index')->with('success', 'Tour created successfully!');
    }

    public function edit(Tour $tour)
    {
        return view('admin.tours.edit', compact('tour'));
    }

    public function update(Request $request, Tour $tour): RedirectResponse
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
            'status' => 'nullable|in:draft,published,unpublished,completed',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            ...$this->imageRules(),
        ], $this->imageMessages());

        // Captured before any mutation so orphaned files can be cleaned up.
        $oldCover = $tour->cover_image;
        $oldGallery = array_values(array_filter((array) $tour->gallery));

        $tour->fill($this->withoutImageInputs($validated));

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

        $tour->includes = $this->listInput($request, 'includes');
        $tour->excludes = $this->listInput($request, 'excludes');
        $tour->important_info = $this->listInput($request, 'important_info');
        $tour->faqs = $this->listInput($request, 'faqs');
        $tour->features = $this->listInput($request, 'features');

        $coverReplaced = false;

        if ($request->boolean('remove_cover')) {
            $tour->cover_image = null;
        } elseif ($request->hasFile('cover_image')) {
            $tour->cover_image = $this->images->store($request->file('cover_image'), 'cover');
            $coverReplaced = true;
        }

        // Keep the images still marked as retained, drop the removed ones, then
        // append anything newly uploaded.
        $removed = array_values(array_filter((array) $request->input('remove_gallery', [])));
        $retained = array_values(array_filter((array) $request->input('existing_gallery', [])));

        $kept = array_values(array_filter(
            array_diff($retained, $removed),
            fn ($path) => is_string($path) && in_array($path, $oldGallery, true)
        ));

        $remaining = TourImageService::MAX_GALLERY_IMAGES - count($kept);

        $tour->gallery = array_merge(
            $kept,
            $this->images->storeMany((array) $request->file('gallery_files', []), 'gallery', max($remaining, 0))
        );

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
        $tour->itinerary = $formattedItinerary;

        $tour->save();

        // Only unlink files this tour no longer references. Anything dropped from
        // the gallery counts, not just what was explicitly flagged, so a partial
        // existing_gallery payload cannot leave files behind on disk.
        $orphans = array_merge(
            $coverReplaced || $request->boolean('remove_cover') ? [$oldCover] : [],
            array_values(array_diff($oldGallery, $kept))
        );

        $this->images->deleteMany(array_filter($orphans));

        return back()->with('success', 'ট্যুর আপডেট সফল হয়েছে।');
    }

    public function destroy(Tour $tour)
    {
        $orphans = array_merge(
            [$tour->cover_image],
            array_filter((array) $tour->gallery)
        );

        $tour->delete();

        $this->images->deleteMany($orphans);

        return redirect()->route('admin.tours.index')->with('success', 'ট্যুর ডিলিট করা হয়েছে।');
    }

    /**
     * Read a repeatable list field, dropping blank rows.
     *
     * The model casts these columns to array, so they must be assigned as
     * arrays. Encoding here as well would store double-encoded JSON, which
     * makes the attribute read back as a string and breaks any @foreach.
     */
    private function listInput(Request $request, string $key): array
    {
        $values = $request->input($key, []);

        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(
            $values,
            fn ($value) => is_scalar($value) ? trim((string) $value) !== '' : $value !== null
        ));
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
