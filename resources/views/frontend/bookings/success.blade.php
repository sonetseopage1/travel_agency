@extends('layouts.frontend')

@section('title', 'বুকিং সফল — '.\App\Models\Setting::string('site_name'))

@section('content')

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <div class="w-20 h-20 rounded-full bg-emerald-500 text-white flex items-center justify-center text-4xl mx-auto">✓</div>

        <h1 class="text-3xl sm:text-4xl font-extrabold mt-8">বুকিং সফল হয়েছে!</h1>
        <p class="text-slate-600 dark:text-slate-300 mt-4 leading-8">
            {{ session('success', 'আপনার বুকিং সফল হয়েছে! শীঘ্রই আমরা আপনার সাথে যোগাযোগ করব।') }}
        </p>

        <div class="mt-10 text-left bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
            <h2 class="text-xl font-extrabold mb-6">বুকিং সারসংক্ষেপ</h2>

            <dl class="space-y-4 text-sm">
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">বুকিং নম্বর</dt>
                    <dd class="font-semibold">#{{ $booking->id ?? 'BT-00000' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">ট্যুর</dt>
                    <dd class="font-semibold">{{ $booking->tour->title ?? '—' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">গ্রাহকের নাম</dt>
                    <dd class="font-semibold">{{ $booking->customer_name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">মোবাইল</dt>
                    <dd class="font-semibold">{{ $booking->customer_phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">যাত্রীর সংখ্যা</dt>
                    <dd class="font-semibold">{{ $booking->guest_count ?? 0 }} জন</dd>
                </div>
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">সাবটোটাল</dt>
                    <dd class="font-semibold">
                        ৳{{ number_format((float) ($booking->subtotal ?? 0) > 0 ? $booking->subtotal : ($booking->total_price ?? 0)) }}
                    </dd>
                </div>
                @if ((float) ($booking->discount_amount ?? 0) > 0)
                    <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <dt class="text-slate-500">
                            ছাড়
                            @if ($booking->promo_code)
                                <span class="font-mono text-emerald-600">({{ $booking->promo_code }})</span>
                            @endif
                        </dt>
                        <dd class="font-semibold text-emerald-600">
                            −৳{{ number_format($booking->discount_amount) }}
                        </dd>
                    </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-slate-500">সর্বমোট</dt>
                    <dd class="font-extrabold text-teal-700 text-lg">৳{{ number_format($booking->total_price ?? 0) }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-10 flex flex-wrap justify-center gap-4">
            <a href="{{ route('tours.index') }}"
                class="px-8 py-3.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-semibold transition">
                আরও ট্যুর দেখুন
            </a>
            <a href="{{ route('home') }}"
                class="px-8 py-3.5 rounded-xl border border-slate-300 dark:border-slate-700 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                হোমে ফিরুন
            </a>
        </div>
    </section>

@endsection
