@extends('layouts.admin')

@section('title', 'Edit Blog Post')
@section('breadcrumb', 'Content / Blog / Edit')
@section('page-title', 'ব্লগ পোস্ট হালনাগাদ')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-4">
        <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}"
            class="w-14 h-14 rounded-2xl object-cover bg-slate-100 dark:bg-slate-800 shrink-0">
        <div>
            <h2 class="text-2xl font-extrabold">{{ $post->title }}</h2>
            <p class="text-sm text-slate-500 mt-1">
                /{{ $post->slug }} · {{ $post->isPublished() ? 'Published' : 'Draft' }}
            </p>
        </div>
    </div>
    <a href="{{ route('admin.blog.index') }}"
        class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
        ← সব পোস্ট
    </a>
</div>

<form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('admin.blog._form', ['submitLabel' => 'হালনাগাদ করুন'])
</form>

@endsection
