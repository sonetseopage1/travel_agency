@extends('layouts.admin')

@section('title', 'Gallery')
@section('breadcrumb', 'Content')
@section('page-title', 'Gallery Management')

@section('content')

@if (session('success'))
    <div class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
        {{ session('success') }}
    </div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Total Photos</p>
        <h3 class="text-2xl font-bold mt-2">{{ $totalCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Visible on Site</p>
        <h3 class="text-2xl font-bold mt-2 text-emerald-600">{{ $activeCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Hidden</p>
        <h3 class="text-2xl font-bold mt-2 text-slate-500">{{ $totalCount - $activeCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Featured</p>
        <h3 class="text-2xl font-bold mt-2 text-teal-600">{{ $featuredCount }}</h3>
    </div>
</div>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">All Photos</h2>
        <p class="text-sm text-slate-500 mt-1">সাইটের গ্যালারি সেকশনে প্রদর্শিত ছবিগুলো পরিচালনা করুন।</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('gallery.index') }}" target="_blank" rel="noopener"
            class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
            সাইটে দেখুন ↗
        </a>
        <a href="{{ route('admin.gallery.create') }}"
            class="px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
            + নতুন ছবি
        </a>
    </div>
</div>

<form method="GET" action="{{ route('admin.gallery.index') }}"
    class="flex flex-col sm:flex-row gap-3 mb-6">
    <select name="destination_id" class="input w-full sm:w-64">
        <option value="">সব গন্তব্য</option>
        @foreach ($destinations as $destination)
            <option value="{{ $destination->id }}" @selected((string) request('destination_id') === (string) $destination->id)>
                {{ $destination->name }}
            </option>
        @endforeach
    </select>
    <label class="flex items-center gap-2 px-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-sm font-semibold cursor-pointer">
        <input type="checkbox" name="featured" value="1" @checked(request()->boolean('featured'))
            class="w-4 h-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
        শুধু ফিচার্ড
    </label>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 text-white text-sm font-semibold">
        ফিল্টার
    </button>
    @if (request('destination_id') || request('featured'))
        <a href="{{ route('admin.gallery.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold">
            রিসেট
        </a>
    @endif
</form>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
    @forelse ($photos as $photo)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
            <a href="{{ route('gallery.show', $photo) }}" target="_blank" rel="noopener" class="block aspect-square bg-slate-100 dark:bg-slate-800">
                <img src="{{ $photo->image_url }}" alt="{{ $photo->alt_text ?: $photo->displayTitle() }}" loading="lazy"
                    class="w-full h-full object-cover">
            </a>
            <div class="p-4">
                <p class="font-bold text-sm leading-6 line-clamp-2 min-h-12">
                    {{ $photo->displayTitle() ?: 'শিরোনামহীন ছবি' }}
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $photo->destination?->name ?? 'গন্তব্য নির্ধারণ করা হয়নি' }}
                </p>
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    @if ($photo->is_featured)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-100 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
                            ফিচার্ড
                        </span>
                    @endif
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $photo->is_active
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                        {{ $photo->is_active ? 'প্রকাশিত' : 'লুকানো' }}
                    </span>
                    <span class="text-xs text-slate-400">অর্ডার: {{ $photo->sort_order }}</span>
                </div>
                <div class="flex gap-2 mt-4">
                    <a href="{{ route('admin.gallery.edit', $photo) }}"
                        class="flex-1 text-center px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold hover:bg-blue-100 transition">
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $photo) }}"
                        onsubmit="return confirm('এই ছবিটি মুছে ফেলবেন?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl py-16 text-center">
            <p class="text-5xl mb-4">🖼️</p>
            <p class="text-slate-500">কোনো ছবি নেই।</p>
            <a href="{{ route('admin.gallery.create') }}"
                class="inline-block mt-6 px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
                প্রথম ছবি যোগ করুন
            </a>
        </div>
    @endforelse
</div>

@if ($photos->hasPages())
    <div class="mt-6">{{ $photos->links() }}</div>
@endif

@endsection
