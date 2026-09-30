@extends('layouts.frontend')

@section('title', 'বুকিং করুন — ' . ($tour->title ?? 'ভ্রমণবিলাস'))

@section('content')

    <section class="bg-slate-900 text-white pt-32 pb-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold">বুকিং ফর্ম</h1>
            <p class="text-white/70 mt-2">নিচের ফর্মটি পূরণ করে জমা দিন</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2">
                <div
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">

                    @if (session('error'))
                        <div
                            class="mb-6 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm px-4 py-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bookings.store') }}" class="space-y-6">
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
                            <input id="guest_count" name="guest_count" type="number" min="1"
                                max="{{ max(($tour->total_seats ?? 30) - ($tour->current_booked ?? 0), 1) }}"
                                value="{{ old('guest_count', 1) }}" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                            <p class="text-xs text-slate-500 mt-2">
                                সর্বোচ্চ
                                {{ max(($tour->total_seats ?? 30) - ($tour->current_booked ?? 0), 1) }} জন বুক করা
                                যাবে
                            </p>
                            @error('guest_count')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
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
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-lg">
                    <img src="{{ $tour->image ?? '' }}" alt="{{ $tour->title ?? '' }}" class="w-full h-48 object-cover">
                    <div class="p-7">
                        <p class="text-sm text-teal-700 font-semibold">📍 {{ $tour->location ?? '' }}</p>
                        <h2 class="font-bold text-lg mt-2 leading-7">{{ $tour->title ?? '' }}</h2>

                        <dl class="mt-6 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">সময়কাল</dt>
                                <dd class="font-semibold">{{ $tour->duration_days ?? 0 }} দিন</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">প্রতি জন</dt>
                                <dd class="font-semibold">৳{{ number_format($tour->price_per_person ?? 0) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">যাত্রা</dt>
                                <dd class="font-semibold">
                                    {{ $tour->transport_icon ?? '🚌' }} {{ $tour->transport_type ?? 'Bus' }}
                                </dd>
                            </div>
                        </dl>

                        <div class="border-t border-slate-200 dark:border-slate-800 mt-6 pt-5 flex items-center justify-between">
                            <span class="font-semibold">সর্বমোট</span>
                            <span class="text-2xl font-extrabold text-teal-700">
                                ৳{{ number_format($tour->price_per_person ?? 0) }}
                            </span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

@endsection
