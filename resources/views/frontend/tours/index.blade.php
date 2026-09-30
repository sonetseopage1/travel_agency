@extends('layouts.frontend')

@section('title', 'ট্যুর প্যাকেজ — '.\App\Models\Setting::string('site_name'))

@section('content')

    <section class="bg-slate-900 text-white pt-32 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold">সব ট্যুর প্যাকেজ</h1>
            <p class="text-white/70 mt-3">আপনার পছন্দের গন্তব্য বেছে নিন এবং আজই বুকিং করুন</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <form method="GET" action="{{ route('tours.index') }}"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-12">
            <div>
                <label for="filter_destination" class="block text-sm font-semibold mb-2">গন্তব্য</label>
                <input id="filter_destination" name="destination" type="text" value="{{ request('destination') }}"
                    placeholder="যেমন: কক্সবাজার"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
            </div>
            <div>
                <label for="filter_category" class="block text-sm font-semibold mb-2">ক্যাটাগরি</label>
                <select id="filter_category" name="category"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
                    <option value="">সব ক্যাটাগরি</option>
                    @foreach (\App\Models\Tour::CATEGORIES as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_price" class="block text-sm font-semibold mb-2">সর্বোচ্চ দাম (৳)</label>
                <input id="filter_price" name="price_max" type="number" min="0" step="100"
                    value="{{ request('price_max') }}" placeholder="যেমন: 30000"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 bg-teal-700 hover:bg-teal-800 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                    ফিল্টার করুন
                </button>
                <a href="{{ route('tours.index') }}"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    রিসেট
                </a>
            </div>
        </form>

        @if ($tours instanceof \Illuminate\Contracts\Pagination\Paginator)
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                মোট {{ $tours->total() }}টি ট্যুর পাওয়া গেছে
            </p>
        @else
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">মোট {{ $tours->count() }}টি ট্যুর পাওয়া গেছে</p>
        @endif

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ($tours as $tour)
                @php
                    $booked = $tour->current_booked ?? 0;
                    $total = $tour->total_seats ?? 1;
                    $left = max($total - $booked, 0);
                    $percent = $total > 0 ? min(round($booked / $total * 100), 100) : 0;
                @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-2xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl transition">
                    <a href="{{ route('tours.show', $tour->slug ?? 'coxs-bazar') }}" class="block relative">
                        <img src="{{ $tour->image_url }}" alt="{{ $tour->title ?? '' }}"
                            class="w-full h-56 object-cover">
                        <div class="absolute top-4 left-4 flex gap-2">
                            @if ($tour->is_international ?? false)
                                <span class="px-3 py-1 rounded-full bg-slate-900/80 text-white text-xs font-semibold">
                                    International
                                </span>
                            @endif
                            <span class="px-3 py-1 rounded-full bg-teal-700 text-white text-xs font-semibold">
                                {{ $tour->transport_icon ?? '🚌' }} {{ $tour->transport_type ?? 'Bus' }}
                            </span>
                        </div>
                    </a>
                    <div class="p-6">
                        <p class="text-sm text-teal-700 font-semibold">📍 {{ $tour->location ?? '' }}</p>
                        <h3 class="font-bold text-lg mt-2 leading-7">
                            <a href="{{ route('tours.show', $tour->slug ?? 'coxs-bazar') }}"
                                class="hover:text-teal-700">{{ $tour->title ?? '' }}</a>
                        </h3>
                        <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400 mt-3">
                            <span>🗓️ {{ $tour->duration_days ?? 0 }} দিন</span>
                            <span>💺 বাকি {{ $left }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800 mt-4 overflow-hidden">
                            <div class="h-full bg-teal-600" style="width: {{ $percent }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-5">
                            <div>
                                <span class="text-2xl font-extrabold text-teal-700">
                                    ৳{{ number_format($tour->price_per_person ?? 0) }}
                                </span>
                                <span class="text-sm text-slate-500">/জন</span>
                            </div>
                            <a href="{{ route('bookings.create', ['tour_id' => $tour->id ?? 1]) }}"
                                class="px-4 py-2 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
                                বুকিং করুন
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($tours instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-12">
                {{ $tours->links() }}
            </div>
        @endif
    </section>

@endsection
