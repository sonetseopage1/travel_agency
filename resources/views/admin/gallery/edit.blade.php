@extends('layouts.admin')

@section('title', 'Edit Photo')
@section('breadcrumb', 'Content / Gallery / Edit')
@section('page-title', 'ছবির তথ্য হালনাগাদ')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-4">
        <img src="{{ $photo->image_url }}" alt="{{ $photo->alt_text ?: $photo->displayTitle() }}"
            class="w-14 h-14 rounded-2xl object-cover bg-slate-100 dark:bg-slate-800">
        <div>
            <h2 class="text-2xl font-extrabold">{{ $photo->displayTitle() ?: 'শিরোনামহীন ছবি' }}</h2>
            <p class="text-sm text-slate-500 mt-1">
                {{ $photo->destination?->name ?? 'গন্তব্য নির্ধারণ করা হয়নি' }}
            </p>
        </div>
    </div>
    <a href="{{ route('admin.gallery.index') }}"
        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
        ← সব ছবি
    </a>
</div>

<form method="POST" action="{{ route('admin.gallery.update', $photo) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.gallery._form', ['submitLabel' => 'হালনাগাদ করুন'])
</form>

@endsection
