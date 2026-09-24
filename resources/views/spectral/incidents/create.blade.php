@extends('layouts.app')

@section('title', 'Report Incident — Spectra')

@section('content')
<div class="flex-1 overflow-y-auto p-6 bg-[#0B0F14] space-y-6 max-w-4xl mx-auto w-full">

    <!-- Top Breadcrumb -->
    <div class="flex items-center justify-between pb-3 border-b border-[#2A3440]">
        <div class="flex items-center gap-2 text-xs font-mono">
            <a href="{{ route('spectral.dashboard') }}" class="text-[#8B5CF6] hover:underline inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Return to Map</span>
            </a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-400">Report Incident</span>
        </div>
    </div>

    <!-- Main Report Card -->
    <div class="p-6 rounded-xl bg-[#151B23] border border-[#2A3440] shadow-2xl space-y-6">
        <div>
            <h1 class="text-lg font-bold text-white">Report Incident</h1>
            <p class="text-xs text-[#9CA3AF]">San Francisco, Agusan del Sur</p>
        </div>

        <form method="POST" action="{{ route('spectral.incidents.store') }}" enctype="multipart/form-data" class="space-y-5 text-xs">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Incident Type -->
                <div class="space-y-1">
                    <label class="block text-slate-300 font-bold">Incident Type</label>
                    <select name="incident_type" class="ecto-select" required>
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
                    <label class="block text-slate-300 font-bold">Severity Assessment</label>
                    <select name="severity" class="ecto-select" required>
                        <option value="LOW">LOW &bull; Minimal spectral trace</option>
                        <option value="MEDIUM" selected>MEDIUM &bull; Active anomaly</option>
                        <option value="HIGH">HIGH &bull; Hazardous manifestation</option>
                        <option value="CRITICAL">CRITICAL &bull; Planar breach / Threat</option>
                    </select>
                </div>
            </div>

            <!-- Title -->
            <div class="space-y-1">
                <label class="block text-slate-300 font-bold">Incident Summary Title</label>
                <input type="text" name="title" class="ecto-input" placeholder="e.g. Glowing viscous pool near Hubang diversion..." required>
            </div>

            <!-- Interactive Map Location Picker -->
            <div class="p-4 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-slate-200">Geographic Location</p>
                        <p class="text-[11px] text-[#9CA3AF]">Click on the map below to pinpoint the incident coordinates.</p>
                    </div>
                    <span id="create-coord-readout" class="text-[11px] font-mono font-bold text-[#8B5CF6]">8.5310° N, 125.9730° E</span>
                </div>

                <div id="create-picker-map" class="w-full h-64 rounded-xl border border-[#2A3440] overflow-hidden cursor-crosshair"></div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Barangay</label>
                        <select name="barangay_id" id="create-barangay" class="ecto-select" required>
                            @foreach($barangays as $b)
                                <option value="{{ $b->id }}" data-lat="{{ $b->latitude }}" data-lng="{{ $b->longitude }}" {{ $b->name === 'Hubang' ? 'selected' : '' }}>
                                    Brgy. {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Latitude</label>
                        <input type="number" step="0.000001" name="latitude" id="create-lat" value="8.5310" class="ecto-input font-mono" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Longitude</label>
                        <input type="number" step="0.000001" name="longitude" id="create-lng" value="125.9730" class="ecto-input font-mono" required>
                    </div>
                <!-- Auto-Resolved Location (OpenStreetMap Nominatim) -->
                <div class="space-y-1 pt-2 border-t border-[#1E2631]">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] text-[#9CA3AF] font-mono flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#8B5CF6]"></span>
                            <span>Readable Location (Nominatim)</span>
                        </label>
                        <span id="create-geocode-status" class="text-[9px] font-mono text-emerald-400">Auto-detected</span>
                    </div>
                    <input type="text" name="resolved_location" id="create-resolved-location" class="ecto-input text-slate-200 text-xs" placeholder="Click map above to auto-detect address..." value="Brgy. Hubang, San Francisco, Agusan del Sur, Philippines">
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-1">
                <label class="block text-slate-300 font-bold">Observed Disturbance / Witness Description</label>
                <textarea name="description" rows="4" class="ecto-input" placeholder="Detail anomalous observations, energy fluctuations, spectral forms, or ward integrity drops..." required></textarea>
            </div>

            <!-- Evidence Photo Upload -->
            <div class="space-y-2">
                <label class="block text-slate-300 font-bold">Attach Photographic Evidence (Optional)</label>
                <input type="file" name="evidence" accept="image/*" class="ecto-input file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#8B5CF6] file:text-white hover:file:bg-[#7C3AED]">
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-[#2A3440] flex items-center justify-end gap-3">
                <a href="{{ route('spectral.dashboard') }}" class="px-5 py-2.5 bg-[#1B222C] hover:bg-[#222B38] text-slate-300 font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold rounded-xl shadow-lg shadow-[#8B5CF6]/30 transition">
                    Submit Incident Report
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let lat = 8.5310;
        let lng = 125.9730;

        const map = L.map('create-picker-map', {
            center: [lat, lng],
            zoom: 13,
            attributionControl: false
        });

        L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: 'Imagery &copy; Google',
            maxZoom: 21,
            subdomains: ['0','1','2','3'],
            tileSize: 256
        }).addTo(map);

        let marker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: '',
                html: `
                    <div class="gis-marker-wrapper">
                        <div class="marker-incident high">
                            <span style="font-size:11px;font-weight:bold;color:#FFFFFF;">●</span>
                        </div>
                    </div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            })
        }).addTo(map);

        map.on('click', (e) => {
            const newLat = e.latlng.lat;
            const newLng = e.latlng.lng;
            marker.setLatLng([newLat, newLng]);

            document.getElementById('create-lat').value = newLat.toFixed(6);
            document.getElementById('create-lng').value = newLng.toFixed(6);
            document.getElementById('create-coord-readout').textContent = `${newLat.toFixed(5)}° N, ${newLng.toFixed(5)}° E`;

            const geocodeStatus = document.getElementById('create-geocode-status');
            const resolvedInput = document.getElementById('create-resolved-location');
            if (geocodeStatus) {
                geocodeStatus.textContent = 'Resolving via Nominatim...';
                geocodeStatus.className = 'text-[9px] font-mono text-amber-400 animate-pulse';
            }

            fetch(`/api/spectral/reverse-geocode?lat=${newLat}&lng=${newLng}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.address) {
                        if (resolvedInput) resolvedInput.value = data.address;
                        if (geocodeStatus) {
                            geocodeStatus.textContent = 'Auto-detected (Nominatim)';
                            geocodeStatus.className = 'text-[9px] font-mono text-emerald-400';
                        }
                        if (data.barangay_id) {
                            const select = document.getElementById('create-barangay');
                            if (select) select.value = data.barangay_id;
                        }
                    }
                })
                .catch(err => console.warn('Nominatim error:', err));
        });
    });
</script>
@endpush
@endsection
