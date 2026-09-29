<aside id="investigator-sidebar" class="w-full md:w-64 lg:w-64 bg-[#151B23] border-r border-[#2A3440] flex flex-col h-[40vh] md:h-full flex-shrink-0 z-20 select-none overflow-hidden transition-all duration-300">

    <!-- Sector Location Header -->
    <div class="px-4 pt-4 pb-3 border-b border-[#2A3440]">
        <div class="text-xs font-semibold text-slate-200">
            San Francisco, Agusan del Sur
        </div>
    </div>

    <!-- Scrollable Navigation -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-5 text-xs">

        <!-- OVERVIEW -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Overview</p>
            <a href="{{ route('investigator.dashboard') }}"
               class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('investigator.dashboard') ? 'bg-[#8B5CF6]/20 text-white border border-[#8B5CF6]/40' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- INCIDENT MANAGEMENT -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Incident Management</p>

            <!-- Incident Reports -->
            <a href="{{ route('investigator.incidents.index') }}"
               class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.incidents.index') && !request()->has('status') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span>Incident Reports</span>
                </span>
            </a>

            <!-- Investigation Queue -->
            <a href="{{ route('investigator.queue') }}"
               class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.queue') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#EAB308]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span>Investigation Queue</span>
                </span>
            </a>

            <!-- Map -->

        </div>

        <!-- RESPONSE -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Response</p>

            <!-- Responders -->
            <a href="{{ route('investigator.responders') }}"
               class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.responders') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Responders</span>
                </span>
            </a>

            <!-- Safe Ward Stations -->
            <a href="{{ route('investigator.safe-zones') }}"
               class="w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.safe-zones') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#22C55E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Safe Ward Stations</span>
                </span>
            </a>
        </div>

        <!-- ACCOUNT -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Account</p>

            <a href="{{ route('investigator.profile') }}"
               class="w-full flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.profile') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Profile</span>
            </a>

            <form method="POST" action="{{ route('investigator.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-red-400 transition text-left">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 hover:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            <img src="{{ asset('images/spectra-logo.png') }}" alt="SpectraWatch" class="w-5 h-5 object-contain opacity-70">
            <div class="leading-tight">
                <p class="text-[10px] font-semibold text-[#8B5CF6]">SpectraWatch</p>
                <p class="text-[9px] text-[#64748B]">Investigation &amp; Response</p>
            </div>
        </div>
    </div>

</aside>
