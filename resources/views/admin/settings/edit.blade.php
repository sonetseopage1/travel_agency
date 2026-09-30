@extends('layouts.admin')

@section('title', 'Settings')
@section('breadcrumb', 'System / Settings')
@section('page-title', 'সেটিংস')

@section('content')

@php $activeTab = session('tab', 'general'); @endphp

@if (session('success'))
    <div class="mb-5 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3">
        <p class="font-semibold mb-1">নিচের বিষয়গুলো ঠিক করতে হবে:</p>
        <ul class="list-disc ps-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-x-auto hide-scrollbar mb-6">
    <div class="flex min-w-max">
        @foreach (['general' => 'সাধারণ', 'branding' => 'লোগো ও ব্র্যান্ডিং', 'seo' => 'SEO ও মেটা', 'contact' => 'যোগাযোগ', 'social' => 'সোশ্যাল মিডিয়া', 'security' => 'পাসওয়ার্ড'] as $key => $label)
            <button type="button" data-tab="{{ $key }}"
                class="settings-tab px-5 py-4 border-b-2 text-sm font-semibold {{ $activeTab === $key ? 'tab-active border-b-teal-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700' }} {{ $key === 'security' ? 'ml-auto' : '' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>

<form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- ---------------- General ---------------- --}}
    <section data-tab-section="general"
        class="settings-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 mb-6 {{ $activeTab === 'general' ? '' : 'hidden' }}">
        <div class="mb-6">
            <h2 class="text-lg font-extrabold">সাইটের তথ্য</h2>
            <p class="text-xs text-slate-500 mt-1">সাইটের নাম ও পরিচিতি — হেডার, ফুটার ও টাইটেলে ব্যবহৃত হয়।</p>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            <div>
                <label class="label" for="site_name">সাইটের নাম <span class="text-red-500">*</span></label>
                <input id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" required class="input">
                @error('site_name')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label" for="site_tagline">ট্যাগলাইন</label>
                <input id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline'] ?? '') }}" class="input"
                    placeholder="Travel • Explore • Memories">
                <p class="mt-1.5 text-xs text-slate-500">লোগোর নিচে ছোট লেখা হিসেবে দেখাবে।</p>
            </div>

            <div class="md:col-span-2">
                <label class="label" for="site_intro">ফুটার পরিচিতি</label>
                <textarea id="site_intro" name="site_intro" rows="3" class="input resize-none"
                    placeholder="ফুটারে লোগোর নিচে যে পরিচিতি লেখা থাকবে...">{{ old('site_intro', $settings['site_intro'] ?? '') }}</textarea>
                <p class="mt-1.5 text-xs text-slate-500">ফুটারের লোগোর নিচে দেখানো হবে।</p>
                @error('site_intro')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="label" for="footer_note">কপিরাইট টেক্সট</label>
                <input id="footer_note" name="footer_note" value="{{ old('footer_note', $settings['footer_note'] ?? '') }}" class="input"
                    placeholder="All rights reserved.">
            </div>
        </div>
    </section>

    {{-- ---------------- Branding ---------------- --}}
    <section data-tab-section="branding"
        class="settings-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 mb-6 {{ $activeTab === 'branding' ? '' : 'hidden' }}">
        <div class="mb-6">
            <h2 class="text-lg font-extrabold">লোগো, ফেভিকন ও শেয়ার ইমেজ</h2>
            <p class="text-xs text-slate-500 mt-1">PNG, JPG, WEBP, SVG বা ICO — সর্বোচ্চ ৪ MB। ছাড়া খালি রাখলে ডিফল্ট চিহ্ন ব্যবহার হবে।</p>
        </div>

        <div class="grid md:grid-cols-3 gap-5">
            @include('admin.settings.partials.image-field', [
                'name' => 'logo',
                'label' => 'সাইট লোগো',
                'url' => $logoUrl,
                'hint' => 'হেডার ও ফুটারে দেখানো হবে।',
                'shape' => 'w-24 h-24',
            ])

            @include('admin.settings.partials.image-field', [
                'name' => 'favicon',
                'label' => 'ফেভিকন',
                'url' => $faviconUrl,
                'hint' => 'ব্রাউজার ট্যাবে দেখানো হবে।',
                'shape' => 'w-16 h-16',
            ])

            @include('admin.settings.partials.image-field', [
                'name' => 'og_image',
                'label' => 'OG শেয়ার ইমেজ',
                'url' => $ogImageUrl,
                'hint' => 'সোশ্যাল মিডিয়ায় লিংক শেয়ার করলে দেখাবে (১২০০x৬৩০ ভালো)।',
                'shape' => 'w-32 h-20',
            ])
        </div>
    </section>

    {{-- ---------------- SEO ---------------- --}}
    <section data-tab-section="seo"
        class="settings-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 mb-6 {{ $activeTab === 'seo' ? '' : 'hidden' }}">
        <div class="mb-6">
            <h2 class="text-lg font-extrabold">SEO ও মেটা তথ্য</h2>
            <p class="text-xs text-slate-500 mt-1">গুগল ও অন্যান্য সার্চ ইঞ্জিনের জন্য। খালি রাখলে সাইটের নাম ও পরিচিতি ব্যবহার হবে।</p>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="label" for="meta_title">মেটা টাইটেল</label>
                <input id="meta_title" name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}" class="input"
                    maxlength="180">
                <p class="mt-1.5 text-xs text-slate-500">সাধারণত ৫০-৬০ অক্ষর। <span id="metaTitleCount">0</span> / 180</p>
            </div>

            <div class="md:col-span-2">
                <label class="label" for="meta_description">মেটা বিবরণ</label>
                <textarea id="meta_description" name="meta_description" rows="3" maxlength="400" class="input resize-none">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                <p class="mt-1.5 text-xs text-slate-500">সাধারণত ১৫০-১৬০ অক্ষর। <span id="metaDescCount">0</span> / 400</p>
            </div>

            <div class="md:col-span-2">
                <label class="label" for="meta_keywords">মেটা কীওয়ার্ড</label>
                <input id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $settings['meta_keywords'] ?? '') }}" class="input"
                    placeholder="ট্রাভেল, ট্যুর প্যাকেজ, কক্সবাজার">
                <p class="mt-1.5 text-xs text-slate-500">কমা দিয়ে আলাদা করুন।</p>
            </div>

            <div class="md:col-span-2 border-t border-slate-200 dark:border-slate-800 pt-6">
                <h3 class="font-bold mb-1">সোশ্যাল শেয়ার (Open Graph)</h3>
                <p class="text-xs text-slate-500 mb-5">ফেসবুক/হোয়াটসঅ্যাপে লিংক শেয়ার করলে এই লেখা দেখাবে।</p>
            </div>

            <div class="md:col-span-2">
                <label class="label" for="og_title">OG টাইটেল</label>
                <input id="og_title" name="og_title" value="{{ old('og_title', $settings['og_title'] ?? '') }}" class="input" maxlength="180">
            </div>

            <div class="md:col-span-2">
                <label class="label" for="og_description">OG বিবরণ</label>
                <textarea id="og_description" name="og_description" rows="3" maxlength="400" class="input resize-none">{{ old('og_description', $settings['og_description'] ?? '') }}</textarea>
            </div>
        </div>
    </section>

    {{-- ---------------- Contact ---------------- --}}
    <section data-tab-section="contact"
        class="settings-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 mb-6 {{ $activeTab === 'contact' ? '' : 'hidden' }}">
        <div class="mb-6">
            <h2 class="text-lg font-extrabold">যোগাযোগের তথ্য</h2>
            <p class="text-xs text-slate-500 mt-1">ফুটার ও যোগাযোগ অংশে দেখানো হবে।</p>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="label" for="contact_address">ঠিকানা</label>
                <input id="contact_address" name="contact_address" value="{{ old('contact_address', $settings['contact_address'] ?? '') }}" class="input"
                    placeholder="গুলশান ১, ঢাকা ১২১২, বাংলাদেশ">
            </div>

            <div>
                <label class="label" for="contact_phone">ফোন নম্বর</label>
                <input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" class="input"
                    placeholder="+880 1700 000000">
            </div>

            <div>
                <label class="label" for="contact_email">ইমেইল</label>
                <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" class="input"
                    placeholder="hello@example.com">
                @error('contact_email')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </section>

    {{-- ---------------- Social ---------------- --}}
    <section data-tab-section="social"
        class="settings-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 mb-6 {{ $activeTab === 'social' ? '' : 'hidden' }}">
        <div class="mb-6">
            <h2 class="text-lg font-extrabold">সোশ্যাল মিডিয়া লিংক</h2>
            <p class="text-xs text-slate-500 mt-1">ফুটারে আইকন হিসেবে দেখাবে। যেগুলো খালি রাখবেন সেগুলো দেখানো হবে না।</p>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
            @foreach ([
        'social_facebook' => ['Facebook', 'https://facebook.com/yourpage'],
        'social_instagram' => ['Instagram', 'https://instagram.com/yourpage'],
        'social_youtube' => ['YouTube', 'https://youtube.com/@yourchannel'],
        'social_twitter' => ['X (Twitter)', 'https://x.com/yourhandle'],
        'social_linkedin' => ['LinkedIn', 'https://linkedin.com/company/yourcompany'],
    ] as $key => [$label, $placeholder])
                <div>
                    <label class="label" for="{{ $key }}">{{ $label }}</label>
                    <input id="{{ $key }}" name="{{ $key }}" type="url" value="{{ old($key, $settings[$key] ?? '') }}"
                        placeholder="{{ $placeholder }}" class="input">
                    @error($key)
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    </section>
</form>

