@extends('layouts.frontend')

@section('title', 'রেজিস্ট্রেশন — '.\App\Models\Setting::string('site_name'))

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 pt-28 pb-16">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 p-8">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-teal-700 text-white flex items-center justify-center text-2xl mx-auto">✈</div>
                <h1 class="text-2xl font-extrabold mt-4">অ্যাকাউন্ট তৈরি করুন</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">দ্রুত ও সহজে রেজিস্টার করুন</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold mb-2">পুরো নাম</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold mb-2">ইমেইল</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-semibold mb-2">মোবাইল নম্বর</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                    @error('phone')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold mb-2">পাসওয়ার্ড</label>
                    <input id="password" name="password" type="password" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold mb-2">পাসওয়ার্ড নিশ্চিত করুন</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                </div>

                <button type="submit"
                    class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3 rounded-xl transition">
                    রেজিস্টার করুন
                </button>
            </form>

            <p class="text-sm text-center mt-6 text-slate-500 dark:text-slate-400">
                ইতিমধ্যে অ্যাকাউন্ট আছে?
                <a href="{{ route('login') }}" class="text-teal-700 font-semibold hover:underline">লগইন করুন</a>
            </p>
        </div>
    </div>
@endsection
