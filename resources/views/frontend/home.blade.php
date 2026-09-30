@extends('layouts.frontend')

@section('title', 'ভ্রমণবিলাস — বাংলাদেশের সেরা ট্যুর ও ট্রাভেল প্যাকেজ')

@section('content')

    <section class="relative h-screen min-h-[600px] flex items-center overflow-hidden">
        <img src="{{ asset('images/photo-1507525428034-b723cf961d3e.jpg') }}"
            alt="Hero" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 hero-overlay"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="glass rounded-3xl px-6 sm:px-10 py-10 sm:py-14 max-w-2xl text-white">
                <span
                    class="inline-block px-4 py-1.5 rounded-full bg-white/20 text-sm font-semibold mb-6">
                    ✈ বাংলাদেশ ও বিদেশ ভ্রমণ প্যাকেজ
                </span>
                <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight">
                    সেরা ভ্রমণ অভিজ্ঞতা<br>
                    এখন আরও সহজ
                </h1>
                <p class="mt-5 text-white/85 text-base sm:text-lg leading-8">
                    পরিবার, বন্ধু বা একা — আপনার পছন্দমতো ট্যুর বেছে নিন। Transport, accommodation এবং
                    guide সবকিছু একসাথে, স্বচ্ছ মূল্যে।
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('tours.index') }}"
                        class="px-6 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 font-semibold transition">
                        ট্যুর দেখুন
                    </a>
                    <a href="{{ route('home') }}#contact"
                        class="px-6 py-3 rounded-xl bg-white/15 hover:bg-white/25 font-semibold transition">
                        যোগাযোগ করুন
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-14">
            <h2 class="text-3xl sm:text-4xl font-extrabold">জনপ্রিয় ট্যুর প্যাকেজ</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-3">আমাদের সবচেয়ে বেশি বুক হওয়া ভ্রমণ প্যাকেজগুলো</p>
        </div>

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

        <div class="text-center mt-12">
            <a href="{{ route('tours.index') }}"
                class="inline-block px-8 py-3.5 rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-semibold hover:opacity-90 transition">
                সব ট্যুর দেখুন
            </a>
        </div>
    </section>

    <section id="destinations" class="bg-white dark:bg-slate-900 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold">জনপ্রিয় গন্তব্য</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-3">যেখানে আমাদের ভ্রমণীরা সবচেয়ে বেশি যান</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($destinations as $destination)
                    <div class="group relative h-64 rounded-2xl overflow-hidden cursor-pointer">
                        <img src="{{ $destination->image_url }}" alt="{{ $destination->name ?? '' }}"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                        <div class="absolute bottom-0 p-6 text-white">
                            <h3 class="text-xl font-bold">{{ $destination->name ?? '' }}</h3>
                            <p class="text-sm text-white/80">{{ $destination->category ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="about" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-14 items-center">
            <div>
                <h2 class="text-3xl sm:text-4xl font-extrabold">আমাদের সম্পর্কে</h2>
                <p class="text-slate-600 dark:text-slate-300 mt-5 leading-8">
                    ভ্রমণবিলাস একটি বাংলাদেশি ট্রাভেল অপারেটর। আমরা মানুষকে সহজ, নিরাপদ এবং স্মরণীয় ভ্রমণ
                    উপভোগ করিয়ে দেওয়াই আমাদের লক্ষ্য। প্রতিটি ট্যুর আমাদের টিম কর্তৃক পরীক্ষিত এবং
                    customer-এর জন্য নিরাপদ হিসেবে তৈরি।
                </p>
                <div class="grid grid-cols-3 gap-6 mt-10">
                    <div>
                        <div class="text-3xl font-extrabold text-teal-700">150+</div>
                        <div class="text-sm text-slate-500 mt-1">সফল ট্যুর</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-teal-700">10K+</div>
                        <div class="text-sm text-slate-500 mt-1">ভ্রমণী</div>
                    </div>
                    <div>
                        <div class="text-3xl font-extrabold text-teal-700">4.9</div>
                        <div class="text-sm text-slate-500 mt-1">গড় রেটিং</div>
                    </div>
                </div>
            </div>
            <img src="{{ asset('images/photo-1469474968028-56623f02e42e.jpg') }}"
                alt="About" class="rounded-3xl w-full h-96 object-cover">
        </div>
    </section>

    <section class="bg-slate-100 dark:bg-slate-900 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold">সফল ভ্রমণের গল্প</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-3">আমাদের ভ্রমণীদের অভিজ্ঞতা</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @foreach ($reviews as $review)
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-7 shadow-sm">
                        <div class="text-amber-500 text-lg">★★★★★</div>
                        <p class="mt-4 text-slate-600 dark:text-slate-300 leading-8">
                            "{{ $review->comment ?? '' }}"
                        </p>
                        <div class="mt-6 flex items-center gap-3">
                            <div
                                class="w-11 h-11 rounded-full bg-teal-700 text-white flex items-center justify-center font-bold">
                                {{ mb_substr($review->name ?? '?', 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold">{{ $review->name ?? '' }}</div>
                                <div class="text-xs text-slate-500">{{ $review->tour_name ?? '' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contact" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-14">
            <div>
                <h2 class="text-3xl sm:text-4xl font-extrabold">যোগাযোগ করুন</h2>
                <p class="text-slate-600 dark:text-slate-300 mt-5 leading-8">
                    কোনো প্রশ্ন বা বিশেষ ভ্রমণ পরিকল্পনা আছে? আমাদের টিমের সাথে যোগাযোগ করুন —
                    আমরা দ্রুত উত্তর দেব।
                </p>
                <div class="space-y-4 mt-8 text-slate-600 dark:text-slate-300">
                    <p>📍 ঢাকা, বাংলাদেশ</p>
                    <p>📞 +880 1700 000000</p>
                    <p>✉️ hello@bongotraveller.com</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-8">
                @if (session('success'))
                    <div
                        class="mb-5 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5">
                    @csrf
                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label for="contact_name" class="block text-sm font-semibold mb-2">আপনার নাম</label>
                            <input id="contact_name" name="name" type="text" value="{{ old('name') }}" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-semibold mb-2">মোবাইল</label>
                            <input id="contact_phone" name="phone" type="text" value="{{ old('phone') }}"
                                class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="contact_email" class="block text-sm font-semibold mb-2">ইমেইল</label>
                        <input id="contact_email" name="email" type="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="contact_message" class="block text-sm font-semibold mb-2">বার্তা</label>
                        <textarea id="contact_message" name="message" rows="5" required
                            class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3.5 rounded-xl transition">
                        বার্তা পাঠান
                    </button>
                </form>
            </div>
        </div>
    </section>

@endsection