{{-- ---------------- Security (separate form) ---------------- --}}
<section data-tab-section="security"
    class="settings-section bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-7 mb-6 {{ $activeTab === 'security' ? '' : 'hidden' }}">
    <div class="mb-6">
        <h2 class="text-lg font-extrabold">অ্যাডমিন পাসওয়ার্ড পরিবর্তন</h2>
        <p class="text-xs text-slate-500 mt-1">নিরাপত্তার জন্য নিয়মিত পাসওয়ার্ড বদলে ফেলুন।</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.password') }}" class="max-w-md space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="label" for="current_password">বর্তমান পাসওয়ার্ড <span class="text-red-500">*</span></label>
            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input">
            @error('current_password')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="label" for="new_password">নতুন পাসওয়ার্ড <span class="text-red-500">*</span></label>
            <input id="new_password" name="password" type="password" required minlength="8" autocomplete="new-password" class="input">
            <p class="mt-1.5 text-xs text-slate-500">অন্তত ৮ অক্ষর। সাধারণ পাসওয়ার্ড ব্যবহার করা যাবে না।</p>
            @error('password')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="label" for="new_password_confirmation">নতুন পাসওয়ার্ড আবার লিখুন <span class="text-red-500">*</span></label>
            <input id="new_password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="input">
        </div>

        <button type="submit"
            class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3 rounded-xl transition">
            পাসওয়ার্ড পরিবর্তন করুন
        </button>
    </form>
