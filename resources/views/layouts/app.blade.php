<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0B0F14] text-[#F3F4F6]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Spectra — Incident & Ward Monitoring')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/spectra-logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Tailwind CSS CDN for utility baseline -->
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
    @stack('styles')
</head>
<body class="h-full bg-[#0B0F14] text-[#F3F4F6] font-sans antialiased flex flex-col overflow-hidden select-none" style="-webkit-tap-highlight-color: transparent; touch-action: manipulation;">

    <!-- Top Application Bar -->
    @include('spectral.partials.header')

    <!-- Flash Notifications (Auto-hides after 1 second) -->
    @if(session('success'))
        <div id="flash-success-toast" class="fixed top-16 right-5 z-[9999] px-4 py-2.5 rounded-xl bg-[#1B222C] border border-[#22C55E] text-white text-xs font-bold shadow-2xl flex items-center gap-2.5 transition-all duration-500 transform translate-y-0 opacity-100">
            <div class="w-5 h-5 rounded-full bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <span class="font-medium text-slate-100">{{ session('success') }}</span>
            <button onclick="document.getElementById('flash-success-toast')?.remove()" class="ml-2 text-slate-400 hover:text-white text-sm font-bold leading-none">&times;</button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('flash-success-toast');
                if (toast) {
                    toast.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-12px)';
                    setTimeout(() => toast.remove(), 400);
                }
            }, 1000);
        </script>
    @endif

    <!-- Main Viewport Body -->
    <div class="flex-1 flex flex-col md:flex-row min-h-0 overflow-hidden relative">
        @yield('content')
    </div>

    <!-- Modals and Overlays -->
    @yield('modals')

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Spectral GIS JS Engine -->
    <script src="{{ asset('js/spectral.js') }}"></script>
    @stack('scripts')
</body>
</html>
