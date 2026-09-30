@extends('layouts.admin')

@section('title', 'Bookings')
@section('breadcrumb', 'Management')
@section('page-title', 'Bookings Management')

@section('content')

@if (session('success'))
    <div class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-sm px-4 py-3">
        {{ session('success') }}
    </div>
@endif

@php
    $hasFilter = request()->filled('search') || request()->filled('status') || request()->filled('payment_status') || request()->filled('tour_id');
@endphp

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Total Bookings</p>
        <h3 class="text-2xl font-bold mt-2">{{ $totalCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Confirmed</p>
        <h3 class="text-2xl font-bold mt-2 text-emerald-600">{{ $confirmedCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Pending</p>
        <h3 class="text-2xl font-bold mt-2 text-amber-600">{{ $pendingCount }}</h3>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <p class="text-sm text-slate-500">Cancelled</p>
        <h3 class="text-2xl font-bold mt-2 text-red-600">{{ $cancelledCount }}</h3>
    </div>
</div>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold">All Bookings</h2>
        <p class="text-sm text-slate-500 mt-1">সব booking এখান থেকে manage করুন।</p>
    </div>
    <a href="{{ route('admin.bookings.create') }}"
        class="px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold whitespace-nowrap transition">
        + নতুন বুকিং
    </a>
</div>

<form method="GET" action="{{ route('admin.bookings.index') }}"
    class="flex flex-col sm:flex-row gap-3 mb-6">
    <input type="text" name="search" value="{{ request('search') }}"
        placeholder="Search customer, phone or tour..."
        class="input w-full sm:w-72">
    <select name="status" class="input w-full sm:w-40">
        <option value="">All Status</option>
        @foreach (\App\Models\Booking::STATUSES as $option)
            <option value="{{ $option }}" @selected(request('status') === $option)>
                {{ ucfirst($option) }}
            </option>
        @endforeach
    </select>
    <select name="payment_status" class="input w-full sm:w-40">
        <option value="">All Payments</option>
        @foreach (\App\Models\Booking::PAYMENT_STATUSES as $option)
            <option value="{{ $option }}" @selected(request('payment_status') === $option)>
                {{ ucfirst($option) }}
            </option>
        @endforeach
    </select>
    <select name="tour_id" class="input w-full sm:w-56">
        <option value="">All Tours</option>
        @foreach ($tours as $tour)
            <option value="{{ $tour->id }}" @selected((string) request('tour_id') === (string) $tour->id)>
                {{ $tour->title }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 text-white text-sm font-semibold">
        ফিল্টার
    </button>
    @if ($hasFilter)
        <a href="{{ route('admin.bookings.index') }}"
            class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
            রিসেট
        </a>
    @endif
</form>

@if ($hasFilter)
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
        ফিল্টার অনুযায়ী {{ $bookings->total() }}টি বুকিং পাওয়া গেছে।
    </p>
@endif

<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr class="text-left text-slate-500">
                    <th class="px-5 py-4 font-medium">ID</th>
                    <th class="px-5 py-4 font-medium">Customer</th>
                    <th class="px-5 py-4 font-medium">Tour</th>
                    <th class="px-5 py-4 font-medium">Guests</th>
                    <th class="px-5 py-4 font-medium">Amount</th>
                    <th class="px-5 py-4 font-medium">Status</th>
                    <th class="px-5 py-4 font-medium">Payment</th>
                    <th class="px-5 py-4 font-medium">Date</th>
                    <th class="px-5 py-4 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($bookings as $booking)
                @php
                    $statusClass = match($booking->status) {
                        'confirmed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                        'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                        default => 'bg-slate-100 text-slate-700',
                    };
                    $payClass = match($booking->payment_status) {
                        'paid' => 'bg-emerald-100 text-emerald-700',
                        'partial' => 'bg-amber-100 text-amber-700',
                        'unpaid' => 'bg-red-100 text-red-700',
                        'refunded' => 'bg-slate-200 text-slate-700',
                        default => 'bg-slate-100 text-slate-700',
                    };
                    $initials = strtoupper(substr(($booking->customer_name ?? 'CU'), 0, 2));
                @endphp
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-5 py-4 font-medium">#{{ $booking->id }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-teal-100 text-teal-700 flex items-center justify-center font-semibold">{{ $initials }}</div>
                            <div>
                                <p class="font-medium">{{ $booking->customer_name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $booking->customer_email ?? '-' }} • {{ $booking->customer_phone ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">{{ $booking->tour?->title ?? 'N/A' }}</td>
                    <td class="px-5 py-4">{{ $booking->guest_count ?? 1 }}</td>
                    <td class="px-5 py-4 font-semibold">৳ {{ number_format($booking->total_price ?? 0) }}</td>
                    <td class="px-5 py-4">
                        <span class="px-3 py-1 rounded-full text-xs {{ $statusClass }}">{{ ucfirst($booking->status ?? 'Pending') }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="px-3 py-1 rounded-full text-xs {{ $payClass }}">{{ ucfirst($booking->payment_status ?? 'Unpaid') }}</span>
                    </td>
                    <td class="px-5 py-4 text-slate-500">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M, Y') }}</td>
                    <td class="px-5 py-4">
                        <div class="flex gap-1">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold">View</a>
                            <form method="POST" action="{{ route('admin.bookings.status', $booking->id) }}">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()"
                                    class="px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs">
                                    @foreach (\App\Models\Booking::STATUSES as $option)
                                        <option value="{{ $option }}" @selected(($booking->status ?? 'pending') === $option)>
                                            {{ ucfirst($option) }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if(count($bookings) === 0)
                <tr><td colspan="9" class="px-5 py-12 text-center text-slate-500">কোনো booking নেই।</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
        @foreach($bookings as $booking)
        @php
            $statusClass = match($booking->status) {
                'confirmed' => 'bg-emerald-100 text-emerald-700',
                'pending' => 'bg-amber-100 text-amber-700',
                'cancelled' => 'bg-red-100 text-red-700',
                default => 'bg-slate-100 text-slate-700',
            };
        @endphp
        <div class="p-5">
            <div class="flex justify-between gap-3 mb-3">
                <div>
                    <p class="font-semibold">{{ $booking->customer_name ?? 'Customer' }}</p>
                    <p class="text-sm text-slate-500">{{ $booking->tour?->title ?? 'N/A' }}</p>
                </div>
                <span class="text-xs px-2 py-1 h-fit rounded-full {{ $statusClass }}">{{ ucfirst($booking->status ?? 'Pending') }}</span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-slate-500">Guests:</span> {{ $booking->guest_count ?? 1 }}</div>
                <div><span class="text-slate-500">Date:</span> {{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</div>
            </div>
            <div class="mt-3 flex justify-between items-center">
                <span class="text-lg font-bold text-teal-700">৳ {{ number_format($booking->total_price ?? 0) }}</span>
                <a href="{{ route('admin.bookings.show', $booking) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold">View Details</a>
            </div>
        </div>
        @endforeach
        @if(count($bookings) === 0)
        <div class="p-12 text-center text-slate-500">কোনো booking নেই।</div>
        @endif
    </div>
</div>

@if ($bookings->hasPages())
    <div class="mt-6">{{ $bookings->links() }}</div>
@endif

@endsection
