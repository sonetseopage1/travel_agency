<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * The public blog listing, newest first, with an optional category filter.
     */
    public function index(Request $request): View
    {
        $category = $request->input('category');

        $posts = BlogPost::query()
            ->published()
            ->when($category, fn ($query) => $query->where('category', $category))
            ->ordered()
            ->paginate(9)
            ->withQueryString();

        // Derived from published posts only, so a category never links to an
        // empty listing.
        $categories = BlogPost::query()
            ->published()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('frontend.blog.index', compact('posts', 'categories', 'category'));
    }

    /**
     * A single published post.
     *
     * The route is bound by slug. Drafts and posts whose publication date has
     * not arrived return a 404 rather than a hint that they exist.
     */
    public function show(BlogPost $post): View
    {
        abort_unless($post->isPublished(), 404);

        $post->increment('view_count');

        $related = BlogPost::query()
            ->published()
            ->whereKeyNot($post->getKey())
            ->when($post->category, fn ($query) => $query->where('category', $post->category))
            ->ordered()
            ->limit(3)
            ->get();

        return view('frontend.blog.show', compact('post', 'related'));
    }
}
