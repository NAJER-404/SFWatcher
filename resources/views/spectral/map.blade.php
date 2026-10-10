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

    <!-- ── BOTTOM PANELS: Recent Incidents ─────────────────── -->
    <div class="flex-shrink-0 border-t border-[#2A3440] bg-[#11161D] flex divide-x divide-[#2A3440] md:h-[160px] max-h-[45vw] md:max-h-none overflow-hidden">

        <!-- Recent Incidents -->
        <div class="flex-1 overflow-hidden flex flex-col min-w-0">
            <div class="px-3 sm:px-4 py-2 flex items-center justify-between border-b border-[#2A3440] flex-shrink-0">
                <span class="text-[11px] font-bold text-slate-200">Recent Incidents</span>
                <a href="{{ route('spectral.incidents.index') }}" class="text-[10px] text-[#8B5CF6] hover:text-[#A78BFA] font-semibold transition inline-flex items-center gap-1">
                    <span>View All</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
            <div class="flex-1 overflow-x-auto overflow-y-hidden">
                <div class="flex gap-2.5 p-3 h-full items-start">
                    @forelse($incidents->take(5) as $inc)
                    <div onclick="SpectralUI.inspectIncident('{{ $inc->incident_code }}')"
                         class="flex-shrink-0 w-44 sm:w-52 p-2.5 rounded-lg bg-[#151B23] border border-[#2A3440] hover:border-[#8B5CF6]/50 cursor-pointer transition-all">
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <span class="text-[9px] font-mono font-bold text-[#8B5CF6]">{{ $inc->incident_code }}</span>
                            <span class="badge-{{ strtolower($inc->severity) }} text-[8px] font-bold px-1.5 rounded">{{ $inc->severity }}</span>
                        </div>
                        <h4 class="text-[11px] font-semibold text-slate-200 leading-snug line-clamp-2">{{ $inc->title }}</h4>
                        <div class="flex items-center justify-between mt-1.5 text-[9px] text-[#9CA3AF]">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $inc->barangay->name ?? 'San Francisco' }}
                            </span>
                            <span class="font-mono font-semibold
                                {{ $inc->status === 'RESOLVED' ? 'text-emerald-400' :
                                   ($inc->status === 'PENDING' ? 'text-yellow-400' :
                                   ($inc->status === 'UNDER INVESTIGATION' ? 'text-[#8B5CF6]' : 'text-slate-400')) }}">
                                {{ $inc->status }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-[11px] text-[#64748B] self-center px-2">No incidents recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

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

    // Auto-zoom and layer handling
    (function () {
        const params = new URLSearchParams(window.location.search);
        const zoomLat = parseFloat(params.get('zoom_lat'));
        const zoomLng = parseFloat(params.get('zoom_lng'));
        const zoomIncidentId = params.get('zoom_incident');
        const layer = params.get('layer');

        if (!isNaN(zoomLat) && !isNaN(zoomLng)) {
            const tryZoom = setInterval(() => {
                if (window.SpectralMap && window.SpectralMap.map) {
                    clearInterval(tryZoom);
                    window.SpectralMap.map.flyTo([zoomLat, zoomLng], 17, {
                        animate: true,
                        duration: 1.2
                    });

                    if (zoomIncidentId) {
                        setTimeout(() => {
                            const marker = window.SpectralMap.incidentMarkers?.[zoomIncidentId];
                            if (marker) marker.openPopup();
                        }, 1500);
                    }

                    const cleanUrl = window.location.pathname;
                    window.history.replaceState({}, '', cleanUrl);
                }
            }, 150);

            setTimeout(() => clearInterval(tryZoom), 8000);
        }

        // If safeZones layer requested, ensure it's visible
        if (layer === 'safeZones') {
            const trySafe = setInterval(() => {
                if (window.SpectralMap && window.SpectralMap.map && window.SpectralMap.layerGroups?.safeZones) {
                    clearInterval(trySafe);
                    if (!window.SpectralMap.map.hasLayer(window.SpectralMap.layerGroups.safeZones)) {
                        window.SpectralMap.layerGroups.safeZones.addTo(window.SpectralMap.map);
                    }
                }
            }, 200);
            setTimeout(() => clearInterval(trySafe), 5000);
        }
    })();

    // Map initialization guarantee for immediate rendering
    (function() {
        function checkAndInitMap() {
            if (window.SpectralMap && !window.SpectralMap.map && typeof L !== 'undefined') {
                try {
                    window.SpectralMap.init();
                } catch (e) {
                    console.error('[Map] Init error:', e);
                }
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkAndInitMap);
        } else {
            checkAndInitMap();
        }
        window.addEventListener('load', checkAndInitMap);
        setTimeout(checkAndInitMap, 300);
        setTimeout(checkAndInitMap, 1000);
    })();
</script>
@endsection
