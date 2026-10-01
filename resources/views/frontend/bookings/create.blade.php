@extends('layouts.frontend')

@section('title', 'বুকিং করুন — '.($tour->title ?? \App\Models\Setting::string('site_name')))

@section('content')

    @php
        $maxGuests = max((int) ($tour->available_slots ?? $tour->total_seats ?? 30), 1);
        $childRows = $childAges ?: [];
        $selectedType = $selectedTier?->type ?? 'single';
    @endphp

    <section class="bg-slate-900 text-white pt-32 pb-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold">বুকিং ফর্ম</h1>
            <p class="text-white/70 mt-2">যাত্রীর সংখ্যা দিন, শিশু থাকলে বয়স লিখুন</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">

                    @if (session('error'))
                        <div
                            class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div
                            class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3">
                            <ul class="list-disc ps-5 space-y-1">
                                @foreach ($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bookings.store') }}"
                        id="bookingForm" class="space-y-6"
                        data-tour-id="{{ $tour->id ?? 1 }}"
                        data-validate-url="{{ route('bookings.validate-promo') }}"
                        data-pricing="{{ json_encode($pricingPayload) }}">

                        @csrf
                        <input type="hidden" name="tour_id" value="{{ $tour->id ?? 1 }}">

                        <div>
                            <label for="customer_name" class="block text-sm font-semibold mb-2">পুরো নাম *</label>
                            <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}"
                                required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                            @error('customer_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="customer_email" class="block text-sm font-semibold mb-2">ইমেইল *</label>
                                <input id="customer_email" name="customer_email" type="email"
                                    value="{{ old('customer_email') }}" required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                                @error('customer_email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="customer_phone" class="block text-sm font-semibold mb-2">মোবাইল নম্বর *</label>
                                <input id="customer_phone" name="customer_phone" type="text"
                                    value="{{ old('customer_phone') }}" required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                                @error('customer_phone')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Step 1: the number of adults, exactly as before. --}}
                        <div>
                            <label for="guest_count" class="block text-sm font-semibold mb-2">
                                প্রাপ্তবয়স্ক যাত্রীর সংখ্যা *
                            </label>
                            <div class="flex items-center gap-3">
                                <button type="button" id="guestMinus" aria-label="একজন কমান"
                                    class="w-12 h-12 shrink-0 rounded-xl border border-slate-300 dark:border-slate-700 text-xl font-bold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    −
                                </button>
                                <input id="guest_count" name="guest_count" type="number" min="1" max="{{ $maxGuests }}"
                                    value="{{ $adults }}" required
                                    class="flex-1 text-center px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 font-bold text-lg">
                                <button type="button" id="guestPlus" aria-label="একজন বাড়ান"
                                    class="w-12 h-12 shrink-0 rounded-xl border border-slate-300 dark:border-slate-700 text-xl font-bold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    +
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2" id="adultLimitHint"></p>
                            @error('guest_count')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Step 2: a couple can need a separate cabin, which the
                                 couple rate covers. --}}
                        <label id="coupleRow"
                            class="flex items-start gap-3 p-4 rounded-xl border border-slate-300 dark:border-slate-700 cursor-pointer hover:border-teal-500 transition">
                            <input type="checkbox" name="is_couple" value="1" id="is_couple"
                                @checked(old('is_couple', $isCouple))
                                class="mt-1 accent-teal-700">
                            <span>
                                <span class="block font-semibold text-sm">আমরা দম্পতি</span>
                                <span class="block text-xs text-slate-500 mt-1">
                                    দম্পতির জন্য আলাদা হার ও অতিরিক্ত কেবিনের সুবিধা
                                </span>
                                <span class="block text-sm font-bold text-teal-700 mt-1" id="coupleRateNote"></span>
                            </span>
                        </label>

                        {{-- Step 3: children, only when the party has any. --}}
                        <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="has_children" value="1" id="has_children"
                                    @checked(old('has_children', count($childRows) > 0))
                                    class="accent-teal-700 w-5 h-5">
                                <span class="font-semibold text-sm">সাথে শিশু আছে</span>
                            </label>

                            <div id="childSection" class="mt-5 {{ count($childRows) > 0 ? '' : 'hidden' }}">
                                <p class="text-xs text-slate-500 mb-4" id="childPolicyNote"></p>

                                <div class="flex items-center gap-3 mb-4">
                                    <button type="button" id="childMinus"
                                        class="w-10 h-10 shrink-0 rounded-lg border border-slate-300 dark:border-slate-700 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 transition">
                                        −
                                    </button>
                                    <span class="font-semibold text-sm">
                                        শিশুর সংখ্যা: <span id="childCount">0</span>
                                    </span>
                                    <button type="button" id="childPlus"
                                        class="w-10 h-10 shrink-0 rounded-lg border border-slate-300 dark:border-slate-700 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 transition">
                                        +
                                    </button>
                                </div>

                                <div id="childRows" class="space-y-3">
                                    @foreach ($childRows as $index => $age)
                                        <div class="child-row grid grid-cols-12 gap-3 items-end" data-index="{{ $index }}">
                                            <div class="col-span-6">
                                                <label class="block text-xs font-semibold mb-1">
                                                    শিশু {{ $index + 1 }} এর বয়স
                                                </label>
                                                <input type="number" name="child_ages[{{ $index }}]" value="{{ $age }}"
                                                    min="0" max="18" placeholder="বয়স"
                                                    class="child-age w-full px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">
                                            </div>
                                            <div class="col-span-6 text-right">
                                                <span class="block text-xs text-slate-500">এই শিশুর খরচ</span>
                                                <span class="child-line block text-sm font-bold text-teal-700">৳0</span>
                                                <span class="child-band block text-xs text-slate-400">—</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="child_count" id="child_count_input" value="{{ count($childRows) }}">

                                @error('child_ages')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('child_ages.*')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="promo_code" class="block text-sm font-semibold mb-2">
                                প্রোমো কোড
                                <span class="text-slate-400 font-normal">(ঐচ্ছিক)</span>
                            </label>
                            <div class="flex gap-3">
                                <input id="promo_code" name="promo_code" type="text" value="{{ old('promo_code') }}"
                                    placeholder="E.g. EID2026" autocomplete="off"
                                    class="flex-1 px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 uppercase font-mono">
                                <button type="button" id="applyPromo"
                                    class="shrink-0 px-6 py-3 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 text-white font-semibold text-sm transition">
                                    প্রয়োগ
                                </button>
                            </div>
                            <div id="promoMessage" class="mt-2 text-sm hidden"></div>
                        </div>

                        <div>
                            <label for="special_notes" class="block text-sm font-semibold mb-2">বিশেষ কোনো অনুরোধ</label>
                            <textarea id="special_notes" name="special_notes" rows="3"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">{{ old('special_notes') }}</textarea>
                            @error('special_notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-4 rounded-xl transition">
                            বুকিং নিশ্চিত করুন
                        </button>
                    </form>
                </div>
            </div>

            <aside class="lg:sticky lg:top-28 h-fit">
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-lg">
                    <img src="{{ $tour->image_url }}" alt="{{ $tour->title ?? '' }}" class="w-full h-48 object-cover">
                    <div class="p-7">
                        <p class="text-sm text-teal-700 font-semibold">📍 {{ $tour->location ?? '' }}</p>
                        <h2 class="font-bold text-lg mt-2 leading-7">{{ $tour->title ?? '' }}</h2>

                        <dl class="mt-6 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">সময়কাল</dt>
                                <dd class="font-semibold">{{ $tour->duration_days ?? 0 }} দিন</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">যাত্রা</dt>
                                <dd class="font-semibold">
                                    {{ $tour->transport_icon ?? '🚌' }} {{ $tour->transport_type ?? 'Bus' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">প্রতি প্রাপ্তবয়স্ক</dt>
                                <dd class="font-semibold" id="summaryAdultRate">—</dd>
                            </div>
                        </dl>

                        <div class="border-t border-slate-200 dark:border-slate-800 mt-6 pt-5 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">প্রাপ্তবয়স্ক</span>
                                <span class="font-semibold"><span id="summaryAdults">0</span> জন</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">শিশু</span>
                                <span class="font-semibold"><span id="summaryChildren">0</span> জন</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">ফ্রি শিশু</span>
                                <span class="font-semibold"><span id="summaryInfants">0</span> জন</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">মোট যাত্রী</span>
                                <span class="font-semibold"><span id="summaryTotalGuests">0</span> জন</span>
                            </div>

                            <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-3 mt-3">
                                <span class="text-slate-500">যাত্রীর খরচ</span>
                                <span class="font-semibold" id="summaryGuestSubtotal">৳0</span>
                            </div>

                            <div id="summaryTierDiscountRow" class="flex justify-between hidden">
                                <span class="text-emerald-600 font-semibold">ছাড়</span>
                                <span class="font-semibold text-emerald-600" id="summaryTierDiscount">−৳0</span>
                            </div>

                            <div id="summaryCabinRow" class="flex justify-between hidden">
                                <span class="text-slate-500" id="summaryCabinLabel">অতিরিক্ত কেবিন</span>
                                <span class="font-semibold" id="summaryCabin">৳0</span>
                            </div>

                            <div id="summaryDiscountRow" class="flex justify-between hidden">
                                <span class="text-emerald-600 font-semibold">
                                    প্রোমো ছাড়
                                    <span id="summaryPromoCode" class="font-mono"></span>
                                </span>
                                <span class="font-semibold text-emerald-600" id="summaryDiscount">−৳0</span>
                            </div>

                            <div
                                class="flex justify-between items-center border-t border-slate-200 dark:border-slate-800 pt-4 mt-4">
                                <span class="font-bold">সর্বমোট</span>
                                <span class="text-2xl font-extrabold text-teal-700" id="summaryTotal">৳0</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 mt-4 leading-6" id="cabinNote"></p>
                    </div>
                </div>
            </aside>
        </div>
    </section>

@endsection

@section('scripts')
    <script>
        (function () {
            const form = document.getElementById('bookingForm');
            if (!form) return;

            let pricing;
            try {
                pricing = JSON.parse(form.dataset.pricing || '{}');
            } catch (e) {
                pricing = {};
            }

            const tiers = pricing.tiers || [];
            const validateUrl = form.dataset.validateUrl;

            const guestInput = document.getElementById('guest_count');
            const guestMinus = document.getElementById('guestMinus');
            const guestPlus = document.getElementById('guestPlus');
            const tourMaxGuests = parseInt(guestInput.max, 10) || 100;
            const coupleBox = document.getElementById('is_couple');
            const coupleRateNote = document.getElementById('coupleRateNote');
            const adultLimitHint = document.getElementById('adultLimitHint');

            const childrenBox = document.getElementById('has_children');
            const childSection = document.getElementById('childSection');
            const childCountLabel = document.getElementById('childCount');
            const childCountInput = document.getElementById('child_count_input');
            const childMinus = document.getElementById('childMinus');
            const childPlus = document.getElementById('childPlus');
            const childRowsWrap = document.getElementById('childRows');
            const childPolicyNote = document.getElementById('childPolicyNote');
            const cabinNote = document.getElementById('cabinNote');

            const promoInput = document.getElementById('promo_code');
            const promoBtn = document.getElementById('applyPromo');
            const promoMessage = document.getElementById('promoMessage');

            const elAdultRate = document.getElementById('summaryAdultRate');
            const elAdults = document.getElementById('summaryAdults');
            const elChildren = document.getElementById('summaryChildren');
            const elInfants = document.getElementById('summaryInfants');
            const elTotalGuests = document.getElementById('summaryTotalGuests');
            const elGuestSubtotal = document.getElementById('summaryGuestSubtotal');
            const elTotal = document.getElementById('summaryTotal');
            const elTierDiscountRow = document.getElementById('summaryTierDiscountRow');
            const elTierDiscount = document.getElementById('summaryTierDiscount');
            const elCabinRow = document.getElementById('summaryCabinRow');
            const elCabin = document.getElementById('summaryCabin');
            const elCabinLabel = document.getElementById('summaryCabinLabel');
            const elDiscountRow = document.getElementById('summaryDiscountRow');
            const elDiscount = document.getElementById('summaryDiscount');
            const elPromoCode = document.getElementById('summaryPromoCode');

            const format = (n) => '৳' + (Number(n) || 0).toLocaleString('en-BD', { maximumFractionDigits: 2 });
            const round2 = (n) => Math.round((Number(n) || 0) * 100) / 100;

            let promo = null;
            let isChecking = false;

            // The min/max attributes do not stop someone typing or scrolling
            // past them, so every path that can change the value funnels
            // through here. Without this the box would sit at 99 while both
            // stepper buttons were disabled.
            function clampGuests() {
                const min = Math.max(1, parseInt(guestInput.min, 10) || 1);
                const max = Math.max(min, parseInt(guestInput.max, 10) || min);
                const raw = parseInt(guestInput.value, 10);
                const value = isNaN(raw) ? min : Math.max(min, Math.min(max, raw));

                if (guestInput.value !== String(value)) guestInput.value = value;

                return value;
            }

            // A number input swallows the wheel, which makes the page feel
            // broken when the cursor happens to rest over it. Page scrolling is
            // the expected behaviour here.
            function blockWheel(input) {
                input.addEventListener('wheel', (e) => e.preventDefault(), { passive: false });
            }

            function coupleTier() {
                return tiers.find((t) => t.type === 'couple') || null;
            }

            // The couple box selects the couple rate, and it is the only thing
            // that does. Otherwise the tier follows the shape of the party:
            // children or a bigger group take the widest tier that fits.
            // Mirrors TourPricingService::suggestTier().
            function selectedTier() {
                if (coupleBox.checked && coupleTier()) return coupleTier();

                const adults = Math.max(1, parseInt(guestInput.value, 10) || 1);
                const candidates = tiers.filter((t) => t.type !== 'couple');
                const fitting = candidates.filter((t) => adults >= t.min_adults && adults <= t.max_adults);

                if (fitting.length) {
                    // Keyed off the ages actually entered, not the checkbox, so a
                    // ticked box with every age blank still books as a plain
                    // party. This mirrors TourPricingService::suggestTier().
                    const widest = childRows().some((row) => row.querySelector('.child-age').value !== '');
                    const sorted = fitting.slice().sort((a, b) => widest
                        ? (b.max_adults - a.max_adults) || (a.price_per_adult - b.price_per_adult)
                        : (a.max_adults - b.max_adults) || (a.price_per_adult - b.price_per_adult));
                    return sorted[0];
                }

                const fallback = candidates.filter((t) => adults <= t.max_adults)
                    .sort((a, b) => b.max_adults - a.max_adults);
                return fallback[0] || tiers[0] || null;
            }

            function childRows() {
                return Array.from(childRowsWrap.querySelectorAll('.child-row'));
            }

            // Mirrors TourPricingTier::guestTypeForAge(): the bands are strict,
            // so exactly at the threshold is no longer free or reduced.
            function bandFor(age, tier) {
                if (age !== null && age < tier.infant_age_max) return 'infant';
                if (age !== null && age < tier.child_age_max) return 'child';
                return 'adult';
            }

            function unitFor(age, tier) {
                const band = bandFor(age, tier);
                if (band === 'infant') return 0;
                if (band === 'child') return round2(tier.price_per_adult * (tier.child_price_percent / 100));
                return tier.price_per_adult;
            }

            // Mirrors TourPricingService::quote().
            function compute() {
                const tier = selectedTier();
                if (!tier) return null;

                let adults = Math.max(1, parseInt(guestInput.value, 10) || 1);
                let childSubtotal = 0;
                let childCount = 0;
                let infantCount = 0;

                childRows().forEach((row) => {
                    const raw = row.querySelector('.child-age').value;
                    const parsed = raw === '' ? null : parseInt(raw, 10);
                    const age = parsed === null || isNaN(parsed) ? null : parsed;
                    const band = bandFor(age, tier);
                    const unit = unitFor(age, tier);

                    childSubtotal += unit;

                    if (band === 'infant') infantCount++;
                    else if (band === 'child') childCount++;
                    else adults++;

                    row.querySelector('.child-line').textContent = format(unit);
                    row.querySelector('.child-band').textContent =
                        band === 'infant' ? 'বিনামূল্যে' : (band === 'child' ? 'শিশুর হার' : 'প্রাপ্তবয়স্ক');
                });

                const adultSubtotal = adults * tier.price_per_adult;
                const guestSubtotal = round2(adultSubtotal + childSubtotal);

                let tierDiscount = 0;
                if (tier.discount_type === 'percent') {
                    tierDiscount = guestSubtotal * (tier.discount_value / 100);
                } else if (tier.discount_type === 'fixed') {
                    tierDiscount = tier.discount_value;
                }
                tierDiscount = round2(Math.max(0, Math.min(tierDiscount, guestSubtotal)));

                const guestCount = adults + childCount + infantCount;
                const capacity = Math.max(1, tier.capacity_per_cabin);
                const cabins = guestCount > 0 ? Math.ceil(guestCount / capacity) : 0;
                const extraCabins = Math.max(0, cabins - tier.included_cabin_count);
                const extraCabinAmount = round2(extraCabins * tier.extra_cabin_fee);

                // The tier discount never touches the cabin surcharge.
                const discountable = round2(Math.max(0, guestSubtotal - tierDiscount));
                const subtotal = round2(discountable + extraCabinAmount);
                const promoDiscount = promo ? discountFor(subtotal) : 0;

                return {
                    tier: tier,
                    adults: adults,
                    childCount: childCount,
                    infantCount: infantCount,
                    guestCount: guestCount,
                    guestSubtotal: guestSubtotal,
                    tierDiscount: tierDiscount,
                    cabins: cabins,
                    extraCabins: extraCabins,
                    extraCabinAmount: extraCabinAmount,
                    subtotal: subtotal,
                    promoDiscount: promoDiscount,
                    total: round2(Math.max(0, subtotal - promoDiscount)),
                };
            }

            // Mirrors PromoCode::discountFor().
            function discountFor(sub) {
                if (!promo || sub <= 0) return 0;
                let value;
                if (promo.type === 'percentage') {
                    value = (sub * promo.value) / 100;
                    if (promo.cap !== null) value = Math.min(value, promo.cap);
                } else {
                    value = promo.value;
                }
                return round2(Math.min(value, sub));
            }

            function setChildCount(count) {
                const target = Math.max(0, Math.min(20, count));

                // The row count has to be re-read inside each loop. Holding a
                // snapshot in `rows` made the guard constant, so adding a child
                // appended rows forever and froze the page.
                while (childRows().length < target) {
                    const index = childRows().length;
                    const row = document.createElement('div');
                    row.className = 'child-row grid grid-cols-12 gap-3 items-end';
                    row.dataset.index = index;
                    row.innerHTML =
                        '<div class="col-span-6">' +
                        '<label class="block text-xs font-semibold mb-1">শিশু ' + (index + 1) + ' এর বয়স</label>' +
                        '<input type="number" name="child_ages[' + index + ']" min="0" max="18" placeholder="বয়স"' +
                        ' class="child-age w-full px-3 py-2.5 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 text-sm">' +
                        '</div>' +
                        '<div class="col-span-6 text-right">' +
                        '<span class="block text-xs text-slate-500">এই শিশুর খরচ</span>' +
                        '<span class="child-line block text-sm font-bold text-teal-700">৳0</span>' +
                        '<span class="child-band block text-xs text-slate-400">—</span>' +
                        '</div>';
                    childRowsWrap.appendChild(row);
                }

                while (childRows().length > target) {
                    childRowsWrap.removeChild(childRowsWrap.lastElementChild);
                }

                syncChildNames();
                childCountLabel.textContent = target;
                childCountInput.value = target;
                childMinus.disabled = target <= 0;
                childPlus.disabled = target >= 20;
            }

            function syncChildNames() {
                childRows().forEach((row, i) => {
                    const input = row.querySelector('.child-age');
                    input.name = 'child_ages[' + i + ']';

                    // Same reason as the adult count: an age outside 0-18 would
                    // be quoted against the wrong band.
                    const min = parseInt(input.min, 10) || 0;
                    const max = parseInt(input.max, 10) || 18;
                    const raw = parseInt(input.value, 10);

                    if (input.value !== '' && !isNaN(raw)) {
                        const value = Math.max(min, Math.min(max, raw));
                        if (input.value !== String(value)) input.value = value;
                    }
                });
            }

            function render() {
                const result = compute();
                if (!result) return;

                const t = result.tier;

                elAdultRate.textContent = format(t.price_per_adult) + '/জন';
                elAdults.textContent = result.adults;
                elChildren.textContent = result.childCount;
                elInfants.textContent = result.infantCount;
                elTotalGuests.textContent = result.guestCount;
                elGuestSubtotal.textContent = format(result.guestSubtotal);
                elTotal.textContent = format(result.total);

                elTierDiscount.textContent = '−' + format(result.tierDiscount);
                elTierDiscountRow.classList.toggle('hidden', result.tierDiscount <= 0);

                elCabin.textContent = format(result.extraCabinAmount);
                elCabinLabel.textContent = 'অতিরিক্ত কেবিন (' + result.extraCabins + ')';
                elCabinRow.classList.toggle('hidden', result.extraCabinAmount <= 0);

                elDiscount.textContent = '−' + format(result.promoDiscount);
                elDiscountRow.classList.toggle('hidden', result.promoDiscount <= 0);
                if (result.promoDiscount > 0) elPromoCode.textContent = '(' + promo.code + ')';

                childPolicyNote.textContent =
                    t.child_age_max + ' বছরের কম শিশুর হার ' + t.child_price_percent +
                    '%, আর ' + t.infant_age_max + ' বছরের কম শিশু বিনামূল্যে।';

                const couple = coupleTier();
                coupleRateNote.textContent = coupleBox.checked && couple
                    ? 'প্রতি জন ' + format(couple.price_per_adult) +
                      (couple.extra_cabin_fee > 0
                          ? ' · অতিরিক্ত কেবিন ' + format(couple.extra_cabin_fee)
                          : '')
                    : '';

                adultLimitHint.textContent =
                    'এই হারে সর্বনিম্ন ' + t.min_adults + ' এবং সর্বোচ্চ ' + t.max_adults +
                    ' জন প্রাপ্তবয়স্ক যাত্রী। প্রতি কেবিনে ' + t.capacity_per_cabin + ' জন।';

                cabinNote.textContent = result.cabins > 0
                    ? 'আপনার দলের জন্য ' + result.cabins + 'টি কেবিন লাগবে।'
                    : '';

                // The tier can be narrower than the tour allows, so the ceiling
                // follows the selected tier. The tour's own ceiling is held
                // separately, because reading max back out of the input would
                // ratchet it down and never let it widen again.
                const maxAllowed = Math.min(t.max_adults, tourMaxGuests);
                guestInput.max = maxAllowed;
                guestMinus.disabled = result.adults <= Math.max(1, t.min_adults);
                guestPlus.disabled = result.adults >= maxAllowed;

                clampGuests();
            }

            function showPromoMessage(text, ok) {
                promoMessage.textContent = text;
                promoMessage.classList.remove('hidden');
                promoMessage.classList.toggle('text-emerald-600', ok);
                promoMessage.classList.toggle('text-red-600', !ok);
            }

            function clearPromo() {
                promo = null;
                render();
            }

            async function applyPromo() {
                const code = promoInput.value.trim();
                if (!code) {
                    clearPromo();
                    promoMessage.classList.add('hidden');
                    return;
                }
                if (isChecking) return;
                isChecking = true;
                promoBtn.disabled = true;
                promoBtn.textContent = 'যাচাই হচ্ছে...';

                try {
                    const params = new URLSearchParams();
                    params.append('promo_code', code);
                    params.append('tour_id', form.dataset.tourId);
                    params.append('guest_count', guestInput.value);
                    if (coupleBox.checked) params.append('is_couple', '1');
                    if (childrenBox.checked) {
                        params.append('has_children', '1');
                        childRows().forEach((row, i) => {
                            params.append('child_ages[' + i + ']', row.querySelector('.child-age').value);
                        });
                    }

                    const response = await fetch(validateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': form.querySelector('[name=_token]').value,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: params,
                    });

                    const data = await response.json();

                    if (!response.ok || !data.valid) {
                        clearPromo();
                        showPromoMessage(data.message || 'প্রোমো কোডটি গ্রহণ করা হয়নি।', false);
                        return;
                    }

                    promo = {
                        code: data.code,
                        type: data.discount_type,
                        value: parseFloat(data.discount_value) || 0,
                        cap: data.max_discount === null ? null : parseFloat(data.max_discount),
                    };

                    render();
                    showPromoMessage(data.message, true);
                } catch (error) {
                    clearPromo();
                    showPromoMessage('প্রোমো কোড যাচাই করা যায়নি। আবার চেষ্টা করুন।', false);
                } finally {
                    isChecking = false;
                    promoBtn.disabled = false;
                    promoBtn.textContent = 'প্রয়োগ';
                }
            }

            function setGuests(value) {
                guestInput.value = value;
                clampGuests();
                if (promo) clearPromo(); else render();
            }

            guestMinus.addEventListener('click', () => {
                setGuests((parseInt(guestInput.value, 10) || 1) - 1);
            });

            guestPlus.addEventListener('click', () => {
                setGuests((parseInt(guestInput.value, 10) || 1) + 1);
            });

            // Typing, pasting, the arrow keys and the native spinners all land
            // on input, so clamping there covers every one of them.
            guestInput.addEventListener('input', () => { clampGuests(); if (promo) clearPromo(); else render(); });
            guestInput.addEventListener('change', () => { clampGuests(); render(); });
            guestInput.addEventListener('blur', () => { clampGuests(); render(); });
            blockWheel(guestInput);

            coupleBox.addEventListener('change', () => {
                // The couple rate is a different price, so a promo must re-check.
                if (coupleBox.checked && (parseInt(guestInput.value, 10) || 1) < 2) {
                    guestInput.value = 2;
                }
                if (promo) clearPromo(); else render();
                // Ticking couple can change the tier ceiling, so a value left
                // above the new maximum has to come back down.
                clampGuests();
                render();
            });

            childrenBox.addEventListener('change', () => {
                childSection.classList.toggle('hidden', !childrenBox.checked);
                if (childrenBox.checked && childRows().length === 0) setChildCount(1);
                if (!childrenBox.checked) setChildCount(0);
                if (promo) clearPromo(); else render();
            });

            childMinus.addEventListener('click', () => {
                setChildCount(childRows().length - 1);
                if (promo) clearPromo(); else render();
            });

            childPlus.addEventListener('click', () => {
                setChildCount(childRows().length + 1);
                if (promo) clearPromo(); else render();
            });

            childRowsWrap.addEventListener('input', () => {
                syncChildNames();
                if (promo) clearPromo(); else render();
            });

            // Scrolling over an age box should scroll the page, not the number.
            childRowsWrap.addEventListener('wheel', (e) => {
                if (e.target.classList.contains('child-age')) e.preventDefault();
            }, { passive: false });

            promoBtn.addEventListener('click', applyPromo);
            promoInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyPromo();
                }
            });

            promoInput.addEventListener('input', () => {
                if (promoInput.value.trim().toUpperCase() !== (promo ? promo.code : '')) {
                    clearPromo();
                    promoMessage.classList.add('hidden');
                }
            });

            // Reflect the restored state before the first paint. A value
            // restored from a failed submission can sit above the tier ceiling,
            // so it is pulled into range before anything is priced.
            childSection.classList.toggle('hidden', !childrenBox.checked);
            setChildCount(childRows().length);
            syncChildNames();
            clampGuests();
            render();
        })();
    </script>
@endsection
