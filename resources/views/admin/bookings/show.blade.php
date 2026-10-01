@extends('layouts.admin')

@section('title', 'Booking Details')
@section('breadcrumb', 'Bookings / Details')
@section('page-title', 'বুকিং বিস্তারিত')

@section('content')

@php
    $statusClass = match ($booking->status) {
        'confirmed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
        'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    };
    $payClass = match ($booking->payment_status) {
        'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'partial' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        'unpaid' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
        default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    };
    $initials = strtoupper(substr($booking->customer_name ?? 'CU', 0, 2));
@endphp

@if (session('success'))
    <div class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
        {{ session('success') }}
    </div>
@endif

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
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-teal-700 text-white flex items-center justify-center text-xl font-bold">
            {{ $initials }}
        </div>
        <div>
            <h2 class="text-2xl font-extrabold">Booking #{{ $booking->id }}</h2>
            <p class="text-sm text-slate-500 mt-1">
                {{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y, h:i A') }}
            </p>
        </div>
    </div>
    <a href="{{ route('admin.bookings.index') }}"
        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
        ← সব বুকিং
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-5">গ্রাহকের তথ্য</h3>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                <div>
                    <dt class="small-label">পুরো নাম</dt>
                    <dd class="font-semibold">{{ $booking->customer_name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="small-label">ইমেইল</dt>
                    <dd class="font-semibold">{{ $booking->customer_email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="small-label">মোবাইল নম্বর</dt>
                    <dd class="font-semibold">{{ $booking->customer_phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="small-label">মোট যাত্রী</dt>
                    <dd class="font-semibold">
                        {{ $booking->guest_count ?? 1 }} জন
                        @if ($booking->pricing_tier_type ?? null)
                            <span class="text-xs text-slate-500">({{ ucfirst($booking->pricing_tier_type) }})</span>
                        @endif
                    </dd>
                </div>
                @if ($booking->pricing_tier_type ?? null)
                    <div>
                        <dt class="small-label">প্রাপ্তবয়স্ক / শিশু / ফ্রি শিশু</dt>
                        <dd class="font-semibold">
                            {{ $booking->adult_count ?? 0 }} / {{ $booking->child_count ?? 0 }} /
                            {{ $booking->infant_count ?? 0 }}
                        </dd>
                    </div>
                    <div>
                        <dt class="small-label">কেবিন</dt>
                        <dd class="font-semibold">
                            {{ $booking->cabin_count ?? 0 }}টি
                            @if ((float) $booking->extra_cabin_amount > 0)
                                <span class="text-emerald-600">(৳{{ number_format($booking->extra_cabin_amount) }})</span>
                            @endif
                        </dd>
                    </div>
                @endif
            </dl>

            @if ($booking->guests->isNotEmpty())
                <div class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                    <p class="small-label mb-2">শিশুদের বয়স</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($booking->guests as $child)
                            <span
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold
                                    {{ $child->type === 'infant' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                বয়স {{ $child->age }} · ৳{{ number_format($child->line_total) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-5">ট্যুরের তথ্য</h3>
            @if ($booking->tour)
                <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                    <div class="sm:col-span-2">
                        <dt class="small-label">ট্যুর</dt>
                        <dd class="font-semibold">
                            <a href="{{ route('tours.show', $booking->tour->slug) }}" target="_blank" rel="noopener"
                                class="text-teal-700 hover:underline">
                                {{ $booking->tour->title }} ↗
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="small-label">গন্তব্য</dt>
                        <dd class="font-semibold">{{ $booking->tour->location ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="small-label">ভ্রমণের তারিখ</dt>
                        <dd class="font-semibold">
                            {{ $booking->tour->departure_date ? \Carbon\Carbon::parse($booking->tour->departure_date)->format('d M Y') : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="small-label">বুকিংয়ের সময় প্রাপ্তবয়স্কর হার</dt>
                        <dd class="font-semibold">
                            ৳ {{ number_format($booking->adult_rate ?? $booking->tour->price_per_person ?? 0) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="small-label">যাত্রা</dt>
                        <dd class="font-semibold">
                            {{ $booking->tour->transport_icon ?? '🚌' }} {{ $booking->tour->transport_type ?? '—' }}
                        </dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-slate-500">এই বুকিংয়ের সাথে কোনো ট্যুর যুক্ত নেই।</p>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold text-lg mb-5">পেমেন্ট ও বিশেষ নোট</h3>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                <div>
                    <dt class="small-label">পেমেন্ট মেথড</dt>
                    <dd class="font-semibold">{{ $booking->payment_method ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="small-label">ট্রানজেকশন আইডি</dt>
                    <dd class="font-semibold">{{ $booking->transaction_id ?: '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="small-label">বিশেষ অনুরোধ</dt>
                    <dd class="font-semibold leading-7">{{ $booking->special_notes ?: 'কোনো বিশেষ অনুরোধ নেই।' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <aside class="space-y-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold mb-5">সারসংক্ষেপ</h3>

            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-slate-500">স্ট্যাটাস</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                    {{ ucfirst($booking->status ?? 'Pending') }}
                </span>
            </div>
            <div class="flex items-center justify-between mb-5">
                <span class="text-sm text-slate-500">পেমেন্ট</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $payClass }}">
                    {{ ucfirst($booking->payment_status ?? 'Unpaid') }}
                </span>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-800 pt-5">
                <p class="text-sm text-slate-500">সর্বমোট</p>
                <p class="text-3xl font-extrabold text-teal-700 mt-1">
                    ৳{{ number_format($booking->total_price ?? 0) }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
            <h3 class="font-bold mb-4">স্ট্যাটাস ও পেমেন্ট আপডেট করুন</h3>
            <form method="POST" action="{{ route('admin.bookings.status', $booking->id) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="status" class="label">বুকিং স্ট্যাটাস</label>
                    <select id="status" name="status" class="input" required>
                        @foreach (['pending', 'confirmed', 'cancelled', 'completed'] as $option)
                            <option value="{{ $option }}" @selected(($booking->status ?? 'pending') === $option)>
                                {{ ucfirst($option) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="payment_status" class="label">পেমেন্ট স্ট্যাটাস</label>
                    <select id="payment_status" name="payment_status" class="input" required>
                        @foreach (['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid'] as $option => $label)
                            <option value="{{ $option }}" @selected(($booking->payment_status ?? 'unpaid') === $option)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="payment_method" class="label">পেমেন্ট মেথড</label>
                    <select id="payment_method" name="payment_method" class="input">
                        <option value="">পরিবর্তন না</option>
                        @foreach (['Cash', 'bKash', 'Nagad', 'Rocket', 'Card', 'Bank Transfer'] as $method)
                            <option value="{{ $method }}" @selected(($booking->payment_method ?? '') === $method)>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="transaction_id" class="label">ট্রানজেকশন আইডি</label>
                    <input id="transaction_id" name="transaction_id" value="{{ $booking->transaction_id }}"
                        class="input" placeholder="পরিবর্তন না">
                </div>

                <div>
                    <label for="admin_note" class="label">প্রতিষ্ঠানের নোট (রসিদে দেখাবে)</label>
                    <textarea id="admin_note" name="admin_note" rows="3"
                        class="input" placeholder="যেমন: বিকাশে পেমেন্ট পাওয়া গেছে">{{ $booking->admin_note }}</textarea>
                </div>

                <p class="text-xs text-slate-500 leading-6">
                    বুকিং বাতিল করলে ট্যুরের সিট স্বয়ংক্রিয়ভাবে ফেরত আসবে।
                </p>

                <button type="submit"
                    class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-2.5 rounded-xl transition">
                    আপডেট করুন
                </button>
            </form>

            {{-- The receipt link is released on approval. Sticking the status
                 at confirmed or completed stamps the approval, and this block
                 appears with the link to hand to the customer. --}}
            <div class="mt-5 pt-5 border-t border-slate-200 dark:border-slate-800">
                <h4 class="font-bold text-sm mb-3">রসিদ ও গ্রাহকের লিংক</h4>

                @if ($booking->is_approved)
                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 mb-3">
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                            ✓ অনুমোদিত{{ $booking->approved_at ? ' — '.$booking->approved_at->format('d M Y, h:i A') : '' }}
                        </p>
                    </div>

                    <label for="receiptLink" class="label">গ্রাহককে এই লিংকটি দিন</label>
                    <div class="flex gap-2">
                        <input id="receiptLink" readonly value="{{ $booking->receiptUrl() }}"
                            class="input font-mono text-xs" onclick="this.select()">
                        <button type="button" id="copyReceipt"
                            class="shrink-0 px-3 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            কপি
                        </button>
                    </div>
                    <p id="copyReceiptDone" class="hidden mt-2 text-xs text-emerald-600 font-semibold">লিংক কপি হয়েছে।</p>

                    <div class="flex gap-2 mt-3">
                        <a href="{{ route('bookings.receipt', ['token' => $booking->receiptToken()]) }}"
                            target="_blank" rel="noopener"
                            class="flex-1 text-center px-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            গ্রাহকের পেজ দেখুন
                        </a>
                        <a href="{{ route('bookings.receipt.download', ['token' => $booking->receiptToken()]) }}"
                            class="flex-1 text-center px-3 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold transition">
                            PDF রসিদ
                        </a>
                    </div>
                @else
                    <p class="text-xs text-slate-500 leading-6">
                        স্ট্যাটাস <span class="font-semibold">Confirmed</span> বা
                        <span class="font-semibold">Completed</span> করলে এই বুকিং অনুমোদিত হিসেবে চিহ্নিত হবে
                        এবং গ্রাহকের জন্য রসিদ ডাউনলোড লিংক তৈরি হবে।
                    </p>
                @endif
            </div>
        </div>
    </aside>
</div>

@section('scripts')
    <script>
        (function () {
            const copyBtn = document.getElementById('copyReceipt');
            const link = document.getElementById('receiptLink');
            const done = document.getElementById('copyReceiptDone');
            if (!copyBtn || !link) return;

            copyBtn.addEventListener('click', async () => {
                // Selecting the field is the fallback, because the async
                // clipboard API needs a secure context and admin is often
                // reached over plain http on a local network.
                try {
                    await navigator.clipboard.writeText(link.value);
                } catch (e) {
                    link.removeAttribute('readonly');
                    link.select();
                    document.execCommand('copy');
                    link.setAttribute('readonly', 'readonly');
                }

                copyBtn.textContent = 'কপি হয়েছে';
                done.classList.remove('hidden');
                window.setTimeout(() => {
                    copyBtn.textContent = 'কপি';
                    done.classList.add('hidden');
                }, 2000);
            });
        })();
    </script>
@endsection

@endsection
