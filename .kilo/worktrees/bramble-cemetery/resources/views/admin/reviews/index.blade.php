@extends('layouts.admin')

@section('title', 'Reviews')
@section('breadcrumb', 'Management')
@section('page-title', 'Reviews Management')

@section('content')

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Total Reviews</p>
        <h3 class="text-2xl font-bold mt-2">{{ count($reviews) }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Approved</p>
        <h3 class="text-2xl font-bold mt-2 text-emerald-600">{{ $approvedCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Pending</p>
        <h3 class="text-2xl font-bold mt-2 text-amber-600">{{ $pendingCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Avg Rating</p>
        <h3 class="text-2xl font-bold mt-2 text-amber-500">⭐ {{ number_format($avgRating, 1) }}</h3>
    </div>
</div>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">All Reviews</h2>
        <p class="text-sm text-slate-500 mt-1">Customer reviews এবং ratings manage করুন।</p>
    </div>
    <div class="flex gap-2">
        <input type="text" placeholder="Search..." class="input w-full sm:w-64">
        <select class="input w-full sm:w-40">
            <option>All</option>
            <option>Pending</option>
            <option>Approved</option>
        </select>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr class="text-left text-slate-500">
                    <th class="px-5 py-4 font-medium">ID</th>
                    <th class="px-5 py-4 font-medium">Customer</th>
                    <th class="px-5 py-4 font-medium">Tour</th>
                    <th class="px-5 py-4 font-medium">Rating</th>
                    <th class="px-5 py-4 font-medium">Comment</th>
                    <th class="px-5 py-4 font-medium">Approved</th>
                    <th class="px-5 py-4 font-medium">Created</th>
                    <th class="px-5 py-4 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($reviews as $review)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-5 py-4 font-medium">#{{ $review->id }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-semibold">
                                {{ strtoupper(substr(($review->customer_name ?? 'CU'), 0, 1)) }}
                            </div>
                            <span class="font-medium">{{ $review->customer_name ?? '-' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">{{ $review->tour?->title ?? 'N/A' }}</td>
                    <td class="px-5 py-4">
                        <span class="text-amber-500">
                            @for($i=1;$i<=5;$i++)
                                @if($i <= ($review->rating ?? 0)) ⭐ @else ☆ @endif
                            @endfor
                        </span>
                        <span class="ml-1 font-semibold">({{ $review->rating ?? 0 }}/5)</span>
                    </td>
                    <td class="px-5 py-4 max-w-xs truncate" title="{{ $review->comment }}">{{ $review->comment ?? '-' }}</td>
                    <td class="px-5 py-4">
                        <form method="POST" action="{{ route('admin.reviews.toggle', $review->id) }}">
                            @csrf
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $review->is_approved ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-700"></div>
                            </label>
                        </form>
                    </td>
                    <td class="px-5 py-4 text-slate-500">{{ \Carbon\Carbon::parse($review->created_at)->format('d M, Y') }}</td>
                    <td class="px-5 py-4">
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review->id) }}" onsubmit="return confirm('Delete this review?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 dark:bg-red-500/10 text-xs font-semibold">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if(count($reviews) === 0)
                <tr><td colspan="8" class="px-5 py-12 text-center text-slate-500">কোনো review নেই।</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
        @foreach($reviews as $review)
        <div class="p-5">
            <div class="flex justify-between gap-3 mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-semibold">
                        {{ strtoupper(substr(($review->customer_name ?? 'CU'), 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold">{{ $review->customer_name ?? 'Customer' }}</p>
                        <p class="text-xs text-slate-500">{{ $review->tour?->title ?? 'N/A' }}</p>
                    </div>
                </div>
                <span class="text-amber-500 text-sm">⭐ {{ $review->rating ?? 0 }}</span>
            </div>
            <p class="text-sm text-slate-600 dark:text-slate-300 mb-3">{{ $review->comment ?? 'No comment' }}</p>
            <div class="flex justify-between items-center text-xs text-slate-500">
                <span>{{ \Carbon\Carbon::parse($review->created_at)->format('d M Y') }}</span>
                <div class="flex gap-2 items-center">
                    <form method="POST" action="{{ route('admin.reviews.toggle', $review->id) }}" class="inline">
                        @csrf
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $review->is_approved ? 'checked' : '' }}>
                            <div class="w-9 h-5 bg-slate-200 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-teal-700"></div>
                        </label>
                    </form>
                    <form method="POST" action="{{ route('admin.reviews.destroy', $review->id) }}" onsubmit="return confirm('Delete?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1 rounded-lg bg-red-50 text-red-600 font-semibold">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
        @if(count($reviews) === 0)
        <div class="p-12 text-center text-slate-500">কোনো review নেই।</div>
        @endif
    </div>
</div>

@endsection
