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

{{-- ═══ SAFE ZONE WARNING MODAL (centered, blocks report until acknowledged) ═══ --}}
<div id="safezone-warning-modal" class="hidden fixed inset-0 z-[1100] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-[#151B23] border-2 border-red-500/50 shadow-2xl overflow-hidden">
        <div class="p-6 sm:p-7 space-y-5 text-center">

            {{-- Warning Icon --}}
            <div class="mx-auto w-16 h-16 rounded-full bg-red-500/10 border-2 border-red-500/40 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>

            {{-- Title --}}
            <h3 class="text-xl font-extrabold text-white tracking-tight">Safe Zone Protected</h3>

            {{-- Message --}}
            <p class="text-sm text-slate-300 leading-relaxed">
                You cannot report an incident within this protected radius. This Safe Zone is protected by Investigators and Responders. Please select a location outside the Safe Zone.
            </p>

            {{-- Protected Zone Info --}}
            <div class="rounded-xl bg-[#11161D] border border-[#2A3440] p-3.5 text-left space-y-1.5">
                <div class="flex items-start gap-2">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider shrink-0 pt-0.5">Zone:</span>
                    <span id="safezone-warning-zone" class="text-xs font-bold text-red-300 leading-snug">—</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider shrink-0 pt-0.5">Radius:</span>
                    <span id="safezone-warning-radius" class="text-xs font-bold text-red-300">—</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider shrink-0 pt-0.5">Your distance:</span>
                    <span id="safezone-warning-distance" class="text-xs font-bold text-amber-400">—</span>
                </div>
            </div>

            {{-- Acknowledge Button --}}
            <button type="button"
                    onclick="acknowledgeSafeZoneWarning()"
                    class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-500 active:scale-[0.98] text-white font-bold text-sm uppercase tracking-wider transition shadow-lg shadow-red-900/40">
                I Understand
            </button>
        </div>
    </div>
</div>

