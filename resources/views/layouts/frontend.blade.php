<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ভ্রমণবিলাস — বাংলাদেশের সেরা ট্যুর ও ট্রাভেল প্যাকেজ')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        bangla: ['Noto Sans Bengali', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0F766E',
                        secondary: '#F59E0B',
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Bengali', sans-serif;
        }

        .hero-overlay {
            background:
                linear-gradient(
                    90deg,
                    rgba(0, 0, 0, .72) 0%,
                    rgba(0, 0, 0, .42) 50%,
                    rgba(0, 0, 0, .15) 100%
                );
        }

        .glass {
            background: rgba(255, 255, 255, .12);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }

        .dark .glass {
            background: rgba(15, 23, 42, .45);
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .timeline-line {
            position: absolute;
            left: 19px;
            top: 42px;
            bottom: -30px;
            width: 2px;
            background: #dbe4e2;
        }

        .dark .timeline-line {
            background: #334155;
        }

        @media (max-width: 767px) {
            .timeline-line {
                left: 17px;
            }
        }
    </style>
</head>

<body
    class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300 @yield('body-class')">

    <header class="fixed top-0 left-0 right-0 z-50">
        <nav class="bg-white/90 dark:bg-slate-950/90 backdrop-blur-lg
                    border-b border-white/20 dark:border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="h-20 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div
                            class="w-11 h-11 rounded-2xl bg-teal-700 text-white
                                   flex items-center justify-center text-xl">
                            ✈
                        </div>
                        <div>
                            <div class="text-xl font-extrabold text-teal-700">
                                ভ্রমণবিলাস
                            </div>
                            <div class="text-[10px] text-slate-500">
                                Travel • Explore • Memories
                            </div>
                        </div>
                    </a>
                    <div class="hidden lg:flex items-center gap-8 text-sm font-semibold">
                        <a href="{{ route('home') }}" class="hover:text-teal-600">
                            হোম
                        </a>
                        <a href="{{ route('tours.index') }}" class="hover:text-teal-600">
                            ট্যুর প্যাকেজ
                        </a>
                        <a href="{{ route('home') }}#destinations" class="hover:text-teal-600">
                            গন্তব্য
                        </a>
                        <a href="{{ route('home') }}#about" class="hover:text-teal-600">
                            আমাদের সম্পর্কে
                        </a>
                        <a href="{{ route('home') }}#contact" class="hover:text-teal-600">
                            যোগাযোগ
                        </a>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            class="hidden sm:flex px-3 py-2 rounded-xl
                                   bg-slate-100 dark:bg-slate-800
                                   text-sm font-semibold">
                            বাংলা
                            <span class="ml-1">⌄</span>
                        </button>
                        <button id="themeToggle"
                            class="w-10 h-10 rounded-xl
                                   bg-slate-100 dark:bg-slate-800
                                   flex items-center justify-center">
                            ☀️
                        </button>
                        <a href="{{ route('tours.index') }}"
                            class="hidden sm:block bg-teal-700 hover:bg-teal-800
                                   text-white px-5 py-2.5 rounded-xl
                                   font-semibold transition">
                            ট্যুর দেখুন
                        </a>
                        <button
                            id="mobileMenuButton" type="button" aria-label="Open menu"
                            aria-controls="mobileMenu" aria-expanded="false"
                            class="lg:hidden w-10 h-10 rounded-xl
                                   bg-slate-100 dark:bg-slate-800
                                   flex items-center justify-center
                                   text-lg leading-none transition">
                            <span id="mobileMenuIcon">☰</span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <div id="mobileMenuOverlay"
            class="fixed inset-0 z-40 hidden bg-black/50 opacity-0 transition-opacity duration-200 lg:hidden"></div>

        <div id="mobileMenu"
            class="fixed top-20 left-0 right-0 z-50 hidden origin-top
                   transform -translate-y-2 opacity-0 transition duration-200 lg:hidden">
            <div class="mx-4 mt-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden">
                <div class="p-3 space-y-1">
                    <a href="{{ route('home') }}"
                        class="block px-4 py-3 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        হোম
                    </a>
                    <a href="{{ route('tours.index') }}"
                        class="block px-4 py-3 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        ট্যুর প্যাকেজ
                    </a>
                    <a href="{{ route('home') }}#destinations"
                        class="block px-4 py-3 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        গন্তব্য
                    </a>
                    <a href="{{ route('home') }}#about"
                        class="block px-4 py-3 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        আমাদের সম্পর্কে
                    </a>
                    <a href="{{ route('home') }}#contact"
                        class="block px-4 py-3 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        যোগাযোগ
                    </a>

                    <div class="border-t border-slate-200 dark:border-slate-800 pt-3 mt-2 space-y-2">
                        @auth
                            @if (auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}"
                                    class="block px-4 py-3 rounded-xl text-sm font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                    Admin Panel
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                    লগআউট
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}"
                                class="block px-4 py-3 rounded-xl text-sm font-semibold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                লগইন
                            </a>
                            <a href="{{ route('tours.index') }}"
                                class="block text-center px-4 py-3 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-sm font-semibold transition">
                                ট্যুর দেখুন
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </header>

    @yield('content')

    <footer class="bg-slate-900 text-white pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-10">
                <div>
                    <div class="text-2xl font-extrabold">
                        ✈ ভ্রমণবিলাস
                    </div>
                    <p class="text-slate-400 mt-4 leading-7">
                        ভ্রমণ হোক সহজ, সুন্দর এবং স্মরণীয়।
                    </p>
                </div>
                <div>
                    <h3 class="font-bold mb-4">
                        Explore
                    </h3>
                    <div class="space-y-3 text-slate-400 text-sm">
                        <a href="{{ route('tours.index') }}" class="block hover:text-white">
                            ট্যুর প্যাকেজ
                        </a>
                        <a href="{{ route('home') }}#destinations" class="block hover:text-white">
                            গন্তব্য
                        </a>
                        <a href="#" class="block hover:text-white">
                            সফল ভ্রমণ
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold mb-4">
                        Company
                    </h3>
                    <div class="space-y-3 text-slate-400 text-sm">
                        <a href="{{ route('home') }}#about" class="block hover:text-white">
                            আমাদের সম্পর্কে
                        </a>
                        <a href="#" class="block hover:text-white">
                            Terms & Conditions
                        </a>
                        <a href="#" class="block hover:text-white">
                            Privacy Policy
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold mb-4">
                        Newsletter
                    </h3>
                    <p class="text-slate-400 text-sm mb-3">
                        নতুন ট্যুর আপডেট পেতে subscribe করুন।
                    </p>
                    <form class="flex gap-2">
                        <input type="email" placeholder="আপনার ইমেইল"
                            class="flex-1 px-3 py-2 rounded-xl bg-slate-800 text-sm outline-none">
                        <button class="px-4 py-2 bg-teal-700 rounded-xl text-sm font-semibold">
                            ✉
                        </button>
                    </form>
                </div>
            </div>
            <div
                class="border-t border-slate-800 mt-12 pt-6
                       text-center text-sm text-slate-500">
                © 2026 ভ্রমণবিলাস. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        const themeToggle = document.getElementById('themeToggle');

        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            themeToggle.innerHTML = isDark ? '🌙' : '☀️';
        });

        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            themeToggle.innerHTML = '🌙';
        }

        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
        const mobileMenuIcon = document.getElementById('mobileMenuIcon');

        function openMobileMenu() {
            mobileMenuOverlay.classList.remove('hidden');
            mobileMenu.classList.remove('hidden');

            requestAnimationFrame(() => {
                mobileMenuOverlay.classList.remove('opacity-0');
                mobileMenu.classList.remove('-translate-y-2', 'opacity-0');
            });

            mobileMenuButton.setAttribute('aria-expanded', 'true');
            mobileMenuButton.setAttribute('aria-label', 'Close menu');
            mobileMenuIcon.textContent = '✕';
            document.body.classList.add('overflow-hidden');
        }

        function closeMobileMenu() {
            mobileMenuOverlay.classList.add('opacity-0');
            mobileMenu.classList.add('-translate-y-2', 'opacity-0');

            setTimeout(() => {
                mobileMenuOverlay.classList.add('hidden');
                mobileMenu.classList.add('hidden');
            }, 200);

            mobileMenuButton.setAttribute('aria-expanded', 'false');
            mobileMenuButton.setAttribute('aria-label', 'Open menu');
            mobileMenuIcon.textContent = '☰';
            document.body.classList.remove('overflow-hidden');
        }

        mobileMenuButton.addEventListener('click', () => {
            const isOpen = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            isOpen ? closeMobileMenu() : openMobileMenu();
        });

        mobileMenuOverlay.addEventListener('click', closeMobileMenu);

        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMobileMenu);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileMenuButton.getAttribute('aria-expanded') === 'true') {
                closeMobileMenu();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024 && mobileMenuButton.getAttribute('aria-expanded') === 'true') {
                closeMobileMenu();
            }
        });
    </script>

    @yield('scripts')

    @yield('floating-bar')
</body>

</html>
