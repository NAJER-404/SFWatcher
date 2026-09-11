@extends('layouts.app')

@section('title', 'Incident ' . $incident->incident_code . ' — Spectra')

@section('content')
<div class="flex-1 overflow-y-auto p-6 bg-[#0B0F14] space-y-6 max-w-6xl mx-auto w-full">

    <!-- Top Breadcrumb & Actions -->
    <div class="flex items-center justify-between pb-3 border-b border-[#2A3440]">
        <div class="flex items-center gap-2 text-xs font-mono">
            <a href="{{ route('spectral.dashboard') }}" class="text-[#8B5CF6] hover:underline">&larr; GIS Map</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('spectral.incidents.index') }}" class="text-[#8B5CF6] hover:underline">Registry</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-400">{{ $incident->incident_code }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="badge-{{ strtolower($incident->severity) }} px-3 py-1 rounded-full text-xs font-bold font-mono">
                SEVERITY: {{ $incident->severity }}
            </span>
            <span class="{{ $incident->status_badge_class }} px-3 py-1 rounded-full text-xs font-bold font-mono">
                STATUS: {{ $incident->status }}
            </span>
        </div>
    </div>

    <!-- Main 2-Column Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Incident Details & Evidence -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Core Details Card -->
            <div class="p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 shadow-xl">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#8B5CF6]">{{ $incident->incident_type }}</span>
                    <h1 class="text-xl font-extrabold text-white mt-1 leading-snug">{{ $incident->title }}</h1>
                </div>

                <div class="text-xs text-slate-300 leading-relaxed space-y-2">
                    <p class="font-bold text-slate-400 uppercase text-[10px] tracking-wider font-mono">Observed Disturbance</p>
                    <p class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] whitespace-pre-line text-slate-200">
                        {{ $incident->description }}
                    </p>
                </div>

                <!-- Reporting Meta -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 border-t border-[#1E2631] text-xs">
                    <div>
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">Reported By</p>
                        <p class="font-bold text-slate-200 mt-0.5">{{ $incident->reporter->name ?? 'Civilian Observer' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">Incident Date</p>
                        <p class="font-mono text-slate-200 font-bold mt-0.5">{{ $incident->incident_date->format('Y-m-d H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">San Francisco Node</p>
                        <p class="font-bold text-[#22C55E] mt-0.5">Recorded</p>
                    </div>
                </div>
            </div>

            <!-- Evidence Photos Section -->
            <div class="p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 shadow-xl">
                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                    <span>📷</span>
                    <span>Attached Spectral Evidence ({{ $incident->evidence->count() }})</span>
                </h3>

                @if($incident->evidence->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($incident->evidence as $ev)
                            <div class="rounded-xl overflow-hidden border border-[#2A3440] bg-[#11161D] space-y-2 p-2">
                                <a href="{{ $ev->url }}" target="_blank" class="block aspect-video overflow-hidden rounded-lg relative group">
                                    <img src="{{ $ev->url }}" alt="{{ $ev->file_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold font-mono">
                                        View Full Resolution &rarr;
                                    </div>
                                </a>
                                <div class="px-1 text-[11px] text-[#9CA3AF] flex justify-between">
                                    <span class="truncate max-w-[160px]">{{ $ev->file_name }}</span>
                                    <span class="font-mono text-[10px]">{{ $ev->created_at->format('M d, H:i') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 rounded-xl bg-[#11161D] border border-[#2A3440] text-center text-slate-500 text-xs">
                        No photographic evidence submitted with this report.
                    </div>
                @endif
            </div>

            <!-- Investigation Timeline Log -->
            <div class="p-6 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-4 shadow-xl">
                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">
                    Investigation &amp; Action Log
                </h3>

                <div class="space-y-3">
                    @forelse($incident->investigations as $inv)
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-white">{{ $inv->investigator->name ?? 'Administrator' }}</span>
                                    <span class="text-[10px] font-mono px-2 py-0.2 rounded bg-[#1B222C] text-[#8B5CF6] border border-[#2A3440]">{{ $inv->result ?? 'UPDATE' }}</span>
                                </div>
                                <span class="font-mono text-[10px] text-[#64748B]">{{ $inv->investigation_date->format('Y-m-d H:i') }}</span>
                            </div>
                            <p class="text-slate-300 leading-relaxed">{{ $inv->notes }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-3">Pending initial review.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right 1 Col: Georeferenced Location & Investigator Controls -->
        <div class="space-y-6">

            <!-- Georeferenced Mini Map -->
            <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-3 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Location</span>
                    <span class="text-[11px] font-mono text-[#8B5CF6]">Brgy. {{ $incident->barangay->name ?? 'San Francisco' }}</span>
                </div>

                <div id="mini-incident-map" class="w-full h-56 rounded-xl border border-[#2A3440] overflow-hidden"></div>

                <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] font-mono text-[11px] space-y-1 text-slate-300">
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Latitude:</span>
                        <span class="font-bold">{{ number_format($incident->latitude, 6) }}° N</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#64748B]">Longitude:</span>
                        <span class="font-bold">{{ number_format($incident->longitude, 6) }}° E</span>
                    </div>
                    <div class="flex justify-between pt-1 border-t border-[#1E2631]">
                        <span class="text-[#64748B]">Municipality:</span>
                        <span>{{ $incident->barangay->municipality ?? 'San Francisco' }}</span>
                    </div>
                </div>
            </div>

            <!-- Investigator Action Workflow Box -->
            <div class="p-5 rounded-xl bg-[#1B222C] border border-[#8B5CF6]/40 space-y-4 shadow-xl">
                <div class="pb-2 border-b border-[#2A3440]">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Administrator Action</h3>
                </div>

                <form method="POST" action="{{ route('spectral.incidents.update-status', $incident->id) }}" class="space-y-3 text-xs">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300 uppercase">Advance Workflow Status</label>
                        <select name="status" class="ecto-select">
                            <option value="PENDING" {{ $incident->status === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            <option value="UNDER INVESTIGATION" {{ $incident->status === 'UNDER INVESTIGATION' ? 'selected' : '' }}>UNDER INVESTIGATION</option>
                            <option value="VERIFIED" {{ $incident->status === 'VERIFIED' ? 'selected' : '' }}>VERIFIED</option>
                            <option value="RESOLVED" {{ $incident->status === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
                            <option value="ESCALATED" {{ $incident->status === 'ESCALATED' ? 'selected' : '' }}>ESCALATED</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300 uppercase">Update Severity</label>
                        <select name="severity" class="ecto-select">
                            <option value="LOW" {{ $incident->severity === 'LOW' ? 'selected' : '' }}>LOW</option>
                            <option value="MEDIUM" {{ $incident->severity === 'MEDIUM' ? 'selected' : '' }}>MEDIUM</option>
                            <option value="HIGH" {{ $incident->severity === 'HIGH' ? 'selected' : '' }}>HIGH</option>
                            <option value="CRITICAL" {{ $incident->severity === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300 uppercase">Field Investigation Notes</label>
                        <textarea name="notes" rows="3" class="ecto-input" placeholder="Enter findings, ward stabilization notes, or dispatch directives..." required></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold rounded-xl transition shadow-md shadow-[#8B5CF6]/30">
                        Commit Investigation Record
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lat = {{ $incident->latitude }};
        const lng = {{ $incident->longitude }};
        const sevColor = '{{ $incident->severity_color }}';

        const miniMap = L.map('mini-incident-map', {
            center: [lat, lng],
            zoom: 14,
            zoomControl: false,
            attributionControl: false
        });

        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(miniMap);

        const markerHtml = `
            <div class="gis-marker-wrapper">
                <div class="marker-incident {{ strtolower($incident->severity) }}">
                    <div class="marker-incident-pulse"></div>
                    <span style="font-size:11px;font-weight:bold;color:#FFFFFF;">●</span>
                </div>
            </div>`;

        L.marker([lat, lng], {
            icon: L.divIcon({
                className: '',
                html: markerHtml,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            })
        }).addTo(miniMap);
    });
</script>
@endpush
@endsection
