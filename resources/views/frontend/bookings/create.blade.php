@extends('layouts.frontend')

@section('title', 'বুকিং করুন — '.($tour->title ?? \App\Models\Setting::string('site_name')))

@section('content')

    @php
        $unitPrice = (float) ($tour->price_per_person ?? 0);
        $maxGuests = max((int) ($tour->total_seats ?? 30) - (int) ($tour->current_booked ?? 0), 1);
        $oldGuests = (int) old('guest_count', 1);
        $oldSubtotal = round($unitPrice * $oldGuests, 2);
    @endphp

    <section class="bg-slate-900 text-white pt-32 pb-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold">বুকিং ফর্ম</h1>
            <p class="text-white/70 mt-2">নিচের ফর্মটি পূরণ করে জমা দিন</p>
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

                    <form method="POST" action="{{ route('bookings.store') }}"
                        id="bookingForm" class="space-y-6"
                        data-tour-id="{{ $tour->id ?? 1 }}"
                        data-unit-price="{{ $unitPrice }}"
                        data-validate-url="{{ route('bookings.validate-promo') }}">

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

                        <div>
                            <label for="guest_count" class="block text-sm font-semibold mb-2">যাত্রীর সংখ্যা *</label>
                            <div class="flex items-center gap-3">
                                <button type="button" id="guestMinus" aria-label="একজন কমান"
                                    class="w-12 h-12 shrink-0 rounded-xl border border-slate-300 dark:border-slate-700 text-xl font-bold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    −
                                </button>
                                <input id="guest_count" name="guest_count" type="number" min="1" max="{{ $maxGuests }}"
                                    value="{{ $oldGuests }}" required
                                    class="flex-1 text-center px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600 font-bold text-lg">
                                <button type="button" id="guestPlus" aria-label="একজন বাড়ান"
                                    class="w-12 h-12 shrink-0 rounded-xl border border-slate-300 dark:border-slate-700 text-xl font-bold hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    +
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">সর্বোচ্চ {{ $maxGuests }} জন বুক করা যাবে</p>
                            @error('guest_count')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                            <textarea id="special_notes" name="special_notes" rows="4"
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
                            @if (! empty($tour->departure_date))
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">ভ্রমণের তারিখ</dt>
                                    <dd class="font-semibold">
                                        {{ \Illuminate\Support\Carbon::parse($tour->departure_date)->format('d M Y') }}
                                    </dd>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-slate-500">প্রতি জন</dt>
                                <dd class="font-semibold">
                                    <span id="unitPriceLabel">৳{{ number_format($unitPrice) }}</span>
                                </dd>
                            </div>
                        </dl>

                        <div class="border-t border-slate-200 dark:border-slate-800 mt-6 pt-5 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">যাত্রীর সংখ্যা</span>
                                <span class="font-semibold">
                                    <span id="summaryGuests">{{ $oldGuests }}</span> জন
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">সাবটোটাল</span>
                                <span class="font-semibold" id="summarySubtotal">৳{{ number_format($oldSubtotal) }}</span>
                            </div>

                            <div id="summaryDiscountRow" class="flex justify-between text-sm hidden">
                                <span class="text-emerald-600 font-semibold">
                                    ছাড়
                                    <span id="summaryPromoCode" class="font-mono"></span>
                                </span>
                                <span class="font-semibold text-emerald-600" id="summaryDiscount">−৳0</span>
                            </div>

                            <div
                                class="flex justify-between items-center border-t border-slate-200 dark:border-slate-800 pt-4 mt-4">
                                <span class="font-bold">সর্বমোট</span>
                                <span class="text-2xl font-extrabold text-teal-700" id="summaryTotal">
                                    ৳{{ number_format($oldSubtotal) }}
                                </span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 mt-4 leading-6">
                            প্রতি জন ৳{{ number_format($unitPrice) }} × যাত্রীর সংখ্যা বুকিং নিশ্চিত করলে পরবর্তী ধাপে
                            payment সম্পন্ন করতে হবে।
                        </p>
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

            const unitPrice = parseFloat(form.dataset.unitPrice) || 0;
            const validateUrl = form.dataset.validateUrl;

            const guestInput = document.getElementById('guest_count');
            const minusBtn = document.getElementById('guestMinus');
            const plusBtn = document.getElementById('guestPlus');
            const promoInput = document.getElementById('promo_code');
            const promoBtn = document.getElementById('applyPromo');
            const promoMessage = document.getElementById('promoMessage');

            const elGuests = document.getElementById('summaryGuests');
            const elSubtotal = document.getElementById('summarySubtotal');
            const elTotal = document.getElementById('summaryTotal');
            const elDiscountRow = document.getElementById('summaryDiscountRow');
            const elDiscount = document.getElementById('summaryDiscount');
            const elPromoCode = document.getElementById('summaryPromoCode');

            const maxGuests = parseInt(guestInput.max, 10) || 1;
            const format = (n) => '৳' + n.toLocaleString('en-BD', { maximumFractionDigits: 2 });

            let promo = null;
            let isChecking = false;

            function clampGuests() {
                let value = parseInt(guestInput.value, 10);
                if (isNaN(value) || value < 1) value = 1;
                if (value > maxGuests) value = maxGuests;
                guestInput.value = value;
                return value;
            }

            function subtotal() {
                return Math.round(unitPrice * clampGuests() * 100) / 100;
            }

            // Mirrors PromoCode::discountFor() so the client total matches the server.
            function discountFor(sub) {
                if (!promo || sub <= 0) return 0;

                let value;
                if (promo.type === 'percentage') {
                    value = (sub * promo.value) / 100;
                    if (promo.cap !== null) value = Math.min(value, promo.cap);
                } else {
                    value = promo.value;
                }

                return Math.round(Math.min(value, sub) * 100) / 100;
            }

            function render() {
                const guests = clampGuests();
                const sub = subtotal();
                const discount = discountFor(sub);

                elGuests.textContent = guests;
                elSubtotal.textContent = format(sub);
                elTotal.textContent = format(Math.round((sub - discount) * 100) / 100);
                elDiscount.textContent = '−' + format(discount);

                if (discount > 0) {
                    elDiscountRow.classList.remove('hidden');
                    elPromoCode.textContent = '(' + promo.code + ')';
                } else {
                    elDiscountRow.classList.add('hidden');
                }

                minusBtn.disabled = guests <= 1;
                plusBtn.disabled = guests >= maxGuests;
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
                    const body = new URLSearchParams({
                        promo_code: code,
                        tour_id: form.dataset.tourId,
                        guest_count: guestInput.value,
                    });

                    const response = await fetch(validateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': form.querySelector('[name=_token]').value,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: body,
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

            minusBtn.addEventListener('click', () => {
                guestInput.value = clampGuests() - 1;
                render();
            });

            plusBtn.addEventListener('click', () => {
                guestInput.value = clampGuests() + 1;
                render();
            });

            guestInput.addEventListener('input', render);
            guestInput.addEventListener('change', render);

            promoBtn.addEventListener('click', applyPromo);
            promoInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyPromo();
                }
            });

            // Editing the code invalidates the previously applied discount.
            promoInput.addEventListener('input', () => {
                if (promoInput.value.trim().toUpperCase() !== (promo ? promo.code : '')) {
                    clearPromo();
                    promoMessage.classList.add('hidden');
                }
            });

            render();
        })();
    </script>
@endsection
