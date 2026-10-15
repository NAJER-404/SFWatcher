<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0B0F14] text-[#F3F4F6]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Portal — SpectraWatch')</title>
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

    <!-- Enterprise GIS Spectral Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/spectral.css') }}">

    <style>
        /* Enterprise administrative scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0B0F14; }
        ::-webkit-scrollbar-thumb { background: #2A3440; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #8B5CF6; }

        /* Mobile drawer transitions */
        #admin-mobile-drawer { transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        #admin-mobile-drawer.open { transform: translateX(0) !important; }
        #admin-drawer-overlay.open { display: block !important; opacity: 1 !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-[#0B0F14] text-[#F3F4F6] font-sans antialiased flex flex-col overflow-hidden select-none" style="-webkit-tap-highlight-color: transparent; touch-action: manipulation;">

    @php
        $adminUser = Auth::guard('admin')->user() ?? Auth::user();
    @endphp

    <!-- Admin Top Header -->
    <header class="h-14 bg-[#11161D] border-b border-[#2A3440] px-3 sm:px-4 flex items-center justify-between flex-shrink-0 z-40 select-none">
        <!-- Left: Hamburger (mobile) + Brand -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Mobile hamburger -->
            <button type="button"
                    id="admin-mobile-menu-toggle"
                    onclick="AdminDrawer.toggle()"
                    class="flex md:hidden w-8 h-8 items-center justify-center rounded-lg bg-[#1B222C] border border-[#2A3440] text-[#9CA3AF] hover:text-white hover:border-[#8B5CF6]/50 transition-all"
                    aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 hover:opacity-90 transition">
                <img src="{{ asset('images/spectra-logo.png') }}" alt="SpectraWatch" class="h-8 w-8 object-contain">
                <span class="text-lg font-extrabold tracking-wide font-sans bg-gradient-to-r from-[#3B82F6] to-[#06B6D4] bg-clip-text text-transparent">SFWatch</span>
            </a>
        </div>

        <!-- Right: Profile Info & Logout -->
        <div class="flex items-center gap-2 sm:gap-3">
            @if($adminUser)
                <!-- Profile Info -->
                <a href="{{ route('admin.profile') }}" title="View profile" class="flex items-center gap-2.5 pl-2 hover:opacity-85 transition">
                    <div class="w-8 h-8 rounded-full bg-[#8B5CF6]/20 border border-[#8B5CF6]/40 flex items-center justify-center text-xs font-bold text-[#A78BFA] font-mono">
                        {{ strtoupper(substr($adminUser->name, 0, 1)) }}{{ strtoupper(substr(strrchr($adminUser->name, ' '), 1, 1) ?: substr($adminUser->name, 1, 1)) }}
                    </div>
                    <div class="text-left hidden sm:block">
                        <p class="text-xs font-semibold text-white leading-tight">{{ $adminUser->name }}</p>
                        <p class="text-[10px] text-[#9CA3AF] font-mono leading-tight">Administrator</p>
                    </div>
                </a>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('admin.logout') }}" class="flex items-center">
                    @csrf
                    <button type="submit"
                        title="Sign out of Admin Portal"
                        class="w-8 h-8 rounded-lg bg-[#1B222C] border border-[#2A3440] flex items-center justify-center text-[#9CA3AF] hover:text-red-400 hover:border-red-400/40 transition-all"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div id="flash-success" class="fixed top-16 right-5 z-[9999] max-w-[calc(100vw-2.5rem)] px-4 py-2.5 rounded-xl bg-[#1B222C] border border-[#22C55E] text-white text-xs font-bold shadow-2xl flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        <script>setTimeout(function(){ var el = document.getElementById('flash-success'); if(el) el.style.display='none'; }, 4000);</script>
    @endif
    @if(session('info'))
        <div id="flash-info" class="fixed top-16 right-5 z-[9999] max-w-[calc(100vw-2.5rem)] px-4 py-2.5 rounded-xl bg-[#1B222C] border border-[#38BDF8] text-white text-xs font-bold shadow-2xl flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-sky-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>{{ session('info') }}</span>
        </div>
        <script>setTimeout(function(){ var el = document.getElementById('flash-info'); if(el) el.style.display='none'; }, 4000);</script>
    @endif
    @if(session('error'))
        <div id="flash-error" class="fixed top-16 right-5 z-[9999] max-w-[calc(100vw-2.5rem)] px-4 py-2.5 rounded-xl bg-[#1B222C] border border-[#EF4444] text-white text-xs font-bold shadow-2xl flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        <script>setTimeout(function(){ var el = document.getElementById('flash-error'); if(el) el.style.display='none'; }, 4000);</script>
    @endif
    @if($errors->any())
        <div id="flash-validation" class="fixed top-16 right-5 z-[9999] max-w-[calc(100vw-2.5rem)] px-4 py-3 rounded-xl bg-[#1B222C] border border-[#EF4444] text-white text-xs shadow-2xl flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mt-0.5 text-red-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <ul class="space-y-0.5 font-semibold">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" onclick="this.parentElement.remove()" class="ml-1 text-slate-400 hover:text-white text-base leading-none" aria-label="Dismiss">&times;</button>
        </div>
        <script>setTimeout(function(){ var el = document.getElementById('flash-validation'); if(el) el.style.display='none'; }, 6000);</script>
    @endif

    <!-- Main Workspace Layout (Sidebar + Content) -->
    <div class="flex-1 flex min-h-0 overflow-hidden relative">

        <!-- Mobile Drawer Overlay -->
        <div id="admin-drawer-overlay"
             onclick="AdminDrawer.close()"
             class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity md:hidden"
             style="display: none;"></div>

        <!-- Mobile Drawer / Desktop Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Main Viewport Body -->
        <div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden bg-[#0B0F14] relative">
            @yield('content')
        </div>
    </div>

    <!-- Mobile drawer script -->
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
                if (this.drawer && this.drawer.classList.contains('open')) {
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
