@extends('layouts.frontend')

@section('title', ($post->meta_title ?: $post->title).' — '.\App\Models\Setting::string('site_name'))
@section('og_title', $post->meta_title ?: $post->title)
@section('og_description', $post->meta_description ?: $post->summary())
@section('og_image', $post->cover_image_url)

@section('content')

    <article>
        <header class="bg-slate-900 text-white pt-32 pb-16">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">
                <a href="{{ route('blog.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-white/70 hover:text-white transition">
                    ← ব্লগে ফিরে যান
                </a>
                <div class="flex flex-wrap items-center gap-3 mt-5 text-xs">
                    @if ($post->category)
                        <a href="{{ route('blog.index', ['category' => $post->category]) }}"
                            class="px-3 py-1 rounded-full bg-teal-500/20 text-teal-200 font-semibold">
                            {{ $post->category }}
                        </a>
                    @endif
                    @if ($post->published_at)
                        <span class="text-white/70">{{ $post->published_at->format('j F Y') }}</span>
                    @endif
                    @if ($post->author)
                        <span class="text-white/70">✍️ {{ $post->author }}</span>
                    @endif
                    <span class="text-white/70">👁 {{ number_format($post->view_count) }}</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold mt-4 leading-tight">
                    {{ $post->title }}
                </h1>
            </div>
        </header>

        @if ($post->cover_image)
            <div class="max-w-4xl mx-auto px-4 sm:px-6 -mt-8">
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}"
                    class="w-full h-72 sm:h-96 object-cover rounded-3xl shadow-xl">
            </div>
        @endif

        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-14">
            @if ($post->excerpt)
                <p class="text-lg text-slate-600 dark:text-slate-300 leading-9 border-s-4 border-teal-600 ps-5 mb-10">
                    {{ $post->excerpt }}
                </p>
            @endif

            <div class="text-slate-700 dark:text-slate-300 leading-9 space-y-5">
                {{-- The body is admin-authored HTML from the admin panel, so it
                     is rendered rather than escaped. Block-level tags are
                 wrapped in <p> so the surrounding spacing still applies. --}}
                @foreach (preg_split('/\n\s*\n/', trim((string) $post->body)) as $paragraph)
                    @if (trim($paragraph) !== '')
                        <p>{!! $paragraph !!}</p>
                    @endif
                @endforeach
            </div>

            @if ($related->isNotEmpty())
                <div class="mt-16 pt-10 border-t border-slate-200 dark:border-slate-800">
                    <h2 class="text-2xl font-extrabold">আরও পড়ুন</h2>
                    <div class="grid sm:grid-cols-3 gap-6 mt-6">
                        @foreach ($related as $item)
                            <a href="{{ route('blog.show', $item) }}"
                                class="group block bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden hover:shadow-xl transition">
                                <img src="{{ $item->cover_image_url }}" alt="{{ $item->title }}" loading="lazy"
                                    class="w-full h-32 object-cover group-hover:scale-105 transition duration-500">
                                <div class="p-4">
                                    <p class="font-bold text-sm leading-6 group-hover:text-teal-700 transition">
                                        {{ $item->title }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </article>

@endsection
