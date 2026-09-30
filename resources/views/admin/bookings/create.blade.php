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
                            <option value="{{ $tour->id }}" data-price="{{ $tour->price_per_person }}"
                                data-available="{{ $left }}"
                                @selected((string) old('tour_id') === (string) $tour->id)>
                                {{ $tour->title }} — ৳{{ number_format($tour->price_per_person) }} ({{ $left }} সিট বাকি)
                            </option>
                        @endforeach
                    </select>
                    @error('tour_id')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-5 mt-5">
                    <div>
                        <label class="label" for="guest_count">যাত্রীর সংখ্যা <span class="text-red-500">*</span></label>
                        <input id="guest_count" name="guest_count" type="number" min="1"
                            value="{{ old('guest_count', 1) }}" required class="input">
                        <p id="availabilityHint" class="mt-1.5 text-xs text-slate-500">ট্যুর নির্বাচন করলে ফাকা সিট দেখা যাবে।</p>
                        @error('guest_count')
                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
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
                        <span class="text-slate-500">প্রতি জন</span>
                        <span class="font-semibold" id="summaryUnitPrice">৳0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">যাত্রী</span>
                        <span class="font-semibold"><span id="summaryGuests">1</span> জন</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">সাবটোটাল</span>
                        <span class="font-semibold" id="summarySubtotal">৳0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">ছাড়</span>
                        <span class="font-semibold text-emerald-600" id="summaryDiscount">−৳0</span>
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
            const promoInput = document.getElementById('promo_code');
            const availabilityHint = document.getElementById('availabilityHint');

            const elUnit = document.getElementById('summaryUnitPrice');
            const elGuests = document.getElementById('summaryGuests');
            const elSubtotal = document.getElementById('summarySubtotal');
            const elTotal = document.getElementById('summaryTotal');
            const elDiscount = document.getElementById('summaryDiscount');

            const format = (n) => '৳' + n.toLocaleString('en-BD', { maximumFractionDigits: 2 });

            function selectedTour() {
                return tourSelect.options[tourSelect.selectedIndex];
            }

            function unitPrice() {
                const option = selectedTour();
                return option && option.dataset.price ? parseFloat(option.dataset.price) : 0;
            }

            function availableSeats() {
                const option = selectedTour();
                if (!option || option.value === '') return null;
                return parseInt(option.dataset.available, 10);
            }

            function render() {
                const price = unitPrice();
                let guests = parseInt(guestInput.value, 10);
                if (isNaN(guests) || guests < 1) guests = 1;
                guestInput.value = guests;

                const subtotal = Math.round(price * guests * 100) / 100;
                elUnit.textContent = format(price);
                elGuests.textContent = guests;
                elSubtotal.textContent = format(subtotal);
                elDiscount.textContent = '−' + format(0);
                elTotal.textContent = format(subtotal);

                const available = availableSeats();
                if (available === null) {
                    availabilityHint.textContent = 'ট্যুর নির্বাচন করলে ফাকা সিট দেখা যাবে।';
                    availabilityHint.className = 'mt-1.5 text-xs text-slate-500';
                    return;
                }

                if (available <= 0) {
                    availabilityHint.textContent = 'এই ট্যুরে কোনো ফাকা সিট নেই।';
                    availabilityHint.className = 'mt-1.5 text-xs text-red-500 font-semibold';
                } else if (guests > available) {
                    availabilityHint.textContent = 'ফাকা আছে মাত্র ' + available + ' সিট। যাত্রীর সংখ্যা কমান।';
                    availabilityHint.className = 'mt-1.5 text-xs text-red-500 font-semibold';
                } else {
                    availabilityHint.textContent = 'ফাকা সিট: ' + available + 'টি';
                    availabilityHint.className = 'mt-1.5 text-xs text-emerald-600 font-semibold';
                }
            }

            tourSelect.addEventListener('change', render);
            guestInput.addEventListener('input', render);
            promoInput.addEventListener('input', () => {
                elDiscount.textContent = '−' + format(0);
            });

            render();
        })();
    </script>
@endsection
