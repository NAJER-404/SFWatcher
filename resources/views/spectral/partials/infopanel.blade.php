<aside id="spectral-infopanel" class="w-full md:w-80 lg:w-96 bg-[#151B23] border-l border-[#2A3440] flex flex-col h-[45vh] md:h-full flex-shrink-0 z-20 select-none overflow-hidden transition-all duration-300">
    
    <!-- MODE A: DEFAULT OVERVIEW PANEL -->
    <div id="panel-default-overview" class="flex-1 flex flex-col overflow-y-auto p-4 space-y-4">
        
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
                <p id="stat-active-incidents" class="text-2xl font-bold text-[#EF4444] font-mono mt-1">{{ sprintf('%02d', $stats['active_incidents'] ?? 7) }}</p>
            </div>
            <div class="p-3 rounded-lg bg-[#1B222C] border border-[#2A3440] flex flex-col justify-between">
                <span class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider font-mono">Under Review</span>
                <p id="stat-investigating" class="text-2xl font-bold text-[#8B5CF6] font-mono mt-1">{{ sprintf('%02d', $stats['investigating'] ?? 3) }}</p>
            </div>
            <div class="p-3 rounded-lg bg-[#1B222C] border border-[#2A3440] flex flex-col justify-between">
                <span class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider font-mono">Ward Stations</span>
                <p id="stat-ward-stations" class="text-2xl font-bold text-[#FACC15] font-mono mt-1">{{ sprintf('%02d', $stats['ward_stations'] ?? 5) }}</p>
            </div>
            <div class="p-3 rounded-lg bg-[#1B222C] border border-[#2A3440] flex flex-col justify-between">
                <span class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider font-mono">Resources</span>
                <p id="stat-resources" class="text-2xl font-bold text-[#38BDF8] font-mono mt-1">{{ sprintf('%02d', $stats['resources'] ?? 4) }}</p>
            </div>
        </div>

        <!-- Sector Details -->
        <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-2">
            <div class="flex items-start justify-between">
                <div>
                    <h4 class="text-xs font-bold text-white">San Francisco Hub</h4>
                    <p class="text-[11px] text-[#9CA3AF]">Province of Agusan del Sur</p>
                    <p class="text-[10px] text-[#64748B] font-mono mt-0.5">8.5100° N, 125.9750° E</p>
                </div>
                <button onclick="SpectralMap.jumpTo('san_francisco')" class="px-2.5 py-1 bg-[#1B222C] hover:bg-[#222B38] text-[#8B5CF6] font-semibold text-[11px] rounded-md border border-[#2A3440] transition">
                    Center
                </button>
            </div>
        </div>

        <!-- Ward Station Status -->
        <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-2 text-xs">
            <p class="text-[10px] font-mono font-bold text-[#64748B] uppercase tracking-wider">Ward Status</p>
            <div class="space-y-1.5 font-mono text-[11px]">
                <div class="flex justify-between text-slate-300">
                    <span>Hubang Station:</span>
                    <span class="text-emerald-400 font-semibold">96%</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Poblacion Station:</span>
                    <span class="text-emerald-400 font-semibold">92%</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Karaus Station:</span>
                    <span class="text-amber-400 font-semibold">64%</span>
                </div>
            </div>
        </div>

        <!-- Hint -->
        <div class="p-3 rounded-lg bg-[#1B222C]/40 border border-[#2A3440]/40 text-center">
            <p class="text-[11px] text-[#9CA3AF]">Click any marker on the map to view details.</p>
        </div>

    </div>

    <!-- MODE B: DETAILED INCIDENT INSPECTOR PANEL -->
    <div id="panel-incident-inspector" class="hidden flex-1 flex flex-col overflow-y-auto p-4 space-y-4">
        
        <!-- Top Bar with Back Button -->
        <div class="flex items-center justify-between border-b border-[#2A3440] pb-3">
            <button onclick="SpectralUI.closeInspector()" class="text-xs text-[#9CA3AF] hover:text-white flex items-center gap-1 font-semibold">
                &larr; Overview
            </button>
            <span id="insp-id" class="text-xs font-mono font-bold text-[#8B5CF6]">SF-INC-001</span>
        </div>

        <!-- Incident Type & Status Badges -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span id="insp-type" class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Incident</span>
                <div class="flex items-center gap-1.5">
                    <span id="insp-severity-badge" class="badge-high text-[10px] font-bold px-2 py-0.5 rounded-full font-mono">HIGH</span>
                    <span id="insp-status-badge" class="badge-investigating text-[10px] font-bold px-2 py-0.5 rounded-full font-mono">PENDING</span>
                </div>
            </div>
            <h3 id="insp-title" class="text-sm font-bold text-white leading-snug">Incident Subject Title</h3>
            <p id="insp-desc" class="text-xs text-[#9CA3AF] leading-relaxed">Description.</p>
        </div>

        <!-- Location Block -->
        <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-1 text-xs">
            <p class="text-[10px] font-mono font-bold text-[#64748B] uppercase">Location</p>
            <p id="insp-location" class="font-semibold text-slate-200">Hubang, San Francisco, Agusan del Sur</p>
            <p id="insp-coords" class="text-[11px] font-mono text-[#8B5CF6]">8.5318° N, 125.9725° E</p>
        </div>

        <!-- Metadata Block -->
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440]">
                <p class="text-[9px] font-mono text-[#64748B] uppercase">Date &amp; Time</p>
                <p id="insp-date" class="text-[11px] font-mono text-slate-300 font-semibold mt-0.5">2026-09-05 14:22</p>
            </div>
            <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440]">
                <p class="text-[9px] font-mono text-[#64748B] uppercase">Reported By</p>
                <p id="insp-reporter" class="text-[11px] text-slate-300 font-semibold truncate mt-0.5">K. Morales</p>
            </div>
        </div>

        <!-- Evidence Photo Block -->
        <div id="insp-evidence-box" class="space-y-1.5">
            <p class="text-[10px] font-mono font-bold text-[#64748B] uppercase">Evidence Photo</p>
            <div class="rounded-lg overflow-hidden border border-[#2A3440] bg-[#11161D] aspect-video relative group">
                <img id="insp-evidence-img" src="" alt="Evidence" class="w-full h-full object-cover">
            </div>
        </div>

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
                    <option value="ESCALATED">ESCALATED</option>
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
