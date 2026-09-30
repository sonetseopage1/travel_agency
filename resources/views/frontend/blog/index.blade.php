@extends('layouts.frontend')

@section('title', 'ব্লগ — '.\App\Models\Setting::string('site_name'))
@section('og_title', 'ব্লগ — '.\App\Models\Setting::string('site_name'))
@section('og_description', 'ট্রাভেল গাইড, গন্তব্য পরিচিতি ও ভ্রমণের টিপস।')

@section('content')

    <section class="bg-slate-900 text-white pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold">ট্রাভেল ব্লগ</h1>
            <p class="text-white/70 mt-3">গন্তব্য পরিচিতি, ভ্রমণের গাইড ও কাজে লাগে এমন টিপস</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap items-center gap-3 mb-10">
                <a href="{{ route('blog.index') }}"
                    class="px-4 py-2 rounded-full text-sm font-semibold border transition
                           {{ $category === null
                              ? 'bg-teal-700 border-teal-700 text-white'
                              : 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    সব পোস্ট
                </a>
                @foreach ($categories as $name)
                    <a href="{{ route('blog.index', ['category' => $name]) }}"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition
                               {{ $category === $name
                                  ? 'bg-teal-700 border-teal-700 text-white'
                                  : 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        {{ $name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($posts as $post)
                <article
                    class="flex flex-col bg-white dark:bg-slate-900 rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl transition">
                    <a href="{{ route('blog.show', $post) }}" class="block h-52 overflow-hidden bg-slate-100 dark:bg-slate-800">
                        <img src="{{ $post->cover_image_url }}"
                            alt="{{ $post->meta_title ?: $post->title }}"
                            loading="lazy"
                            class="w-full h-full object-cover hover:scale-105 transition duration-500">
                    </a>
                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            @if ($post->category)
                                <a href="{{ route('blog.index', ['category' => $post->category]) }}"
                                    class="px-3 py-1 rounded-full bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400 font-semibold">
                                    {{ $post->category }}
                                </a>
                            @endif
                            @if ($post->published_at)
                                <span class="text-slate-500 dark:text-slate-400">
                                    {{ $post->published_at->format('j M Y') }}
                                </span>
                            @endif
                        </div>
                        <h2 class="font-bold text-lg mt-3 leading-7">
                            <a href="{{ route('blog.show', $post) }}" class="hover:text-teal-700 transition">
                                {{ $post->title }}
                            </a>
                        </h2>
                        @if ($post->summary() !== '')
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-3 leading-7 flex-1">
                                {{ $post->summary() }}
                            </p>
                        @endif
                        <div class="flex items-center justify-between mt-5 text-xs text-slate-500 dark:text-slate-400">
                            @if ($post->author)
                                <span>✍️ {{ $post->author }}</span>
                            @endif
                            <span>👁 {{ number_format($post->view_count) }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-20 text-center">
                    <p class="text-5xl mb-4">📝</p>
                    <p class="text-slate-500 dark:text-slate-400">এখনো কোনো ব্লগ পোস্ট প্রকাশ করা হয়নি।</p>
                    @if ($categories->isNotEmpty())
                        <a href="{{ route('blog.index') }}"
                            class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
                            সব পোস্ট দেখুন
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($posts->hasPages())
            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </section>

@endsection
