<aside id="spectral-sidebar" class="w-full md:w-64 lg:w-64 bg-[#151B23] border-r border-[#2A3440] flex flex-col h-[40vh] md:h-full flex-shrink-0 z-20 select-none overflow-hidden transition-all duration-300">

    <!-- Location Header -->
    <div class="px-4 pt-4 pb-3 border-b border-[#2A3440]">
        <div class="flex items-center gap-1.5 text-xs text-slate-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#8B5CF6] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <span class="font-medium text-slate-200 text-[12px]">San Francisco, Agusan del Sur</span>
        </div>
    </div>

    <!-- Scrollable Nav -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 text-xs">

        <!-- OVERVIEW -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Overview</p>
            <a href="{{ route('spectral.dashboard') }}"
               class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('spectral.dashboard') || request()->routeIs('spectral.index') ? 'bg-[#8B5CF6]/10 text-white border border-[#8B5CF6]/30' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- MAP LAYERS -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Map</p>

            <!-- Interactive Map -->
            <a href="{{ route('spectral.dashboard') }}"
               class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                        <line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>
                    </svg>
                    <span>Interactive Map</span>
                </span>
                <span class="text-[10px] text-[#8B5CF6] font-mono font-semibold">Active</span>
            </a>

            <!-- Incidents layer toggle -->
            <button onclick="SpectralMap.toggleLayer('incidents')" id="layer-btn-incidents"
                    class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-[#EF4444]"></span>
                    <span>Incidents</span>
                </span>
                <span id="sidebar-incidents-count" class="font-mono text-[11px] text-[#EF4444] font-bold">{{ $stats['active_incidents'] ?? 0 }}</span>
            </button>

            <!-- Safe Ward Stations layer toggle -->
            <button onclick="SpectralMap.toggleLayer('safeZones')" id="layer-btn-safeZones"
                    class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span class="flex items-center gap-2.5">
                    <span class="w-2 h-2 rounded-full bg-[#22C55E]"></span>
                    <span>Safe Ward Stations</span>
                </span>
                <span class="font-mono text-[11px] text-[#22C55E] font-bold">2</span>
            </button>
        </div>

        <!-- MY ACTIVITY -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">My Activity</p>

            <!-- My Reports -->
            <a href="{{ route('spectral.my-reports') }}"
               class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('spectral.my-reports') ? 'bg-[#1B222C] text-white' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span>My Reports</span>
                </span>
                @if(($stats['my_reports'] ?? 0) > 0)
                <span class="font-mono text-[11px] text-slate-400 font-bold">{{ $stats['my_reports'] }}</span>
                @endif
            </a>

            <!-- Incident Reports -->
            <a href="{{ route('spectral.incidents.index') }}"
               class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('spectral.incidents.*') ? 'bg-[#1B222C] text-white' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <span>Incident Reports</span>
                </span>
                @if(($stats['total_incidents'] ?? 0) > 0)
                <span class="font-mono text-[11px] text-slate-400 font-bold">{{ $stats['total_incidents'] }}</span>
                @endif
            </a>
        </div>

        <!-- ACCOUNT -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Account</p>

            <a href="{{ route('spectral.profile') }}"
               class="w-full flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-red-400 transition text-left">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Sidebar Footer -->
    <div class="px-4 py-3 border-t border-[#2A3440] bg-[#11161D]">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra" class="w-6 h-6 object-contain opacity-60">
            <div class="leading-tight">
                <p class="text-[10px] font-semibold text-[#64748B]">Safer Communities</p>
                <p class="text-[10px] text-[#475569]">Stronger Tomorrow</p>
            </div>
        </div>
    </div>

</aside>
