<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\GalleryPhoto;
use App\Services\ContentImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function __construct(private readonly ContentImageService $images) {}

    public function index(Request $request): View
    {
        $photos = GalleryPhoto::query()
            ->with('destination')
            ->when($request->filled('destination_id'), fn ($query) => $query->where('destination_id', $request->input('destination_id')))
            ->when($request->boolean('featured'), fn ($query) => $query->where('is_featured', true))
            ->ordered()
            ->paginate(24)
            ->withQueryString();

        $destinations = Destination::orderBy('name')->get();

        $totalCount = GalleryPhoto::count();
        $activeCount = GalleryPhoto::active()->count();
        $featuredCount = GalleryPhoto::where('is_featured', true)->count();

        return view('admin.gallery.index', compact(
            'photos',
            'destinations',
            'totalCount',
            'activeCount',
            'featuredCount',
        ));
    }

    public function create(): View
    {
        return view('admin.gallery.create', [
            'photo' => new GalleryPhoto(['is_active' => true, 'is_featured' => false, 'sort_order' => 0]),
            'destinations' => Destination::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $photo = GalleryPhoto::create($this->withStoredImage($validated, $request));

        return redirect()->route('admin.gallery.index')->with('success', 'গ্যালারিতে ছবি যোগ হয়েছে।');
    }

    public function edit(GalleryPhoto $photo): View
    {
        return view('admin.gallery.edit', [
            'photo' => $photo,
            'destinations' => Destination::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, GalleryPhoto $photo): RedirectResponse
    {
        $validated = $request->validate($this->rules($photo));

        $previous = $photo->image;

        $photo->update($this->withStoredImage($validated, $request));

        // Only unreferenced files are unlinked, so a replacement image that is
        // still used elsewhere is left in place.
        if ($previous !== $photo->image) {
            $this->images->delete($previous);
        }

        return redirect()->route('admin.gallery.index')->with('success', 'ছবির তথ্য হালনাগাদ হয়েছে।');
    }

    public function destroy(GalleryPhoto $photo): RedirectResponse
    {
        $image = $photo->image;

        $photo->delete();

        $this->images->delete($image);

        return redirect()->route('admin.gallery.index')->with('success', 'ছবি মুছে ফেলা হয়েছে।');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?GalleryPhoto $photo = null): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:255',
            'image' => $photo === null
                ? $this->images->rules()
                : ['nullable', 'file', 'mimes:'.ContentImageService::ALLOWED_MIMES, 'max:'.ContentImageService::MAX_BYTES],
            'alt_text' => 'nullable|string|max:255',
            'destination_id' => 'nullable|exists:destinations,id',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ];
    }

    /**
     * Persist any uploaded image and normalise the checkbox fields.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withStoredImage(array $validated, Request $request): array
    {
        if ($request->hasFile('image')) {
            $validated['image'] = $this->images->store($request->file('image'), 'gallery');
        } else {
            unset($validated['image']);
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active');

        // Blank inputs are cast here rather than stored, because an empty
        // string is not a valid integer under a strict MySQL sql_mode.
        $validated['sort_order'] = (int) ($request->input('sort_order') ?? 0);
        $validated['destination_id'] = $request->input('destination_id') ?: null;

        return $validated;
    }
}
