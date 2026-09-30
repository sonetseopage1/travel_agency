@extends('layouts.admin')

@section('title', 'New Promo Code')
@section('breadcrumb', 'Marketing / Promo Codes / Create')
@section('page-title', 'নতুন প্রোমো কোড')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">নতুন প্রোমো কোড তৈরি করুন</h2>
        <p class="text-sm text-slate-500 mt-1">Discount code সেট করুন এবং কোন ট্যুরে প্রযোজ্য তা বেছে নিন।</p>
    </div>
    <a href="{{ route('admin.promo-codes.index') }}"
        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
        ← সব প্রোমো কোড
    </a>
</div>

<form method="POST" action="{{ route('admin.promo-codes.store') }}">
    @csrf
    @include('admin.promo-codes._form', ['submitLabel' => 'তৈরি করুন'])
</form>

@endsection
