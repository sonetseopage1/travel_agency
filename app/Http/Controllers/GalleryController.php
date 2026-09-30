<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    /**
     * The public photo wall.
     *
     * Photos can be narrowed to one destination, which is the only filter
     * offered: it maps to how an admin actually groups a set of photos.
     */
    public function index(Request $request): View
    {
        $photos = GalleryPhoto::query()
            ->active()
            ->with('destination')
            ->when($request->filled('destination'), function ($query) use ($request) {
                $query->whereHas('destination', fn ($q) => $q->where('slug', $request->input('destination')));
            })
            ->ordered()
            ->paginate(24)
            ->withQueryString();

        $destinations = Destination::where('is_active', true)->orderBy('name')->get();

        return view('frontend.gallery.index', compact('photos', 'destinations'));
    }

    /**
     * A single photo, shown large with its neighbours.
     */
    public function show(GalleryPhoto $photo): View
    {
        abort_unless($photo->is_active, 404);

        $photo->load('destination');

        $neighbours = GalleryPhoto::query()
            ->active()
            ->whereKeyNot($photo->getKey())
            ->ordered()
            ->limit(8)
            ->get();

        return view('frontend.gallery.show', compact('photo', 'neighbours'));
    }
}
