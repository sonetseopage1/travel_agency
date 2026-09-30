@extends('layouts.frontend')

@section('title', 'লগইন — ভ্রমণবিলাস')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 pt-28 pb-16">
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-800 p-8">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-teal-700 text-white flex items-center justify-center text-2xl mx-auto">✈</div>
                <h1 class="text-2xl font-extrabold mt-4">লগইন করুন</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">আপনার অ্যাকাউন্টে প্রবেশ করতে তথ্য দিন</p>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold mb-2">ইমেইল</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 outline-none focus:ring-2 focus:ring-teal-600">
                    @error('email')
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

                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <input type="checkbox" name="remember" value="1"
                        class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                    আমাকে মনে রাখুন
                </label>

                <button type="submit"
                    class="w-full bg-teal-700 hover:bg-teal-800 text-white font-semibold py-3 rounded-xl transition">
                    লগইন করুন
                </button>
            </form>

            <p class="text-sm text-center mt-6 text-slate-500 dark:text-slate-400">
                অ্যাকাউন্ট নেই?
                <a href="{{ route('register') }}" class="text-teal-700 font-semibold hover:underline">রেজিস্টার করুন</a>
            </p>
        </div>
    </div>
@endsection
