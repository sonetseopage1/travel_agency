@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Overview')
@section('page-title', 'Dashboard')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-7">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold">শুভ সকাল, Admin 👋</h1>
        <p class="mt-1 text-slate-500 dark:text-slate-400">আজকের tour business-এর অবস্থা দেখে নাও।</p>
    </div>
    <a href="{{ route('admin.tours.create') }}"
       class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-semibold shadow-lg shadow-teal-700/20">
        + নতুন Tour
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Total Tours</p>
                <h3 class="text-3xl font-bold mt-2">{{ count($tours) }}</h3>
                <p class="text-sm text-emerald-600 mt-2">↑ 12% এই মাসে</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-xl">🗺️</div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Total Bookings</p>
                <h3 class="text-3xl font-bold mt-2">{{ count($bookings) }}</h3>
                <p class="text-sm text-emerald-600 mt-2">↑ 18.4% এই মাসে</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center text-xl">📅</div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Customers</p>
                <h3 class="text-3xl font-bold mt-2">{{ $customers }}</h3>
                <p class="text-sm text-emerald-600 mt-2">↑ 9.8% এই মাসে</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center text-xl">👥</div>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">Total Revenue</p>
                <h3 class="text-3xl font-bold mt-2">৳ {{ number_format($revenue) }}</h3>
                <p class="text-sm text-emerald-600 mt-2">↑ 22.5% এই মাসে</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-xl">💰</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mt-5">
    <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
            <div>
                <h3 class="font-bold text-lg">Revenue Overview</h3>
                <p class="text-sm text-slate-500">গত ৬ মাসের revenue</p>
            </div>
            <select class="px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                <option>Last 6 Months</option>
                <option>This Year</option>
                <option>Last Year</option>
            </select>
        </div>
        <div class="h-[300px]">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <div class="mb-5">
            <h3 class="font-bold text-lg">Booking Status</h3>
            <p class="text-sm text-slate-500">এই মাসের booking</p>
        </div>
        <div class="h-[220px]">
            <canvas id="bookingChart"></canvas>
        </div>
        <div class="grid grid-cols-2 gap-3 mt-5">
            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10">
                <p class="text-xs text-slate-500">Confirmed</p>
                <p class="font-bold text-emerald-600">{{ $confirmedCount }}</p>
            </div>
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10">
                <p class="text-xs text-slate-500">Pending</p>
                <p class="font-bold text-amber-600">{{ $pendingCount }}</p>
            </div>
            <div class="p-3 rounded-xl bg-red-50 dark:bg-red-500/10">
                <p class="text-xs text-slate-500">Cancelled</p>
                <p class="font-bold text-red-600">{{ $cancelledCount }}</p>
            </div>
            <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-500/10">
                <p class="text-xs text-slate-500">Completed</p>
                <p class="font-bold text-blue-600">{{ $completedCount }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mt-5">
    <div class="xl:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-lg">Upcoming Tours</h3>
                <p class="text-sm text-slate-500">সামনে যেসব tour আছে</p>
            </div>
            <a href="{{ route('admin.tours.index') }}" class="text-sm text-teal-700 font-semibold">সব দেখুন →</a>
        </div>
        <div class="divide-y divide-slate-200 dark:divide-slate-800">
            @foreach($upcomingTours as $tour)
            <div class="p-5 flex flex-col sm:flex-row gap-4">
                @php
                    $img = $tour->image_url;
                    $slotPercent = $tour->max_slots > 0 ? (($tour->current_booked ?? 0) / $tour->max_slots) * 100 : 0;
                    if ($tour->status === 'published' && $slotPercent >= 90) {
                        $badgeClass = 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400';
                        $badgeText = 'Almost Full';
                    } elseif ($tour->status === 'published') {
                        $badgeClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400';
                        $badgeText = 'Confirmed';
                    } else {
                        $badgeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400';
                        $badgeText = 'Open';
                    }
                @endphp
                <img src="{{ $img }}" class="w-full sm:w-28 h-24 object-cover rounded-xl" alt="Tour">
                <div class="flex-1">
                    <div class="flex flex-wrap gap-2 items-center">
                        <h4 class="font-semibold">{{ $tour->title }}</h4>
                        <span class="text-xs px-2 py-1 rounded-full {{ $badgeClass }}">{{ $badgeText }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mt-1">
                        📅 {{ \Carbon\Carbon::parse($tour->departure_date)->format('d M Y') }}
                        &nbsp; • &nbsp;
                        📍 Dhaka → {{ $tour->destination }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm">
                        <span>👥 {{ $tour->current_booked ?? 0 }} / {{ $tour->max_slots ?? 30 }} Slots</span>
                        <span class="text-teal-700 font-semibold">৳ {{ number_format($tour->price_per_person ?? 0) }} / person</span>
                    </div>
                </div>
                <a href="{{ route('admin.tours.edit', $tour) }}" class="self-start px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 text-sm">View</a>
            </div>
            @endforeach
            @if(count($upcomingTours) === 0)
            <div class="p-8 text-center text-slate-500">কোনো upcoming tour নেই।</div>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl">
        <div class="p-5 border-b border-slate-200 dark:border-slate-800">
            <h3 class="font-bold text-lg">Pending Actions</h3>
            <p class="text-sm text-slate-500">তোমার attention দরকার</p>
        </div>
        <div class="p-5 space-y-4">
            <a href="{{ route('admin.bookings.index') }}" class="flex gap-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10">
                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">⏳</div>
                <div>
                    <p class="font-semibold">Pending Bookings</p>
                    <p class="text-sm text-slate-500 mt-1">{{ $pendingCount }}টি booking confirmation-এর অপেক্ষায়</p>
                </div>
            </a>
            <a href="{{ route('admin.tours.index') }}" class="flex gap-4 p-4 rounded-xl bg-red-50 dark:bg-red-500/10">
                <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-500/20 flex items-center justify-center">⚠️</div>
                <div>
                    <p class="font-semibold">Low Slot Tours</p>
                    <p class="text-sm text-slate-500 mt-1">3টি tour প্রায় full হয়ে গেছে</p>
                </div>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="flex gap-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-500/10">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center">⭐</div>
                <div>
                    <p class="font-semibold">New Reviews</p>
                    <p class="text-sm text-slate-500 mt-1">7টি নতুন review-এর reply দরকার</p>
                </div>
            </a>
            <a href="#" class="flex gap-4 p-4 rounded-xl bg-purple-50 dark:bg-purple-500/10">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-500/20 flex items-center justify-center">🖼️</div>
                <div>
                    <p class="font-semibold">Missing Media</p>
                    <p class="text-sm text-slate-500 mt-1">2টি tour-এর cover image নেই</p>
                </div>
            </a>
        </div>
    </div>
</div>

<div class="mt-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
    <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="font-bold text-lg">Recent Bookings</h3>
            <p class="text-sm text-slate-500">সর্বশেষ customer bookings</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="text-sm font-semibold text-teal-700">সব Booking দেখুন →</a>
    </div>

    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr class="text-left text-slate-500">
                    <th class="px-5 py-4 font-medium">Customer</th>
                    <th class="px-5 py-4 font-medium">Tour</th>
                    <th class="px-5 py-4 font-medium">Date</th>
                    <th class="px-5 py-4 font-medium">Guests</th>
                    <th class="px-5 py-4 font-medium">Amount</th>
                    <th class="px-5 py-4 font-medium">Status</th>
                    <th class="px-5 py-4 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($recentBookings as $booking)
                @php
                    $statusClass = match($booking->status) {
                        'confirmed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                        'completed' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                        default => 'bg-slate-100 text-slate-700',
                    };
                    $initials = strtoupper(substr(($booking->customer_name ?? 'CU'), 0, 2));
                    $colors = ['bg-pink-100 text-pink-600', 'bg-blue-100 text-blue-600', 'bg-purple-100 text-purple-600', 'bg-amber-100 text-amber-600'];
                    $color = $colors[array_rand($colors)];
                @endphp
                <tr>
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full {{ $color }} flex items-center justify-center font-semibold">{{ $initials }}</div>
                            <div>
                                <p class="font-medium">{{ $booking->customer_name ?? 'Customer' }}</p>
                                <p class="text-xs text-slate-500">{{ $booking->customer_email ?? '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">{{ $booking->tour?->title ?? 'N/A' }}</td>
                    <td class="px-5 py-4">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</td>
                    <td class="px-5 py-4">{{ $booking->guests_count ?? 1 }}</td>
                    <td class="px-5 py-4 font-semibold">৳ {{ number_format($booking->total_price ?? 0) }}</td>
                    <td class="px-5 py-4">
                        <span class="px-3 py-1 rounded-full text-xs {{ $statusClass }}">{{ ucfirst($booking->status ?? 'Pending') }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="text-teal-700 font-medium">View</a>
                    </td>
                </tr>
                @endforeach
                @if(count($recentBookings) === 0)
                <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">কোনো booking নেই।</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-slate-200 dark:divide-slate-800">
        @foreach($recentBookings as $booking)
        @php
            $statusClass = match($booking->status) {
                'confirmed' => 'bg-emerald-100 text-emerald-700',
                'pending' => 'bg-amber-100 text-amber-700',
                'cancelled' => 'bg-red-100 text-red-700',
                default => 'bg-slate-100 text-slate-700',
            };
        @endphp
        <div class="p-5">
            <div class="flex justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ $booking->customer_name ?? 'Customer' }}</p>
                    <p class="text-sm text-slate-500">{{ $booking->tour?->title ?? 'N/A' }}</p>
                </div>
                <span class="text-xs px-2 py-1 h-fit rounded-full {{ $statusClass }}">{{ ucfirst($booking->status ?? 'Pending') }}</span>
            </div>
            <div class="mt-3 flex justify-between text-sm">
                <span>{{ $booking->guests_count ?? 1 }} Guests</span>
                <span class="font-semibold">৳ {{ number_format($booking->total_price ?? 0) }}</span>
            </div>
        </div>
        @endforeach
        @if(count($recentBookings) === 0)
        <div class="p-8 text-center text-slate-500">কোনো booking নেই।</div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="font-bold text-lg">Top Performing Tours</h3>
                <p class="text-sm text-slate-500">Booking অনুযায়ী</p>
            </div>
        </div>
        <div class="space-y-5">
            @php $barColors = ['bg-teal-700', 'bg-purple-600', 'bg-blue-600', 'bg-emerald-500']; @endphp
            @foreach($topTours as $idx => $tour)
            @php
                $maxSlots = $tour->max_slots ?: 1;
                $booked = $tour->current_booked ?: 0;
                $percent = min(100, round(($booked / $maxSlots) * 100));
            @endphp
            <div>
                <div class="flex justify-between mb-2">
                    <span class="font-medium">{{ $tour->title }}</span>
                    <span class="text-sm text-slate-500">{{ $percent }}%</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full {{ $barColors[$idx % 4] }}" style="width: {{ $percent }}%"></div>
                </div>
            </div>
            @endforeach
            @if(count($topTours) === 0)
            <div class="text-center text-slate-500 py-4">তথ্য নেই</div>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5">
        <h3 class="font-bold text-lg">Quick Actions</h3>
        <p class="text-sm text-slate-500 mb-5">দ্রুত কাজগুলো এখান থেকে করো</p>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('admin.tours.create') }}" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-teal-600 hover:text-teal-700 transition">
                <div class="text-2xl mb-2">🗺️</div>
                <p class="font-semibold">Add Tour</p>
                <p class="text-xs text-slate-500 mt-1">নতুন tour তৈরি করুন</p>
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-teal-600 hover:text-teal-700 transition">
                <div class="text-2xl mb-2">📅</div>
                <p class="font-semibold">Add Booking</p>
                <p class="text-xs text-slate-500 mt-1">Booking manage করুন</p>
            </a>
            <a href="#" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-teal-600 hover:text-teal-700 transition">
                <div class="text-2xl mb-2">📊</div>
                <p class="font-semibold">View Reports</p>
                <p class="text-xs text-slate-500 mt-1">Business report দেখুন</p>
            </a>
            <a href="#" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-teal-600 hover:text-teal-700 transition">
                <div class="text-2xl mb-2">📤</div>
                <p class="font-semibold">Export CSV</p>
                <p class="text-xs text-slate-500 mt-1">Data export করুন</p>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-teal-600 hover:text-teal-700 transition">
                <div class="text-2xl mb-2">⭐</div>
                <p class="font-semibold">Manage Reviews</p>
                <p class="text-xs text-slate-500 mt-1">Review approve করুন</p>
            </a>
            <a href="#" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-teal-600 hover:text-teal-700 transition">
                <div class="text-2xl mb-2">📧</div>
                <p class="font-semibold">Bulk Email</p>
                <p class="text-xs text-slate-500 mt-1">Customer-দের email পাঠান</p>
            </a>
            <a href="#" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-teal-600 hover:text-teal-700 transition">
                <div class="text-2xl mb-2">⚙️</div>
                <p class="font-semibold">Settings</p>
                <p class="text-xs text-slate-500 mt-1">System config করুন</p>
            </a>
            <a href="#" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-teal-600 hover:text-teal-700 transition">
                <div class="text-2xl mb-2">🖼️</div>
                <p class="font-semibold">Media Library</p>
                <p class="text-xs text-slate-500 mt-1">Photos manage করুন</p>
            </a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const revenueCtx = document.getElementById('revenueChart');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            datasets: [{
                label: 'Revenue',
                data: [180000, 240000, 210000, 320000, 410000, 520000],
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                borderColor: '#0F766E',
                backgroundColor: 'rgba(15, 118, 110, 0.1)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.05)' : '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });

    const bookingCtx = document.getElementById('bookingChart');
    new Chart(bookingCtx, {
        type: 'doughnut',
        data: {
            labels: ['Confirmed', 'Pending', 'Cancelled', 'Completed'],
            datasets: [{
                data: [{{ $confirmedCount }}, {{ $pendingCount }}, {{ $cancelledCount }}, {{ $completedCount }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 11 } } } },
            cutout: '65%'
        }
    });
</script>
@endsection
