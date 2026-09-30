<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') | ভ্রমণবিলাস</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        bangla: ['Noto Sans Bengali', 'sans-serif']
                    },
                    colors: {
                        teal: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0F766E',
                            800: '#115e59',
                            950: '#042f2e',
                        }
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Noto Sans Bengali', sans-serif; }
        .sidebar { transition: transform .25s ease; }
        .overlay { transition: opacity .25s ease; }
        .tab-active { color: #0f766e; border-color: #0f766e; background: #f0fdfa; }
        .dark .tab-active { background: rgba(15, 118, 110, .15); }
        .label { display: block; font-size: .875rem; font-weight: 600; margin-bottom: .5rem; }
        .small-label { display: block; font-size: .7rem; color: #64748b; margin-bottom: .35rem; font-weight: 600; }
        .dark .small-label { color: #94a3b8; }
        .input { width: 100%; border-radius: .75rem; border: 1px solid #e2e8f0; background: #fff; padding: .75rem 1rem; font-size: .875rem; outline: none; transition: all .15s; }
        .input:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15, 118, 110, .1); }
        .dark .input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
        .glass { backdrop-filter: blur(12px); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100">

<div id="sidebarOverlay" class="overlay fixed inset-0 bg-black/50 z-40 hidden opacity-0 lg:hidden"></div>

<aside id="sidebar" class="sidebar fixed left-0 top-0 bottom-0 w-72 bg-slate-950 text-white z-50 -translate-x-full lg:translate-x-0">

    <div class="h-20 flex items-center px-6 border-b border-white/10">
        <div class="w-10 h-10 rounded-xl bg-teal-700 flex items-center justify-center text-xl">✈</div>
        <div class="ml-3">
            <div class="font-extrabold">ভ্রমণবিলাস</div>
            <div class="text-[10px] text-slate-400">ADMIN PANEL</div>
        </div>
        <button id="closeSidebar" class="ml-auto lg:hidden text-xl text-slate-400 hover:text-white">×</button>
    </div>

    <nav class="p-4 space-y-1">
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Main</p>

        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700 text-white' : '' }}">
            <span>📊</span> Dashboard
        </a>

        <a href="{{ route('admin.tours.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.tours.*') ? 'bg-teal-700 text-white' : '' }}">
            <span>🧳</span> Tours
            @php $tourCount = \App\Models\Tour::count(); @endphp
            @if($tourCount > 0)
            <span class="ml-auto text-xs bg-white/10 px-2 py-1 rounded-full">{{ $tourCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.bookings.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.bookings.*') ? 'bg-teal-700 text-white' : '' }}">
            <span>📅</span> Bookings
            @php $pendingBookings = \App\Models\Booking::where('status', 'pending')->count(); @endphp
            @if($pendingBookings > 0)
            <span class="ml-auto text-xs bg-amber-500/20 text-amber-400 px-2 py-1 rounded-full">{{ $pendingBookings }}</span>
            @endif
        </a>

        <a href="{{ route('admin.reviews.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5 {{ request()->routeIs('admin.reviews.*') ? 'bg-teal-700 text-white' : '' }}">
            <span>⭐</span> Reviews
        </a>

        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5">
            <span>👥</span> Travellers / Customers
        </a>

        <a href="{{ route('home') }}" target="_blank" rel="noopener"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5">
            <span>🌐</span> View Main Site
            <span class="ml-auto text-xs text-slate-500">↗</span>
        </a>

        <p class="px-3 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">System</p>

        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5">
            <span>📊</span> Reports
        </a>

        <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:text-white hover:bg-white/5">
            <span>⚙️</span> Settings
        </a>
    </nav>

    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-teal-700 flex items-center justify-center font-bold">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-bold truncate">{{ auth()->check() ? auth()->user()->name : 'Admin' }}</div>
                <div class="text-xs text-slate-500 truncate">{{ auth()->check() ? auth()->user()->email : 'admin@bromonbilash.com' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="hidden" id="logout-form">@csrf</form>
            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-slate-400 hover:text-white text-sm" title="Logout">🚪</button>
        </div>
    </div>
</aside>

<div class="lg:ml-72 min-h-screen">

    <header class="sticky top-0 z-30 h-20 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800">
        <div class="h-full px-4 sm:px-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="openSidebar" class="lg:hidden w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">☰</button>
                <div>
                    <div class="text-xs text-slate-500">@yield('breadcrumb', 'Dashboard')</div>
                    <h1 class="text-lg sm:text-xl font-extrabold">@yield('page-title', 'Dashboard')</h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" target="_blank" rel="noopener"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
                    <span>🌐</span>
                    <span class="hidden sm:inline">View Site</span>
                </a>
                <button id="themeToggle" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">☀️</button>
                <button class="hidden sm:flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-sm font-semibold">
                    <span class="w-6 h-6 rounded-full bg-teal-700 flex items-center justify-center text-xs text-white">
                        {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A' }}
                    </span>
                    Admin
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-[1500px] mx-auto px-4 sm:px-6 py-6 pb-32">
        @yield('content')
    </main>

</div>

@yield('mobile-actions')

<script>
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', () => {
        document.documentElement.classList.toggle('dark');
        const dark = document.documentElement.classList.contains('dark');
        localStorage.setItem('theme', dark ? 'dark' : 'light');
        themeToggle.innerHTML = dark ? '🌙' : '☀️';
    });
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark');
        themeToggle.innerHTML = '🌙';
    }

    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openSidebarBtn = document.getElementById('openSidebar');
    const closeSidebarBtn = document.getElementById('closeSidebar');

    function showSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        setTimeout(() => overlay.classList.remove('opacity-0'), 10);
    }
    function hideSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0');
        setTimeout(() => overlay.classList.add('hidden'), 250);
    }
    openSidebarBtn.addEventListener('click', showSidebar);
    closeSidebarBtn.addEventListener('click', hideSidebar);
    overlay.addEventListener('click', hideSidebar);
</script>

@yield('scripts')

</body>
</html>
