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
    class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300">

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
                            class="lg:hidden w-10 h-10 rounded-xl
                                   bg-slate-100 dark:bg-slate-800
                                   flex items-center justify-center">
                            ☰
                        </button>
                    </div>
                </div>
            </div>
        </nav>
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
    </script>

    @yield('scripts')
</body>

</html>
