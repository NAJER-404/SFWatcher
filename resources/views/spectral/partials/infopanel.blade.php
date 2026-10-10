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
                <div class="flex justify-between text-slate-300">
                    <span class="truncate pr-2">SF Gymnasium Sanctuary:</span>
                    <span class="text-emerald-400 font-semibold flex-shrink-0">Active</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span class="truncate pr-2">Hubang Transport Haven:</span>
                    <span class="text-emerald-400 font-semibold flex-shrink-0">Active</span>
                </div>
            </div>
        </div>

    </div>

    <!-- MODE B: DETAILED INCIDENT INSPECTOR PANEL -->
    <div id="panel-incident-inspector" class="hidden flex-1 flex flex-col overflow-y-auto p-4 space-y-4 min-h-0">

        <!-- Top Bar with Back Button -->
        <div class="flex items-center justify-between border-b border-[#2A3440] pb-3">
            <button onclick="SpectralUI.closeInspector()" class="text-xs text-[#9CA3AF] hover:text-white inline-flex items-center gap-1.5 font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Incident Details</span>
            </button>
            <span id="insp-id" class="text-xs font-mono font-bold text-[#8B5CF6]">SF-INC-001</span>
        </div>

        <!-- Incident Title & Status Badges -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span id="insp-type" class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Incident</span>
                <span id="insp-status-badge" class="badge-investigating text-[10px] font-bold px-2 py-0.5 rounded-full font-mono">PENDING</span>
            </div>
            <h3 id="insp-title" class="text-sm font-bold text-white leading-snug">Incident Subject Title</h3>
            <!-- Severity badge below title -->
            <span id="insp-severity-badge" class="inline-block badge-high text-[10px] font-bold px-2.5 py-1 rounded-md font-mono">HIGH SEVERITY</span>
        </div>

        <!-- Location Block -->
        <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-1.5 text-xs">
            <p class="text-[9px] font-mono font-bold text-[#64748B] uppercase tracking-wider inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Location</span>
            </p>
            <p id="insp-location" class="font-semibold text-slate-200">Hubang, San Francisco, Agusan del Sur</p>
            <p id="insp-coords" class="text-[11px] font-mono text-[#8B5CF6]">8.5318° N, 125.9725° E</p>
            <button id="insp-focus-map-btn" onclick="SpectralMap.focusOnInspected()" class="mt-1 px-2.5 py-1 bg-[#1B222C] hover:bg-[#222B38] border border-[#2A3440] text-slate-300 hover:text-white rounded text-[10px] font-semibold transition flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Focus on Map
            </button>
        </div>

        <!-- Reported & Reported By -->
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440]">
                <p class="text-[9px] font-mono text-[#64748B] uppercase inline-flex items-center gap-1.5 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Reported</span>
                </p>
                <p id="insp-date" class="text-[11px] font-mono text-slate-300 font-semibold">—</p>
            </div>
            <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440]">
                <p class="text-[9px] font-mono text-[#64748B] uppercase inline-flex items-center gap-1.5 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Reported By</span>
                </p>
                <p id="insp-reporter" class="text-[11px] text-slate-300 font-semibold truncate">—</p>
            </div>
        </div>

        <!-- Status Timeline (Stage Progression) -->
        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-2 font-mono">
            <div class="flex items-center justify-between border-b border-[#1E2631] pb-2">
                <h3 class="text-[11px] font-bold uppercase tracking-wider text-white">STATUS TIMELINE</h3>
                <span class="text-[9px] text-[#64748B]">STAGE PROGRESSION</span>
            </div>
            <div id="insp-timeline" class="space-y-1">
                <!-- Timeline items injected by JS -->
                <div class="text-[10px] text-[#64748B] italic">Loading timeline...</div>
            </div>
        </div>

        <!-- Footer hint -->
        <p class="text-[9px] text-[#475569] leading-relaxed border-t border-[#2A3440] pt-3">
            Severity indicates how serious the incident is. Status shows the current stage of investigation.
        </p>

        <!-- Investigator Actions (Administrator Mode) -->
        <div id="insp-investigator-actions" class="hidden p-3.5 rounded-lg bg-[#1B222C] border border-[#8B5CF6]/30 space-y-3">
            <span class="text-xs font-bold text-[#8B5CF6] font-mono uppercase tracking-wider">Update Incident</span>

            <!-- Change Status -->
            <div class="space-y-1">
                <label class="text-[10px] text-slate-400 font-semibold uppercase">Status</label>
                <select id="investigator-status-select" class="ecto-select">
                    <option value="PENDING">PENDING</option>
                    <option value="UNDER INVESTIGATION">UNDER INVESTIGATION</option>
                    <option value="VERIFIED">VERIFIED</option>
                    <option value="RESOLVED">RESOLVED</option>
                </select>
            </div>

            <!-- Change Severity -->
            <div class="space-y-1">
                <label class="text-[10px] text-slate-400 font-semibold uppercase">Severity</label>
                <select id="investigator-severity-select" class="ecto-select">
                    <option value="LOW">LOW</option>
                    <option value="MEDIUM">MEDIUM</option>
                    <option value="HIGH">HIGH</option>
                    <option value="CRITICAL">CRITICAL</option>
                </select>
            </div>

            <!-- Notes -->
            <div class="space-y-1">
                <label class="text-[10px] text-slate-400 font-semibold uppercase">Notes</label>
                <textarea id="investigator-notes-input" rows="2" class="ecto-input text-xs" placeholder="Add investigation notes..."></textarea>
            </div>

            <button onclick="SpectralUI.saveInvestigatorChanges()" class="w-full py-2 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold rounded-md text-xs transition">
                Save Changes
            </button>
        </div>

    </div>

</aside>
