@extends('layouts.admin')

@section('title', 'New Blog Post')
@section('breadcrumb', 'Content / Blog / Create')
@section('page-title', 'নতুন ব্লগ পোস্ট')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">নতুন ব্লগ পোস্ট তৈরি করুন</h2>
        <p class="text-sm text-slate-500 mt-1">পোস্টটি সংরক্ষণ করলে স্ট্যাটাস অনুযায়ী সাইটে দেখানো হবে।</p>
    </div>
    <a href="{{ route('admin.blog.index') }}"
        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
        ← সব পোস্ট
    </a>
</div>

<form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
    @csrf
    @include('admin.blog._form', ['submitLabel' => 'পোস্ট সংরক্ষণ করুন'])
</form>

@endsection
