@extends('layouts.frontend')

@php
    // Blade's inline @section form cannot reliably parse the nested quotes a
    // ternary needs, so the value is built first.
    $pageHeading = ($booking->is_approved ? 'বুকিং কনফার্মড' : 'বুকিং আবেদন গ্রহণ');
@endphp

@section('title', $pageHeading.' — '.\App\Models\Setting::string('site_name'))

@section('content')

    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        {{-- Submitted and approved are deliberately different states. Right
             after submitting nothing is confirmed, and saying "সফল হয়েছে"
             reads as though the seat is already secured. --}}
        @if ($booking->is_approved)
            <div class="w-20 h-20 rounded-full bg-emerald-500 text-white flex items-center justify-center text-4xl mx-auto">✓</div>
            <h1 class="text-3xl sm:text-4xl font-extrabold mt-8">আপনার বুকিং নিশ্চিত হয়েছে!</h1>
            <p class="text-slate-600 dark:text-slate-300 mt-4 leading-8">
                নিচে থেকে আপনার রসিদটি ডাউনলোড করে রাখতে পারেন। ভ্রমণের তারিখের আগে যেকোনো সময়
                এই পেজটি দিয়ে আপনার বুকিং যাচাই করতে পারবেন।
            </p>
        @else
            <div class="w-20 h-20 rounded-full bg-amber-500 text-white flex items-center justify-center text-4xl mx-auto">✓</div>
            <h1 class="text-3xl sm:text-4xl font-extrabold mt-8">আবেদনটি গ্রহণ করা হয়েছে</h1>
            <p class="text-slate-600 dark:text-slate-300 mt-4 leading-8">
                {{ session('success', 'আপনার বুকিং আবেদনটি সফলভাবে জমা হয়েছে। আমাদের প্রতিনিধি আপনার সাথে খুব দ্রুত যোগাযোগ করবেন।') }}
            </p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-4 max-w-xl mx-auto">
                আপনার আবেদনটি এখনো অনুমোদনের অপেক্ষায় আছে। আমাদের প্রতিনিধি আপনার সাথে যোগাযোগ করে
                অনুমোদন জানাবেন এবং রসিদের লিংকটি আপনাকে দিয়ে দেবেন।
            </p>
        @endif

        @if ($booking->is_approved)
            <div class="mt-8">
                <a href="{{ route('bookings.receipt.download', ['token' => $booking->receiptToken()]) }}"
                    class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition">
                    <span aria-hidden="true">⬇</span> রসিদ ডাউনলোড করুন (PDF)
                </a>
            </div>
        @endif

        <div class="mt-10 text-left bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
            <h2 class="text-xl font-extrabold mb-6">বুকিং সারসংক্ষেপ</h2>

            <dl class="space-y-4 text-sm">
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">বুকিং নম্বর</dt>
                    <dd class="font-semibold">#{{ $booking->id ?? 'BT-00000' }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">বর্তমান অবস্থা</dt>
                    <dd class="font-semibold">
                        @if ($booking->is_approved)
                            <span class="text-emerald-600">অনুমোদিত</span>
                        @else
                            <span class="text-amber-600">অনুমোদনের অপেক্ষায়</span>
                        @endif
                    </dd>
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
                    <dt class="text-slate-500">মোট যাত্রী</dt>
                    <dd class="font-semibold">
                        {{ $booking->guest_count ?? 0 }} জন
                        @if ($booking->pricing_tier_type ?? null)
                            <span class="text-xs text-slate-500">({{ ucfirst($booking->pricing_tier_type) }})</span>
                        @endif
                    </dd>
                </div>

                {{-- The party split, so a customer can see why the total is what
                     it is. Adults are counted; each child is listed with its age. --}}
                @if (($booking->adult_count ?? 0) > 0 || ($booking->child_count ?? 0) > 0 || ($booking->infant_count ?? 0) > 0)
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <dt class="text-slate-500">প্রাপ্তবয়স্ক</dt>
                            <dd class="font-semibold">{{ $booking->adult_count ?? 0 }} জন</dd>
                        </div>
                        @if (($booking->child_count ?? 0) > 0)
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-500">শিশু</dt>
                                <dd class="font-semibold">{{ $booking->child_count }} জন</dd>
                            </div>
                        @endif
                        @if (($booking->infant_count ?? 0) > 0)
                            <div class="flex justify-between text-sm">
                                <dt class="text-slate-500">বিনামূল্যে শিশু</dt>
                                <dd class="font-semibold">{{ $booking->infant_count }} জন</dd>
                            </div>
                        @endif
                        @foreach ($booking->guests ?? [] as $child)
                            <div class="flex justify-between text-xs text-slate-500">
                                <dt>
                                    শিশু {{ $child->sort_order + 1 }} (বয়স {{ $child->age }})
                                    @if ($child->type === 'infant')
                                        · বিনামূল্যে
                                    @endif
                                </dt>
                                <dd>৳{{ number_format($child->line_total) }}</dd>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (($booking->cabin_count ?? 0) > 0)
                    <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <dt class="text-slate-500">কেবিন</dt>
                        <dd class="font-semibold">
                            {{ $booking->cabin_count }}টি
                            @if ((float) $booking->extra_cabin_amount > 0)
                                <span class="text-emerald-600">(৳{{ number_format($booking->extra_cabin_amount) }})</span>
                            @endif
                        </dd>
                    </div>
                @endif

                <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <dt class="text-slate-500">যাত্রীর খরচ</dt>
                    <dd class="font-semibold">৳{{ number_format($booking->subtotal ?? 0) }}</dd>
                </div>

                @if ((float) ($booking->tier_discount_amount ?? 0) > 0)
                    <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <dt class="text-slate-500">ছাড়</dt>
                        <dd class="font-semibold text-emerald-600">−৳{{ number_format($booking->tier_discount_amount) }}</dd>
                    </div>
                @endif

                @if ((float) ($booking->discount_amount ?? 0) > 0)
                    <div class="flex justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <dt class="text-slate-500">
                            প্রোমো ছাড়
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
