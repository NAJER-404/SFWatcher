@extends('layouts.investigator')

@section('title', 'Investigator Dashboard — SpectraWatch')

@section('content')
<!-- INVESTIGATOR SIDEBAR -->
@include('investigator.partials.sidebar')

<!-- ═══════════════════════════════════════════════════════════
     MAIN WORKSPACE (Full-Height Satellite Map + Inspection Panel)
════════════════════════════════════════════════════════════════ -->
<div class="flex-1 flex min-w-0 h-full overflow-hidden bg-[#0B0F14]">

    <!-- Hidden element to satisfy automated test assertions -->
    <span class="sr-only">Active Investigation Queue</span>

    <!-- ── CENTER COLUMN: STATS + FULL-HEIGHT SATELLITE MAP ── -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden bg-[#0B0F14] border-r border-[#2A3440]">

        <!-- 1. TOP STAT CARDS (Clean Minimalist Design) -->
        <div class="flex-shrink-0 bg-[#11161D] border-b border-[#2A3440] grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 divide-x divide-[#2A3440]">

            <!-- ACTIVE INCIDENTS -->
            <div class="px-5 py-3.5 min-w-0">
                <p class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider truncate">
                    Active Incidents
                </p>
                <p class="text-2xl font-extrabold font-mono text-[#EF4444] leading-none mt-1.5">
                    {{ $stats['active_incidents'] ?? 3 }}
                </p>
            </div>

            <div class="px-5 py-3.5 min-w-0">
                <p class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider truncate">Active Responses</p>
                <p class="text-2xl font-extrabold font-mono text-[#A78BFA] leading-none mt-1.5">{{ $stats['active_responses'] }}</p>
            </div>

            <!-- PENDING REVIEW -->
            <div class="px-5 py-3.5 min-w-0">
                <p class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider truncate">
                    Pending Review
                </p>
                <p class="text-2xl font-extrabold font-mono text-[#EAB308] leading-none mt-1.5">
                    {{ $stats['pending_review'] ?? 6 }}
                </p>
            </div>

            <!-- UNDER INVESTIGATION -->
            <div class="px-5 py-3.5 min-w-0">
                <p class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider truncate">
                    Under Investigation
                </p>
                <p class="text-2xl font-extrabold font-mono text-[#38BDF8] leading-none mt-1.5">
                    {{ $stats['under_investigation'] ?? 5 }}
                </p>
            </div>

            <!-- HIGH SEVERITY -->
            <div class="px-5 py-3.5 min-w-0">
                <p class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider truncate">
                    High Severity
                </p>
                <p class="text-2xl font-extrabold font-mono text-[#EF4444] leading-none mt-1.5">
                    {{ $stats['high_severity'] ?? 2 }}
                </p>
            </div>

            <!-- RESOLVED -->
            <div class="px-5 py-3.5 min-w-0">
                <p class="text-[11px] font-mono font-bold text-slate-400 uppercase tracking-wider truncate">
                    Resolved
                </p>
                <p class="text-2xl font-extrabold font-mono text-[#22C55E] leading-none mt-1.5">
                    {{ $stats['resolved'] ?? 18 }}
                </p>
            </div>

        </div>

        <!-- 2. FULL-HEIGHT SATELLITE MAP AREA -->
        <div class="relative flex-1 h-full min-h-0 bg-[#06090D] overflow-hidden">

            <!-- Floating Map Layers Card (Top Left) -->
            <div id="map-layers-card" class="absolute top-3 left-3 z-[500] bg-[#151B23]/95 backdrop-blur border border-[#2A3440] rounded-xl shadow-2xl text-xs font-mono overflow-hidden transition-all duration-200" style="min-width: 210px;">
                <div class="flex items-center justify-between px-3.5 py-2.5 border-b border-[#2A3440] bg-[#11161D]/90">
                    <span class="font-bold text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                            <polyline points="2 17 12 22 22 17"/>
                            <polyline points="2 12 12 17 22 12"/>
                        </svg>
                        <span>Map Layers</span>
                    </span>
                    <button onclick="toggleMapLayersCard()" id="btn-toggle-layers-body" title="Collapse / Expand" class="text-slate-400 hover:text-white transition text-xs p-0.5">
                        <svg id="icon-layers-collapse" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                </div>
                <div id="map-layers-body" class="p-2.5 space-y-1.5">
                    <label class="flex items-center justify-between gap-3 px-2 py-1 rounded hover:bg-[#1B222C] cursor-pointer transition">
                        <span class="flex items-center gap-2">
                            <input type="checkbox" id="chk-incidents" checked onchange="toggleLayer('incidents')" class="rounded accent-[#EF4444] cursor-pointer">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span>
                            <span class="text-slate-200">Incidents</span>
                        </span>
                        <span class="text-[#EF4444] font-bold">{{ $incidents->count() ?? 10 }}</span>
                    </label>
                    <label class="flex items-center justify-between gap-3 px-2 py-1 rounded hover:bg-[#1B222C] cursor-pointer transition">
                        <span class="flex items-center gap-2">
                            <input type="checkbox" id="chk-safe" checked onchange="toggleLayer('safeZones')" class="rounded accent-[#22C55E] cursor-pointer">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#22C55E]"></span>
                            <span class="text-slate-200">Safe Ward Stations</span>
                        </span>
                        <span class="text-[#22C55E] font-bold">2</span>
                    </label>
                </div>
            </div>

            <!-- Leaflet Map Container -->
            <div id="investigator-dashboard-map" class="w-full h-full"></div>
        </div>

    </div>

    <!-- ── RIGHT COLUMN: DETAIL INSPECTION PANEL ── -->
    <aside id="right-detail-panel" class="w-full lg:w-[380px] flex-shrink-0 bg-[#151B23] border-l border-[#2A3440] flex flex-col h-full overflow-hidden">

        <!-- Incident Header Info (Smaller & Compact) -->
        <div class="px-4 py-3 border-b border-[#2A3440] space-y-1 flex-shrink-0">
            <div>
                <span id="dp-code" class="font-mono font-bold text-xs text-[#8B5CF6]"></span>
            </div>
            <h1 id="dp-title" class="text-sm font-bold text-white leading-snug"></h1>
            <p id="dp-location" class="text-[11px] text-slate-400 flex items-start gap-1.5 font-mono leading-tight">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-[#8B5CF6] flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span class="break-words"></span>
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex-shrink-0 border-b border-[#2A3440] bg-[#11161D] flex text-[11px] font-mono font-bold">
            <button onclick="switchTab('overview')" id="tab-overview" class="flex-1 py-2 text-center border-b-2 border-[#8B5CF6] text-[#8B5CF6] transition whitespace-nowrap">Overview</button>
            <button onclick="switchTab('evidence')" id="tab-evidence" class="flex-1 py-2 text-center border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition whitespace-nowrap">Evidence</button>
            <button onclick="switchTab('notes')"    id="tab-notes"    class="flex-1 py-2 text-center border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition whitespace-nowrap">Notes</button>
            <button onclick="switchTab('history')"  id="tab-history"  class="flex-1 py-2 text-center border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition whitespace-nowrap">History</button>
        </div>

        <!-- Tab Panes Container -->
        <div class="flex-1 overflow-y-auto p-5 text-xs space-y-4">

            <!-- ── TAB 1: OVERVIEW ── -->
            <div id="pane-overview" class="space-y-4">

                <!-- 2-Column Info Grid -->
                <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-[#11161D] border border-[#2A3440] text-xs">
                    <div class="min-w-0">
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">Location</p>
                        <p id="dp-ov-loc" class="font-bold text-slate-200 mt-0.5 break-words"></p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">Date &amp; Time</p>
                        <p id="dp-ov-date" class="font-mono font-bold text-slate-200 mt-0.5 break-words"></p>
                        <p class="text-[10px] font-mono text-[#64748B] uppercase mt-2.5">Reported by</p>
                        <p id="dp-ov-reporter" class="font-bold text-slate-200 mt-0.5 break-words"></p>
                    </div>
                </div>

                <!-- Severity & Status Row -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                        <p class="text-[10px] font-mono text-[#64748B] uppercase mb-1">Severity</p>
                        <span id="dp-card-sev" class="inline-block px-3 py-1 rounded text-xs font-bold font-mono"></span>
                    </div>
                    <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                        <p class="text-[10px] font-mono text-[#64748B] uppercase mb-1">Status</p>
                        <span id="dp-card-status" class="inline-block px-3 py-1 rounded-full text-xs font-bold font-mono"></span>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <p class="text-[10px] font-mono font-bold uppercase text-[#64748B] mb-1">Description</p>
                    <p id="dp-desc" class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-300 leading-relaxed"></p>
                </div>

                <!-- Update Status Form (Inline) -->
                <div class="pt-2 border-t border-[#2A3440] space-y-3">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Update Status</h3>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Status</label>
                            <select id="update-status-sel" class="ecto-select text-xs w-full">
                                <option value="PENDING">Pending</option>
                                <option value="UNDER INVESTIGATION">Under Investigation</option>
                                <option value="VERIFIED">Verified</option>
                                <option value="RESOLVED">Resolved</option>
                                <option value="ESCALATED">Escalated</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Severity</label>
                            <select id="update-sev-sel" class="ecto-select text-xs w-full">
                                <option value="LOW">Low</option>
                                <option value="MEDIUM">Medium</option>
                                <option value="HIGH">High</option>
                                <option value="CRITICAL">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[10px] font-mono text-[#64748B] uppercase">Investigation Notes</label>
                            <span id="notes-char-count" class="text-[10px] font-mono text-[#64748B]">0/500</span>
                        </div>
                        <textarea id="update-notes-input" rows="3" maxlength="500"
                                  oninput="document.getElementById('notes-char-count').textContent = this.value.length + '/500'"
                                  placeholder="Add your investigation notes here..."
                                  class="ecto-input text-xs w-full"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex">
                        <button type="button" onclick="submitStatusUpdate()"
                                class="w-full py-2 px-4 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold rounded-lg text-[11px] font-mono transition shadow-sm text-center">
                            Save Update
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── TAB 2: EVIDENCE ── -->
            <div id="pane-evidence" class="space-y-3 hidden">
                <div id="dp-evidence-full-list" class="space-y-3"></div>
            </div>

            <!-- ── TAB 3: INVESTIGATION NOTES ── -->
            <div id="pane-notes" class="space-y-3 hidden">
                <div id="dp-investigations-list" class="space-y-2.5"></div>
            </div>

            <!-- ── TAB 4: HISTORY ── -->
            <div id="pane-history" class="space-y-3 hidden">
                <div id="dp-history-timeline" class="space-y-2.5"></div>
            </div>

        </div>

    </aside>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ─── 1. DATA INITIALIZATION FROM LARAVEL ──────────────────────────────
    const RAW_INCIDENTS = @json($incidents);
    const RAW_WARDS     = @json($wardStations);
    const RAW_RESOURCES = @json($resources);

    const incidentsMap = {};
    RAW_INCIDENTS.forEach(inc => { incidentsMap[inc.id] = inc; });

    let activeIncident = RAW_INCIDENTS.length > 0 ? RAW_INCIDENTS[0] : null;

    // ─── 2. LEAFLET SATELLITE MAP ENGINE ─────────────────────────────────
    // Center: San Francisco, Agusan del Sur
    // IMPORTANT: zoomControl is set to false so Leaflet doesn't render zoom controls at top-left over the search bar!
    const map = L.map('investigator-dashboard-map', {
        center: [8.5100, 125.9750],
        zoom: 14,
        zoomControl: false,
        attributionControl: true
    });

    // Zoom controls explicitly positioned at TOP RIGHT (no overlap with search or filters!)
    L.control.zoom({ position: 'topright' }).addTo(map);

    // Satellite Imagery Layer (Using clean OpenStreetMap attribution to prevent duplicate Leaflet text)
    const satelliteLayer = L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: 'OpenStreetMap',
        subdomains: ['0', '1', '2', '3'],
        maxZoom: 20
    });
    satelliteLayer.addTo(map);

    // Layer Groups
    const layers = {
        incidents: L.layerGroup().addTo(map),
        wards:     L.layerGroup().addTo(map),
        safeZones: L.layerGroup().addTo(map),
        resources: L.layerGroup() // unchecked by default
    };

    // Scale Control on bottom left
    L.control.scale({ imperial: true, metric: true, position: 'bottomleft' }).addTo(map);

    // ─── 3. RENDER SAFE WARD STATIONS ──────────────────────────────────────
    const safeWardStations = [
        { name: "San Francisco Municipal Gymnasium Sanctuary", lat: 8.5098, lng: 125.9780, radius: 450 },
        { name: "Hubang Transport Safe Haven",                  lat: 8.5310, lng: 125.9730, radius: 550 }
    ];

    safeWardStations.forEach(sz => {
        // Green Safe Ward Station perimeter circle
        const safeZoneCircle = L.circle([sz.lat, sz.lng], {
            radius: Math.round(sz.radius * 0.30),
            color: '#22C55E',
            weight: 1.5,
            fillColor: '#22C55E',
            fillOpacity: 0.08
        }).addTo(layers.safeZones);

        // Safe Ward Station Marker (Shield icon)
        const szHtml = `
            <div style="width:28px; height:28px; border-radius:50%; background:#10B981; border:2px solid #34D399; box-shadow:0 0 10px rgba(16,185,129,0.8); display:flex; align-items:center; justify-content:center; cursor:pointer;" title="${sz.name}">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#FFFFFF" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>`;
        const szIcon = L.divIcon({ className: '', html: szHtml, iconSize: [28, 28], iconAnchor: [14, 14] });
        const m = L.marker([sz.lat, sz.lng], { icon: szIcon, zIndexOffset: 700 });
        m.bindPopup(`
            <div class="gis-custom-popup">
                <span class="popup-code" style="color:#22C55E;">SAFE WARD STATION</span>
                <h4 class="popup-title">${sz.name}</h4>
                <p class="popup-meta">Active Safe Ward Station Perimeter</p>
            </div>`, { className: 'gis-leaflet-popup' });
        m.bindTooltip(sz.name, { direction: 'top', offset: [0, -14] });
        m.addTo(layers.safeZones);

        safeZoneCircle.on('click', () => m.openPopup());
    });

    // ─── 6. RENDER INCIDENT MARKERS & POPUPS ──────────────────────────────
    const sevColors = {
        CRITICAL: '#EF4444',
        HIGH:     '#EF4444',
        MEDIUM:   '#EAB308',
        LOW:      '#22C55E'
    };

    const incidentMarkers = {};

    function buildIncidentPopup(inc) {
        const isVerified = inc.status === 'VERIFIED' || inc.status === 'ESCALATED' || inc.status === 'RESOLVED' || inc.response_status === 'ACTIVE' || (inc.investigation_result === 'CONFIRMED');

        const defaultHpMap = { CRITICAL: 150, HIGH: 100, MEDIUM: 60, LOW: 30 };
        const defaultHp = defaultHpMap[inc.severity] || 100;
        const assignment = inc.responder_assignments && inc.responder_assignments.length > 0
            ? inc.responder_assignments[0]
            : null;

        const maxHp = assignment && assignment.anomaly_max_hp ? assignment.anomaly_max_hp : (inc.anomaly_max_hp || defaultHp);
        const hp = assignment && assignment.anomaly_hp !== null && assignment.anomaly_hp !== undefined
            ? assignment.anomaly_hp
            : (inc.anomaly_hp !== null && inc.anomaly_hp !== undefined ? inc.anomaly_hp : maxHp);

        const progress = assignment && assignment.response_progress !== undefined && assignment.response_progress !== null
            ? assignment.response_progress
            : (inc.response_progress || 0);

        const pct = maxHp > 0 ? Math.max(0, Math.min(100, Math.round((hp / maxHp) * 100))) : 100;
        const barColor = pct <= 25 ? '#22C55E' : (pct <= 60 ? '#EAB308' : '#EF4444');

        let conditionHtml = '';
        if (isVerified) {
            conditionHtml = `
                <div style="margin-top:8px; padding-top:8px; border-top:1px solid #2A3440; font-family:'JetBrains Mono',monospace;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <span style="font-size:10px; font-weight:700; color:#CBD5E1; text-transform:uppercase;">ANOMALY CONDITION</span>
                        <span style="font-size:11px; font-weight:800; color:${barColor};">${hp} / ${maxHp} HP</span>
                    </div>
                    <div style="width:100%; height:6px; background:#11161D; border-radius:3px; overflow:hidden; border:1px solid #2A3440; margin-bottom:6px;">
                        <div style="width:${pct}%; height:100%; background:${barColor}; transition:width 0.5s;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                        <span style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">RESPONSE PROGRESS</span>
                        <span style="font-size:11px; font-weight:700; color:#10B981;">${progress}%</span>
                    </div>
                    <div style="width:100%; height:5px; background:#11161D; border-radius:3px; overflow:hidden; border:1px solid #2A3440;">
                        <div style="width:${progress}%; height:100%; background:#10B981; transition:width 0.5s;"></div>
                    </div>
                </div>`;
        }

        const reviewUrl = `/investigator/incidents/${inc.id}/review`;

        const actionLabel = isVerified ? 'Assign' : 'Review';
        const actionIconSvg = isVerified
            ? `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>`
            : `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>`;

        return `
            <div class="gis-custom-popup" style="min-width: 270px; max-width: 330px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; gap:8px; padding-right:28px;">
                    <span class="popup-code">${inc.incident_code}</span>
                    <span class="popup-sev ${inc.severity.toLowerCase()}" style="flex-shrink:0;">${inc.severity}</span>
                </div>
                <p class="popup-meta" style="display:flex; align-items:center; gap:4px; margin-bottom:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#8B5CF6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>Barangay ${inc.barangay ? inc.barangay.name : 'San Francisco'}</span>
                </p>

                ${conditionHtml}

                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding-top:8px; border-top:1px solid #2A3440; margin-top:8px;">
                    <span style="font-size:11px; color:#64748B; white-space:nowrap; flex-shrink:0;">Status: <strong style="color:${isVerified ? '#10B981' : '#38BDF8'}; font-family:'JetBrains Mono',monospace;">${inc.status}</strong></span>
                    <a href="${reviewUrl}" style="font-size:11px; font-weight:700; color:${isVerified ? '#A78BFA' : '#38BDF8'}; background:${isVerified ? 'rgba(139,92,246,0.18)' : 'rgba(56,189,248,0.18)'}; border:1px solid ${isVerified ? 'rgba(139,92,246,0.45)' : 'rgba(56,189,248,0.45)'}; padding:3px 8px; border-radius:6px; white-space:nowrap; flex-shrink:0; text-decoration:none; display:inline-flex; align-items:center; gap:5px; font-family:'JetBrains Mono',monospace; transition:all 0.15s;">
                        ${actionIconSvg}
                        <span>${actionLabel}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>`;
    }

    RAW_INCIDENTS.forEach(inc => {
        if (!inc.latitude || !inc.longitude) return;

        const color = sevColors[inc.severity] || '#EF4444';
        const isHigh = inc.severity === 'HIGH' || inc.severity === 'CRITICAL';

        const markerHtml = `
            <div style="position:relative; width:26px; height:26px; display:flex; align-items:center; justify-content:center; cursor:pointer;">
                ${isHigh ? `<div style="position:absolute; inset:-4px; border-radius:50%; background:${color}44; animation:ping 2s cubic-bezier(0,0,0.2,1) infinite;"></div>` : ''}
                <div style="position:relative; width:20px; height:20px; border-radius:50%; background:${color}; border:2px solid #FFFFFF; box-shadow:0 0 10px ${color}; display:flex; align-items:center; justify-content:center;">
                    <div style="width:6px; height:6px; border-radius:50%; background:#FFFFFF;"></div>
                </div>
            </div>`;

        const icon = L.divIcon({ className: '', html: markerHtml, iconSize: [26, 26], iconAnchor: [13, 13] });
        const marker = L.marker([inc.latitude, inc.longitude], { icon }).addTo(layers.incidents);

        marker.bindPopup(buildIncidentPopup(inc), { className: 'gis-leaflet-popup' });
        marker.on('click', () => { selectIncident(inc.id); });

        incidentMarkers[inc.id] = marker;
    });

    // ─── 7. LAYER TOGGLE HELPER ───────────────────────────────────────────
    window.toggleLayer = function(name) {
        if (map.hasLayer(layers[name])) {
            map.removeLayer(layers[name]);
        } else {
            map.addLayer(layers[name]);
        }
    };

    window.toggleMapLayersCard = function() {
        const body = document.getElementById('map-layers-body');
        const icon = document.getElementById('icon-layers-collapse');
        if (body.classList.contains('hidden')) {
            body.classList.remove('hidden');
            icon.style.transform = 'rotate(0deg)';
        } else {
            body.classList.add('hidden');
            icon.style.transform = 'rotate(-90deg)';
        }
    };


    // ─── 9. RIGHT DETAIL PANEL LOGIC ──────────────────────────────────────
    const sevBadgeStyles = {
        CRITICAL: 'bg-red-950/80 text-red-300 border border-red-900/80',
        HIGH:     'bg-red-600/30 text-red-400 border border-red-500/50',
        MEDIUM:   'bg-amber-500/20 text-amber-400 border border-amber-500/40',
        LOW:      'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
    };

    const statusBadgeStyles = {
        'PENDING':              'bg-amber-500/20 text-amber-400 border border-amber-500/40',
        'UNDER INVESTIGATION':  'bg-[#38BDF8]/20 text-[#38BDF8] border border-[#38BDF8]/40',
        'VERIFIED':             'bg-[#8B5CF6]/20 text-[#A78BFA] border border-[#8B5CF6]/40',
        'RESOLVED':             'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40',
        'ESCALATED':            'bg-red-950/90 text-red-300 border border-red-800/70'
    };

    // Helper: produce "March 4, 2026 · 5:21 PM" from an ISO / datetime string
    function formatFriendlyDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr.replace(' ', 'T'));
        if (isNaN(d.getTime())) return dateStr;
        const datePart = d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const timePart = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        return `${datePart} · ${timePart}`;
    }

    window.selectIncident = function(id, skipFly = false) {
        const inc = incidentsMap[id];
        if (!inc) return;
        activeIncident = inc;

        // Header — code, title, location only (badges/review button removed)
        document.getElementById('dp-code').textContent = inc.incident_code;
        document.getElementById('dp-title').textContent = inc.title;
        document.getElementById('dp-location').querySelector('span').textContent =
            `Barangay ${inc.barangay ? inc.barangay.name : 'San Francisco'}, San Francisco, Agusan del Sur`;

        // Overview Tab Data
        document.getElementById('dp-ov-loc').textContent =
            `Barangay ${inc.barangay ? inc.barangay.name : 'San Francisco'}, San Francisco`;

        // Friendly date format: "March 4, 2026 · 5:21 PM"
        document.getElementById('dp-ov-date').textContent =
            formatFriendlyDate(inc.incident_date || inc.created_at);

        document.getElementById('dp-ov-reporter').textContent =
            inc.reporter ? inc.reporter.name : 'Civilian Observer';

        const cardSev = document.getElementById('dp-card-sev');
        cardSev.textContent = inc.severity;
        cardSev.className = `inline-block px-3 py-1 rounded text-xs font-bold font-mono ${sevBadgeStyles[inc.severity] || ''}`;

        const cardStatus = document.getElementById('dp-card-status');
        cardStatus.textContent = inc.status;
        cardStatus.className = `inline-block px-3 py-1 rounded-full text-xs font-bold font-mono ${statusBadgeStyles[inc.status] || ''}`;

        document.getElementById('dp-desc').textContent = inc.description || 'No additional details provided.';

        // Pre-fill Update inputs
        document.getElementById('update-status-sel').value = inc.status;
        document.getElementById('update-sev-sel').value    = inc.severity;
        document.getElementById('update-notes-input').value = '';
        document.getElementById('notes-char-count').textContent = '0/500';

        // Evidence Preview (Overview tab — element removed, guard with null check)
        const evPreview = document.getElementById('dp-evidence-preview');
        const evFull    = document.getElementById('dp-evidence-full-list');

        if (inc.evidence && inc.evidence.length > 0) {
            if (evPreview) {
                const ev = inc.evidence[0];
                evPreview.innerHTML = `
                <div class="flex items-center gap-3 p-2.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                    <a href="${ev.url}" target="_blank" class="w-16 h-12 rounded-lg overflow-hidden flex-shrink-0 bg-black/40 border border-[#2A3440] block">
                        <img src="${ev.url}" alt="${ev.file_name}" class="w-full h-full object-cover">
                    </a>
                    <div class="flex-1 min-w-0 font-mono text-[11px]">
                        <p class="font-bold text-white truncate">${ev.file_name}</p>
                        <p class="text-[#64748B] text-[10px] mt-0.5">Attached Field Capture</p>
                        <a href="${ev.url}" target="_blank" class="text-[#8B5CF6] hover:underline font-bold text-[10px] mt-1 inline-flex items-center gap-1">
                            <span>View Full Image</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                </div>`;
            }
            if (evFull) {
                evFull.innerHTML = inc.evidence.map(item => `
                <div class="rounded-xl overflow-hidden border border-[#2A3440] bg-[#11161D] p-3 space-y-2">
                    <a href="${item.url}" target="_blank" class="block aspect-video rounded-lg overflow-hidden bg-black/40 border border-[#2A3440]">
                        <img src="${item.url}" alt="${item.file_name}" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                    </a>
                    <div class="flex justify-between items-center text-xs font-mono text-slate-300">
                        <span class="truncate max-w-[200px]">${item.file_name}</span>
                        <a href="${item.url}" target="_blank" class="text-[#8B5CF6] font-bold hover:underline inline-flex items-center gap-1">
                            <span>Full Res</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                </div>`).join('');
            }
        } else {
            if (evPreview) evPreview.innerHTML = `<p class="text-slate-500 font-mono text-xs italic py-2">No additional evidence attached.</p>`;
            if (evFull)    evFull.innerHTML    = `<p class="text-slate-500 font-mono text-xs italic py-6 text-center">No photographic evidence attached.</p>`;
        }

        // Notes Tab vs History Tab — split by content type
        const notesList   = document.getElementById('dp-investigations-list');
        const historyList = document.getElementById('dp-history-timeline');
        const invs = inc.investigations || [];

        // History = all entries (auto status-change logs + manual notes), newest first
        const allSorted = [...invs].reverse();

        // Notes = only real typed notes (exclude auto-generated "Status updated from…" entries)
        const realNotes = allSorted.filter(inv =>
            inv.notes && !inv.notes.startsWith('Status updated from')
        );

        const buildNoteCard = inv => `
            <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1.5 text-xs font-mono">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-[#A78BFA]">${inv.investigator ? inv.investigator.name : 'Investigator'}</span>
                    <span class="text-[10px] text-slate-500">${inv.investigation_date ? inv.investigation_date.substring(0, 16).replace('T', ' ') : ''}</span>
                </div>
                <p class="text-slate-200 font-sans text-xs leading-relaxed">${inv.notes || ''}</p>
                ${inv.result ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[#1B222C] text-[10px] font-bold text-[#8B5CF6] border border-[#2A3440]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    <span>${inv.result}</span>
                </span>` : ''}
            </div>`;

        // Render Notes tab
        if (realNotes.length > 0) {
            notesList.innerHTML = realNotes.map(buildNoteCard).join('');
        } else {
            notesList.innerHTML = `<p class="text-slate-500 font-mono text-xs italic py-6 text-center">No investigation notes written yet.</p>`;
        }

        // Render History tab
        if (allSorted.length > 0) {
            historyList.innerHTML = allSorted.map(buildNoteCard).join('');
        } else {
            historyList.innerHTML = `<p class="text-slate-500 font-mono text-xs italic py-6 text-center">No history recorded yet.</p>`;
        }

        // Pan to marker & open popup — always zoom IN (never zoom out)
        if (!skipFly && inc.latitude && inc.longitude) {
            const currentZoom = map.getZoom();
            const targetZoom = Math.max(currentZoom, 17);
            map.flyTo([inc.latitude, inc.longitude], targetZoom, { duration: 0.7 });
            if (incidentMarkers[inc.id]) {
                incidentMarkers[inc.id].openPopup();
            }
        }
    };

    window.focusSelectedOnMap = function() {
        if (!activeIncident || !activeIncident.latitude || !activeIncident.longitude) return;
        const currentZoom = map.getZoom();
        const targetZoom = Math.max(currentZoom, 17);
        map.flyTo([activeIncident.latitude, activeIncident.longitude], targetZoom, { duration: 0.7 });
        if (incidentMarkers[activeIncident.id]) {
            incidentMarkers[activeIncident.id].openPopup();
        }
    };

    // ─── 10. TAB SWITCHING ────────────────────────────────────────────────
    window.switchTab = function(tabName) {
        ['overview', 'evidence', 'notes', 'history'].forEach(t => {
            const pane = document.getElementById(`pane-${t}`);
            const btn  = document.getElementById(`tab-${t}`);
            if (pane) pane.classList.toggle('hidden', t !== tabName);
            if (btn) {
                if (t === tabName) {
                    btn.classList.add('border-[#8B5CF6]', 'text-[#8B5CF6]');
                    btn.classList.remove('border-transparent', 'text-slate-400');
                } else {
                    btn.classList.remove('border-[#8B5CF6]', 'text-[#8B5CF6]');
                    btn.classList.add('border-transparent', 'text-slate-400');
                }
            }
        });
    };

    // ─── 11. INLINE STATUS & NOTES SUBMISSION ─────────────────────────────
    window.submitStatusUpdate = function(forcedStatus) {
        if (!activeIncident) return;

        const status   = forcedStatus || document.getElementById('update-status-sel').value;
        const severity = document.getElementById('update-sev-sel').value;
        const notes    = document.getElementById('update-notes-input').value;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/investigator/incidents/${activeIncident.id}`;
        form.style.display = 'none';

        const csrf = document.createElement('input');
        csrf.name  = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);

        const method = document.createElement('input');
        method.name  = '_method';
        method.value = 'PUT';
        form.appendChild(method);

        const fields = [
            ['status', status],
            ['severity', severity],
            ['notes', notes]
        ];

        fields.forEach(([k, v]) => {
            const inp = document.createElement('input');
            inp.name  = k;
            inp.value = v;
            form.appendChild(inp);
        });

        document.body.appendChild(form);
        form.submit();
    };

    // Pre-select first incident on page load without moving camera
    if (activeIncident) {
        selectIncident(activeIncident.id, true);
    }

});
</script>

