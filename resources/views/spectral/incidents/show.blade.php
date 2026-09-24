@extends('layouts.app')

@section('title', 'Incident ' . $incident->incident_code . ' — Spectra')

@section('content')
<div class="flex-1 overflow-y-auto p-6 bg-[#0B0F14] space-y-6 max-w-6xl mx-auto w-full">

    <!-- Top Breadcrumb & Actions -->
    <div class="flex items-center justify-between pb-3 border-b border-[#2A3440]">
        <div class="flex items-center gap-2 text-xs font-mono">
            <a href="{{ route('spectral.dashboard') }}" class="text-[#8B5CF6] hover:underline inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                <span>GIS Map</span>
            </a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('spectral.incidents.index') }}" class="text-[#8B5CF6] hover:underline">Registry</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-400">{{ $incident->incident_code }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if($incident->reported_by === auth()->id() || auth()->user()?->isAdmin())
            <button type="button"
                    onclick="openDeleteModal('{{ route('spectral.incidents.destroy', $incident->id) }}', '{{ $incident->incident_code }}', '{{ addslashes($incident->title) }}')"
                    class="flex items-center gap-1.5 px-3 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 border border-rose-500/30 hover:border-rose-500/50 rounded-full text-xs font-mono font-bold transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span>Delete</span>
            </button>
            @endif

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
                        <p class="font-mono text-slate-200 font-bold mt-0.5">{{ $incident->incident_date->format('Y-m-d h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">San Francisco Node</p>
                        <p class="font-bold text-[#22C55E] mt-0.5">Recorded</p>
                    </div>
                </div>
            </div>

            @php
                $latestAssignment = $incident->responderAssignments->sortByDesc('assigned_at')->first();
            @endphp

            {{-- ANOMALY CONDITION: Only show for VERIFIED / ESCALATED / RESOLVED --}}
            @if(in_array($incident->status, ['VERIFIED', 'ESCALATED', 'RESOLVED']))
            @php
                $anomalyMaxVal = $latestAssignment?->anomaly_max_hp ?: ($incident->anomaly_max_hp ?: config('spectral_response.anomaly_hp.' . $incident->severity, 100));
                $anomalyHpVal  = $latestAssignment?->anomaly_hp !== null ? $latestAssignment->anomaly_hp : ($incident->anomaly_hp !== null ? $incident->anomaly_hp : $anomalyMaxVal);
                $anomalyPctLeft = ($anomalyMaxVal > 0)
                    ? max(0, min(100, round(($anomalyHpVal / $anomalyMaxVal) * 100)))
                    : 100;
                $responseProgress = $latestAssignment?->response_progress ?? ($incident->response_progress ?? 0);
                $responseTimeLeft = null;
                if ($latestAssignment?->response_deadline) {
                    $secsLeft = max(0, now()->diffInSeconds($latestAssignment->response_deadline, false));
                    $responseTimeLeft = $secsLeft > 0
                        ? sprintf('%d:%02d', intdiv($secsLeft, 60), $secsLeft % 60)
                        : '00:00';
                }
            @endphp
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] shadow-xl space-y-4 font-mono">
                <div class="flex items-center justify-between border-b border-[#1E2631] pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">ANOMALY CONDITION</h3>
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold
                        {{ $incident->status === 'RESOLVED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' :
                           ($incident->status === 'ESCALATED' ? 'bg-red-500/20 text-red-400 border border-red-500/30' :
                           'bg-blue-500/20 text-blue-400 border border-blue-500/30') }}">
                        {{ $incident->status }}
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- HP Row -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[#64748B] uppercase text-[10px] font-bold">HP</span>
                            <span class="font-extrabold text-lg {{ $anomalyPctLeft <= 25 ? 'text-emerald-400' : ($anomalyPctLeft <= 60 ? 'text-yellow-400' : 'text-rose-400') }}">
                                {{ $anomalyHpVal }} / {{ $anomalyMaxVal }}
                            </span>
                        </div>
                        <div class="w-full bg-[#11161D] h-3 rounded-full overflow-hidden border border-[#2A3440]">
                            <div class="{{ $anomalyPctLeft <= 25 ? 'bg-emerald-500' : ($anomalyPctLeft <= 60 ? 'bg-yellow-500' : 'bg-rose-500') }} h-full transition-all duration-500"
                                 style="width: {{ $anomalyPctLeft }}%;"></div>
                        </div>
                        <p class="text-[10px] text-slate-500">
                            @if($latestAssignment && $latestAssignment->status === 'ACTIVE')
                                Active response synchronization — real-time containment tracking
                            @elseif($incident->status === 'RESOLVED')
                                Anomaly neutralized and safely stabilized
                            @else
                                Anomaly confirmed — awaiting responder deployment
                            @endif
                        </p>
                    </div>

                    <!-- Response Progress + Time + Status -->
                    <div class="pt-2 border-t border-[#1E2631] space-y-2">
                        <div class="space-y-1">
                            <div class="flex justify-between text-[11px]">
                                <span class="text-[#64748B]">RESPONSE PROGRESS:</span>
                                <strong class="text-emerald-400">{{ $responseProgress }}%</strong>
                            </div>
                            <div class="w-full bg-[#11161D] h-1.5 rounded-full overflow-hidden border border-[#2A3440]">
                                <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $responseProgress }}%;"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <span class="block text-[#64748B] text-[10px] uppercase">RESPONSE STATUS</span>
                                <strong class="text-white text-[11px]">
                                    @if($latestAssignment)
                                        @if($latestAssignment->status === 'ACTIVE')
                                            Under Response
                                        @elseif($latestAssignment->status === 'ASSIGNED')
                                            Responder Assigned
                                        @elseif($latestAssignment->status === 'ACCEPTED')
                                            Responder En Route
                                        @elseif($latestAssignment->status === 'COMPLETED')
                                            Neutralized
                                        @else
                                            {{ $latestAssignment->status }}
                                        @endif
                                    @elseif($incident->status === 'VERIFIED')
                                        Confirmed — Awaiting Responder
                                    @else
                                        {{ $incident->status }}
                                    @endif
                                </strong>
                            </div>
                            <div>
                                <span class="block text-[#64748B] text-[10px] uppercase">TIME REMAINING</span>
                                <strong class="text-[#A78BFA] text-[11px]">
                                    @if($responseTimeLeft)
                                        {{ $responseTimeLeft }}
                                    @elseif($latestAssignment && in_array($latestAssignment->status, ['ASSIGNED','ACCEPTED']))
                                        Begins on deployment
                                    @else
                                        --:--
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- STATUS TIMELINE -->
            @php
                $isReported = true;
                $isInvestigating = in_array($incident->status, ['UNDER INVESTIGATION', 'VERIFIED', 'RESOLVED', 'ESCALATED']) || $incident->investigations->isNotEmpty();
                $isConfirmed = $incident->investigation_result === 'CONFIRMED' || in_array($incident->status, ['VERIFIED', 'RESOLVED']) || $latestAssignment !== null;
                $isResponderAssigned = $latestAssignment && $latestAssignment->responder_id;
                $isUnderResponse = ($latestAssignment && in_array($latestAssignment->status, ['ACTIVE', 'COMPLETED', 'CRITICAL', 'SUPPORT_REQUIRED'])) || $incident->status === 'RESOLVED';
            @endphp
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] shadow-xl space-y-3 font-mono">
                <div class="flex items-center justify-between border-b border-[#1E2631] pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-white">STATUS TIMELINE</h3>
                    <span class="text-[10px] text-[#64748B]">STAGE PROGRESSION</span>
                </div>
                <div class="space-y-2 text-xs pt-1">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $isReported ? 'bg-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-600' }}"></span>
                        <span class="{{ $isReported ? 'text-white font-bold' : 'text-slate-500' }}">Reported</span>
                        <span class="text-[10px] text-slate-500 ml-auto">{{ $incident->incident_date ? $incident->incident_date->format('M j, h:i A') : '' }}</span>
                    </div>
                    <div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $isInvestigating ? 'bg-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-600' }}"></span>
                        <span class="{{ $isInvestigating ? 'text-white font-bold' : 'text-slate-500' }}">Under Investigation</span>
                    </div>
                    <div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $isConfirmed ? 'bg-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-600' }}"></span>
                        <span class="{{ $isConfirmed ? 'text-white font-bold' : 'text-slate-500' }}">Confirmed</span>
                    </div>
                    <div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $isResponderAssigned ? 'bg-emerald-400 ring-2 ring-emerald-500/20' : 'bg-slate-600' }}"></span>
                        <span class="{{ $isResponderAssigned ? 'text-white font-bold' : 'text-slate-500' }}">Responder Assigned</span>
                        @if($isResponderAssigned && $latestAssignment->responder)
                            <span class="text-[10px] text-[#A78BFA] ml-auto font-bold">{{ $latestAssignment->responder->name }} (Class {{ $latestAssignment->responder->responder_class }})</span>
                        @endif
                    </div>
                    <div class="pl-1 text-slate-600 text-[10px] leading-none">↓</div>
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $isUnderResponse ? 'bg-[#8B5CF6] ring-2 ring-[#8B5CF6]/30 animate-pulse' : 'bg-slate-600' }}"></span>
                        <span class="{{ $isUnderResponse ? ($incident->status === 'RESOLVED' ? 'text-emerald-400 font-bold' : 'text-[#A78BFA] font-bold') : 'text-slate-500' }}">
                            {{ $incident->status === 'RESOLVED' ? 'Neutralized / Resolved' : 'Under Response' }}
                        </span>
                        @if($isUnderResponse && $latestAssignment && $latestAssignment->status === 'ACTIVE')
                            <span class="text-[10px] text-emerald-400 ml-auto font-bold">{{ $latestAssignment->response_progress }}% Stabilized</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Evidence Photos Section -->
            <div class="p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 shadow-xl">
                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
                    </svg>
                    <span>Attached Spectral Evidence ({{ $incident->evidence->count() }})</span>
                </h3>

                @if($incident->evidence->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($incident->evidence as $ev)
                            <div class="rounded-xl overflow-hidden border border-[#2A3440] bg-[#11161D] space-y-2 p-2">
                                <a href="{{ $ev->url }}" target="_blank" class="block aspect-video overflow-hidden rounded-lg relative group">
                                    <img src="{{ $ev->url }}" alt="{{ $ev->file_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold font-mono gap-1.5">
                                        <span>View Full Resolution</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
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
                            <p class="text-slate-400 leading-relaxed">Operational update recorded. Internal investigator notes are restricted.</p>
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
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Current Location</span>
                    <span class="text-[11px] font-mono text-[#8B5CF6]">Brgy. {{ $incident->barangay->name ?? 'San Francisco' }}</span>
                </div>

                <div id="mini-incident-map" class="w-full h-56 rounded-xl border border-[#2A3440] overflow-hidden"></div>

                {{-- "View on GIS Map" button — goes back to dashboard and auto-zooms to this incident --}}
                <a href="{{ route('spectral.dashboard') }}?zoom_lat={{ $incident->latitude }}&zoom_lng={{ $incident->longitude }}&zoom_incident={{ $incident->id }}"
                   class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-[#0E131A] border border-[#2A3440] hover:border-[#8B5CF6]/60 hover:bg-[#1B222C] text-[11px] font-mono font-bold text-[#64748B] hover:text-white transition group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#8B5CF6] flex-shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    <span>View on GIS Map</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#64748B] group-hover:text-[#8B5CF6] transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </a>
            </div>


            @if(auth()->user()?->isAdmin())
            <!-- Administrator Action Workflow Box -->
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
            @endif

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
            zoom: 16,
            zoomControl: false,
            attributionControl: false
        });

        // Google Satellite Tile Layer — identical to dashboard map API
        L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: 'Imagery &copy; Google',
            maxZoom: 21,
            subdomains: ['0','1','2','3'],
            tileSize: 256
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

@section('modals')
@include('spectral.partials.delete_modal_partial')
@endsection
