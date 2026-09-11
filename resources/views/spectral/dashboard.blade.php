@extends('layouts.app')

@section('title', 'Spectra — Incident & Ward Monitoring')

@section('content')
<!-- LEFT SIDEBAR -->
@include('spectral.partials.sidebar')

<!-- CENTER MAP CANVAS: Leaflet Interactive GIS Map Workspace -->
<main class="flex-1 relative h-full w-full overflow-hidden bg-[#06090D]">

    <!-- Real Geographic Leaflet Map Container -->
    <div id="spectral-map"></div>

    <!-- Location Selection Active HUD Banner -->
    <div id="location-picker-hud" class="absolute top-3 inset-x-0 mx-auto max-w-md z-[500] px-4 py-2.5 rounded-lg bg-[#1B222C] border border-[#8B5CF6] text-white text-xs shadow-2xl flex items-center justify-between gap-3">
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
        <button onclick="SpectralMap.jumpTo('san_francisco')" title="San Francisco Hub" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">
            SF
        </button>
        <button onclick="SpectralMap.jumpTo('agusan')" title="Agusan del Sur" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">
            ADS
        </button>
        <button onclick="SpectralMap.jumpTo('mindanao')" title="Mindanao Region" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">
            MIN
        </button>
        <button onclick="SpectralMap.jumpTo('philippines')" title="Philippines" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">
            PH
        </button>
        <button onclick="SpectralMap.jumpTo('world')" title="Global View" class="w-9 h-8 rounded-md bg-[#151B23]/95 backdrop-blur border border-[#2A3440] text-[11px] font-mono font-bold text-slate-200 flex items-center justify-center hover:border-[#8B5CF6] hover:text-[#8B5CF6] transition shadow-md">
            GLB
        </button>
    </div>

    <!-- Map Telemetry HUD Overlay (Top Right Below Zoom) -->
    <div class="absolute top-20 right-3 z-[400] hidden sm:block p-3 rounded-lg bg-[#151B23]/90 backdrop-blur border border-[#2A3440] shadow-xl text-xs font-mono max-w-[210px]">
        <div class="flex items-center justify-between pb-2 mb-2 border-b border-[#2A3440]">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Summary</span>
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        </div>
        <div class="space-y-1 text-[11px]">
            <div class="flex justify-between text-slate-400">
                <span>Incidents:</span>
                <strong id="map-hud-incidents" class="text-[#EF4444]">{{ $stats['active_incidents'] ?? 7 }}</strong>
            </div>
            <div class="flex justify-between text-slate-400">
                <span>Ward Stations:</span>
                <strong id="map-hud-wards" class="text-[#8B5CF6]">{{ $stats['ward_stations'] ?? 5 }}</strong>
            </div>
            <div class="flex justify-between text-slate-400">
                <span>Resources:</span>
                <strong id="map-hud-resources" class="text-[#38BDF8]">{{ $stats['resources'] ?? 4 }}</strong>
            </div>
        </div>
        <p class="text-[9px] text-[#64748B] mt-2 pt-1 border-t border-[#1E2631]">
            San Francisco, Agusan del Sur
        </p>
    </div>

    <!-- Street View Toggle Button (Bottom Left) -->
    <div class="absolute bottom-4 left-3 z-[400]">
        <button id="streetview-toggle-btn"
            type="button"
            onclick="SpectralMap.toggleStreetView()"
            title="Toggle Street View — click any location on satellite map"
            class="flex items-center gap-2 px-3 py-2 rounded-lg bg-[#151B23]/95 backdrop-blur border border-[#2A3440] shadow-lg text-xs font-medium text-[#9CA3AF] hover:text-white hover:border-[#FACC15]/60 transition-all group">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#FACC15] group-hover:scale-110 transition" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="4" r="2.5"/>
                <path d="M12 7.5c-2.5 0-4.5 1.5-4.5 3.5v2h2v5h5v-5h2v-2c0-2-2-3.5-4.5-3.5z"/>
                <path d="M9.5 18h5v2.5h-5z"/>
            </svg>
            <span class="font-mono text-[11px]" id="streetview-btn-label">Street View</span>
        </button>
    </div>

    <!-- Tile load error banner -->
    <div id="map-tile-error">Map tiles unavailable — check connection</div>

    <!-- LEFT-SIDE STREET VIEW PANEL (Simultaneous Satellite + Street View) -->
    <div id="streetview-left-panel"
        class="absolute top-0 bottom-0 left-0 w-full sm:w-[460px] lg:w-[500px] z-[500] bg-[#11161D] border-r border-[#2A3440] shadow-2xl flex flex-col transition-transform duration-300 transform -translate-x-full overflow-hidden select-none">

        <!-- Top Header -->
        <div class="px-4 py-3 bg-[#0B0F14] border-b border-[#2A3440] flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-[#FACC15]/10 border border-[#FACC15]/40 flex items-center justify-center text-[#FACC15] flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="4" r="2.5"/>
                        <path d="M12 7.5c-2.5 0-4.5 1.5-4.5 3.5v2h2v5h5v-5h2v-2c0-2-2-3.5-4.5-3.5z"/>
                        <path d="M9.5 18h5v2.5h-5z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-white tracking-wide">STREET VIEW</span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-[9px] font-mono font-semibold text-emerald-400 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>LIVE SYNC</span>
                        </span>
                    </div>
                    <p id="sv-road-subtitle" class="text-[10px] text-[#9CA3AF] font-mono truncate">San Francisco, Agusan del Sur</p>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button type="button" onclick="SpectralMap.closeStreetView()" title="Close Street View"
                    class="w-7 h-7 rounded-md bg-[#1B222C] hover:bg-red-500/20 text-[#9CA3AF] hover:text-red-400 border border-[#2A3440] flex items-center justify-center text-sm font-bold transition">
                    &times;
                </button>
            </div>
        </div>

        <!-- 360° Interactive Road Viewport (Drag to watch Left / Right) -->
        <div class="relative w-full h-[290px] bg-[#06090D] overflow-hidden flex-shrink-0">
            <!-- Real Google Street View Container (Loaded when API key is provided) -->
            <div id="streetview-real-container" class="w-full h-full hidden"></div>

            <!-- 360 Canvas (Procedural Standalone Road Viewer) -->
            <canvas id="streetview-canvas" class="w-full h-full cursor-grab active:cursor-grabbing block"></canvas>

            <!-- Top Location Floating Pill (matching Google Maps in screenshot) -->
            <div class="absolute top-2.5 left-2.5 right-2.5 z-10 flex items-start justify-between pointer-events-none">
                <div class="bg-[#0B0F14]/90 backdrop-blur border border-[#2A3440] rounded-lg px-3 py-1.5 shadow-lg max-w-[85%]">
                    <h4 id="sv-road-title" class="text-xs font-bold text-white leading-tight truncate">Davao-Agusan National Hwy</h4>
                    <p id="sv-road-meta" class="text-[10px] text-[#9CA3AF] font-mono leading-tight">San Francisco, Agusan del Sur · Dec 2024</p>
                </div>
                <div id="sv-mode-badge" class="bg-[#0B0F14]/90 backdrop-blur border border-[#2A3440] rounded-md px-2 py-1 shadow-lg text-[9px] font-mono text-[#FACC15] font-bold">
                    360°
                </div>
            </div>

            <!-- Compass & Heading Indicator -->
            <div id="sv-compass-container" class="absolute bottom-2.5 right-2.5 z-10 flex items-center gap-1.5 bg-[#0B0F14]/90 backdrop-blur border border-[#2A3440] px-2.5 py-1 rounded-md text-[10px] font-mono text-slate-300 shadow-md">
                <span id="sv-compass-icon" class="inline-block transition-transform duration-75">🧭</span>
                <span id="sv-heading-display" class="font-bold text-[#FACC15]">172° S</span>
            </div>

            <!-- Road Look Left / Look Right Control Overlay -->
            <div id="sv-controls-overlay" class="absolute bottom-2.5 left-2.5 z-10 flex items-center gap-1.5">
                <button type="button" onclick="SpectralMap.panStreetView(-45)" title="Turn Left (Look Left)"
                    class="w-8 h-8 rounded-full bg-[#1B222C]/90 hover:bg-[#8B5CF6] hover:text-white text-[#9CA3AF] border border-[#2A3440] flex items-center justify-center font-bold text-base shadow-lg transition active:scale-95">
                    ‹
                </button>
                <button type="button" onclick="SpectralMap.panStreetView(45)" title="Turn Right (Look Right)"
                    class="w-8 h-8 rounded-full bg-[#1B222C]/90 hover:bg-[#8B5CF6] hover:text-white text-[#9CA3AF] border border-[#2A3440] flex items-center justify-center font-bold text-base shadow-lg transition active:scale-95">
                    ›
                </button>
                <button type="button" onclick="SpectralMap.resetStreetViewNorth()" title="Reset to North"
                    class="px-2.5 h-8 rounded-full bg-[#1B222C]/90 hover:bg-[#2A3440] text-[10px] font-mono text-slate-300 border border-[#2A3440] flex items-center justify-center shadow-lg transition">
                    North
                </button>
            </div>

            <!-- Hint overlay (fades out on interaction) -->
            <div id="sv-drag-hint" class="absolute top-14 inset-x-0 mx-auto text-center pointer-events-none transition-opacity duration-500">
                <span class="px-2.5 py-1 rounded-full bg-black/70 border border-white/10 text-[10px] font-mono text-slate-300 shadow-lg">
                    ⟵ Drag to look left &amp; right ⟶
                </span>
            </div>
        </div>

        <!-- Details & Actions Section (Matching Google Maps overview) -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#11161D]">

            <!-- Direct Google Maps Street View Launcher Button -->
            <a id="sv-google-deep-link" href="#" target="_blank" rel="noopener noreferrer"
                class="w-full py-2.5 px-3 bg-gradient-to-r from-[#1B222C] to-[#222B38] hover:from-[#8B5CF6]/20 hover:to-[#8B5CF6]/30 border border-[#8B5CF6]/40 hover:border-[#8B5CF6] rounded-xl text-white text-xs font-bold flex items-center justify-between transition group shadow-md">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#FACC15]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                    <span>Open Official Google Street View</span>
                </div>
                <svg class="w-4 h-4 text-[#9CA3AF] group-hover:text-white transition transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>

            <!-- Street View Mode Status -->
            <div id="sv-key-box" class="p-3 rounded-xl bg-[#0B0F14] border border-[#2A3440] space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-[#64748B]">Ground View Engine</span>
                    <span id="sv-key-status-badge" class="px-1.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-[9px] font-mono text-emerald-400">ACTIVE 360° ENGINE</span>
                </div>
                <p id="sv-key-desc" class="text-[10px] text-[#9CA3AF] leading-relaxed">
                    Synchronized live 360° road perspective with Philippine highway layout, vehicle recognition, and terrain mapping.
                </p>
            </div>

            <!-- Location Coordinates Card -->
            <div class="p-3 rounded-xl bg-[#0B0F14] border border-[#2A3440] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-[#64748B]">GPS Coordinate</span>
                    <button type="button" onclick="SpectralMap.copyCoordinates()" title="Copy coordinates" class="text-[10px] text-[#8B5CF6] hover:text-[#A78BFA] font-mono font-semibold">
                        Copy
                    </button>
                </div>
                <div id="sv-coord-text" class="text-xs font-mono font-bold text-slate-200">
                    8.51000° N, 125.97500° E
                </div>
                <div class="flex items-center justify-between text-[11px] pt-2 border-t border-[#1E2631]">
                    <span class="text-[#9CA3AF]">Barangay:</span>
                    <span id="sv-barangay-text" class="font-semibold text-slate-200">Hubang</span>
                </div>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-[#9CA3AF]">Nearest Ward:</span>
                    <span id="sv-nearest-ward" class="font-mono text-[#8B5CF6]">WS-01 (~450m)</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="SpectralMap.reportAtCurrentStreetView()"
                    class="w-full py-2 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-1.5 shadow-md shadow-[#8B5CF6]/20">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Report Anomaly</span>
                </button>
                <button type="button" onclick="SpectralMap.jumpToStreetViewMarker()"
                    class="w-full py-2 bg-[#1B222C] hover:bg-[#222B38] text-slate-300 hover:text-white text-xs font-semibold rounded-lg border border-[#2A3440] transition flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Focus Satellite</span>
                </button>
            </div>

            <!-- Ground Reconnaissance Info -->
            <div class="p-3 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-mono uppercase text-[#64748B]">Ground Reconnaissance</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <p id="sv-recon-notes" class="text-[11px] text-[#9CA3AF] leading-relaxed">
                    Satellite scan synchronized with ground reconnaissance. Road surface and surrounding perimeter clear of containment breaches.
                </p>
            </div>

        </div>

    </div>

</main>


<!-- RIGHT INFORMATION PANEL -->
@include('spectral.partials.infopanel')

@endsection

@section('modals')
<!-- INCIDENT REPORTING MODAL -->
<div id="report-modal" class="hidden fixed inset-0 z-[1000] flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 ecto-modal-backdrop" onclick="SpectralUI.closeReportModal()"></div>

    <!-- Modal Content -->
    <div class="relative ecto-modal-content w-full max-w-lg max-h-[90vh] flex flex-col select-none rounded-xl bg-[#151B23] border border-[#2A3440] shadow-2xl overflow-hidden">
        
        <!-- Modal Header -->
        <div class="px-5 py-3.5 border-b border-[#2A3440] flex items-center justify-between flex-shrink-0 bg-[#11161D]">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra" class="w-6 h-6 object-contain">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-wide">Report Incident</h3>
                    <p class="text-[10px] text-[#9CA3AF]">San Francisco, Agusan del Sur</p>
                </div>
            </div>
            <button onclick="SpectralUI.closeReportModal()" class="w-7 h-7 rounded-md bg-[#1B222C] hover:bg-[#222B38] text-[#9CA3AF] hover:text-white flex items-center justify-center text-sm font-bold transition">
                &times;
            </button>
        </div>

        <!-- Form Body -->
        <form id="report-form" onsubmit="SpectralUI.submitReport(event)" class="p-5 overflow-y-auto space-y-3.5 text-xs">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Incident Type -->
                <div class="space-y-1">
                    <label class="text-[11px] font-semibold text-slate-300">Incident Type</label>
                    <select id="report-type" class="ecto-select" required>
                        <option value="Ectoplasmic Anomaly">Ectoplasmic Anomaly</option>
                        <option value="Spirit Activity">Spirit Activity</option>
                        <option value="Spectral Residue">Spectral Residue</option>
                        <option value="Ward Failure">Ward Failure</option>
                        <option value="Containment Breach">Containment Breach</option>
                        <option value="Unknown Phenomenon">Unknown Phenomenon</option>
                    </select>
                </div>

                <!-- Severity -->
                <div class="space-y-1">
                    <label class="text-[11px] font-semibold text-slate-300">Severity</label>
                    <select id="report-severity" class="ecto-select" required>
                        <option value="LOW">Low</option>
                        <option value="MEDIUM" selected>Medium</option>
                        <option value="HIGH">High</option>
                        <option value="CRITICAL">Critical</option>
                    </select>
                </div>
            </div>

            <!-- Incident Title -->
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">Title</label>
                <input type="text" id="report-title" class="ecto-input" placeholder="e.g. Energy surge detected near Hubang..." required>
            </div>

            <!-- Location Picker Action & Barangay Selector -->
            <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-slate-300">Coordinates</span>
                    <button type="button" onclick="SpectralMap.startLocationPicking()" class="px-2.5 py-1 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold text-[11px] rounded-md transition flex items-center gap-1.5 shadow-sm">
                        <span>Select on Map</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Barangay</label>
                        <select id="report-barangay" class="ecto-select" required>
                            @foreach($barangays as $b)
                                <option value="{{ $b->name }}" data-id="{{ $b->id }}" {{ $b->name === 'Hubang' ? 'selected' : '' }}>
                                    Brgy. {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Latitude</label>
                        <input type="number" step="0.000001" id="report-lat" class="ecto-input font-mono" placeholder="8.5310" value="8.5310" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Longitude</label>
                        <input type="number" step="0.000001" id="report-lng" class="ecto-input font-mono" placeholder="125.9730" value="125.9730" required>
                    </div>
                </div>
            </div>

            <!-- Date & Time -->
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">Date &amp; Time</label>
                <input type="datetime-local" id="report-datetime" class="ecto-input font-mono" required>
            </div>

            <!-- Description -->
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">Description</label>
                <textarea id="report-desc" rows="3" class="ecto-input" placeholder="Provide incident details, observations, or warnings..." required></textarea>
            </div>

            <!-- Evidence Photo Upload -->
            <div class="space-y-2">
                <label class="text-[11px] font-semibold text-slate-300">Evidence Photo (Optional)</label>
                <input type="file" id="report-evidence-file" accept="image/*" onchange="SpectralUI.handleEvidenceUpload(this)" class="ecto-input file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#8B5CF6] file:text-white hover:file:bg-[#7C3AED]">
                
                <!-- Image Preview Card -->
                <div id="report-evidence-preview-container" class="hidden relative rounded-lg border border-[#2A3440] bg-[#11161D] p-2 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img id="report-evidence-preview" src="" alt="Preview" class="w-16 h-12 object-cover rounded-md border border-[#2A3440]">
                        <span class="text-[11px] text-slate-300 font-mono">File Attached</span>
                    </div>
                    <button type="button" onclick="SpectralUI.removeEvidencePreview()" class="px-2 py-1 bg-[#1B222C] hover:bg-[#EF4444] text-slate-300 hover:text-white rounded text-[10px] font-semibold transition">
                        Remove
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-[#2A3440] flex items-center justify-end gap-2">
                <button type="button" onclick="SpectralUI.closeReportModal()" class="px-4 py-2 bg-[#1B222C] hover:bg-[#222B38] text-slate-300 font-semibold rounded-lg text-xs transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold rounded-lg text-xs shadow-md shadow-[#8B5CF6]/20 transition">
                    Submit Report
                </button>
            </div>

        </form>

    </div>
</div>

<!-- Server-injected initial state for instant Leaflet map bootstrap -->
<script>
    window.INITIAL_SPECTRAL_STATE = {
        incidents: @json($incidents),
        wards: @json($wardStations),
        resources: @json($resources),
        barangays: @json($barangays),
    };
    window.GOOGLE_MAPS_API_KEY = @json(config('services.google.maps_api_key'));
</script>
@endsection
