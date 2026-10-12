@extends('layouts.app')

@section('title', 'SFwatch — Interactive Map')

@section('content')
<!-- LEFT SIDEBAR -->
@include('spectral.partials.sidebar')

<!-- CENTER COLUMN: Stat Bar + Map + Bottom Panels -->
<div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden">

    <!-- ── MAP ──────────────────────────────────────────────── -->
    <main class="relative flex-1 h-full min-h-0 bg-[#06090D] overflow-hidden">

        <!-- Real Geographic Leaflet Map Container -->
        <div id="spectral-map" class="w-full h-full"></div>

        <!-- Location Selection Active HUD Banner -->
        <div id="location-picker-hud" style="display: none;" class="absolute top-3 inset-x-0 mx-auto max-w-md z-[500] px-4 py-2.5 rounded-lg bg-[#1B222C] border border-[#8B5CF6] text-white text-xs shadow-2xl flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#8B5CF6] animate-pulse"></span>
                <span class="font-medium text-slate-100">Click on the map to set coordinates</span>
            </div>
            <button onclick="SpectralMap.cancelLocationPicking()" class="px-2 py-1 bg-[#151B23] hover:bg-[#222B38] text-[#9CA3AF] hover:text-white border border-[#2A3440] rounded text-[10px] font-semibold transition">
                Cancel
            </button>
        </div>

        <!-- Geographic Jump Navigator (Top Left) -->
        <div class="absolute top-3 left-3 z-[400] flex flex-col gap-1">
            <button onclick="SpectralMap.jumpTo('san_francisco')" title="San Francisco Hub" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">SF</button>
            <button onclick="SpectralMap.jumpTo('agusan')" title="Agusan del Sur" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">ADS</button>
            <button onclick="SpectralMap.jumpTo('mindanao')" title="Mindanao Region" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">MIN</button>
            <button onclick="SpectralMap.jumpTo('philippines')" title="Philippines" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">PH</button>
        </div>

        <!-- Tile load error banner -->
        <div id="map-tile-error">Map tiles unavailable — check connection</div>

    </main>

</div>

<!-- RIGHT INFORMATION PANEL -->
@include('spectral.partials.infopanel')

@endsection

@section('modals')
@include('spectral.partials.report_modal_partial')

<!-- Server-injected initial state for instant Leaflet map bootstrap -->
<script>
    window.INITIAL_SPECTRAL_STATE = {
        incidents: @json($incidents),
        wards: @json($wardStations),
        resources: @json($resources),
        barangays: @json($barangays),
    };
