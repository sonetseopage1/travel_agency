@extends('layouts.admin')

@section('title', 'New Photo')
@section('breadcrumb', 'Content / Gallery / Create')
@section('page-title', 'গ্যালারিতে নতুন ছবি')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">নতুন ছবি যোগ করুন</h2>
        <p class="text-sm text-slate-500 mt-1">আপলোড করা ছবি সাইটের গ্যালারি সেকশনে দেখানো হবে।</p>
    </div>
    <a href="{{ route('admin.gallery.index') }}"
        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
        ← সব ছবি
    </a>
</div>

<form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data">
    @csrf
    @include('admin.gallery._form', ['submitLabel' => 'ছবি সংরক্ষণ করুন'])
</form>

@endsection