</section>

<div id="saveBar"
    class="sticky bottom-0 z-30 -mx-4 sm:mx-0 mt-6 px-4 sm:px-0 py-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-t border-slate-200 dark:border-slate-800 {{ $activeTab === 'security' ? 'hidden' : '' }}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <p class="text-xs text-slate-500">পরিবর্তনগুলো সব ট্যাবে প্রযোজ্য। সেভ করলে সাথে সাথেই সাইটে দেখা যাবে।</p>
        <div class="flex gap-2">
            <button type="button" form="settingsForm"
                class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                onclick="document.querySelectorAll('.settings-section input, .settings-section textarea').forEach(el => { if (el.type !== 'file' && !el.name.startsWith('remove_')) el.value = el.defaultValue ?? ''; });">
                রিসেট
            </button>
            <button type="submit" form="settingsForm"
                class="px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
                সেটিংস সংরক্ষণ করুন
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    (function () {
        const tabs = document.querySelectorAll('.settings-tab');
        const sections = document.querySelectorAll('.settings-section');
        const saveBar = document.getElementById('saveBar');

        function activate(name) {
            tabs.forEach((tab) => {
                const on = tab.dataset.tab === name;
                tab.classList.toggle('tab-active', on);
                tab.classList.toggle('font-bold', on);
                tab.classList.toggle('text-slate-500', !on);
                tab.classList.toggle('border-transparent', !on);
                tab.classList.toggle('border-b-teal-700', on);
            });

            sections.forEach((section) => {
                section.classList.toggle('hidden', section.dataset.tabSection !== name);
            });

            // The save bar submits the settings form, which the security tab excludes.
            saveBar.classList.toggle('hidden', name === 'security');
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activate(tab.dataset.tab));
        });

        // Live character counts for the SEO fields.
        const counters = [
            ['meta_title', 'metaTitleCount'],
            ['meta_description', 'metaDescCount'],
        ];

        counters.forEach(([fieldId, counterId]) => {
            const field = document.getElementById(fieldId);
            const counter = document.getElementById(counterId);
            if (!field || !counter) return;

            const update = () => {
                counter.textContent = field.value.length;
            };
            field.addEventListener('input', update);
            update();
        });
    })();
</script>
@endsection
