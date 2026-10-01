@extends('layouts.admin')

@section('title', 'New Booking')
@section('breadcrumb', 'Bookings / Create')
@section('page-title', 'ম্যানুয়াল বুকিং তৈরি করুন')

@section('content')

@if (session('error'))
    <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3">
        <ul class="list-disc ps-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">নতুন বুকিং যোগ করুন</h2>
        <p class="text-sm text-slate-500 mt-1">ফোনে বা কাউন্টারে গ্রাহকের বুকিং নিজে থেকে তৈরি করুন।</p>
    </div>
    <a href="{{ route('admin.bookings.index') }}"
        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
        ← সব বুকিং
    </a>
</div>

<form method="POST" action="{{ route('admin.bookings.store') }}" id="manualBookingForm">

    @csrf

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-5">গ্রাহকের তথ্য</h3>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="label" for="customer_name">পুরো নাম <span class="text-red-500">*</span></label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required class="input">
                        @error('customer_name')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label" for="customer_phone">মোবাইল নম্বর <span class="text-red-500">*</span></label>
                        <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required class="input">
                        @error('customer_phone')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label" for="customer_email">ইমেইল <span class="text-red-500">*</span></label>
                        <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email') }}" required class="input">
                        <p class="mt-1.5 text-xs text-slate-500">বুকিং confirmation email পাঠানোর জন্য ব্যবহৃত হবে।</p>
                        @error('customer_email')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-5">ট্যুর ও যাত্রী</h3>

                <div>
                    <label class="label" for="tour_id">ট্যুর <span class="text-red-500">*</span></label>
                    <select id="tour_id" name="tour_id" required class="input">
                        <option value="">ট্যুর নির্বাচন করুন...</option>
                        @foreach ($tours as $tour)
                            @php $left = max($tour->max_slots - $tour->current_booked, 0); @endphp
                            <option value="{{ $tour->id }}"
                                data-pricing="{{ json_encode(['tiers' => app(App\Services\TourPricingService::class)->toBrowserPayload($tour->bookableTiers())['tiers'], 'available_slots' => $left]) }}"
                                data-available="{{ $left }}"
                                @selected((string) old('tour_id') === (string) $tour->id)>
                                {{ $tour->title }} ({{ $left }} সিট বাকি)
                            </option>
                        @endforeach
                    </select>
                    @error('tour_id')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-5 mt-5">
                    <div>
                        <label class="label" for="guest_count">প্রাপ্তবয়স্ক যাত্রী <span class="text-red-500">*</span></label>
                        <input id="guest_count" name="guest_count" type="number" min="1"
                            value="{{ old('guest_count', 1) }}" required class="input">
                        <p id="availabilityHint" class="mt-1.5 text-xs text-slate-500">ট্যুর নির্বাচন করলে ফাকা সিট দেখা যাবে।</p>
                        @error('guest_count')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_couple" value="1" id="is_couple"
                                @checked(old('is_couple'))
                                class="w-5 h-5 accent-teal-700">
                            <span class="font-semibold text-sm">দম্পতি হিসেবে বুকিং</span>
                        </label>
                    </div>
                </div>

                <div class="mt-5 border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="has_children" value="1" id="has_children"
                            @checked(old('has_children'))
                            class="w-5 h-5 accent-teal-700">
                        <span class="font-semibold text-sm">সাথে শিশু আছে</span>
                    </label>

                    <div id="childSection" class="mt-4 {{ old('has_children') ? '' : 'hidden' }}">
                        <div class="flex items-center gap-3 mb-4">
                            <button type="button" id="childMinus"
                                class="w-9 h-9 rounded-lg border border-slate-300 dark:border-slate-700 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40">
                                −
                            </button>
                            <span class="text-sm font-semibold">শিশু: <span id="childCount">0</span></span>
                            <button type="button" id="childPlus"
                                class="w-9 h-9 rounded-lg border border-slate-300 dark:border-slate-700 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40">
                                +
                            </button>
                        </div>

                        <div id="childRows" class="space-y-2">
                            @php $rows = old('child_ages', []); @endphp
                            @foreach ($rows as $index => $age)
                                <div class="child-row grid grid-cols-12 gap-2 items-end">
                                    <div class="col-span-7">
                                        <input type="number" name="child_ages[{{ $index }}]" value="{{ $age }}"
                                            min="0" max="18" placeholder="বয়স" class="child-age input py-2 text-sm">
                                    </div>
                                    <div class="col-span-5 text-right">
                                        <span class="child-line block text-xs font-bold text-teal-700">৳0</span>
                                        <span class="child-band block text-[11px] text-slate-400">—</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('child_ages')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        @error('child_ages.*')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label class="label" for="promo_code">প্রোমো কোড</label>
                    <input id="promo_code" name="promo_code" list="promoCodeList" autocomplete="off"
                        value="{{ old('promo_code') }}" placeholder="ঐচ্ছিক" class="input uppercase font-mono">
                    <datalist id="promoCodeList">
                        @foreach ($promoCodes as $promo)
                            <option value="{{ $promo->code }}">{{ $promo->describeDiscount() }}</option>
                        @endforeach
                    </datalist>
                    <p class="mt-1.5 text-xs text-slate-500">সেভ করার সময় যাচাই করা হবে।</p>
                    @error('promo_code')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
                <h3 class="font-bold text-lg mb-5">পেমেন্ট ও স্ট্যাটাস</h3>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="label" for="status">বুকিং স্ট্যাটাস <span class="text-red-500">*</span></label>
                        <select id="status" name="status" class="input" required>
                            @foreach (['confirmed' => 'Confirmed', 'pending' => 'Pending', 'cancelled' => 'Cancelled', 'completed' => 'Completed'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'confirmed') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label" for="payment_status">পেমেন্ট স্ট্যাটাস <span class="text-red-500">*</span></label>
                        <select id="payment_status" name="payment_status" class="input" required>
                            @foreach (['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('payment_status', 'unpaid') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('payment_status')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label" for="payment_method">পেমেন্ট মেথড</label>
                        <select id="payment_method" name="payment_method" class="input">
                            <option value="">নির্বাচন করুন...</option>
                            @foreach (['Cash', 'bKash', 'Nagad', 'Rocket', 'Card', 'Bank Transfer'] as $method)
                                <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="label" for="transaction_id">ট্রানজেকশন আইডি</label>
                        <input id="transaction_id" name="transaction_id" value="{{ old('transaction_id') }}" class="input">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label" for="special_notes">বিশেষ নোট</label>
                        <textarea id="special_notes" name="special_notes" rows="4" class="input resize-none"
                            placeholder="যেমন: ফোনে বুকিং নেওয়া হয়েছে, ফ্লাইট নম্বর...">{{ old('special_notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
                <h3 class="font-bold mb-5">সারসংক্ষেপ</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">প্রাপ্তবয়স্কর হার</span>
                        <span class="font-semibold" id="summaryUnitPrice">৳0</span>
                    </div>
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
                    <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-3 mt-3">
                        <span class="text-slate-500">যাত্রীর খরচ</span>
                        <span class="font-semibold" id="summaryGuestSubtotal">৳0</span>
                    </div>
                    <div id="summaryTierDiscountRow" class="flex justify-between hidden">
                        <span class="text-emerald-600 font-semibold">ধরনের ছাড়</span>
                        <span class="font-semibold text-emerald-600" id="summaryTierDiscount">−৳0</span>
                    </div>
                    <div id="summaryCabinRow" class="flex justify-between hidden">
                        <span class="text-slate-500" id="summaryCabinLabel">অতিরিক্ত কেবিন</span>
                        <span class="font-semibold" id="summaryCabin">৳0</span>
                    </div>
                </div>

                <div class="flex justify-between items-center border-t border-slate-200 dark:border-slate-800 pt-4 mt-4">
                    <span class="font-bold">সর্বমোট</span>
                    <span class="text-2xl font-extrabold text-teal-700" id="summaryTotal">৳0</span>
                </div>

                <p class="text-xs text-slate-500 mt-4 leading-6">
                    প্রোমো কোড বসালে ছাড় সেভ করার সময় প্রয়োগ হবে। ফাইলে মোট টাকা লেখা হবে।
                </p>

                <button type="submit"
                    class="w-full mt-6 bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3 rounded-xl transition">
                    বুকিং তৈরি করুন
                </button>
                <a href="{{ route('admin.bookings.index') }}"
                    class="w-full mt-3 block text-center border border-slate-300 dark:border-slate-700 font-semibold py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    বাতিল
                </a>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-5 text-sm">
                <p class="font-bold text-blue-800 dark:text-blue-300 mb-2">মনে রাখবেন</p>
                <ul class="space-y-2 text-blue-700 dark:text-blue-300 text-xs leading-6">
                    <li>• বুকিং তৈরি হলে ট্যুরের ফাকা সিট স্বয়ংক্রিয়ভাবে কমে যাবে।</li>
                    <li>• সিটের চেয়ে বেশি যাত্রী দিলে বুকিং নেওয়া হবে না।</li>
                    <li>• প্রোমো কোডের মেয়াদ ও কোড কপি ফাঁকি করা হলে বুকিং নেওয়া হবে না।</li>
                </ul>
            </div>
        </aside>
    </div>
</form>

@endsection

@section('scripts')
    <script>
        (function () {
            const tourSelect = document.getElementById('tour_id');
            const guestInput = document.getElementById('guest_count');
            const coupleBox = document.getElementById('is_couple');
            const childrenBox = document.getElementById('has_children');
            const childSection = document.getElementById('childSection');
            const childCountLabel = document.getElementById('childCount');
            const childMinus = document.getElementById('childMinus');
            const childPlus = document.getElementById('childPlus');
            const childRowsWrap = document.getElementById('childRows');
            const availabilityHint = document.getElementById('availabilityHint');

            const elUnit = document.getElementById('summaryUnitPrice');
            const elAdults = document.getElementById('summaryAdults');
            const elChildren = document.getElementById('summaryChildren');
            const elInfants = document.getElementById('summaryInfants');
            const elGuestSubtotal = document.getElementById('summaryGuestSubtotal');
            const elTotal = document.getElementById('summaryTotal');
            const elTierDiscountRow = document.getElementById('summaryTierDiscountRow');
            const elTierDiscount = document.getElementById('summaryTierDiscount');
            const elCabinRow = document.getElementById('summaryCabinRow');
            const elCabin = document.getElementById('summaryCabin');
            const elCabinLabel = document.getElementById('summaryCabinLabel');

            const format = (n) => '৳' + (Number(n) || 0).toLocaleString('en-BD', { maximumFractionDigits: 2 });
            const round2 = (n) => Math.round((Number(n) || 0) * 100) / 100;

            let tiers = [];

            // The min/max attributes do not stop someone typing or scrolling
            // past them, so every path that can change the value funnels
            // through here.
            function clampGuests() {
                const min = Math.max(1, parseInt(guestInput.min, 10) || 1);
                const maxAttr = parseInt(guestInput.max, 10);
                const max = isNaN(maxAttr) ? Infinity : Math.max(min, maxAttr);
                const raw = parseInt(guestInput.value, 10);
                const value = isNaN(raw) ? min : Math.max(min, Math.min(max, raw));

                if (guestInput.value !== String(value)) guestInput.value = value;

                return value;
            }

            // A number input swallows the wheel, which makes the page feel
            // broken when the cursor happens to rest over it.
            guestInput.addEventListener('wheel', (e) => e.preventDefault(), { passive: false });

            function loadTiers() {
                const option = tourSelect.options[tourSelect.selectedIndex];
                if (!option || option.value === '') {
                    tiers = [];
                    return;
                }
                try {
                    tiers = (JSON.parse(option.dataset.pricing || '{}')).tiers || [];
                } catch (e) {
                    tiers = [];
                }
            }

            function coupleTier() {
                return tiers.find((t) => t.type === 'couple') || null;
            }

            function selectedTier() {
                if (coupleBox.checked && coupleTier()) return coupleTier();
                const adults = Math.max(1, parseInt(guestInput.value, 10) || 1);
                const candidates = tiers.filter((t) => t.type !== 'couple');
                const fitting = candidates.filter((t) => adults >= t.min_adults && adults <= t.max_adults);

                if (fitting.length) {
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
            function render() {
                const tier = selectedTier();

                if (!tier) {
                    [elAdults, elChildren, elInfants].forEach((el) => (el.textContent = '0'));
                    elUnit.textContent = '৳0';
                    elGuestSubtotal.textContent = '৳0';
                    elTotal.textContent = '৳0';
                    elTierDiscountRow.classList.add('hidden');
                    elCabinRow.classList.add('hidden');
                    availabilityHint.textContent = tourSelect.value === ''
                        ? 'ট্যুর নির্বাচন করলে হার দেখা যাবে।'
                        : 'এই ট্যুরে কোনো সুবিধা সক্রিয় নেই।';
                    availabilityHint.className = 'mt-1.5 text-xs text-slate-500';
                    return;
                }

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
                        band === 'infant' ? 'ফ্রি' : (band === 'child' ? 'শিশু' : 'পূর্ণ');
                });

                const guestSubtotal = round2(adults * tier.price_per_adult + childSubtotal);

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

                elUnit.textContent = format(tier.price_per_adult);
                elAdults.textContent = adults;
                elChildren.textContent = childCount;
                elInfants.textContent = infantCount;
                elGuestSubtotal.textContent = format(guestSubtotal);
                elTierDiscount.textContent = '−' + format(tierDiscount);
                elTierDiscountRow.classList.toggle('hidden', tierDiscount <= 0);
                elCabin.textContent = format(extraCabinAmount);
                elCabinLabel.textContent = 'অতিরিক্ত কেবিন (' + extraCabins + ')';
                elCabinRow.classList.toggle('hidden', extraCabinAmount <= 0);

                // The tier discount never touches the cabin surcharge; the promo
                // is validated server-side on save.
                elTotal.textContent = format(round2(Math.max(0, guestSubtotal - tierDiscount) + extraCabinAmount));

                const available = parseInt(tourSelect.options[tourSelect.selectedIndex].dataset.available, 10);

                if (available <= 0) {
                    availabilityHint.textContent = 'এই ট্যুরে কোনো ফাকা সিট নেই।';
                    availabilityHint.className = 'mt-1.5 text-xs text-red-500 font-semibold';
                } else if (guestCount > available) {
                    availabilityHint.textContent = 'ফাকা আছে মাত্র ' + available + ' সিট। যাত্রী কমান।';
                    availabilityHint.className = 'mt-1.5 text-xs text-red-500 font-semibold';
                } else if (adults < tier.min_adults || adults > tier.max_adults) {
                    availabilityHint.textContent =
                        'এই হারে ' + tier.min_adults + '-' + tier.max_adults + ' জন প্রাপ্তবয়স্ক যাত্রী লাগবে।';
                    availabilityHint.className = 'mt-1.5 text-xs text-red-500 font-semibold';
                } else if (tier.cabins_available !== null && cabins > tier.cabins_available) {
                    availabilityHint.textContent = 'ফাকা কেবিন: ' + tier.cabins_available + 'টি।';
                    availabilityHint.className = 'mt-1.5 text-xs text-red-500 font-semibold';
                } else {
                    availabilityHint.textContent = 'ফাকা সিট: ' + available + 'টি' +
                        (tier.cabins_available !== null ? ' · ফাকা কেবিন: ' + tier.cabins_available + 'টি' : '');
                    availabilityHint.className = 'mt-1.5 text-xs text-emerald-600 font-semibold';
                }

                // The tier ceiling is a hard limit, so the box cannot sit above
                // it. Seat availability stays a soft warning, because an admin
                // may legitimately be overriding it.
                guestInput.max = tier.max_adults;
                clampGuests();
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

            function setChildCount(count) {
                const target = Math.max(0, Math.min(20, count));

                // The row count has to be re-read inside each loop. Holding a
                // snapshot in `rows` made the guard constant, so adding a child
                // appended rows forever and froze the page.
                while (childRows().length < target) {
                    const index = childRows().length;
                    const row = document.createElement('div');
                    row.className = 'child-row grid grid-cols-12 gap-2 items-end';
                    row.innerHTML =
                        '<div class="col-span-7"><input type="number" name="child_ages[' + index + ']" min="0" max="18" placeholder="বয়স" class="child-age input py-2 text-sm"></div>' +
                        '<div class="col-span-5 text-right"><span class="child-line block text-xs font-bold text-teal-700">৳0</span><span class="child-band block text-[11px] text-slate-400">—</span></div>';
                    childRowsWrap.appendChild(row);
                }

                while (childRows().length > target) {
                    childRowsWrap.removeChild(childRowsWrap.lastElementChild);
                }

                syncChildNames();
                childCountLabel.textContent = target;
                childMinus.disabled = target <= 0;
                childPlus.disabled = target >= 20;
            }

            tourSelect.addEventListener('change', () => {
                loadTiers();
                render();
            });

            coupleBox.addEventListener('change', () => {
                if (coupleBox.checked && (parseInt(guestInput.value, 10) || 1) < 2) {
                    guestInput.value = 2;
                }
                render();
            });

            childrenBox.addEventListener('change', () => {
                childSection.classList.toggle('hidden', !childrenBox.checked);
                if (childrenBox.checked && childRows().length === 0) setChildCount(1);
                if (!childrenBox.checked) setChildCount(0);
                render();
            });

            childMinus.addEventListener('click', () => {
                setChildCount(childRows().length - 1);
                render();
            });

            childPlus.addEventListener('click', () => {
                setChildCount(childRows().length + 1);
                render();
            });

            // Typing, pasting, the arrow keys and the native spinners all land
            // on input, so clamping there covers every one of them.
            guestInput.addEventListener('input', () => { clampGuests(); render(); });
            guestInput.addEventListener('change', () => { clampGuests(); render(); });
            guestInput.addEventListener('blur', () => { clampGuests(); render(); });

            childRowsWrap.addEventListener('input', () => {
                syncChildNames();
                render();
            });

            // Scrolling over an age box should scroll the page, not the number.
            childRowsWrap.addEventListener('wheel', (e) => {
                if (e.target.classList.contains('child-age')) e.preventDefault();
            }, { passive: false });

            loadTiers();
            childSection.classList.toggle('hidden', !childrenBox.checked);
            setChildCount(childRows().length);
            syncChildNames();
            clampGuests();
            render();
        })();
    </script>
@endsection

