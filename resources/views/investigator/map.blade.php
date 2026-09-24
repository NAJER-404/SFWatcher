@extends('layouts.investigator')

@section('title', 'GIS Operational Map — Investigator Portal')

@section('content')
<!-- INVESTIGATOR SIDEBAR -->
@include('investigator.partials.sidebar')

<!-- FULL MAP CANVAS -->
<main class="flex-1 flex flex-col h-full min-w-0 overflow-hidden relative bg-[#06090D]">

    <!-- Top Overlay Header for Map Controls -->
    <div class="absolute top-3 left-3 right-3 z-[400] flex flex-wrap items-center justify-between gap-3 pointer-events-none">
        <!-- Breadcrumb Pill -->
        <div class="pointer-events-auto px-3 py-1.5 rounded-lg bg-[#151B23]/90 backdrop-blur border border-[#2A3440] text-xs font-mono text-slate-200 shadow-xl flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>San Francisco, Agusan del Sur &bull; Active GIS Layer</span>
        </div>

        <!-- Layer Selector Pills -->
        <div class="pointer-events-auto flex items-center gap-1.5 text-xs font-mono">
            <button id="toggle-incidents-btn" onclick="toggleMapLayer('incidents')" class="px-2.5 py-1.5 rounded-lg bg-[#151B23]/90 backdrop-blur border border-[#EF4444]/50 text-[#EF4444] font-bold hover:bg-[#1B222C] transition shadow-lg">
                &bull; Incidents ({{ $incidents->count() }})
            </button>
            <button id="toggle-safe-btn" onclick="toggleMapLayer('safeZones')" class="px-2.5 py-1.5 rounded-lg bg-[#151B23]/90 backdrop-blur border border-[#22C55E]/50 text-[#22C55E] font-bold hover:bg-[#1B222C] transition shadow-lg">
                &bull; Safe Ward Stations
            </button>
        </div>
    </div>

    <!-- Leaflet Map Container -->
    <div id="full-investigator-map" class="w-full h-full bg-[#06090D]"></div>

</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const incidentsData = @json($incidents);
    const wardsData = @json($wardStations);
    const resourcesData = @json($resources);

    const map = L.map('full-investigator-map', {
        center: [8.5100, 125.9750],
        zoom: 14,
        zoomControl: true,
        attributionControl: false
    });

    L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: 'Imagery &copy; Google',
        maxZoom: 21,
        subdomains: ['0','1','2','3'],
        tileSize: 256
    }).addTo(map);

    const layers = {
        incidents: L.layerGroup().addTo(map),
        wards: L.layerGroup().addTo(map),
        safeZones: L.layerGroup().addTo(map),
        resources: L.layerGroup().addTo(map)
    };

    // 1. Incidents
    incidentsData.forEach(inc => {
        const sevLower = (inc.severity || 'low').toLowerCase();
        const iconHtml = `
            <div class="gis-marker-wrapper" title="${inc.incident_code}: ${inc.title}">
                <div class="marker-incident ${sevLower}">
                    <span style="font-size:10px;font-weight:bold;color:#FFFFFF;">●</span>
                </div>
            </div>`;

        const icon = L.divIcon({
            className: '',
            html: iconHtml,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -16]
        });

        const m = L.marker([inc.latitude, inc.longitude], { icon });
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

        m.bindPopup(`
            <div style="padding: 12px 14px; min-width: 250px; font-family:'Plus Jakarta Sans',sans-serif;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
                    <span style="font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:700; color:#8B5CF6;">${inc.incident_code}</span>
                    <span class="badge-${sevLower}" style="font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px;">${inc.severity}</span>
                </div>
                <h4 style="font-size:12px; font-weight:700; color:#FFFFFF; line-height:1.3; margin-bottom:4px;">${inc.title}</h4>
                <p style="font-size:10px; color:#9CA3AF; margin-bottom:4px;">Brgy. ${inc.barangay ? inc.barangay.name : 'San Francisco'} &bull; ${repDate}</p>

                ${conditionHtml}

                <div style="display:flex; justify-content:space-between; align-items:center; padding-top:6px; border-top:1px solid #2A3440; margin-top:6px;">
                    <span style="font-size:10px; color:#64748B;">Status: <strong style="color:${isVerified ? '#10B981' : '#D1D5DB'}; font-family:'JetBrains Mono',monospace;">${inc.status}</strong></span>
                    <a href="${reviewUrl}" style="background:#8B5CF6; color:#FFFFFF; padding:5px 10px; border-radius:6px; font-size:10px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                        <span>${isVerified ? 'ASSIGN' : 'REVIEW'}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        `);

        layers.incidents.addLayer(m);
    });

    });

    // 2. Safe Ward Stations
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
            <div style="padding: 10px 12px; font-family:'Plus Jakarta Sans',sans-serif;">
                <div style="font-size:9px; font-weight:700; color:#22C55E; font-family:'JetBrains Mono',monospace;">SAFE WARD STATION</div>
                <h4 style="font-size:12px; font-weight:700; color:#FFFFFF; margin-bottom:2px;">${sz.name}</h4>
                <p style="font-size:10px; color:#9CA3AF;">Capacity: ${sz.capacity} civilians &bull; Status: Operational</p>
            </div>
        `);

        layers.safeZones.addLayer(m);
    });

    window.toggleMapLayer = function(layerKey) {
        const btnMap = {
            incidents: document.getElementById('toggle-incidents-btn'),
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
});
</script>
@endpush
