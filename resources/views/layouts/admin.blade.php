<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0B0F14] text-[#F3F4F6]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Command — SpectraWatch')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/spectra-logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        ecto: {
                            base: '#0B0F14',
                            surface: '#11161D',
                            panel: '#151B23',
                            card: '#1B222C',
                            border: '#2A3440',
                            accent: '#8B5CF6',
                            amber: '#D97706',
                            success: '#22C55E',
                            warning: '#EAB308',
                            danger: '#EF4444',
                            text: '#F3F4F6',
                            muted: '#9CA3AF'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Global Spectra Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/spectral.css') }}">

    <style>
        /* Enterprise Administrative Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0B0F14;
        }
        ::-webkit-scrollbar-thumb {
            background: #2A3440;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #8B5CF6;
        }

        /* Mobile drawer transitions */
        #admin-mobile-drawer {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #admin-mobile-drawer.open {
            transform: translateX(0) !important;
        }
        #admin-drawer-overlay.open {
            display: block !important;
            opacity: 1 !important;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-[#0B0F14] text-[#F3F4F6] font-sans antialiased flex flex-col overflow-hidden select-none" style="-webkit-tap-highlight-color: transparent;">

    <!-- Top Administrative Header -->
    <header class="h-14 bg-[#11161D] border-b border-[#2A3440] px-3 sm:px-6 flex items-center justify-between flex-shrink-0 z-40 select-none">
        <!-- Left: Brand + Mobile Menu Button -->
        <div class="flex items-center gap-2 sm:gap-4">
            <button type="button"
                    onclick="AdminDrawer.toggle()"
                    class="md:hidden px-2.5 py-1 rounded bg-[#1B222C] border border-[#2A3440] text-xs font-mono font-bold text-slate-300 hover:text-white hover:border-[#8B5CF6] transition"
                    aria-label="Toggle Navigation Menu">
                [ MENU ]
            </button>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 hover:opacity-90 transition">
                <img src="{{ asset('images/spectra-logo.png') }}" alt="SFWatch" class="h-8 w-8 object-contain">
                <span class="text-base sm:text-lg font-extrabold tracking-wide font-sans bg-gradient-to-r from-[#3B82F6] to-[#06B6D4] bg-clip-text text-transparent">SFWatch</span>
            </a>
        </div>

        <!-- Right: Admin Profile Info + Logout -->
        <div class="flex items-center gap-2 sm:gap-4 text-xs font-mono">
            @php
                $adminUser = Auth::guard('admin')->user() ?? Auth::user();
            @endphp
            @if($adminUser)
                <a href="{{ route('admin.profile') }}" class="hidden sm:flex items-center gap-2 px-2.5 py-1 rounded-lg bg-[#151B23] border border-[#2A3440] hover:border-[#8B5CF6]/60 transition">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="font-bold text-slate-200">{{ $adminUser->name }}</span>
                    <span class="text-[10px] text-[#A78BFA] uppercase">[ ADMIN ]</span>
                </a>
            @endif

            <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-2.5 py-1 rounded-lg bg-[#1B222C] border border-rose-500/30 text-rose-400 hover:bg-rose-500/10 hover:border-rose-500/60 font-mono font-bold text-xs transition">
                    [ Sign Out ]
                </button>
            </form>
        </div>
    </header>

    <!-- Main Workspace Layout (Sidebar + Content) -->
    <div class="flex-1 flex overflow-hidden relative">

        <!-- Mobile Drawer Overlay -->
        <div id="admin-drawer-overlay"
             onclick="AdminDrawer.close()"
             class="fixed inset-0 bg-black/70 backdrop-blur-xs z-50 hidden opacity-0 transition-opacity md:hidden"
             style="display: none;"></div>

        <!-- Mobile Drawer / Desktop Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Scrollable Main Content Container -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#0B0F14]">

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="m-3 sm:m-4 mb-0 p-3 rounded-xl bg-emerald-950/40 border border-emerald-500/50 text-emerald-300 text-xs font-mono flex items-center justify-between">
                    <span>[ OK ] {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">[ dismiss ]</button>
                </div>
            @endif
            @if(session('info'))
                <div class="m-3 sm:m-4 mb-0 p-3 rounded-xl bg-sky-950/40 border border-sky-500/50 text-sky-300 text-xs font-mono flex items-center justify-between">
                    <span>[ INFO ] {{ session('info') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-sky-400 hover:text-white">[ dismiss ]</button>
                </div>
            @endif
            @if($errors->any())
                <div class="m-3 sm:m-4 mb-0 p-3 rounded-xl bg-rose-950/40 border border-rose-500/50 text-rose-300 text-xs font-mono space-y-1">
                    <p class="font-bold text-rose-400">[ NOTICE ] Execution Alert:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Page Content -->
            @yield('content')
        </div>
    </div>

    <!-- Client-side navigation & mobile drawer script -->
    <script>
        const AdminDrawer = {
            drawer: null,
            overlay: null,
            init() {
                this.drawer = document.getElementById('admin-mobile-drawer');
                this.overlay = document.getElementById('admin-drawer-overlay');
            },
            open() {
                if (!this.drawer) this.init();
                if (this.drawer && this.overlay) {
                    this.overlay.style.display = 'block';
                    setTimeout(() => {
                        this.overlay.classList.add('open');
                        this.drawer.classList.add('open');
                    }, 10);
                }
            },
            close() {
                if (!this.drawer) this.init();
                if (this.drawer && this.overlay) {
                    this.drawer.classList.remove('open');
                    this.overlay.classList.remove('open');
                    setTimeout(() => {
                        this.overlay.style.display = 'none';
                    }, 250);
                }
            },
            toggle() {
                if (!this.drawer) this.init();
                if (this.drawer.classList.contains('open')) {
                    this.close();
                } else {
                    this.open();
                }
            }
        };

        window.AdminDrawer = AdminDrawer;
        document.addEventListener('DOMContentLoaded', () => AdminDrawer.init());
    </script>
    @stack('scripts')
</body>
</html>
