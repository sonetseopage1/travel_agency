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

        {{-- On a phone the filter form lives in a drawer that slides up from
             the bottom. The form itself is a single element with an id, moved
             between the drawer and the desktop panel by script, so the two
             never hold duplicate ids. --}}
        <div id="filterBar" class="lg:hidden mb-6">
            <button type="button" id="filterOpen"
                class="w-full flex items-center justify-between gap-3 px-5 py-4 rounded-2xl
                    bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800
                    shadow-sm active:scale-[0.99] transition"
                aria-expanded="false" aria-controls="filterDrawer">
                <span class="flex items-center gap-3 min-w-0">
                    <span aria-hidden="true" class="text-lg">⚙️</span>
                    <span class="font-semibold text-sm">ফিল্টার</span>
                    <span id="filterBadge"
                        class="hidden shrink-0 min-w-6 h-6 px-2 rounded-full bg-teal-700 text-white
                            text-xs font-bold grid place-items-center">0</span>
                </span>
                <span id="filterSummary" class="text-xs text-slate-500 truncate">সব ট্যুর দেখানো হচ্ছে</span>
            </button>
        </div>

        {{-- The drawer. Rounded along the top so it reads as a sheet, and
             scrollable, since the form is taller than a phone screen. --}}
        <div id="filterDrawerOverlay"
            class="fixed inset-0 z-[60] hidden bg-black/50 opacity-0 transition-opacity duration-300 lg:hidden"></div>

        <div id="filterDrawer" role="dialog" aria-modal="true" aria-labelledby="filterDrawerTitle"
            class="fixed inset-x-0 bottom-0 z-[70] lg:hidden
                bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800
                rounded-t-3xl shadow-2xl
                translate-y-full transition-transform duration-300 ease-out
                max-h-[88vh] flex flex-col">

            {{-- Drag affordance. Purely decorative. --}}
            <div class="pt-3 pb-1 flex justify-center shrink-0" aria-hidden="true">
                <span class="h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-700"></span>
            </div>

            <div class="flex items-center justify-between px-5 pb-3 shrink-0">
                <h2 id="filterDrawerTitle" class="text-lg font-extrabold">ফিল্টার করুন</h2>
                <button type="button" id="filterClose" aria-label="ফিল্টার বন্ধ করুন"
                    class="w-9 h-9 grid place-items-center rounded-full
                        bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300
                        active:scale-95 transition">
                    ✕
                </button>
            </div>

            {{-- The form is moved in here on small screens. --}}
            <div id="filterDrawerBody" class="overflow-y-auto px-5 pb-5 overscroll-contain"></div>
        </div>

        <div id="filterInline" class="hidden lg:block">
            {{-- The full desktop panel is the default, so the form looks right
                 before script runs and stays right if script never runs. On a
                 phone the script swaps in the single-column sheet classes. --}}
            <form method="GET" action="{{ route('tours.index') }}" id="tourFilterForm"
                data-desktop="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-12"
                data-mobile="block space-y-4"
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-12">
            <div>
                <label for="filter_destination" class="block text-sm font-semibold mb-2">গন্তব্য</label>
                <input id="filter_destination" name="destination" type="text" value="{{ request('destination') }}"
                    placeholder="যেমন: কক্সবাজার"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
            </div>
            <div>
                <label for="filter_category" class="block text-sm font-semibold mb-2">ক্যাটাগরি</label>
                <select id="filter_category" name="category"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
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
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
            </div>

            {{-- The travel window. Both ends are optional; an open end means
                 no bound on that side. The col-span keeps this row full width
                 once the form is back in the desktop grid; it is inert while
                 the form is a single column in the drawer. --}}
            <fieldset class="lg:col-span-3">
                <legend class="block text-sm font-semibold mb-2">ভ্রমণের তারিখ</legend>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="filter_date_from" class="block text-xs text-slate-500 mb-1.5">শুরু (যাওয়ার)</label>
                        <input id="filter_date_from" name="date_from" type="date"
                            value="{{ request('date_from') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
                    </div>
                    <div>
                        <label for="filter_date_to" class="block text-xs text-slate-500 mb-1.5">শেষ (ফেরার)</label>
                        <input id="filter_date_to" name="date_to" type="date"
                            value="{{ request('date_to') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
                    </div>
                    {{-- Full width on a phone, one of three columns on desktop. --}}
                    <div class="col-span-2 lg:col-span-1">
                        <label for="filter_month" class="block text-xs text-slate-500 mb-1.5">অথবা মাস অনুযায়ী</label>
                        <div class="flex gap-2">
                            <select id="filter_month" name="month"
                                class="flex-1 min-w-0 px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
                                <option value="">সব মাস</option>
                                @foreach ([
                                    1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
                                    5 => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
                                    9 => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর',
                                ] as $number => $name)
                                    <option value="{{ $number }}" @selected((int) request('month') === $number)>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="year" aria-label="বছর"
                                class="w-28 shrink-0 px-3 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" @selected((int) request('year', $currentYear) === $year)>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </fieldset>

            {{-- Sticky on mobile so applying a filter never requires scrolling
                 back up inside the sheet. Static again in the desktop grid,
                 where sticky would fight the page scroll. --}}
            <div class="sticky bottom-0 -mx-5 px-5 py-4 bg-white dark:bg-slate-900
                        lg:static lg:mx-0 lg:px-0 lg:col-span-3
                        flex gap-2">
                <button type="submit"
                    class="flex-1 bg-teal-700 active:bg-teal-800 text-white font-semibold py-3.5 rounded-xl text-sm transition">
                    ফিল্টার করুন
                </button>
                <a href="{{ route('tours.index') }}"
                    class="px-5 py-3.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold
                        hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    রিসেট
                </a>
            </div>
        </form>
        </div>

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
                        @if ($tour->travel_date_label ?? null)
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-3">
                                <span class="font-semibold">ভ্রমণের তারিখ:</span>
                                {{ $tour->travel_date_label }}
                            </p>
                        @endif
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

    @section('scripts')
        <script>
            (function () {
                const form = document.getElementById('tourFilterForm');
                const inline = document.getElementById('filterInline');
                const drawerBody = document.getElementById('filterDrawerBody');
                const drawer = document.getElementById('filterDrawer');
                const overlay = document.getElementById('filterDrawerOverlay');
                const openBtn = document.getElementById('filterOpen');
                const closeBtn = document.getElementById('filterClose');
                const bar = document.getElementById('filterBar');
                const badge = document.getElementById('filterBadge');
                const summary = document.getElementById('filterSummary');

                if (!form || !drawer || !overlay || !openBtn) return;

                // A year with no month chosen is not a filter on its own, so it
                // must not be counted or shown in the summary.
                const DESKTOP = '(min-width: 1024px)';
                const desktop = window.matchMedia(DESKTOP);

                // Both layouts are declared in the markup, so the form is
                // correct before this runs and stays correct if it never runs.
                const desktopClasses = form.dataset.desktop || '';
                const mobileClasses = form.dataset.mobile || 'block space-y-4';

                const isDesktop = () => desktop.matches;

                function mount() {
                    if (isDesktop()) {
                        if (form.parentElement !== inline) inline.appendChild(form);
                        form.className = desktopClasses;
                        return;
                    }

                    if (form.parentElement !== drawerBody) drawerBody.appendChild(form);
                    form.className = mobileClasses;
                }

                // Counts what the customer has actually chosen, so the trigger
                // can say "3 filters applied" instead of hiding the state.
                function activeCount() {
                    const data = new FormData(form);
                    let count = 0;

                    ['destination', 'category', 'price_max', 'date_from', 'date_to', 'month'].forEach((name) => {
                        if (String(data.get(name) || '').trim() !== '') count++;
                    });

                    // The year only counts alongside a month.
                    if (String(data.get('month') || '').trim() !== ''
                        && String(data.get('year') || '').trim() !== ''
                        && String(data.get('year')) !== String({{ $currentYear }})) {
                        count++;
                    }

                    return count;
                }

                // The form has no name/id property shortcuts, so fields are
                // looked up explicitly.
                const field = (name) => form.querySelector('[name="' + name + '"]');

                function labelFor(name) {
                    const el = field(name);
                    if (!el) return '';
                    return (el.selectedOptions?.[0]?.textContent || el.value || '').trim();
                }

                function shortDate(value) {
                    if (!value) return '';
                    const parts = value.split('-');
                    if (parts.length !== 3) return value;
                    return parts[2] + '/' + parts[1] + '/' + parts[0];
                }

                function updateSummary() {
                    const count = activeCount();
                    const bits = [];

                    if (field('destination').value.trim()) bits.push(field('destination').value.trim());
                    if (field('category').value) bits.push(labelFor('category'));
                    if (field('price_max').value) bits.push('≤ ৳' + field('price_max').value);
                    if (field('date_from').value) bits.push(shortDate(field('date_from').value) + ' থেকে');
                    if (field('date_to').value) bits.push(shortDate(field('date_to').value) + ' পর্যন্ত');

                    if (field('month').value) {
                        bits.push(labelFor('month') + (field('year').value ? ' ' + field('year').value : ''));
                    }

                    badge.textContent = String(count);
                    badge.classList.toggle('hidden', count === 0);
                    summary.textContent = bits.length ? bits.join(' · ') : 'সব ট্যুর দেখানো হচ্ছে';
                }

                let lastFocused = null;

                function openDrawer() {
                    if (isDesktop()) return;

                    lastFocused = document.activeElement;
                    overlay.classList.remove('hidden');
                    // Force a reflow so the opacity transition actually runs
                    // after the element becomes visible.
                    void overlay.offsetWidth;

                    overlay.classList.remove('opacity-0');
                    drawer.classList.remove('translate-y-full');
                    document.body.classList.add('overflow-hidden');
                    openBtn.setAttribute('aria-expanded', 'true');

                    const first = drawerBody.querySelector('input, select, button');
                    if (first) first.focus();
                }

                function closeDrawer() {
                    overlay.classList.add('opacity-0');
                    drawer.classList.add('translate-y-full');
                    document.body.classList.remove('overflow-hidden');
                    openBtn.setAttribute('aria-expanded', 'false');

                    const hide = () => {
                        overlay.classList.add('hidden');
                    };
                    // Wait out the fade before removing it from the layout.
                    window.setTimeout(hide, 300);

                    if (lastFocused && typeof lastFocused.focus === 'function') {
                        lastFocused.focus();
                    }
                }

                openBtn.addEventListener('click', openDrawer);
                closeBtn.addEventListener('click', closeDrawer);
                overlay.addEventListener('click', closeDrawer);

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && openBtn.getAttribute('aria-expanded') === 'true') {
                        closeDrawer();
                    }
                });

                // Rotating a phone or resizing across the breakpoint must not
                // leave the form stranded in the hidden panel.
                desktop.addEventListener('change', () => {
                    if (isDesktop()) closeDrawer();
                    mount();
                });

                form.addEventListener('input', updateSummary);
                form.addEventListener('change', updateSummary);

                // Submitting from the drawer should not leave the page scrolled
                // past the filter panel on the way back.
                form.addEventListener('submit', () => {
                    document.body.classList.remove('overflow-hidden');
                });

                mount();
                updateSummary();
            })();
        </script>
    @endsection

@endsection
