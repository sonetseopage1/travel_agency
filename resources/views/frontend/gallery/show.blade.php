@extends('layouts.frontend')

@section('title', ($photo->displayTitle() ?: 'ছবি').' — '.\App\Models\Setting::string('site_name'))
@section('og_title', $photo->displayTitle() ?: 'গ্যালারি')
@section('og_description', $photo->caption ?: 'আমাদের ভ্রমণের ছবি।')

@section('content')

    <section class="bg-slate-900 text-white pt-32 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('gallery.index') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-white/70 hover:text-white transition">
                ← গ্যালারিতে ফিরে যান
            </a>
            <h1 class="text-3xl sm:text-4xl font-extrabold mt-4">
                {{ $photo->displayTitle() ?: 'ছবি' }}
            </h1>
            @if ($photo->destination)
                <p class="text-white/70 mt-3">📍 {{ $photo->destination->name }}</p>
            @endif
        </div>
    </section>

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <figure class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden">
            <img src="{{ $photo->image_url }}"
                alt="{{ $photo->alt_text ?: $photo->displayTitle() }}"
                class="w-full max-h-[75vh] object-contain bg-slate-100 dark:bg-slate-950">
            @if ($photo->caption || $photo->alt_text)
                <figcaption class="px-6 py-5 text-slate-600 dark:text-slate-300 leading-8">
                    {{ $photo->caption ?: $photo->alt_text }}
                </figcaption>
            @endif
        </figure>

        <div class="mt-14">
            <h2 class="text-2xl font-extrabold">আরও ছবি</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                @foreach ($neighbours as $neighbour)
                    <a href="{{ route('gallery.show', $neighbour) }}"
                        class="group relative aspect-square rounded-2xl overflow-hidden bg-slate-200 dark:bg-slate-800">
                        <img src="{{ $neighbour->image_url }}"
                            alt="{{ $neighbour->alt_text ?: $neighbour->displayTitle() }}"
                            loading="lazy"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </a>
                @endforeach
            </div>
        </div>
    </section>

@endsection
