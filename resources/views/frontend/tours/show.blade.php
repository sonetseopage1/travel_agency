@extends('layouts.frontend')

@section('title', ($tour->title ?? 'ট্যুর') . ' — ভ্রমণবিলাস')

@section('body-class', 'pb-24 lg:pb-0')

@section('content')

    @php
        $booked = $tour->current_booked ?? 0;
        $total = $tour->total_seats ?? 1;
        $left = max($total - $booked, 0);
        $percent = $total > 0 ? min(round($booked / $total * 100), 100) : 0;
    @endphp

    <section class="relative h-[70vh] min-h-[420px] flex items-end overflow-hidden">
        <img src="{{ $tour->image ?? '' }}" alt="{{ $tour->title ?? '' }}" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pb-14">
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold">
                    📍 {{ $tour->location ?? '' }}
                </span>
                <span class="px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold">
                    {{ $tour->transport_icon ?? '🚌' }} {{ $tour->transport_type ?? 'Bus' }}
                </span>
                @if ($tour->is_international ?? false)
                    <span class="px-3 py-1 rounded-full bg-amber-500 text-white text-xs font-semibold">International</span>
                @endif
            </div>
            <h1 class="text-white text-3xl sm:text-5xl font-extrabold max-w-3xl leading-tight">
                {{ $tour->title ?? '' }}
            </h1>
            <div class="flex flex-wrap items-center gap-6 text-white/85 mt-5">
                <span class="text-amber-400 font-bold">★ {{ $tour->rating ?? '4.8' }} ({{ $tour->review_count ?? 0 }}টি রিভিউ)</span>
                <span>🗓️ {{ $tour->duration_days ?? 0 }} দিন / {{ $tour->duration_nights ?? max(($tour->duration_days ?? 1) - 1, 0) }} রাত</span>
                <span>💺 {{ $left }}টি সিট বাকি</span>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-14">

                <div>
                    <h2 class="text-2xl font-extrabold">ট্যুর সম্পর্কে</h2>
                    <p class="text-slate-600 dark:text-slate-300 leading-8 mt-4">
                        {{ $tour->description ?? '' }}
                    </p>
                </div>

                @if (! empty($tour->itinerary_days))
                    <div>
                        <h2 class="text-2xl font-extrabold">ভ্রমণসূচি</h2>
                        <div class="mt-6 space-y-8">
                            @foreach ($tour->itinerary_days as $day)
                                <div class="relative pl-14">
                                    @unless ($loop->first)
                                        <div class="timeline-line"></div>
                                    @endunless
                                    <div
                                        class="absolute left-0 top-0 w-10 h-10 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold text-sm">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <h3 class="font-bold text-lg">{{ $day['day_title'] }}</h3>
                                        @if ($loop->first)
                                            <span class="px-3 py-1 rounded-full bg-teal-50 text-teal-700 text-xs font-semibold">
                                                Travel Day
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-4 space-y-4">
                                        @foreach ($day['activities'] as $activity)
                                            <div class="flex gap-4">
                                                <div class="text-xl">{{ $activity['icon'] }}</div>
                                                <div>
                                                    <div class="font-semibold">
                                                        @if ($activity['time'] !== '')
                                                            <span class="text-teal-700">{{ $activity['time'] }}</span> —
                                                        @endif
                                                        {{ $activity['title'] }}
                                                    </div>
                                                    @if ($activity['location'] !== '')
                                                        <p class="text-xs text-slate-500 mt-1">
                                                            📍 {{ $activity['location'] }}
                                                        </p>
                                                    @endif
                                                    @if ($activity['description'] !== '')
                                                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-7">
                                                            {{ $activity['description'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div>
                    <h2 class="text-2xl font-extrabold">প্যাকেজে যা যা আছে</h2>
                    <div class="grid sm:grid-cols-2 gap-4 mt-6">
                        @foreach (['includes' => ['✅', 'অন্তর্ভুক্ত'], 'excludes' => ['❌', 'অন্তর্ভুক্ত নয়']] as $key => [$icon, $label])
                            <div
                                class="border border-slate-200 dark:border-slate-800 rounded-2xl p-6 bg-white dark:bg-slate-900">
                                <h3 class="font-bold mb-4">{{ $icon }} {{ $label }}</h3>
                                <ul class="space-y-2.5 text-sm text-slate-600 dark:text-slate-300">
                                    @foreach ($tour->{$key} ?? [] as $item)
                                        <li class="flex gap-2">
                                            <span>{{ $icon }}</span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (! empty($tour->important_info))
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-7">
                        <h2 class="text-xl font-extrabold">গুরুত্বপূর্ণ তথ্য</h2>
                        <ul class="mt-4 space-y-2.5 text-sm text-slate-700 dark:text-slate-200">
                            @foreach ($tour->important_info as $info)
                                <li class="flex gap-2">
                                    <span>•</span>
                                    <span>{{ $info }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($tour->gallery_images))
                    <div>
                        <h2 class="text-2xl font-extrabold">গ্যালারি</h2>
                        <div class="grid grid-cols-2 gap-4 mt-6">
                            @foreach ($tour->gallery_images as $image)
                                <img src="{{ $image }}" alt="Gallery" class="rounded-2xl w-full h-52 object-cover">
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! empty($tour->faq_items))
                    <div>
                        <h2 class="text-2xl font-extrabold">সাধারণ জিজ্ঞাসা</h2>
                        <div class="mt-6 space-y-3">
                            @foreach ($tour->faq_items as $faq)
                                <details
                                    class="group border border-slate-200 dark:border-slate-800 rounded-2xl p-5 bg-white dark:bg-slate-900">
                                    <summary class="font-semibold cursor-pointer flex items-center justify-between">
                                        {{ $faq['question'] }}
                                        <span class="transition group-open:rotate-45">＋</span>
                                    </summary>
                                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-3 leading-7">
                                        {{ $faq['answer'] }}
                                    </p>
                                </details>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($reviews->isNotEmpty())
                    <div>
                        <h2 class="text-2xl font-extrabold">ভ্রমণীদের রিভিউ</h2>
                        <div class="grid sm:grid-cols-2 gap-5 mt-6">
                            @foreach ($reviews as $review)
                                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-6 bg-white dark:bg-slate-900">
                                    <div class="text-amber-500">★★★★★</div>
                                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-7 mt-3">
                                        "{{ $review->comment ?? '' }}"
                                    </p>
                                    <div class="font-bold mt-4">{{ $review->name ?? '' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="lg:sticky lg:top-28 h-fit">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-7 shadow-lg">
                    <p class="text-sm text-slate-500">প্রতি জনের দাম</p>
                    <div class="text-4xl font-extrabold text-teal-700 mt-1">
                        ৳{{ number_format($tour->price_per_person ?? 0) }}
                    </div>
                    @if (! empty($tour->departure_date))
                        <p class="text-sm text-slate-500 mt-2">
                            ভ্রমণের তারিখ:
                            <span class="font-semibold text-slate-700 dark:text-slate-200">
                                {{ \Illuminate\Support\Carbon::parse($tour->departure_date)->format('d M Y') }}
                            </span>
                        </p>
                    @endif

                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-slate-500">বুক হয়েছে</span>
                            <span class="font-semibold">{{ $booked }} / {{ $total }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full bg-teal-600" style="width: {{ $percent }}%"></div>
                        </div>
                        <p class="text-xs text-amber-600 font-semibold mt-2">এখনই বুক করুন — মাত্র {{ $left }}টি সিট বাকি</p>
                    </div>

                    <a href="{{ route('bookings.create', ['tour_id' => $tour->id ?? 1]) }}"
                        class="w-full mt-6 block text-center bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3.5 rounded-xl transition">
                        এখনই বুকিং করুন
                    </a>
                    <a href="{{ route('tours.index') }}"
                        class="w-full mt-3 block text-center border border-slate-300 dark:border-slate-700 font-semibold py-3.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        সব ট্যুর দেখুন
                    </a>
                </div>
            </aside>
        </div>
    </section>

    @if ($relatedTours->isNotEmpty())
        <section class="bg-white dark:bg-slate-900 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-extrabold">সম্পর্কিত ট্যুর</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 mt-8">
                    @foreach ($relatedTours as $related)
                        <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                            <a href="{{ route('tours.show', $related->slug ?? '') }}">
                                <img src="{{ $related->image ?? '' }}" alt="{{ $related->title ?? '' }}"
                                    class="w-full h-48 object-cover">
                            </a>
                            <div class="p-5">
                                <p class="text-sm text-teal-700 font-semibold">📍 {{ $related->location ?? '' }}</p>
                                <h3 class="font-bold mt-2">{{ $related->title ?? '' }}</h3>
                                <p class="text-teal-700 font-extrabold mt-3">
                                    ৳{{ number_format($related->price_per_person ?? 0) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection

@section('floating-bar')
    {{-- Mobile-first floating booking bar. Hidden on desktop, where the sticky
         sidebar card already carries the CTA. --}}
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40">
        <div
            class="bg-white/95 dark:bg-slate-950/95 backdrop-blur-lg border-t border-slate-200 dark:border-slate-800 shadow-[0_-4px_20px_rgba(15,23,42,0.08)]">
            <div class="px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <div class="text-lg font-extrabold text-teal-700 leading-tight">
                        ৳{{ number_format($tour->price_per_person ?? 0) }}
                        <span class="text-xs font-medium text-slate-500">/জন</span>
                    </div>
                    <div class="text-xs font-semibold text-amber-600 truncate">
                        মাত্র {{ $left }}টি সিট বাকি
                    </div>
                </div>
                <a href="{{ route('bookings.create', ['tour_id' => $tour->id ?? 1]) }}"
                    class="shrink-0 px-7 py-3.5 rounded-xl bg-teal-700 active:bg-teal-800 text-white text-sm font-bold shadow-lg shadow-teal-700/20 transition">
                    এখনই বুকিং করুন
                </a>
            </div>
        </div>
    </div>
@endsection
