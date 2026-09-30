@extends('layouts.admin')

@section('title', 'Promo Codes')
@section('breadcrumb', 'Marketing')
@section('page-title', 'Promo Code Management')

@section('content')

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Total Promo Codes</p>
        <h3 class="text-2xl font-bold mt-2">{{ $promoCodes->total() }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Currently Active</p>
        <h3 class="text-2xl font-bold mt-2 text-emerald-600">{{ $activeCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Expired</p>
        <h3 class="text-2xl font-bold mt-2 text-red-600">{{ $expiredCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Total Redemptions</p>
        <h3 class="text-2xl font-bold mt-2 text-teal-600">{{ $usageCount }}</h3>
    </div>
</div>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">All Promo Codes</h2>
        <p class="text-sm text-slate-500 mt-1">Discount code তৈরি ও পরিচালনা করুন।</p>
    </div>
    <a href="{{ route('admin.promo-codes.create') }}"
        class="px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
        + নতুন প্রোমো কোড
    </a>
</div>

<form method="GET" action="{{ route('admin.promo-codes.index') }}"
    class="flex flex-col sm:flex-row gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Code or description..."
        class="input w-full sm:w-72">
    <select name="discount_type" class="input w-full sm:w-48">
        <option value="">সব ধরন</option>
        <option value="percentage" @selected(request('discount_type') === 'percentage')>Percentage</option>
        <option value="fixed" @selected(request('discount_type') === 'fixed')>Fixed Amount</option>
    </select>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 text-white text-sm font-semibold">
        ফিল্টার
    </button>
    @if (request('search') || request('discount_type'))
        <a href="{{ route('admin.promo-codes.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold">
            রিসেট
        </a>
    @endif
</form>

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr class="text-left text-slate-500">
                    <th class="px-5 py-4 font-medium">Code</th>
                    <th class="px-5 py-4 font-medium">Discount</th>
                    <th class="px-5 py-4 font-medium">Applies To</th>
                    <th class="px-5 py-4 font-medium">Date Range</th>
                    <th class="px-5 py-4 font-medium">Usage</th>
                    <th class="px-5 py-4 font-medium">Status</th>
                    <th class="px-5 py-4 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @forelse ($promoCodes as $promo)
                    @php
                        $reason = $promo->rejectionReason();
                        $statusLabel = $reason ?? 'Active';
                        $statusClass = $reason === null
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                            : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
                    @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-5 py-4">
                            <p class="font-mono font-bold text-teal-700">{{ $promo->code }}</p>
                            @if ($promo->description)
                                <p class="text-xs text-slate-500 mt-0.5">{{ $promo->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-semibold">{{ $promo->describeDiscount() }}</td>
                        <td class="px-5 py-4">
                            @if ($promo->tours_count === 0)
                                <span class="px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                    সব ট্যুর
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $promo->tours_count }}টি ট্যুর
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500">
                            @if ($promo->valid_from || $promo->valid_until)
                                {{ $promo->valid_from?->format('d M Y') ?? '—' }}
                                <span class="mx-1">→</span>
                                {{ $promo->valid_until?->format('d M Y') ?? '∞' }}
                            @else
                                সীমাহীন
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            {{ $promo->used_count }}{{ $promo->usage_limit ? ' / '.$promo->usage_limit : '' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}"
                                title="{{ $reason }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex gap-1">
                                <a href="{{ route('admin.promo-codes.edit', $promo) }}"
                                    class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold">Edit</a>
                                <form method="POST" action="{{ route('admin.promo-codes.destroy', $promo) }}"
                                    onsubmit="return confirm('এই প্রোমো কোডটি মুছে ফেলবেন?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                            কোনো প্রোমো কোড নেই।
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
        @forelse ($promoCodes as $promo)
            <div class="p-5">
                <div class="flex justify-between gap-3 mb-3">
                    <div>
                        <p class="font-mono font-bold text-teal-700">{{ $promo->code }}</p>
                        @if ($promo->description)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $promo->description }}</p>
                        @endif
                    </div>
                    <span class="px-3 py-1 h-fit rounded-full text-xs font-semibold {{ $promo->rejectionReason() === null ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $promo->rejectionReason() ?? 'Active' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-slate-500">Discount:</span> {{ $promo->describeDiscount() }}</div>
                    <div><span class="text-slate-500">Used:</span> {{ $promo->used_count }}{{ $promo->usage_limit ? ' / '.$promo->usage_limit : '' }}</div>
                    <div class="col-span-2">
                        <span class="text-slate-500">Applies:</span>
                        {{ $promo->tours_count === 0 ? 'সব ট্যুর' : $promo->tours_count.'টি ট্যুর' }}
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.promo-codes.edit', $promo) }}"
                        class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold">Edit</a>
                    <form method="POST" action="{{ route('admin.promo-codes.destroy', $promo) }}"
                        onsubmit="return confirm('এই প্রোমো কোডটি মুছে ফেলবেন?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-slate-500">কোনো প্রোমো কোড নেই।</div>
        @endforelse
    </div>
</div>

@if ($promoCodes->hasPages())
    <div class="mt-6">{{ $promoCodes->links() }}</div>
@endif

@endsection