<!-- Flag: reporter map handles its own init — spectral.js must skip SpectralMap.init() -->
<script>
    window.SPECTRAL_REPORTER_MAP = true;
    window.INITIAL_SPECTRAL_STATE = {
        incidents: @json($incidents),
        wards: @json($wardStations),
        safeWardStations: @json($safeWardStations),
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

    // Initialize SpectralData store so sidebar notifications / address resolver work
    if (typeof SpectralData !== 'undefined') {
        try { SpectralData.init(); } catch (_) {}
    }

    if (typeof L === 'undefined') {
        console.error('[Reporter Map] Leaflet not loaded');
        return;
    }

    const mapElement = document.getElementById('spectral-map');
    if (!mapElement) return;

    // ── 1. RESTORE PERSISTED LAYER STATES ──────────────────────────────────
    let incidentsOn = true;
    let safeZonesOn = true;
    try {
        const savedInc  = localStorage.getItem('spectral_layer_incidents');
        const savedSafe = localStorage.getItem('spectral_layer_safeZones');
        if (savedInc  !== null) incidentsOn  = (savedInc  !== '0');
        if (savedSafe !== null) safeZonesOn  = (savedSafe !== '0');
    } catch (_) {}

    // ── 2. INIT LEAFLET MAP ─────────────────────────────────────────────────
    const map = L.map('spectral-map', {
        center: [8.5100, 125.9750],
        zoom: 14,
        zoomControl: false,
        attributionControl: true
    });

    L.control.zoom({ position: 'topright' }).addTo(map);

    L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        attribution: 'OpenStreetMap',
        subdomains: ['0', '1', '2', '3'],
        maxZoom: 20
    }).addTo(map);

    L.control.scale({ imperial: true, metric: true, position: 'bottomleft' }).addTo(map);

    // ── 3. LAYER GROUPS ────────────────────────────────────────────────────
    const layers = {
        incidents: L.layerGroup(),
        safeZones: L.layerGroup()
    };
    if (incidentsOn) map.addLayer(layers.incidents);
    if (safeZonesOn) map.addLayer(layers.safeZones);

    // ── 4. EXPOSE TO WINDOW ────────────────────────────────────────────────
    window.SpectralMap = window.SpectralMap || {};
    window.SpectralMap.map           = map;
    window.SpectralMap.layerGroups   = layers;
    window.SpectralMap.incidentMarkers = {};
    window.SpectralMap.isPickingLocation = false;
    window.SpectralMap.activeFilters = {
        incidents: incidentsOn,
        safeZones: safeZonesOn,
        severity: 'ALL'
    };

    window.SpectralMap.toggleLayer = function(name) {
        if (!layers[name]) return;
        const nowOn = !map.hasLayer(layers[name]);
        if (nowOn) {
            map.addLayer(layers[name]);
        } else {
            map.removeLayer(layers[name]);
        }
        this.activeFilters[name] = nowOn;
        try { localStorage.setItem('spectral_layer_' + name, nowOn ? '1' : '0'); } catch (_) {}
        if (typeof updateSidebarBadge === 'function') updateSidebarBadge(name, nowOn);
    };

    window.SpectralMap.jumpTo = function(region) {
        const coords = {
            san_francisco: { center: [8.5100, 125.9750], zoom: 14 },
            agusan:        { center: [8.5000, 125.8000], zoom: 11 },
            mindanao:      { center: [8.0000, 125.0000], zoom: 8  },
            philippines:   { center: [12.8797, 121.7740], zoom: 6 }
        };
        if (coords[region]) map.flyTo(coords[region].center, coords[region].zoom, { duration: 1.0 });
    };

    window.SpectralMap.startLocationPicking = function() {
        this.isPickingLocation = true;
        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.add('hidden');
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
        const modal = document.getElementById('report-modal');
        if (modal) modal.classList.remove('hidden');
    };

    // ── Helper functions for Safe Zone Popup (Copied from Admin Map) ───────
    function formatProtectionTime(isoString) {
        if (!isoString) return { text: 'No protection set', expired: true };
        const expires = new Date(isoString);
        const now = new Date();
        const diffMs = expires - now;
        if (diffMs <= 0) return { text: 'Protection Expired', expired: true };

        const totalSeconds = Math.floor(diffMs / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (days > 0) return { text: `${days}d ${String(hours).padStart(2,'0')}h ${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
        if (hours > 0) return { text: `${String(hours).padStart(2,'0')}h ${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
        return { text: `${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
    }

    function suppliesBadge(status) {
        const map = {
            'available':    { color: '#22C55E', bg: 'rgba(34,197,94,0.15)',  border: 'rgba(34,197,94,0.4)',  label: 'Available' },
            'limited':      { color: '#EAB308', bg: 'rgba(234,179,8,0.15)',  border: 'rgba(234,179,8,0.4)',  label: 'Limited' },
            'out_of_stock': { color: '#EF4444', bg: 'rgba(239,68,68,0.15)',  border: 'rgba(239,68,68,0.4)',  label: 'Out of stock' }
        };
        const s = map[status] || map['available'];
        return `<span style="display:inline-flex;align-items:center;justify-content:center;background:${s.bg};color:${s.color};border:1px solid ${s.border};font-size:10px;font-weight:700;padding:3px 10px;border-radius:5px;min-width:84px;height:22px;box-sizing:border-box;">${s.label}</span>`;
    }

    function icon(name, color, size = 12) {
        const icons = {
            clock:   `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
            box:     `<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>`,
            coffee:  `<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>`,
            droplet: `<path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>`,
            firstaid:`<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="14"/><line x1="9" y1="11" x2="15" y2="11"/>`,
            heart:   `<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>`
        };
        return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">${icons[name] || ''}</svg>`;
    }

    // ── 5. SAFE WARD STATIONS (🌐 Loaded from DB via DashboardController) ──
    const safeWardStations = (window.INITIAL_SPECTRAL_STATE && window.INITIAL_SPECTRAL_STATE.safeWardStations)
        ? window.INITIAL_SPECTRAL_STATE.safeWardStations.map(sz => ({
            id:       sz.id,
            code:     sz.code,
            name:     sz.name,
            lat:      parseFloat(sz.lat ?? sz.latitude),
            lng:      parseFloat(sz.lng ?? sz.longitude),
            radius:   parseFloat(sz.radius ?? 450),
            status:   sz.status || 'active',
            barangay: sz.barangay || 'San Francisco',
            // 🆕 Include the new fields
            description:            sz.description,
            address:                sz.address,
            protection_expires_at:  sz.protection_expires_at,
            protection_active:      sz.protection_active,
            protection_time:        sz.protection_time,
            relief_food:            sz.relief_food,
            relief_water:           sz.relief_water,
            relief_first_aid:       sz.relief_first_aid,
            assistance_services:    sz.assistance_services || []
        }))
        : [];

    safeWardStations.forEach(sz => {
        // Green Safe Ward Station perimeter circle — using FULL radius (matches real protection)
        const safeZoneCircle = L.circle([sz.lat, sz.lng], {
            radius: Math.round(sz.radius),
            color: '#22C55E',
            weight: 1.5,
            fillColor: '#22C55E',
            fillOpacity: 0.08
        });

        // Safe Ward Station Marker (Shield icon)
        const szHtml = `
            <div style="width:28px;height:28px;border-radius:50%;background:#10B981;border:2px solid #34D399;box-shadow:0 0 10px rgba(16,185,129,0.8);display:flex;align-items:center;justify-content:center;cursor:pointer;" title="${sz.name}">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#FFFFFF" stroke="#10B981" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>`;
        const szIcon = L.divIcon({ className: '', html: szHtml, iconSize: [28, 28], iconAnchor: [14, 14] });
        const m = L.marker([sz.lat, sz.lng], { icon: szIcon, zIndexOffset: 700 });

        // 🆕 Detailed Popup HTML (Matches Admin Map exactly)
        const time = formatProtectionTime(sz.protection_expires_at);
        const timeColor = time.expired ? '#EF4444' : '#22C55E';
        const statusLabel = time.expired ? 'WARD OFFLINE' : 'WARD ACTIVE';
        const statusDot = `<span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:${timeColor};box-shadow:0 0 6px ${timeColor};margin-right:5px;vertical-align:middle;"></span>`;
        const services = sz.assistance_services || [];

        const servicesHtml = services.length > 0
            ? services.map(s => `<li style="display:flex;align-items:center;gap:7px;font-size:11px;color:#CBD5E1;margin-bottom:5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>${s}</span>
                </li>`).join('')
            : '<li style="font-size:11px;color:#64748B;font-style:italic;">No assistance listed</li>';

        const popupHtml = `
            <div style="padding:14px 16px;min-width:290px;max-width:340px;font-family:'Plus Jakarta Sans',sans-serif;color:#F3F4F6;">
                <div style="margin-bottom:12px;">
                    <span style="display:inline-flex;align-items:center;color:${timeColor};font-size:9px;font-weight:800;font-family:'JetBrains Mono',monospace;letter-spacing:0.08em;">
                        ${statusDot}${statusLabel}
                    </span>
                    <h4 style="font-size:14px;font-weight:800;color:#FFFFFF;margin:5px 0 3px 0;line-height:1.25;">${sz.name}</h4>
                    <p style="font-size:10.5px;color:#9CA3AF;margin:0;">${sz.description || sz.address}</p>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:5px;">
                        ${icon('clock', '#A78BFA', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Protection Remaining</span>
                    </div>
                    <p style="font-size:16px;font-weight:800;color:${timeColor};font-family:'JetBrains Mono',monospace;margin:0;line-height:1;">${time.text}</p>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
                        ${icon('box', '#2DD4BF', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Relief Supplies</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('coffee', '#9CA3AF', 12)}<span>Food &amp; Water</span></span>
                        ${suppliesBadge(sz.relief_food)}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('droplet', '#9CA3AF', 12)}<span>Drinking Water</span></span>
                        ${suppliesBadge(sz.relief_water)}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('firstaid', '#9CA3AF', 12)}<span>First-aid Kits</span></span>
                        ${suppliesBadge(sz.relief_first_aid)}
                    </div>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
                        ${icon('heart', '#22C55E', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Free Assistance</span>
                    </div>
                    <ul style="list-style:none;padding:0;margin:0;">${servicesHtml}</ul>
                </div>
            </div>`;

        m.bindPopup(popupHtml, { className: 'gis-leaflet-popup', maxWidth: 360 });
        m.bindTooltip(sz.name, { direction: 'top', offset: [0, -14] });

        safeZoneCircle.on('click', () => {
            m.openPopup();
            map.flyTo([sz.lat, sz.lng], Math.max(map.getZoom(), 16), { duration: 0.7 });
        });
        m.on('click', () => {
            map.flyTo([sz.lat, sz.lng], Math.max(map.getZoom(), 16), { duration: 0.7 });
        });

        layers.safeZones.addLayer(safeZoneCircle);
        layers.safeZones.addLayer(m);
    });

    // ── Safe Zone Protection Verification ────────────────────────────────────
    function checkSafeZoneProtection(lat, lng) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (isNaN(lat) || isNaN(lng)) return null;

        const zones = (window.SpectralData && window.SpectralData.safeZones && window.SpectralData.safeZones.length > 0)
            ? window.SpectralData.safeZones
            : safeWardStations;

        for (let i = 0; i < zones.length; i++) {
            const sz = zones[i];
            if (sz.status && sz.status !== 'active') continue;
            const szLat = parseFloat(sz.latitude || sz.lat);
            const szLng = parseFloat(sz.longitude || sz.lng);
            const radius = parseFloat(sz.radius || 450);
            if (isNaN(szLat) || isNaN(szLng) || isNaN(radius)) continue;

            const dLat = (szLat - lat) * Math.PI / 180;
            const dLng = (szLng - lng) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat * Math.PI / 180) * Math.cos(szLat * Math.PI / 180) *
                      Math.sin(dLng / 2) * Math.sin(dLng / 2);
            const dist = 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            // Condition: distance <= radius (including boundary distance == radius)
            if (dist <= radius) {
                return {
                    protected: true,
                    safeZone: sz,
                    distance: dist,
                    radius: radius,
                    title: 'Safe Zone Protected',
                    message: `You cannot report an incident within ${Math.round(radius)}m of ${sz.name}. This Safe Zone is protected by Investigators and Responders. Please select a location outside the Safe Zone.`
                };
            }
        }
        return null;
    }
    window.checkSafeZoneProtection = checkSafeZoneProtection;

    // ── 6. INCIDENT MARKERS CREATION & HELPER ──────────────────────────────
    const sevColors = { CRITICAL: '#000000', HIGH: '#EF4444', MEDIUM: '#EAB308', LOW: '#22C55E' };
    const incidentMarkers = {};

    function createIncidentMarker(inc) {
        if (!inc.latitude || !inc.longitude) return null;
        const lat = parseFloat(inc.latitude);
        const lng = parseFloat(inc.longitude);
        if (isNaN(lat) || isNaN(lng)) return null;

        const color      = sevColors[inc.severity] || '#22C55E';
        const isHigh     = inc.severity === 'HIGH' || inc.severity === 'CRITICAL';
        const isCritical = inc.severity === 'CRITICAL';
        const pulseBg    = isCritical ? 'rgba(0,0,0,0.6)' : `${color}44`;
        const markerBdr  = isCritical ? '#9CA3AF' : '#FFFFFF';
        const markerShd  = isCritical ? '0 0 12px rgba(0,0,0,0.95)' : `0 0 10px ${color}`;

        const markerHtml = `<div style="position:relative;width:26px;height:26px;display:flex;align-items:center;justify-content:center;cursor:pointer;" title="${inc.incident_code || ('SF-INC-' + inc.id)}: ${inc.title}">${isHigh ? `<div style="position:absolute;inset:-4px;border-radius:50%;background:${pulseBg};animation:ping 2s cubic-bezier(0,0,0.2,1) infinite;"></div>` : ''}<div style="position:relative;width:20px;height:20px;border-radius:50%;background:${color};border:2px solid ${markerBdr};box-shadow:${markerShd};display:flex;align-items:center;justify-content:center;"><div style="width:6px;height:6px;border-radius:50%;background:#FFFFFF;"></div></div></div>`;
        const icon = L.divIcon({ className: '', html: markerHtml, iconSize: [26, 26], iconAnchor: [13, 13] });
        const marker = L.marker([lat, lng], { icon, zIndexOffset: 800 });

        const assignments = inc.responder_assignments || [];
        const assignment = assignments.length > 0 ? assignments[assignments.length - 1] : null;
        const isResponderAssigned = !!(assignment && assignment.responder_id);
        const isVerified = ['VERIFIED', 'RESOLVED'].includes(inc.status) || inc.response_status === 'ACTIVE' || inc.investigation_result === 'CONFIRMED' || isResponderAssigned;
        const isInvestigating = isVerified || ['UNDER INVESTIGATION'].includes(inc.status) || (inc.investigations && inc.investigations.length > 0);

        let statusLabel = 'Pending';
        if (inc.status === 'RESOLVED') {
            statusLabel = 'Resolved';
        } else if (isResponderAssigned) {
            statusLabel = 'Responder Assigned';
        } else if (isVerified) {
            statusLabel = 'Verified';
        } else if (isInvestigating) {
            statusLabel = 'Under Investigation';
        } else {
            statusLabel = 'Pending';
        }

        const progress = assignment && assignment.response_progress !== undefined && assignment.response_progress !== null ? assignment.response_progress : (inc.response_progress || (inc.status === 'RESOLVED' ? 100 : 0));

        const statusColor = inc.status === 'RESOLVED' ? '#38BDF8' : '#A78BFA';
        const conditionHtml = isVerified ? `<div style="margin-top:8px;padding-top:8px;border-top:1px solid #2A3440;font-family:'JetBrains Mono',monospace;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px;"><span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;">RESPONSE PROGRESS</span><span style="font-size:11px;font-weight:700;color:#10B981;">${progress}%</span></div><div style="width:100%;height:5px;background:#11161D;border-radius:3px;overflow:hidden;border:1px solid #2A3440;margin-bottom:6px;"><div style="width:${progress}%;height:100%;background:#10B981;transition:width 0.5s;"></div></div><div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;"><span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;">STATUS</span><span style="font-size:11px;font-weight:700;color:${statusColor};">${statusLabel}</span></div></div>` : `<div style="margin-top:8px;padding-top:8px;border-top:1px solid #2A3440;font-family:'JetBrains Mono',monospace;display:flex;justify-content:space-between;align-items:center;"><span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;">STATUS</span><span style="font-size:11px;font-weight:700;color:#EAB308;">${statusLabel}</span></div>`;

        const bName  = inc.barangay ? (inc.barangay.name || inc.barangay) : 'San Francisco';
        const addr   = `Brgy. ${bName}, San Francisco, Agusan del Sur`;
        const incCode = inc.incident_code || ('SF-INC-' + inc.id);

        marker.bindPopup(`<div style="padding:12px 14px;min-width:270px;font-family:'Plus Jakarta Sans',sans-serif;box-sizing:border-box;"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;gap:8px;padding-right:24px;"><span style="font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700;color:#8B5CF6;">${incCode}</span><span class="badge-${(inc.severity||'low').toLowerCase()}" style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;flex-shrink:0;">${inc.severity}</span></div><h4 style="font-size:12px;font-weight:800;color:#FFFFFF;line-height:1.3;margin-bottom:4px;padding-right:12px;">${inc.title}</h4><p id="popup-addr-${inc.id}" style="font-size:11px;color:#9CA3AF;margin-bottom:8px;">${addr}</p>${conditionHtml}<div style="display:flex;justify-content:flex-end;align-items:center;gap:12px;padding-top:8px;margin-top:8px;border-top:1px solid #2A3440;"><a href="/incidents/${inc.id}" style="background:#8B5CF6;color:#FFFFFF;border:none;padding:6px 12px;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;white-space:nowrap;flex-shrink:0;display:inline-flex;align-items:center;gap:5px;text-decoration:none;" onmouseover="this.style.background='#7C3AED'" onmouseout="this.style.background='#8B5CF6'"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><span>INSPECT</span><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></a></div></div>`, { className: 'gis-leaflet-popup' });

        marker.on('click', () => {
            map.flyTo([lat, lng], Math.max(map.getZoom(), 17), { duration: 0.7 });
        });

        return marker;
    }

    RAW_INCIDENTS.forEach(inc => {
        const marker = createIncidentMarker(inc);
        if (marker) {
            layers.incidents.addLayer(marker);
            incidentMarkers[inc.id] = marker;
            if (inc.incident_code) incidentMarkers[inc.incident_code] = marker;
        }
    });

    window.SpectralMap.incidentMarkers = incidentMarkers;

    // Callable hook for when a new incident is submitted via the report modal
    window.SpectralMap.addIncidentMarker = function(inc) {
        if (!inc) return;
        const marker = createIncidentMarker(inc);
        if (marker) {
            layers.incidents.addLayer(marker);
            incidentMarkers[inc.id] = marker;
            if (inc.incident_code) incidentMarkers[inc.incident_code] = marker;
            const lat = parseFloat(inc.latitude);
            const lng = parseFloat(inc.longitude);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.flyTo([lat, lng], 16, { duration: 0.8 });
                setTimeout(() => marker.openPopup(), 850);
            }
            const elInc = document.getElementById('stat-active-incidents');
            if (elInc) {
                const current = parseInt(elInc.textContent, 10) || 0;
                elInc.textContent = String(current + 1).padStart(2, '0');
            }
        }
    };

    // ── 7. MAP CLICK: Location picker (for Report Incident modal) ──────────
    map.on('click', function(e) {
        if (window.SpectralMap && window.SpectralMap.isPickingLocation) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            const latInp = document.getElementById('report-lat');
            const lngInp = document.getElementById('report-lng');
            if (latInp) latInp.value = lat.toFixed(6);
            if (lngInp) lngInp.value = lng.toFixed(6);

            if (window.SpectralData && window.SpectralData.barangays && window.SpectralData.barangays.length > 0) {
                let closest = null;
                let minDist = Infinity;
                window.SpectralData.barangays.forEach(b => {
                    const bLat = parseFloat(b.latitude || b.lat);
                    const bLng = parseFloat(b.longitude || b.lng);
                    if (!isNaN(bLat) && !isNaN(bLng)) {
                        const d = Math.pow(bLat - lat, 2) + Math.pow(bLng - lng, 2);
                        if (d < minDist) { minDist = d; closest = b; }
                    }
                });
                if (closest) {
                    const bSelect = document.getElementById('report-barangay');
                    if (bSelect) {
                        for (let opt of bSelect.options) {
                            if (opt.value == closest.id || (opt.text && opt.text.includes(closest.name))) {
                                bSelect.value = opt.value;
                                break;
                            }
                        }
                    }
                    // Update geocode status label
                    const statusEl = document.getElementById('report-geocode-status');
                    if (statusEl) {
                        statusEl.textContent = `📍 ${closest.name} (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
                        statusEl.style.color = '#34D399';
                    }
                }
            }

            if (window.SpectralMap.pickingMarker) map.removeLayer(window.SpectralMap.pickingMarker);
            window.SpectralMap.pickingMarker = L.marker([lat, lng], {
                icon: L.divIcon({ className: '', html: '<div style="width:22px;height:22px;border-radius:50%;background:#8B5CF6;border:3px solid #FFFFFF;box-shadow:0 0 12px #8B5CF6;"></div>', iconSize: [22, 22], iconAnchor: [11, 11] })
            }).addTo(map);

            window.SpectralMap.isPickingLocation = false;
            const hud = document.getElementById('location-picker-hud');
            if (hud) hud.style.display = 'none';
            const mapEl = document.getElementById('spectral-map');
            if (mapEl) mapEl.classList.remove('picking-location');

            // 🔒 Safe Zone check FIRST — before showing report modal
            const protection = checkSafeZoneProtection(lat, lng);

            if (protection) {
                // ❌ Inside Safe Zone — show centered warning modal INSTEAD of report modal
                showSafeZoneWarningModal(protection);
                return;
            }

            // ✅ Not protected — safe to open report modal
            const modal = document.getElementById('report-modal');
            if (modal) modal.classList.remove('hidden');

            // Clear any old alert
            const safeAlertEl = document.getElementById('report-safezone-alert');
            if (safeAlertEl) safeAlertEl.classList.add('hidden');

            // Update geocode status to show we're good
            const statusEl = document.getElementById('report-geocode-status');
            if (statusEl) {
                statusEl.textContent = 'Location confirmed';
                statusEl.style.color = '#34D399';
            }

            // Automatically retrieve readable address and update location review card
            if (window.SpectralUI && typeof window.SpectralUI.lookupAddress === 'function') {
                window.SpectralUI.lookupAddress(lat, lng);
            }
        }
    });

    // ── 8. URL PARAM: zoom to specific incident, layer or pick location ───
    const params = new URLSearchParams(window.location.search);
    const zoomLat = parseFloat(params.get('zoom_lat'));
    const zoomLng = parseFloat(params.get('zoom_lng'));
    const zoomIncidentId = params.get('zoom_incident');
    const layerParam = params.get('layer');

    if (params.get('pick') === '1') {
        setTimeout(() => {
            if (window.SpectralMap && typeof window.SpectralMap.startLocationPicking === 'function') {
                window.SpectralMap.startLocationPicking();
            }
        }, 350);
    }

    if (!isNaN(zoomLat) && !isNaN(zoomLng)) {
        setTimeout(() => {
            map.flyTo([zoomLat, zoomLng], 17, { animate: true, duration: 1.2 });
            if (zoomIncidentId && incidentMarkers[zoomIncidentId]) {
                setTimeout(() => incidentMarkers[zoomIncidentId].openPopup(), 1200);
            }
        }, 300);
    }

    if (layerParam === 'safeZones') {
        setTimeout(() => {
            if (!map.hasLayer(layers.safeZones)) {
                map.addLayer(layers.safeZones);
                try { localStorage.setItem('spectral_layer_safeZones', '1'); } catch (_) {}
                if (typeof updateSidebarBadge === 'function') updateSidebarBadge('safeZones', true);
                window.SpectralMap.activeFilters.safeZones = true;
            }
            if (safeWardStations.length > 0) {
                map.flyTo([safeWardStations[0].lat, safeWardStations[0].lng], 15, { duration: 1.0 });
            }
        }, 300);
    }

    // ── 9. RIGHT PANEL STAT COUNTERS (Direct database counts) ───────────────
    const activeIncCount    = RAW_INCIDENTS.filter(i => ['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'].includes(i.status)).length;
    const underReviewCount  = RAW_INCIDENTS.filter(i => i.status === 'UNDER INVESTIGATION').length;
    const resolvedCount     = RAW_INCIDENTS.filter(i => i.status === 'RESOLVED').length;
    const wardStationsCount = safeWardStations.length;

    const elInc  = document.getElementById('stat-active-incidents');
    const elInv  = document.getElementById('stat-investigating');
    const elWard = document.getElementById('stat-ward-stations');
    const elRes  = document.getElementById('stat-resolved');

    if (elInc)  elInc.textContent  = String(activeIncCount).padStart(2, '0');
    if (elInv)  elInv.textContent  = String(underReviewCount).padStart(2, '0');
    if (elWard) elWard.textContent = String(wardStationsCount).padStart(2, '0');
    if (elRes)  elRes.textContent  = String(resolvedCount).padStart(2, '0');

    // ── 10. SYNC SIDEBAR BADGES ─────────────────────────────────────────────
    if (typeof updateSidebarBadge === 'function') {
        updateSidebarBadge('incidents', incidentsOn);
        updateSidebarBadge('safeZones', safeZonesOn);
    }

    // ── 11. INVALIDATE SIZE ─────────────────────────────────────────────────
    setTimeout(() => { map.invalidateSize(); }, 200);
    window.addEventListener('resize', () => { map.invalidateSize(); });
});

// ═══════════════════════════════════════════════════════════════════════════
// 🛡️ SAFE ZONE WARNING MODAL
// Shows centered popup when user tries to pick a location inside a Safe Zone.
// Blocks reporting until acknowledged. Closes both modals on "I Understand".
// ═══════════════════════════════════════════════════════════════════════════

function showSafeZoneWarningModal(protection) {
    const modal = document.getElementById('safezone-warning-modal');
    if (!modal) return;

    // Populate zone details
    const zoneName = protection.safeZone?.name || 'Protected Safe Zone';
    const radius = Math.round(protection.radius || protection.safeZone?.radius || 450);
    const distance = Math.round(protection.distance || 0);

    const zoneEl = document.getElementById('safezone-warning-zone');
    const radiusEl = document.getElementById('safezone-warning-radius');
    const distEl = document.getElementById('safezone-warning-distance');

    if (zoneEl) zoneEl.textContent = zoneName;
    if (radiusEl) radiusEl.textContent = radius + ' m';
    if (distEl) distEl.textContent = distance + ' m from center';

    // Hide the report modal (in case it was open)
    const reportModal = document.getElementById('report-modal');
    if (reportModal) reportModal.classList.add('hidden');

    // Show the warning modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    // Focus the button for accessibility
    setTimeout(() => {
        const btn = modal.querySelector('button');
        if (btn) btn.focus();
    }, 50);
}

function acknowledgeSafeZoneWarning() {
    const modal = document.getElementById('safezone-warning-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Also hide the report modal (user must start over with a new location)
    const reportModal = document.getElementById('report-modal');
    if (reportModal) reportModal.classList.add('hidden');

    // Clear safe zone alert inside the report modal
    const safeAlertEl = document.getElementById('report-safezone-alert');
    if (safeAlertEl) safeAlertEl.classList.add('hidden');

    // Clear any leftover picking marker
    if (window.SpectralMap && window.SpectralMap.pickingMarker && window.SpectralMap.map) {
        try {
            window.SpectralMap.map.removeLayer(window.SpectralMap.pickingMarker);
            window.SpectralMap.pickingMarker = null;
        } catch (_) {}
    }

    // Small toast confirming next steps
    if (window.SpectralUI && typeof window.SpectralUI.showToast === 'function') {
        window.SpectralUI.showToast('Please click "Report Incident" again and select a location outside the Safe Zone.');
    }
}

// Expose globally
window.showSafeZoneWarningModal = showSafeZoneWarningModal;
window.acknowledgeSafeZoneWarning = acknowledgeSafeZoneWarning;

// ESC key also closes the warning modal (acts like acknowledge)
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('safezone-warning-modal');
        if (modal && !modal.classList.contains('hidden')) {
            acknowledgeSafeZoneWarning();
        }
    }
});
</script>
@endpush
