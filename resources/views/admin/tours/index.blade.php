@extends('layouts.admin')

@section('title', 'Tours')
@section('breadcrumb', 'Management')
@section('page-title', 'Tours Management')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">All Tours</h2>
        <p class="text-sm text-slate-500 mt-1">সব tour package এখান থেকে manage করুন।</p>
    </div>
    <div class="flex flex-col sm:flex-row gap-2">
        <a href="{{ route('admin.tours.create') }}"
           class="px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-bold">
            + Add Tour
        </a>
    </div>
</div>

@php
    $hasFilter = request()->filled('search') || request()->filled('status') || request()->filled('category') || request()->filled('destination');
@endphp

<form method="GET" action="{{ route('admin.tours.index') }}"
    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 mb-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="small-label" for="tour_search">Search</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                <input id="tour_search" type="text" name="search" placeholder="Search tours..."
                    class="input pl-10" value="{{ request('search') }}">
            </div>
        </div>
        <div>
            <label class="small-label" for="tour_status">Status</label>
            <select id="tour_status" name="status" class="input">
                <option value="">All Status</option>
                @foreach (\App\Models\Tour::STATUSES as $option)
                    <option value="{{ $option }}" @selected(request('status') === $option)>
                        {{ ucfirst($option) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="small-label" for="tour_category">Category</label>
            <select id="tour_category" name="category" class="input">
                <option value="">All Categories</option>
                @foreach (\App\Models\Tour::CATEGORIES as $option)
                    <option value="{{ $option }}" @selected(request('category') === $option)>
                        {{ ucfirst($option) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="small-label" for="tour_destination">Destination</label>
            <select id="tour_destination" name="destination" class="input">
                <option value="">All Destinations</option>
                @foreach ($destinations as $option)
                    <option value="{{ $option }}" @selected(request('destination') === $option)>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit"
                class="flex-1 px-4 py-3 rounded-xl bg-slate-800 dark:bg-slate-700 text-white text-sm font-semibold hover:bg-slate-700 transition">
                Apply Filters
            </button>
            @if ($hasFilter)
                <a href="{{ route('admin.tours.index') }}" title="রিসেট"
                    class="px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    ✕
                </a>
            @endif
        </div>
    </div>
</form>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
        <p class="text-xs text-slate-500">Total Tours</p>
        <p class="text-xl font-bold mt-1">{{ $totalCount }}</p>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
        <p class="text-xs text-slate-500">Published</p>
        <p class="text-xl font-bold mt-1 text-emerald-600">{{ $publishedCount }}</p>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
        <p class="text-xs text-slate-500">Drafts</p>
        <p class="text-xl font-bold mt-1 text-amber-600">{{ $draftCount }}</p>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
        <p class="text-xs text-slate-500">Matching Filter</p>
        <p class="text-xl font-bold mt-1 text-teal-600">{{ $tours->total() }}</p>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr class="text-left text-slate-500">
                    <th class="px-5 py-4 font-medium">ID</th>
                    <th class="px-5 py-4 font-medium">Cover</th>
                    <th class="px-5 py-4 font-medium">Title</th>
                    <th class="px-5 py-4 font-medium">Destination</th>
                    <th class="px-5 py-4 font-medium">Price</th>
                    <th class="px-5 py-4 font-medium">Slots</th>
                    <th class="px-5 py-4 font-medium">Status</th>
                    <th class="px-5 py-4 font-medium">Featured</th>
                    <th class="px-5 py-4 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($tours as $tour)
                @php
                    $statusClass = match($tour->status) {
                        'published' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                        'draft' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                        'unpublished' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                        'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-5 py-4 font-medium">#{{ $tour->id }}</td>
                    <td class="px-5 py-4">
                        <img src="{{ $tour->image_url }}"
                             class="w-14 h-10 rounded-lg object-cover" alt="">
                    </td>
                    <td class="px-5 py-4 font-medium max-w-xs truncate">{{ $tour->title }}</td>
                    <td class="px-5 py-4">{{ $tour->destination }}</td>
                    <td class="px-5 py-4 font-semibold">৳ {{ number_format($tour->price_per_person ?? 0) }}</td>
                    <td class="px-5 py-4">{{ $tour->current_booked ?? 0 }} / {{ $tour->max_slots ?? 0 }}</td>
                    <td class="px-5 py-4">
                        <span class="px-3 py-1 rounded-full text-xs {{ $statusClass }}">{{ ucfirst($tour->status) }}</span>
                    </td>
                    <td class="px-5 py-4">
                        @if($tour->is_featured)
                            <span class="text-amber-500">⭐</span>
                        @else
                            <span class="text-slate-300">☆</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex gap-1">
                            <a href="{{ route('admin.tours.edit', $tour) }}"
                               class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 text-xs font-semibold">
                                Edit
                            </a>
                            <a href="#" class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300 text-xs font-semibold">
                                View
                            </a>
                            <form method="POST" action="{{ route('admin.tours.destroy', $tour) }}" onsubmit="return confirm('Delete this tour?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 dark:bg-red-500/10 text-xs font-semibold">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if(count($tours) === 0)
                <tr><td colspan="9" class="px-5 py-12 text-center text-slate-500">কোনো tour নেই। <a href="{{ route('admin.tours.create') }}" class="text-teal-700 font-semibold">প্রথমটি তৈরি করুন →</a></td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
        @foreach($tours as $tour)
        @php
            $statusClass = match($tour->status) {
                'published' => 'bg-emerald-100 text-emerald-700',
                'draft' => 'bg-slate-100 text-slate-700',
                default => 'bg-amber-100 text-amber-700',
            };
        @endphp
        <div class="p-5">
            <div class="flex gap-3">
                <img src="{{ $tour->image_url }}"
                     class="w-20 h-16 rounded-xl object-cover" alt="">
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <h4 class="font-semibold truncate">{{ $tour->title }}</h4>
                        <span class="text-xs px-2 py-1 rounded-full {{ $statusClass }} shrink-0">{{ ucfirst($tour->status) }}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">{{ $tour->destination }} • {{ $tour->current_booked ?? 0 }}/{{ $tour->max_slots ?? 0 }} slots</p>
                    <p class="text-sm font-bold text-teal-700 mt-2">৳ {{ number_format($tour->price_per_person ?? 0) }}</p>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <a href="{{ route('admin.tours.edit', $tour) }}" class="flex-1 px-3 py-2 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold text-center">Edit</a>
                <form method="POST" action="{{ route('admin.tours.destroy', $tour) }}" onsubmit="return confirm('Delete?');" class="flex-1">
                    @csrf @method('DELETE')
                    <button class="w-full px-3 py-2 rounded-lg bg-red-50 text-red-600 text-xs font-semibold">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
        @if(count($tours) === 0)
        <div class="p-12 text-center text-slate-500">কোনো tour নেই।</div>
        @endif
    </div>

    @if(method_exists($tours, 'links'))
    <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800">
        {{ $tours->links() }}
    </div>
    @endif
</div>

@endsection