</script>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const RAW_INCIDENTS = @json($incidents);
    const RAW_WARDS     = @json($wardStations);
    const RAW_RESOURCES = @json($resources);

    // Initialize SpectralData store if spectral.js is present
    if (typeof SpectralData !== 'undefined') {
        try { SpectralData.init(); } catch (_) {}
    }

    // Check if map already created
    if (window.SpectralMap && window.SpectralMap.map) {
        window.SpectralMap.map.invalidateSize();
        return;
    }

    if (typeof L === 'undefined') {
        console.error('[Map] Leaflet is not loaded');
        return;
    }

    const mapElement = document.getElementById('spectral-map');
    if (!mapElement) return;

    // ─── 1. LEAFLET SATELLITE MAP ENGINE (Identical setup to Investigator map) ───
    const map = L.map('spectral-map', {
        center: [8.5100, 125.9750],
        zoom: 14,
        zoomControl: false,
        attributionControl: true
    });

    // Zoom controls at TOP RIGHT
    L.control.zoom({ position: 'topright' }).addTo(map);

    // Satellite Imagery Layer (Same tile source as Investigator map)
    const satelliteLayer = L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: 'OpenStreetMap',
        subdomains: ['0', '1', '2', '3'],
        maxZoom: 20
    });
    satelliteLayer.addTo(map);

    // Layer Groups
    const layers = {
        incidents: L.layerGroup().addTo(map),
        safeZones: L.layerGroup().addTo(map)
    };

    // Scale Control at bottom left
    L.control.scale({ imperial: true, metric: true, position: 'bottomleft' }).addTo(map);

    // Restore persisted filter states from localStorage
    let incidentsVisible = true;
    let safeZonesVisible = true;
    try {
        const savedInc = localStorage.getItem('spectral_layer_incidents');
        if (savedInc !== null) incidentsVisible = (savedInc !== '0');
        const savedSafe = localStorage.getItem('spectral_layer_safeZones');
        if (savedSafe !== null) safeZonesVisible = (savedSafe !== '0');
    } catch (_) {}

    if (!incidentsVisible) map.removeLayer(layers.incidents);
    if (!safeZonesVisible) map.removeLayer(layers.safeZones);

    // ─── 2. RENDER SAFE WARD STATIONS (Same setup as Investigator map) ───
    const safeWardStations = [
        { name: "San Francisco Municipal Gymnasium Sanctuary", lat: 8.5098, lng: 125.9780, radius: 450, capacity: 2500 },
        { name: "Hubang Transport Safe Haven",                  lat: 8.5310, lng: 125.9730, radius: 550, capacity: 1200 }
    ];

    safeWardStations.forEach(sz => {
        const safeZoneCircle = L.circle([sz.lat, sz.lng], {
            radius: Math.round(sz.radius * 0.30),
            color: '#22C55E',
            weight: 1.5,
            fillColor: '#22C55E',
            fillOpacity: 0.08
        }).addTo(layers.safeZones);

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
                <p class="popup-meta">Active Safe Ward Station Perimeter &bull; Capacity: ${sz.capacity} civilians</p>
            </div>`, { className: 'gis-leaflet-popup' });
        m.bindTooltip(sz.name, { direction: 'top', offset: [0, -14] });
        m.addTo(layers.safeZones);

        safeZoneCircle.on('click', () => {
            m.openPopup();
            map.flyTo([sz.lat, sz.lng], Math.max(map.getZoom(), 16), { duration: 0.7 });
        });
        m.on('click', () => {
            map.flyTo([sz.lat, sz.lng], Math.max(map.getZoom(), 16), { duration: 0.7 });
        });
    });

    // ─── 3. RENDER INCIDENT MARKERS & POPUPS (Same styling as Investigator map) ───
    const sevColors = {
        CRITICAL: '#000000',
        HIGH:     '#EF4444',
        MEDIUM:   '#EAB308',
        LOW:      '#22C55E'
    };

    const incidentMarkers = {};

    RAW_INCIDENTS.forEach(inc => {
        if (!inc.latitude || !inc.longitude) return;
        const lat = parseFloat(inc.latitude);
        const lng = parseFloat(inc.longitude);
        if (isNaN(lat) || isNaN(lng)) return;

        const color = sevColors[inc.severity] || '#22C55E';
        const isHigh = inc.severity === 'HIGH' || inc.severity === 'CRITICAL';
        const isCritical = inc.severity === 'CRITICAL';
        const pulseBg = isCritical ? 'rgba(0,0,0,0.6)' : `${color}44`;
        const markerBorder = isCritical ? '#9CA3AF' : '#FFFFFF';
        const markerShadow = isCritical ? '0 0 12px rgba(0,0,0,0.95)' : `0 0 10px ${color}`;

        const markerHtml = `
            <div style="position:relative; width:26px; height:26px; display:flex; align-items:center; justify-content:center; cursor:pointer;" title="${inc.incident_code || ('SF-INC-' + inc.id)}: ${inc.title}">
                ${isHigh ? `<div style="position:absolute; inset:-4px; border-radius:50%; background:${pulseBg}; animation:ping 2s cubic-bezier(0,0,0.2,1) infinite;"></div>` : ''}
                <div style="position:relative; width:20px; height:20px; border-radius:50%; background:${color}; border:2px solid ${markerBorder}; box-shadow:${markerShadow}; display:flex; align-items:center; justify-content:center;">
                    <div style="width:6px; height:6px; border-radius:50%; background:#FFFFFF;"></div>
                </div>
            </div>`;

        const icon = L.divIcon({ className: '', html: markerHtml, iconSize: [26, 26], iconAnchor: [13, 13] });
        const marker = L.marker([lat, lng], { icon, zIndexOffset: 800 }).addTo(layers.incidents);

        // Anomaly condition calculation (consistent with Investigator map)
        const isVerified = ['VERIFIED', 'RESOLVED'].includes(inc.status) || inc.response_status === 'ACTIVE' || (inc.investigation_result === 'CONFIRMED');
        const defaultHpMap = { CRITICAL: 150, HIGH: 110, MEDIUM: 80, LOW: 50 };
        const defaultHp = defaultHpMap[inc.severity] || 80;
        const assignment = inc.responder_assignments && inc.responder_assignments.length > 0 ? inc.responder_assignments[0] : null;
        const maxHp = assignment && assignment.anomaly_max_hp ? assignment.anomaly_max_hp : (inc.anomaly_max_hp || defaultHp);
        const hp = assignment && assignment.anomaly_hp !== null && assignment.anomaly_hp !== undefined ? assignment.anomaly_hp : (inc.anomaly_hp !== null && inc.anomaly_hp !== undefined ? inc.anomaly_hp : maxHp);
        const progress = assignment && assignment.response_progress !== undefined && assignment.response_progress !== null ? assignment.response_progress : (inc.response_progress || 0);
        const pct = maxHp > 0 ? Math.max(0, Math.min(100, Math.round((hp / maxHp) * 100))) : 100;
        const barColor = pct <= 25 ? '#22C55E' : (pct <= 60 ? '#EAB308' : '#EF4444');
        const statusLabel = (inc.status || '').toLowerCase().replace(/\b\w/g, c => c.toUpperCase());

        const conditionHtml = isVerified ? `
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
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
                    <span style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">STATUS</span>
                    <span style="font-size:11px; font-weight:700; color:#A78BFA;">${statusLabel}</span>
                </div>
            </div>` : `
            <div style="margin-top:8px; padding-top:8px; border-top:1px solid #2A3440; font-family:'JetBrains Mono',monospace; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:10px; font-weight:700; color:#64748B; text-transform:uppercase;">STATUS</span>
                <span style="font-size:11px; font-weight:700; color:#EAB308;">${statusLabel}</span>
            </div>`;

        const bName = inc.barangay ? (inc.barangay.name || inc.barangay) : 'San Francisco';
        const addr = `Brgy. ${bName}, San Francisco, Agusan del Sur`;
        const incCode = inc.incident_code || ('SF-INC-' + inc.id);

        marker.bindPopup(`
            <div style="padding: 12px 14px; min-width: 270px; font-family: 'Plus Jakarta Sans', sans-serif; box-sizing: border-box;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px; gap: 8px; padding-right: 24px;">
                    <span style="font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:700; color:#8B5CF6;">${incCode}</span>
                    <span class="badge-${inc.severity.toLowerCase()}" style="font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px; flex-shrink:0;">${inc.severity}</span>
                </div>
                <h4 style="font-size:12px; font-weight:800; color:#FFFFFF; line-height:1.3; margin-bottom:4px; padding-right: 12px;">${inc.title}</h4>
                <p id="popup-addr-${inc.id}" style="font-size:11px; color:#9CA3AF; margin-bottom:8px;">${addr}</p>
                ${conditionHtml}
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px; padding-top:8px; margin-top:8px; border-top:1px solid #2A3440;">
                    <a href="/incidents/${inc.id}" style="background:#8B5CF6; color:#FFFFFF; border:none; padding:6px 12px; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0; display:inline-flex; align-items:center; gap:5px; text-decoration:none;" onmouseover="this.style.background='#7C3AED'" onmouseout="this.style.background='#8B5CF6'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <span>INSPECT</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        `, { className: 'gis-leaflet-popup' });

        marker.on('click', () => {
            map.flyTo([lat, lng], Math.max(map.getZoom(), 17), { duration: 0.7 });
            if (window.SpectralUI && typeof window.SpectralUI.inspectIncident === 'function') {
                window.SpectralUI.inspectIncident(incCode);
            }
        });

        incidentMarkers[inc.id] = marker;
        if (inc.incident_code) incidentMarkers[inc.incident_code] = marker;
    });

    // ─── 4. BIND INTEROPERABILITY METHODS TO WINDOW.SPECTRALMAP ───
    window.SpectralMap = window.SpectralMap || {};
    window.SpectralMap.map = map;
    window.SpectralMap.layerGroups = layers;
    window.SpectralMap.incidentMarkers = incidentMarkers;
    window.SpectralMap.activeFilters = { incidents: incidentsVisible, safeZones: safeZonesVisible, severity: 'ALL' };

    window.SpectralMap.toggleLayer = function(name) {
        if (!layers[name]) return;
        const isVisible = map.hasLayer(layers[name]);
        if (isVisible) {
            map.removeLayer(layers[name]);
            this.activeFilters[name] = false;
            try { localStorage.setItem('spectral_layer_' + name, '0'); } catch (_) {}
            if (typeof updateSidebarBadge === 'function') updateSidebarBadge(name, false);
        } else {
            map.addLayer(layers[name]);
            this.activeFilters[name] = true;
            try { localStorage.setItem('spectral_layer_' + name, '1'); } catch (_) {}
            if (typeof updateSidebarBadge === 'function') updateSidebarBadge(name, true);
        }
    };

    window.SpectralMap.updateLayerToggleButtons = function() {
        ['incidents', 'safeZones'].forEach(name => {
            const isVisible = map.hasLayer(layers[name]);
            if (typeof updateSidebarBadge === 'function') updateSidebarBadge(name, isVisible);
        });
    };

    window.SpectralMap.jumpTo = function(region) {
        const coords = {
            san_francisco: { center: [8.5100, 125.9750], zoom: 14 },
            agusan:        { center: [8.5000, 125.8000], zoom: 11 },
            mindanao:      { center: [8.0000, 125.0000], zoom: 8 },
            philippines:   { center: [12.8797, 121.7740], zoom: 6 }
        };
        if (coords[region]) {
            map.flyTo(coords[region].center, coords[region].zoom, { duration: 1.0 });
        }
    };

    window.SpectralMap.focusOnInspected = function() {
        const selectedId = window.SpectralUI ? window.SpectralUI.selectedIncidentId : null;
        if (!selectedId) return;
        const marker = incidentMarkers[selectedId];
        if (marker) {
            map.flyTo(marker.getLatLng(), 17, { duration: 0.8 });
            marker.openPopup();
        }
    };

    window.SpectralMap.startLocationPicking = function() {
        this.isPickingLocation = true;
        const hud = document.getElementById('location-picker-hud');
        if (hud) hud.style.display = 'flex';
        const mapEl = document.getElementById('spectral-map');
        if (mapEl) mapEl.classList.add('picking-location');
    };

    window.SpectralMap.cancelLocationPicking = function() {
        this.isPickingLocation = false;
        const hud = document.getElementById('location-picker-hud');
        if (hud) hud.style.display = 'none';
        const mapEl = document.getElementById('spectral-map');
        if (mapEl) mapEl.classList.remove('picking-location');
    };

    map.on('click', function(e) {
        if (window.SpectralMap && window.SpectralMap.isPickingLocation) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            const latInp = document.getElementById('report-latitude');
            const lngInp = document.getElementById('report-longitude');
            if (latInp) latInp.value = lat.toFixed(6);
            if (lngInp) lngInp.value = lng.toFixed(6);

            if (window.SpectralMap.pickingMarker) {
                map.removeLayer(window.SpectralMap.pickingMarker);
            }
            window.SpectralMap.pickingMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="width:22px;height:22px;border-radius:50%;background:#8B5CF6;border:3px solid #FFFFFF;box-shadow:0 0 12px #8B5CF6;"></div>',
                    iconSize: [22, 22],
                    iconAnchor: [11, 11]
                })
            }).addTo(map);

            window.SpectralMap.cancelLocationPicking();

            const modal = document.getElementById('report-modal');
            if (modal) modal.classList.remove('hidden');
        }
    });

    // ─── 5. HANDLE URL NAVIGATION PARAMETERS ───
    const params = new URLSearchParams(window.location.search);
    const zoomLat = parseFloat(params.get('zoom_lat'));
    const zoomLng = parseFloat(params.get('zoom_lng'));
    const zoomIncidentId = params.get('zoom_incident');
    const layer = params.get('layer');

    if (!isNaN(zoomLat) && !isNaN(zoomLng)) {
        setTimeout(() => {
            map.flyTo([zoomLat, zoomLng], 17, { animate: true, duration: 1.2 });
            if (zoomIncidentId && incidentMarkers[zoomIncidentId]) {
                setTimeout(() => incidentMarkers[zoomIncidentId].openPopup(), 1200);
            }
        }, 300);
    }

    if (layer === 'safeZones') {
        setTimeout(() => {
            if (!map.hasLayer(layers.safeZones)) {
                map.addLayer(layers.safeZones);
                try { localStorage.setItem('spectral_layer_safeZones', '1'); } catch (_) {}
                if (typeof updateSidebarBadge === 'function') updateSidebarBadge('safeZones', true);
            }
            map.flyTo([8.5098, 125.9780], 15, { duration: 1.0 });
        }, 300);
    }

    // ─── 6. UPDATE RIGHT INFO PANEL STATS COUNTERS ───
    const activeIncCount = RAW_INCIDENTS.filter(i => i.status !== 'RESOLVED' && i.status !== 'REJECTED' && i.status !== 'ARCHIVED').length;
    const underReviewCount = RAW_INCIDENTS.filter(i => i.status === 'UNDER INVESTIGATION').length;
    const resolvedCount = RAW_INCIDENTS.filter(i => i.status === 'RESOLVED').length;

    const elInc  = document.getElementById('stat-active-incidents');
    const elInv  = document.getElementById('stat-investigating');
    const elWard = document.getElementById('stat-ward-stations');
    const elRes  = document.getElementById('stat-resolved');

    if (elInc)  elInc.textContent  = String(activeIncCount).padStart(2, '0');
    if (elInv)  elInv.textContent  = String(underReviewCount).padStart(2, '0');
    if (elWard) elWard.textContent = String(safeWardStations.length).padStart(2, '0');
    if (elRes)  elRes.textContent  = String(resolvedCount).padStart(2, '0');

    // Invalidate size to guarantee tile rendering
    setTimeout(() => { map.invalidateSize(); }, 200);
    window.addEventListener('resize', () => { map.invalidateSize(); });
});
</script>
@endpush
