<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\ContentImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    public function __construct(private readonly ContentImageService $images) {}

    public function index(Request $request): View
    {
        $posts = BlogPost::query()
            ->when($request->input('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            })
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        $totalCount = BlogPost::count();
        $publishedCount = BlogPost::published()->count();
        $draftCount = BlogPost::where('status', 'draft')->count();
        $totalViews = BlogPost::sum('view_count');

        return view('admin.blog.index', compact('posts', 'totalCount', 'publishedCount', 'draftCount', 'totalViews'));
    }

    public function create(): View
    {
        return view('admin.blog.create', [
            'post' => new BlogPost(['status' => 'draft', 'is_featured' => false]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $post = BlogPost::create($this->withStoredImage($this->withSlug($validated, $request), $request));

        return redirect()->route('admin.blog.index')->with('success', 'ব্লগ পোস্ট তৈরি হয়েছে।');
    }

    public function edit(BlogPost $post): View
    {
        return view('admin.blog.edit', compact('post'));
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $validated = $request->validate($this->rules($post));

        $previous = $post->cover_image;

        $post->update($this->withStoredImage($this->withSlug($validated, $request, $post), $request));

        if ($previous !== $post->cover_image) {
            $this->images->delete($previous);
        }

        return redirect()->route('admin.blog.index')->with('success', 'ব্লগ পোস্ট হালনাগাদ হয়েছে।');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $image = $post->cover_image;

        $post->delete();

        $this->images->delete($image);

        return redirect()->route('admin.blog.index')->with('success', 'ব্লগ পোস্ট মুছে ফেলা হয়েছে।');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?BlogPost $post = null): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('blog_posts', 'slug')->ignore($post?->id)],
            'excerpt' => 'nullable|string|max:1000',
            'body' => 'nullable|string',
            'cover_image' => $post === null
                ? ['nullable', 'file', 'mimes:'.ContentImageService::ALLOWED_MIMES, 'max:'.ContentImageService::MAX_BYTES]
                : ['nullable', 'file', 'mimes:'.ContentImageService::ALLOWED_MIMES, 'max:'.ContentImageService::MAX_BYTES],
            'author' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'status' => ['required', Rule::in(BlogPost::STATUSES)],
            'published_at' => 'nullable|date',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
        ];
    }

    /**
     * Fill in the slug from the title when the admin left it blank.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withSlug(array $validated, Request $request, ?BlogPost $post = null): array
    {
        $slug = trim((string) $request->input('slug', ''));

        $validated['slug'] = $slug !== ''
            ? BlogPost::uniqueSlug($slug, $post?->id)
            : BlogPost::uniqueSlug((string) $validated['title'], $post?->id);

        return $validated;
    }

    /**
     * Persist any uploaded cover image and normalise the checkbox fields.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withStoredImage(array $validated, Request $request): array
    {
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $this->images->store($request->file('cover_image'), 'blog');
        } else {
            unset($validated['cover_image']);
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        if (array_key_exists('published_at', $validated)) {
            // A cleared date input submits an empty string, which is not a
            // valid timestamp under a strict MySQL sql_mode. An absent field
            // means the request never carried one, so the stored date stands.
            $validated['published_at'] = empty($validated['published_at'])
                ? null
                // A post with no date could never appear on the public blog,
                // so publishing one stamps it now.
                : $validated['published_at'];
        }

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        return $validated;
    }
}
