@extends('layouts.responder')

@section('title', 'Tactical Response Map — Responder Portal')

@section('content')
@php
    // Falls back to the dashboard if your "Active Responses" route has a different name.
    // Change 'responder.assignments.index' to your real route name if needed.
    $backUrl = \Illuminate\Support\Facades\Route::has('responder.assignments.index')
        ? route('responder.assignments.index')
        : route('responder.dashboard');
@endphp

<style>
    /* ---------- Popup shell: remove Leaflet's default white frame ---------- */
    .rp-popup .leaflet-popup-content-wrapper {
        background: transparent;
        padding: 0;
        border-radius: 16px;
        box-shadow: 0 18px 40px rgba(0, 0, 0, .55);
    }
    .rp-popup .leaflet-popup-content { margin: 0; width: auto !important; }
    .rp-popup .leaflet-popup-tip { background: #151B23; box-shadow: none; }
    .rp-popup a.leaflet-popup-close-button {
        top: 10px; right: 10px; width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; line-height: 1; padding: 0;
        color: #94A3B8; background: #1E2733; border-radius: 8px;
        transition: background .15s, color .15s;
    }
    .rp-popup a.leaflet-popup-close-button:hover { background: #2A3440; color: #fff; }

    /* ---------- Popup card ---------- */
    .rp-card {
        width: min(300px, calc(100vw - 56px));
        background: #151B23; border: 1px solid #2A3440; border-radius: 16px;
        padding: 14px 16px 16px; color: #F3F4F6;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .rp-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .rp-top { padding-right: 34px; margin-bottom: 10px; min-height: 28px; }
    .rp-assigned {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 10px; border-radius: 999px;
        background: rgba(139, 92, 246, .16); border: 1px solid rgba(139, 92, 246, .45);
        color: #C4B5FD; font-size: 12px; font-weight: 600;
    }
    .rp-code { font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 600; color: #A78BFA; }
    .rp-sev { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 999px; border: 1px solid; letter-spacing: .02em; }
    .rp-title { font-size: 17px; font-weight: 700; color: #fff; line-height: 1.3; margin: 8px 0 6px; word-break: break-word; }
    .rp-meta { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #A8B3C2; }
    .rp-meta svg { flex: none; }
    .rp-section { margin-top: 14px; padding-top: 14px; border-top: 1px solid #2A3440; }
    .rp-stat + .rp-stat { margin-top: 12px; }
    .rp-label { font-size: 13px; font-weight: 500; color: #CBD5E1; }
    .rp-value { font-size: 13px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
    .rp-track { height: 8px; margin-top: 6px; background: #0E131A; border: 1px solid #2A3440; border-radius: 999px; overflow: hidden; }
    .rp-fill { height: 100%; border-radius: 999px; transition: width .5s; }
    .rp-gps { font-size: 13px; color: #A8B3C2; }
    .rp-gps strong { color: #E2E8F0; font-family: 'JetBrains Mono', monospace; font-weight: 600; white-space: nowrap; }
    .rp-btn {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        width: 100%; min-height: 42px; margin-top: 14px; padding: 10px 14px;
        border-radius: 10px; font-size: 14px; font-weight: 700; text-decoration: none;
        background: #8B5CF6; color: #fff !important; box-shadow: 0 4px 14px rgba(139, 92, 246, .4);
        transition: background .15s;
    }
    .rp-btn:hover { background: #7C3AED; }
    .rp-chip {
        display: flex; align-items: center; justify-content: center;
        width: 100%; min-height: 40px; margin-top: 14px; border-radius: 10px;
        font-size: 13px; font-weight: 600; background: #1E2733; color: #CBD5E1; border: 1px solid #2A3440;
    }
    .rp-chip.done { background: rgba(34, 197, 94, .15); color: #4ADE80; border-color: rgba(34, 197, 94, .4); }

    /* Layer pill scroller (mobile) */
    .rp-pills { scrollbar-width: none; }
    .rp-pills::-webkit-scrollbar { display: none; }
</style>

<!-- FULL MAP CANVAS (same structure as the investigator map) -->
<main class="flex-1 flex flex-col h-full min-w-0 overflow-hidden bg-[#06090D] p-3 sm:p-4">

    <!-- Framed map panel: margins on left, right and bottom -->
    <div class="relative isolate flex-1 min-h-[320px] w-full rounded-xl overflow-hidden border border-[#2A3440] shadow-xl">

    <!-- Top Overlay Header for Map Controls -->
    <div class="absolute top-3 left-3 right-3 z-[1000] flex flex-wrap items-center justify-between gap-3 pointer-events-none">
        <!-- Back to Active Responses -->
        <a href="{{ $backUrl }}"
           class="pointer-events-auto px-3 py-1.5 rounded-lg bg-[#151B23]/90 hover:bg-[#1B222C] backdrop-blur border border-[#2A3440] hover:border-[#8B5CF6] text-xs font-mono font-bold text-slate-200 hover:text-white shadow-xl flex items-center gap-1.5 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Active Responses</span>
        </a>

        <!-- Layer Selector Pills -->
        <div class="rp-pills pointer-events-auto flex items-center gap-1.5 text-xs font-mono overflow-x-auto max-w-full">
            <button id="toggle-my-assignments-btn" type="button" onclick="toggleMapLayer('myAssignments')" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-[#151B23]/90 backdrop-blur border border-[#8B5CF6]/60 text-[#A78BFA] font-bold hover:bg-[#1B222C] transition shadow-lg">
                &bull; <span class="hidden sm:inline">My Assignments</span><span class="sm:hidden">Mine</span> ({{ $myAssignments->count() }})
            </button>
            <button id="toggle-all-incidents-btn" type="button" onclick="toggleMapLayer('allIncidents')" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-[#151B23]/90 backdrop-blur border border-[#EF4444]/50 text-[#EF4444] font-bold hover:bg-[#1B222C] transition shadow-lg">
                &bull; All Incidents ({{ $incidents->count() }})
            </button>
            <button id="toggle-safe-btn" type="button" onclick="toggleMapLayer('safeZones')" class="shrink-0 px-2.5 py-1.5 rounded-lg bg-[#151B23]/90 backdrop-blur border border-[#22C55E]/50 text-[#22C55E] font-bold hover:bg-[#1B222C] transition shadow-lg">
                &bull; <span class="hidden sm:inline">Safe Ward Stations</span><span class="sm:hidden">Safe wards</span>
            </button>
        </div>
    </div>

    <!-- Leaflet Map Container -->
    <div id="full-responder-map" class="absolute inset-0 z-0 bg-[#06090D]"></div>

    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const allIncidents = @json($incidents);
    const myAssignments = @json($myAssignments);
    const myIncidentIds = @json($myIncidentIds);
    const wardsData = @json($wardStations);
    const focusIncidentId = {{ $focusId ?? 'null' }};
    const focusCode = @json($focusCode ?? null);

    // Escape text before putting it into popup HTML
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const myAssignmentMap = {};
    myAssignments.forEach(asgn => {
        if (asgn.incident_id) myAssignmentMap[asgn.incident_id] = asgn;
    });

    const map = L.map('full-responder-map', {
        center: [8.5100, 125.9750],
        zoom: 14,
        zoomControl: false,
        attributionControl: false
    });
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: 'Imagery &copy; Google',
        maxZoom: 21,
        subdomains: ['0', '1', '2', '3'],
        tileSize: 256
    }).addTo(map);

    // Keep the map sized correctly on rotate / window resize / sidebar toggle
    const mapEl = document.getElementById('full-responder-map');
    if (window.ResizeObserver) {
        new ResizeObserver(() => map.invalidateSize()).observe(mapEl);
    } else {
        window.addEventListener('resize', () => map.invalidateSize());
    }

    const layers = {
        myAssignments: L.layerGroup().addTo(map),
        allIncidents: L.layerGroup().addTo(map),
        safeZones: L.layerGroup().addTo(map)
    };

    const markerMapById = {};
    const markerMapByCode = {};

    const SEVERITY_STYLE = {
        CRITICAL: { bg: 'rgba(239,68,68,.18)',  fg: '#FCA5A5', bd: 'rgba(239,68,68,.5)' },
        HIGH:     { bg: 'rgba(249,115,22,.18)', fg: '#FDBA74', bd: 'rgba(249,115,22,.5)' },
        MEDIUM:   { bg: 'rgba(234,179,8,.18)',  fg: '#FDE047', bd: 'rgba(234,179,8,.5)' },
        LOW:      { bg: 'rgba(34,197,94,.18)',  fg: '#86EFAC', bd: 'rgba(34,197,94,.5)' }
    };

    const ICON_PIN = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>`;
    const ICON_BOLT = `<svg width="13" height="13" viewBox="0 0 24 24" fill="#C4B5FD" stroke="none"><path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/></svg>`;
    const ICON_ARROW = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>`;

    const prettyStatus = (s) => {
        const t = String(s || '').replace(/_/g, ' ').toLowerCase();
        return t.charAt(0).toUpperCase() + t.slice(1);
    };

    // 1. Plot incidents
    allIncidents.forEach(inc => {
        if (!inc.latitude || !inc.longitude) return;

        const isMyAssignment = myIncidentIds.includes(inc.id);
        const myAssignment = myAssignmentMap[inc.id] || null;
        const sevLower = (inc.severity || 'low').toLowerCase();
        const sevKey = (inc.severity || 'LOW').toUpperCase();
        const sev = SEVERITY_STYLE[sevKey] || SEVERITY_STYLE.LOW;

        let iconHtml = '';
        if (isMyAssignment) {
            iconHtml = `
                <div class="relative flex items-center justify-center cursor-pointer" title="Assigned to you - ${esc(inc.incident_code)}: ${esc(inc.title)}">
                    <span class="absolute w-8 h-8 rounded-full bg-[#8B5CF6]/40 animate-ping"></span>
                    <div class="w-7 h-7 rounded-full bg-[#8B5CF6] border-2 border-white flex items-center justify-center shadow-xl z-10 text-white font-mono text-[10px] font-bold">⚡</div>
                </div>`;
        } else {
            iconHtml = `
                <div class="gis-marker-wrapper cursor-pointer" title="${esc(inc.incident_code)}: ${esc(inc.title)}">
                    <div class="marker-incident ${sevLower}">
                        <span style="font-size:10px;font-weight:bold;color:#FFFFFF;">●</span>
                    </div>
                </div>`;
        }

        const icon = L.divIcon({
            className: '',
            html: iconHtml,
            iconSize: isMyAssignment ? [32, 32] : [28, 28],
            iconAnchor: isMyAssignment ? [16, 16] : [14, 14],
            popupAnchor: [0, -16]
        });

        const m = L.marker([inc.latitude, inc.longitude], { icon });

        const repDate = inc.created_at
            ? new Date(inc.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
            : '';
        const defaultHpMap = { CRITICAL: 150, HIGH: 110, MEDIUM: 80, LOW: 50 };
        const defaultHp = defaultHpMap[sevKey] || 80;

        const maxHp = myAssignment && myAssignment.anomaly_max_hp ? myAssignment.anomaly_max_hp : (inc.anomaly_max_hp || defaultHp);
        const hp = myAssignment && myAssignment.anomaly_hp !== null && myAssignment.anomaly_hp !== undefined
            ? myAssignment.anomaly_hp
            : (inc.anomaly_hp !== null && inc.anomaly_hp !== undefined ? inc.anomaly_hp : maxHp);
        const progress = myAssignment && myAssignment.response_progress !== undefined && myAssignment.response_progress !== null
            ? myAssignment.response_progress
            : (inc.response_progress || 0);

        const pct = maxHp > 0 ? Math.max(0, Math.min(100, Math.round((hp / maxHp) * 100))) : 100;
        const barColor = pct <= 25 ? '#22C55E' : (pct <= 60 ? '#EAB308' : '#EF4444');

        const topRow = isMyAssignment
            ? `<div class="rp-row rp-top"><span class="rp-assigned">${ICON_BOLT}Assigned to you</span></div>`
            : `<div class="rp-top" style="min-height:0;"></div>`;

        const conditionHtml = isMyAssignment ? `
            <div class="rp-section">
                <div class="rp-stat">
                    <div class="rp-row">
                        <span class="rp-label">Anomaly condition</span>
                        <span class="rp-value" style="color:${barColor};">${hp} / ${maxHp} HP</span>
                    </div>
                    <div class="rp-track"><div class="rp-fill" style="width:${pct}%; background:${barColor};"></div></div>
                </div>
                <div class="rp-stat">
                    <div class="rp-row">
                        <span class="rp-label">Containment progress</span>
                        <span class="rp-value" style="color:#34D399;">${progress}%</span>
                    </div>
                    <div class="rp-track"><div class="rp-fill" style="width:${progress}%; background:#10B981;"></div></div>
                </div>
            </div>` : '';

        let actionHtml = '';
        if (isMyAssignment && myAssignment) {
            const url = `/responder/assignments/${myAssignment.id}`;
            if (myAssignment.status === 'ASSIGNED') {
                actionHtml = `<a class="rp-btn" href="${url}"><span>Accept and respond</span>${ICON_ARROW}</a>`;
            } else if (myAssignment.status === 'COMPLETED') {
                actionHtml = `<div class="rp-chip done">Neutralized</div>`;
            } else {
                actionHtml = `<a class="rp-btn" href="${url}"><span>Open response screen</span>${ICON_ARROW}</a>`;
            }
        } else {
            actionHtml = `<div class="rp-chip">Status: ${esc(prettyStatus(inc.status))}</div>`;
        }

        m.bindPopup(`
            <div class="rp-card">
                ${topRow}
                <div class="rp-row">
                    <span class="rp-code">${esc(inc.incident_code)}</span>
                    <span class="rp-sev" style="background:${sev.bg}; color:${sev.fg}; border-color:${sev.bd};">${esc(prettyStatus(sevKey))}</span>
                </div>
                <h4 class="rp-title">${esc(inc.title)}</h4>
                <div class="rp-meta">${ICON_PIN}<span>Brgy. ${esc(inc.barangay ? inc.barangay.name : 'San Francisco')}${repDate ? ' &middot; ' + repDate : ''}</span></div>
                ${conditionHtml}
                <div class="rp-section rp-row" style="margin-top:14px;">
                    <span class="rp-gps">Coordinates</span>
                    <strong class="rp-gps" style="font-family:'JetBrains Mono',monospace; color:#E2E8F0; white-space:nowrap;">${Number(inc.latitude).toFixed(4)}, ${Number(inc.longitude).toFixed(4)}</strong>
                </div>
                ${actionHtml}
            </div>
        `, {
            className: 'rp-popup',
            maxWidth: Math.min(320, window.innerWidth - 40),
            minWidth: 0,
            autoPanPaddingTopLeft: [16, 70],
            autoPanPaddingBottomRight: [16, 16]
        });

        if (isMyAssignment) {
            layers.myAssignments.addLayer(m);
        } else {
            layers.allIncidents.addLayer(m);
        }

        markerMapById[inc.id] = m;
        if (inc.incident_code) markerMapByCode[inc.incident_code] = m;
    });

    // 2. Safe ward stations
    const safeWardStations = [
        { name: "San Francisco Municipal Gymnasium Sanctuary", lat: 8.5098, lng: 125.9780, capacity: 2500 },
        { name: "Hubang Transport Safe Haven", lat: 8.5310, lng: 125.9730, capacity: 1200 }
    ];

    safeWardStations.forEach(sz => {
        const shieldIcon = L.divIcon({
            className: '',
            html: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="30" height="30" fill="#22C55E" style="filter:drop-shadow(0 0 6px #22C55E99)"><path d="M12 2L3 7v5c0 5.25 3.75 10.15 9 11.35C17.25 22.15 21 17.25 21 12V7L12 2z"/></svg>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15],
        });

        const m = L.marker([sz.lat, sz.lng], { icon: shieldIcon });
        m.bindTooltip(sz.name, { direction: 'top', offset: [0, -14] });

        m.bindPopup(`
            <div class="rp-card">
                <div class="rp-row rp-top">
                    <span class="rp-assigned" style="background:rgba(34,197,94,.15); border-color:rgba(34,197,94,.45); color:#86EFAC;">Safe ward station</span>
                </div>
                <h4 class="rp-title" style="margin-top:0;">${esc(sz.name)}</h4>
                <div class="rp-section rp-row" style="margin-top:12px;">
                    <span class="rp-label">Capacity</span>
                    <span class="rp-value" style="color:#E2E8F0;">${sz.capacity.toLocaleString()} civilians</span>
                </div>
                <div class="rp-row" style="margin-top:10px;">
                    <span class="rp-label">Status</span>
                    <span class="rp-value" style="color:#4ADE80;">Operational</span>
                </div>
            </div>
        `, { className: 'rp-popup', maxWidth: Math.min(320, window.innerWidth - 40), minWidth: 0, autoPanPaddingTopLeft: [16, 70] });

        layers.safeZones.addLayer(m);
    });

    // 3. Layer toggle
    window.toggleMapLayer = function (layerKey) {
        const btnMap = {
            myAssignments: document.getElementById('toggle-my-assignments-btn'),
            allIncidents: document.getElementById('toggle-all-incidents-btn'),
            safeZones: document.getElementById('toggle-safe-btn')
        };
        const targetLayer = layers[layerKey];
        const btn = btnMap[layerKey];

        if (map.hasLayer(targetLayer)) {
            map.removeLayer(targetLayer);
            if (btn) btn.style.opacity = '0.4';
        } else {
            map.addLayer(targetLayer);
            if (btn) btn.style.opacity = '1';
        }
    };

    // 4. Auto-focus from "Check Map Location"
    if (focusIncidentId || focusCode) {
        const targetMarker = markerMapById[focusIncidentId] || markerMapByCode[focusCode];
        if (targetMarker) {
            setTimeout(() => {
                map.flyTo(targetMarker.getLatLng(), 16, { animate: true, duration: 1.2 });
                setTimeout(() => targetMarker.openPopup(), 1300);
            }, 300);
        }
    }
});
</script>
@endpush
