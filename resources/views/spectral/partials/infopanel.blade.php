<aside id="spectral-infopanel" class="w-full md:w-80 lg:w-96 bg-[#151B23] border-t md:border-t-0 md:border-l border-[#2A3440] flex flex-col h-10 md:h-full flex-shrink-0 z-20 select-none overflow-hidden transition-all duration-300">

    <!-- Mobile Drawer Drag Handle / Toggle Header (Mobile only) -->
    <div onclick="SpectralUI.toggleMobileInfoPanel()" class="flex md:hidden items-center justify-between px-3.5 py-2 bg-[#11161D] border-b border-[#2A3440] cursor-pointer active:bg-[#1B222C] transition select-none flex-shrink-0 h-10">
        <div class="flex items-center gap-2 min-w-0">
            <span class="w-2 h-2 rounded-full bg-[#8B5CF6] animate-pulse flex-shrink-0"></span>
            <span id="mobile-infopanel-title" class="text-[11px] font-mono font-bold text-slate-200 truncate">System Summary</span>
        </div>
        <div class="flex items-center gap-1.5 flex-shrink-0 text-slate-400">
            <span id="mobile-infopanel-hint" class="text-[10px] font-mono">Expand</span>
            <svg id="mobile-infopanel-icon" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
        </div>
    </div>

    <!-- MODE A: DEFAULT OVERVIEW PANEL -->
    <div id="panel-default-overview" class="flex-1 flex flex-col overflow-y-auto p-4 space-y-4 min-h-0">

        <!-- Header -->
        <div class="border-b border-[#2A3440] pb-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-300 tracking-wider font-mono uppercase">System Summary</h3>
                <span class="text-[10px] font-mono font-medium px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">Active</span>
            </div>
            <p class="text-[11px] text-[#9CA3AF] mt-0.5">San Francisco, Agusan del Sur</p>
        </div>

        <!-- 4 Key Metric Cards -->
        <div class="grid grid-cols-2 gap-2">
            <div class="p-3 rounded-lg bg-[#1B222C] border border-[#2A3440] flex flex-col justify-between">
                <span class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider font-mono">Active Incidents</span>
                <p id="stat-active-incidents" class="text-2xl font-bold text-[#EF4444] font-mono mt-1">{{ sprintf('%02d', $stats['active_incidents'] ?? 0) }}</p>
            </div>
            <div class="p-3 rounded-lg bg-[#1B222C] border border-[#2A3440] flex flex-col justify-between">
                <span class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider font-mono">Under Review</span>
                <p id="stat-investigating" class="text-2xl font-bold text-[#8B5CF6] font-mono mt-1">{{ sprintf('%02d', $stats['investigating'] ?? 0) }}</p>
            </div>
            <div class="p-3 rounded-lg bg-[#1B222C] border border-[#2A3440] flex flex-col justify-between">
                <span class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider font-mono">Safe Ward Stations</span>
                <p id="stat-ward-stations" class="text-2xl font-bold text-[#22C55E] font-mono mt-1">{{ sprintf('%02d', $stats['ward_stations'] ?? 2) }}</p>
            </div>
            <div class="p-3 rounded-lg bg-[#1B222C] border border-[#2A3440] flex flex-col justify-between">
                <span class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider font-mono">Resolved</span>
                <p id="stat-resolved" class="text-2xl font-bold text-[#38BDF8] font-mono mt-1">{{ sprintf('%02d', $stats['resolved'] ?? 0) }}</p>
            </div>
        </div>

        <!-- Safe Ward Stations Status -->
        <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-2 text-xs">
            <p class="text-[10px] font-mono font-bold text-[#64748B] uppercase tracking-wider">Safe Ward Stations</p>
            <div class="space-y-1.5 font-mono text-[11px]">
                @forelse($safeWardStations ?? [] as $ws)
                <div class="flex justify-between text-slate-300">
                    <span class="truncate pr-2" title="{{ $ws->name }}">{{ $ws->name }}:</span>
                    <span class="{{ ($ws->status ?? 'active') === 'active' ? 'text-emerald-400' : 'text-amber-400' }} font-semibold flex-shrink-0 capitalize">{{ $ws->status ?? 'active' }}</span>
                </div>
                @empty
                <div class="text-slate-500 text-[10px]">No ward stations registered</div>
                @endforelse
            </div>
        </div>

    </div>



</aside>
