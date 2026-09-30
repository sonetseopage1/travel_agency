@extends('layouts.frontend')

@section('title', 'গ্যালারি — '.\App\Models\Setting::string('site_name'))
@section('og_title', 'গ্যালারি — '.\App\Models\Setting::string('site_name'))
@section('og_description', 'আমাদের ভ্রমণের ছবিগুলো দেখুন — সাজেক, কক্সবাজার, সুন্দরবন ও আরও অনেক গন্তব্য।')

@section('content')

    <section class="bg-slate-900 text-white pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold">ছবির গ্যালারি</h1>
            <p class="text-white/70 mt-3">আমাদের ভ্রমণের মুহূর্তগুলো এক নজরে দেখুন</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if ($destinations->isNotEmpty())
            <div class="flex flex-wrap items-center gap-3 mb-10">
                <a href="{{ route('gallery.index') }}"
                    class="px-4 py-2 rounded-full text-sm font-semibold border transition
                           {{ request('destination')
                              ? 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                              : 'bg-teal-700 border-teal-700 text-white' }}">
                    সব ছবি
                </a>
                @foreach ($destinations as $destination)
                    <a href="{{ route('gallery.index', ['destination' => $destination->slug]) }}"
                        class="px-4 py-2 rounded-full text-sm font-semibold border transition
                               {{ request('destination') === $destination->slug
                                  ? 'bg-teal-700 border-teal-700 text-white'
                                  : 'border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                        {{ $destination->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
            মোট {{ $photos->total() }}টি ছবি
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse ($photos as $photo)
                <a href="{{ route('gallery.show', $photo) }}"
                    class="group relative aspect-square rounded-2xl overflow-hidden bg-slate-200 dark:bg-slate-800">
                    <img src="{{ $photo->image_url }}"
                        alt="{{ $photo->alt_text ?: $photo->displayTitle() }}"
                        loading="lazy"
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @if ($photo->displayTitle() !== '')
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>
                        <div class="absolute bottom-0 p-4 text-white">
                            <p class="font-bold text-sm leading-6">{{ $photo->displayTitle() }}</p>
                            @if ($photo->destination)
                                <p class="text-xs text-white/75 mt-0.5">📍 {{ $photo->destination->name }}</p>
                            @endif
                        </div>
                    @endif
                </a>
            @empty
                <div class="col-span-full py-20 text-center">
                    <p class="text-5xl mb-4">🖼️</p>
                    <p class="text-slate-500 dark:text-slate-400">এখনো কোনো ছবি যোগ করা হয়নি।</p>
                    @if ($destinations->isNotEmpty())
                        <a href="{{ route('gallery.index') }}"
                            class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
                            সব ছবি দেখুন
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($photos->hasPages())
            <div class="mt-12">
                {{ $photos->links() }}
            </div>
        @endif
    </section>

@endsection
