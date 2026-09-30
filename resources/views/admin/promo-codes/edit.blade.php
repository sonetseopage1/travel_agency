@extends('layouts.admin')

@section('title', 'Edit Promo Code')
@section('breadcrumb', 'Marketing / Promo Codes / Edit')
@section('page-title', 'প্রোমো কোড এডিট করুন')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-teal-700 text-white flex items-center justify-center font-mono text-sm font-bold uppercase">
            {{ Str::substr($promoCode->code, 0, 4) }}
        </div>
        <div>
            <h2 class="text-2xl font-extrabold font-mono">{{ $promoCode->code }}</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $promoCode->describeDiscount() }}</p>
        </div>
    </div>
    <a href="{{ route('admin.promo-codes.index') }}"
        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
        ← সব প্রোমো কোড
    </a>
</div>

<form method="POST" action="{{ route('admin.promo-codes.update', $promoCode) }}">
    @csrf
    @method('PUT')
    @include('admin.promo-codes._form', ['submitLabel' => 'আপডেট করুন'])
</form>

@endsection
