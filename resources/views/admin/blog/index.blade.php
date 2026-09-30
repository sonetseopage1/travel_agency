@extends('layouts.admin')

@section('title', 'Blog')
@section('breadcrumb', 'Content')
@section('page-title', 'Blog Management')

@section('content')

@if (session('success'))
    <div class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Total Posts</p>
        <h3 class="text-2xl font-bold mt-2">{{ $totalCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Published</p>
        <h3 class="text-2xl font-bold mt-2 text-emerald-600">{{ $publishedCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Drafts</p>
        <h3 class="text-2xl font-bold mt-2 text-amber-600">{{ $draftCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Total Views</p>
        <h3 class="text-2xl font-bold mt-2 text-teal-600">{{ number_format($totalViews) }}</h3>
    </div>
</div>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">All Blog Posts</h2>
        <p class="text-sm text-slate-500 mt-1">ট্রাভেল গাইড ও গন্তব্য পরিচিতির পোস্ট তৈরি ও পরিচালনা করুন।</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('blog.index') }}" target="_blank" rel="noopener"
            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
            সাইটে দেখুন ↗
        </a>
        <a href="{{ route('admin.blog.create') }}"
            class="px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
            + নতুন পোস্ট
        </a>
    </div>
</div>

<form method="GET" action="{{ route('admin.blog.index') }}"
    class="flex flex-col sm:flex-row gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Title or excerpt..."
        class="input w-full sm:w-72">
    <select name="status" class="input w-full sm:w-48">
        <option value="">সব স্ট্যাটাস</option>
        @foreach (\App\Models\BlogPost::STATUSES as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>
                {{ ucfirst($status) }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 text-white text-sm font-semibold">
        ফিল্টার
    </button>
    @if (request('search') || request('status'))
        <a href="{{ route('admin.blog.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold">
            রিসেট
        </a>
    @endif
</form>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr class="text-left text-slate-500">
                    <th class="px-5 py-4 font-medium">Title</th>
                    <th class="px-5 py-4 font-medium">Category</th>
                    <th class="px-5 py-4 font-medium">Published</th>
                    <th class="px-5 py-4 font-medium">Views</th>
                    <th class="px-5 py-4 font-medium">Status</th>
                    <th class="px-5 py-4 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($posts as $post)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" loading="lazy"
                                    class="w-12 h-12 rounded-xl object-cover bg-slate-100 dark:bg-slate-800 shrink-0">
                                <div class="min-w-0">
                                    <p class="font-bold truncate max-w-xs">{{ $post->title }}</p>
                                    <p class="text-xs text-slate-500 truncate max-w-xs">/{{ $post->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ $post->category ?? '—' }}</td>
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300">
                            {{ $post->published_at?->format('j M Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-4">{{ number_format($post->view_count) }}</td>
                        <td class="px-5 py-4">
                            @if ($post->is_featured)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
                                    ফিচার্ড
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $post->isPublished()
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                                    : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">
                                    {{ $post->isPublished() ? 'Published' : 'Draft' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex gap-1">
                                @if ($post->isPublished())
                                    <a href="{{ route('blog.show', $post) }}" target="_blank" rel="noopener"
                                        class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition">
                                        View
                                    </a>
                                @endif
                                <a href="{{ route('admin.blog.edit', $post) }}"
                                    class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold hover:bg-blue-100 transition">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.blog.destroy', $post) }}"
                                    onsubmit="return confirm('এই পোস্টটি মুছে ফেলবেন?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                            কোনো ব্লগ পোস্ট নেই।
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
        @forelse ($posts as $post)
            <div class="p-5">
                <div class="flex items-start gap-3 mb-3">
                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" loading="lazy"
                        class="w-12 h-12 rounded-xl object-cover bg-slate-100 dark:bg-slate-800 shrink-0">
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-sm">{{ $post->title }}</p>
                        <p class="text-xs text-slate-500">/{{ $post->slug }}</p>
                    </div>
                    <span class="px-2.5 py-0.5 h-fit rounded-full text-xs font-semibold {{ $post->isPublished()
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-amber-100 text-amber-700' }}">
                        {{ $post->isPublished() ? 'Published' : 'Draft' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-slate-500">Category:</span> {{ $post->category ?? '—' }}</div>
                    <div><span class="text-slate-500">Views:</span> {{ number_format($post->view_count) }}</div>
                </div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.blog.edit', $post) }}"
                        class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold">Edit</a>
                    <form method="POST" action="{{ route('admin.blog.destroy', $post) }}"
                        onsubmit="return confirm('এই পোস্টটি মুছে ফেলবেন?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-slate-500">কোনো ব্লগ পোস্ট নেই।</div>
        @endforelse
    </div>
</div>

@if ($posts->hasPages())
    <div class="mt-6">{{ $posts->links() }}</div>
@endif

@endsection