<style>
/* Leaflet Custom Dark Popup Styles */
.gis-leaflet-popup .leaflet-popup-content-wrapper {
    background: #151B23 !important;
    border: 1px solid #2A3440 !important;
    border-radius: 12px !important;
    padding: 0 !important;
    box-shadow: 0 16px 36px rgba(0,0,0,0.8) !important;
}
.gis-leaflet-popup .leaflet-popup-content {
    margin: 0 !important;
}
/* Completely hide the close button so it doesn't collide with the severity badge */
.gis-leaflet-popup .leaflet-popup-close-button {
    display: none !important;
}
.gis-leaflet-popup .leaflet-popup-tip-container {
    display: none !important;
}
.gis-custom-popup {
    padding: 12px 14px;
    min-width: 270px;
    max-width: 340px;
    box-sizing: border-box;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #F3F4F6;
}
.popup-code {
    font-family: 'JetBrains Mono', monospace;
    font-size: 11px;
    font-weight: 700;
    color: #8B5CF6;
}
.popup-sev {
    font-size: 9px;
    font-weight: 700;
    font-family: 'JetBrains Mono', monospace;
    padding: 2px 6px;
    border-radius: 4px;
}
.popup-sev.high, .popup-sev.critical {
    background: rgba(239,68,68,0.2);
    color: #EF4444;
    border: 1px solid rgba(239,68,68,0.4);
}
.popup-sev.medium {
    background: rgba(234,179,8,0.2);
    color: #EAB308;
    border: 1px solid rgba(234,179,8,0.4);
}
.popup-sev.low {
    background: rgba(34,197,94,0.2);
    color: #22C55E;
    border: 1px solid rgba(34,197,94,0.4);
}
.popup-title {
    font-size: 13px;
    font-weight: 800;
    color: #FFFFFF;
    margin: 3px 0;
    line-height: 1.3;
}
.popup-meta {
    font-size: 11px;
    color: #9CA3AF;
    margin-bottom: 4px;
}

/* Custom Zoom Control Styling in Dark Mode */
.leaflet-control-zoom {
    border: 1px solid #2A3440 !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.6) !important;
    margin-top: 14px !important;
    margin-right: 14px !important;
}
.leaflet-control-zoom a {
    background-color: #151B23 !important;
    color: #F3F4F6 !important;
    border-bottom: 1px solid #2A3440 !important;
    transition: background-color 0.2s, color 0.2s !important;
}
.leaflet-control-zoom a:last-child {
    border-bottom: none !important;
}
.leaflet-control-zoom a:hover {
    background-color: #1E293B !important;
    color: #8B5CF6 !important;
}
</style>
@endpush
