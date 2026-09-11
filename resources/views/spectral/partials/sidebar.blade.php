<aside id="spectral-sidebar" class="w-full md:w-72 lg:w-80 bg-[#151B23] border-r border-[#2A3440] flex flex-col h-[40vh] md:h-full flex-shrink-0 z-20 select-none overflow-hidden transition-all duration-300">
    <!-- Sidebar Header -->
    <div class="p-3.5 border-b border-[#2A3440] bg-[#11161D] flex items-center justify-between">
        <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider font-mono">Navigation</span>
        <span class="text-[11px] font-mono text-[#8B5CF6] font-medium">San Francisco</span>
    </div>

    <!-- Navigation Scrollable Area -->
    <div class="flex-1 overflow-y-auto p-3 space-y-4 text-xs">
        
        <!-- SECTION 1: MAP LAYERS -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#64748B] px-2">Map Layers</p>
            <a href="{{ route('spectral.dashboard') }}" class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg bg-[#1B222C] text-white font-semibold hover:bg-[#222B38] border border-[#2A3440] transition">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                    <span>Interactive Map</span>
                </span>
                <span class="text-[10px] text-[#8B5CF6] font-mono">Active</span>
            </a>
            <button onclick="SpectralMap.toggleLayer('incidents')" id="layer-btn-incidents" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#EF4444]"></span>
                    <span>Incidents</span>
                </span>
                <span id="sidebar-incidents-count" class="font-mono text-[11px] text-[#EF4444] font-bold">{{ $stats['active_incidents'] ?? 7 }}</span>
            </button>
            <button onclick="SpectralMap.toggleLayer('wards')" id="layer-btn-wards" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                    <span>Ward Stations</span>
                </span>
                <span class="font-mono text-[11px] text-[#8B5CF6] font-bold">{{ $stats['ward_stations'] ?? 5 }}</span>
            </button>
            <button onclick="SpectralMap.toggleLayer('resources')" id="layer-btn-resources" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#38BDF8]"></span>
                    <span>Resource Nodes</span>
                </span>
                <span class="font-mono text-[11px] text-[#38BDF8] font-bold">{{ $stats['resources'] ?? 4 }}</span>
            </button>
            <button onclick="SpectralMap.toggleLayer('safeZones')" id="layer-btn-safeZones" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#22C55E]"></span>
                    <span>Safe Zones</span>
                </span>
                <span class="font-mono text-[11px] text-[#22C55E] font-bold">2</span>
            </button>
        </div>

        <!-- SECTION 2: REGISTRIES -->
        <div class="space-y-1">
            <p class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#64748B] px-2">Registries</p>
            <a href="{{ route('spectral.incidents.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span>Incident Records</span>
                <span class="text-[11px] text-[#64748B]">&rarr;</span>
            </a>
            <a href="{{ route('spectral.wards.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span>Ward Stations</span>
                <span class="text-[11px] text-[#64748B]">&rarr;</span>
            </a>
            <a href="{{ route('spectral.resources.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span>Resources</span>
                <span class="text-[11px] text-[#64748B]">&rarr;</span>
            </a>
            <a href="{{ route('spectral.equipment.index') }}" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-white transition">
                <span>Equipment</span>
                <span class="text-[11px] text-[#64748B]">&rarr;</span>
            </a>
        </div>

        <!-- SECTION 3: RECENT INCIDENTS -->
        <div class="space-y-2">
            <div class="flex items-center justify-between px-2">
                <p class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#64748B]">Recent Incidents</p>
            </div>
            
            <div id="sidebar-incident-list" class="space-y-1.5">
                @forelse($incidents->take(5) as $inc)
                    <div onclick="SpectralUI.inspectIncident('{{ $inc->incident_code ?? 'SF-INC-'.$inc->id }}')" class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440] hover:border-[#8B5CF6]/50 cursor-pointer transition-all">
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <span class="text-[10px] font-mono font-bold text-[#8B5CF6]">{{ $inc->incident_code }}</span>
                            <span class="badge-{{ strtolower($inc->severity) }} text-[9px] font-bold px-1.5 py-0.2 rounded">{{ $inc->severity }}</span>
                        </div>
                        <h4 class="text-xs font-semibold text-slate-200 truncate leading-snug">{{ $inc->title }}</h4>
                        <div class="flex items-center justify-between text-[10px] text-[#9CA3AF] mt-1 pt-1 border-t border-[#1E2631]">
                            <span>{{ $inc->barangay->name ?? 'San Francisco' }}</span>
                            <span class="font-mono text-[9px]">{{ $inc->status }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-[11px] text-[#64748B] text-center py-2">No active incidents recorded.</p>
                @endforelse
            </div>
        </div>

        <!-- SECTION 4: QUICK ACTION -->
        <div class="pt-2 border-t border-[#2A3440]">
            <button onclick="SpectralUI.openReportModal()" class="w-full py-2 px-3 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold rounded-lg text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Report Incident</span>
            </button>
        </div>

    </div>

    <!-- Sidebar Footer -->
    <div class="p-3 bg-[#11161D] border-t border-[#2A3440] text-[10px] text-[#64748B] font-mono flex items-center justify-between">
        <span>System Status</span>
        <span class="text-emerald-400 font-semibold">Normal</span>
    </div>
</aside>
