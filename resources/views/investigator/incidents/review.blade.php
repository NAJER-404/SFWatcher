@extends('layouts.investigator')

@section('title', 'Review ' . $incident->incident_code . ' — Investigator Portal')

@section('content')
@include('investigator.partials.sidebar')

<main class="flex-1 overflow-y-auto p-5 md:p-7 bg-[#0B0F14] space-y-6 max-w-5xl mx-auto w-full">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-mono flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 12 4 10"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->has('status'))
        <div class="px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-mono flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $errors->first('status') }}
        </div>
    @endif

    {{-- Breadcrumb + Status Badges --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs font-mono">
            <a href="{{ route('investigator.dashboard') }}" class="text-[#8B5CF6] hover:underline inline-flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                Dashboard
            </a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('investigator.queue') }}" class="text-[#8B5CF6] hover:underline">Queue</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold">{{ $incident->incident_code }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="badge-{{ strtolower($incident->severity) }} px-3 py-1 rounded-full text-xs font-bold font-mono">
                SEVERITY: {{ $incident->severity }}
            </span>
            <span class="{{ $incident->status_badge_class }} px-3 py-1 rounded-full text-xs font-bold font-mono">
                {{ $incident->status }}
            </span>
        </div>
    </div>

    {{-- 2-Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Incident Details + Evidence + History --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Core Details --}}
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#8B5CF6]">{{ $incident->incident_type }}</span>
                        <h1 class="text-xl font-extrabold text-white mt-1 leading-snug">{{ $incident->title }}</h1>
                    </div>
                    <span class="font-mono text-xs font-bold text-slate-400 bg-[#11161D] px-2.5 py-1 rounded border border-[#2A3440] whitespace-nowrap">
                        {{ $incident->incident_code }}
                    </span>
                </div>

                <p class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-200 text-xs leading-relaxed whitespace-pre-line">{{ $incident->description }}</p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-3 border-t border-[#1E2631] text-xs">
                    <div>
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">Reporter</p>
                        <p class="font-bold text-slate-200 mt-0.5">{{ $incident->reporter->name ?? 'Civilian Field Scout' }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">{{ $incident->reporter->email ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">Reported</p>
                        <p class="font-mono text-slate-200 font-bold mt-0.5 text-[11px]">
                            {{ $incident->incident_date ? $incident->incident_date->format('Y-m-d h:i A') : ($incident->created_at ? $incident->created_at->format('Y-m-d h:i A') : '—') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[10px] font-mono text-[#64748B] uppercase">Location</p>
                        <p class="font-bold text-slate-200 mt-0.5">Brgy. {{ $incident->barangay->name ?? 'San Francisco' }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">San Francisco, Agusan del Sur</p>
                    </div>
                </div>
            </div>

            {{-- Evidence --}}
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        Field Evidence ({{ $incident->evidence->count() }})
                    </h3>
                    <span class="text-[10px] font-mono text-[#64748B]">Read-Only</span>
                </div>

                @if($incident->evidence->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($incident->evidence as $ev)
                            <div class="rounded-xl overflow-hidden border border-[#2A3440] bg-[#11161D] p-2">
                                <a href="{{ $ev->url }}" target="_blank" class="block aspect-video overflow-hidden rounded-lg relative group bg-black/40">
                                    <img src="{{ $ev->url }}" alt="{{ $ev->file_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold font-mono gap-1.5">
                                        <span>View Full Resolution</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                    </div>
                                </a>
                                <div class="px-1 pt-1.5 text-[11px] text-[#9CA3AF] flex justify-between items-center">
                                    <span class="truncate max-w-[160px] font-mono">{{ $ev->file_name }}</span>
                                    <span class="font-mono text-[10px] text-[#64748B]">{{ $ev->created_at?->format('M d, Y') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-5 rounded-xl bg-[#11161D] border border-[#2A3440] text-center text-slate-500 text-xs font-mono">
                        No evidence attached to this report.
                    </div>
                @endif
            </div>

            {{-- Investigation History --}}
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-[#1E2631]">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                        Audit Trail
                    </h3>
                    <span class="text-[10px] font-mono text-slate-400">{{ $incident->investigations->count() }} record(s)</span>
                </div>

                <div class="space-y-2.5">
                    @forelse($incident->investigations->sortByDesc('investigation_date') as $inv)
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1.5 text-xs">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-[#8B5CF6]/20 border border-[#8B5CF6]/40 flex items-center justify-center text-[10px] font-bold text-[#A78BFA] font-mono">INV</span>
                                    <span class="font-bold text-white">{{ $inv->investigator->name ?? 'Investigator' }}</span>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[#1B222C] text-[#8B5CF6] border border-[#2A3440] font-bold">{{ $inv->result ?? 'UPDATE' }}</span>
                                </div>
                                <span class="font-mono text-[10px] text-[#64748B]">
                                    {{ $inv->investigation_date ? $inv->investigation_date->format('Y-m-d h:i A') : ($inv->created_at?->format('Y-m-d h:i A') ?? '—') }}
                                </span>
                            </div>
                            <p class="text-slate-300 leading-relaxed pl-8">{{ $inv->notes }}</p>
                        </div>
                    @empty
                        <div class="p-5 rounded-xl bg-[#11161D] border border-[#2A3440] text-center text-slate-500 text-xs font-mono">
                            No investigation history yet.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- RIGHT: Map + Action Panel --}}
        <div class="space-y-5">

            {{-- Mini Map --}}
            <div class="p-4 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Location</span>
                    <span class="text-[11px] font-mono text-[#8B5CF6]">Brgy. {{ $incident->barangay->name ?? 'N/A' }}</span>
                </div>
                <div id="investigator-review-mini-map" class="w-full h-48 rounded-xl border border-[#2A3440] overflow-hidden"></div>
                <div class="font-mono text-[11px] text-slate-400 flex justify-between">
                    <span>{{ number_format($incident->latitude, 5) }}° N</span>
                    <span>{{ number_format($incident->longitude, 5) }}° E</span>
                </div>
            </div>

            {{-- Assign Responder — ONLY visible when VERIFIED --}}
            @php
                $latestAssignment = $incident->responderAssignments->sortByDesc('assigned_at')->first();
                $isVerified = $incident->status === 'VERIFIED';
            @endphp

            @if($isVerified)
            <div class="p-4 rounded-2xl bg-[#151B23] border border-emerald-500/30 space-y-3">
                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Assign Responder
                </h3>

                @if($latestAssignment && $latestAssignment->responder)
                    <div class="p-3 rounded-xl bg-[#11161D] border border-emerald-500/20 text-xs space-y-1">
                        <p class="text-[10px] font-mono text-emerald-400 uppercase font-bold">Currently Assigned</p>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white">{{ $latestAssignment->responder->name }}</span>
                            <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold
                                {{ $latestAssignment->status === 'COMPLETED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' :
                                   ($latestAssignment->status === 'IN_PROGRESS' ? 'bg-[#8B5CF6]/20 text-[#A78BFA] border border-[#8B5CF6]/30' :
                                   'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30') }}">
                                {{ $latestAssignment->status }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-mono">
                            Class {{ $latestAssignment->responder->responder_class }} &bull; {{ $latestAssignment->responder->responder_status }}
                        </p>
                    </div>
                @endif

                @if(isset($availableResponders) && $availableResponders->isNotEmpty())
                    <form method="POST" action="{{ route('investigator.incidents.assign-responder', $incident->id) }}" class="space-y-2">
                        @csrf
                        <select name="responder_id" class="ecto-select text-xs w-full" required>
                            <option value="">{{ $latestAssignment ? 'Reassign responder...' : 'Select responder...' }}</option>
                            @foreach($availableResponders as $resp)
                                <option value="{{ $resp->id }}" {{ ($latestAssignment && $latestAssignment->responder_id === $resp->id) ? 'selected' : '' }}>
                                    {{ $resp->name }} — Class {{ $resp->responder_class }} ({{ $resp->responder_status }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg transition font-mono">
                            ASSIGN RESPONDER
                        </button>
                    </form>
                @else
                    <div class="p-3 rounded-lg bg-amber-500/10 border border-amber-500/30 text-xs text-amber-300 font-mono">
                        No AVAILABLE responders at this time. Assignment will dispatch when one becomes available.
                    </div>
                @endif
            </div>
            @endif

            {{-- Investigator Action Panel --}}
            <div class="p-4 rounded-2xl bg-[#1B222C] border border-[#8B5CF6]/40 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Investigator Actions</h3>
                    <span class="px-2 py-0.5 rounded bg-[#8B5CF6]/20 border border-[#8B5CF6]/40 text-[#A78BFA] font-mono text-[10px] font-bold">AUTHORIZED</span>
                </div>

                <form method="POST" action="{{ route('investigator.incidents.update', $incident->id) }}" class="space-y-4" id="action-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" id="status-input" value="{{ $incident->status }}">
                    <input type="hidden" name="severity" value="{{ $incident->severity }}">

                    {{-- Workflow Action Buttons --}}
                    <div class="space-y-1.5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase">Workflow Stage</p>

                        @php
                            $canResolve = $incident->responderAssignments->where('status', 'COMPLETED')->count() > 0;
                        @endphp

                        {{-- Under Investigation --}}
                        <button type="button" onclick="setStatus('UNDER INVESTIGATION')"
                            class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono transition flex items-center gap-2
                                {{ $incident->status === 'UNDER INVESTIGATION'
                                    ? 'bg-[#8B5CF6]/20 border border-[#8B5CF6]/50 text-[#C4B5FD]'
                                    : 'bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-slate-200 hover:border-[#3A4450]' }}">
                            <span class="w-2 h-2 rounded-full {{ $incident->status === 'UNDER INVESTIGATION' ? 'bg-[#8B5CF6]' : 'bg-slate-600' }}"></span>
                            Under Investigation
                        </button>

                        {{-- Verify Incident --}}
                        <button type="button" onclick="setStatus('VERIFIED')"
                            class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono transition flex items-center gap-2
                                {{ $incident->status === 'VERIFIED'
                                    ? 'bg-emerald-500/20 border border-emerald-500/50 text-emerald-300'
                                    : 'bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-emerald-300 hover:border-emerald-500/30' }}">
                            <span class="w-2 h-2 rounded-full {{ $incident->status === 'VERIFIED' ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                            Verify Incident
                            <span class="ml-auto text-[10px] text-slate-500 font-normal">→ unlocks responder assignment</span>
                        </button>

                        {{-- Mark Resolved --}}
                        @if($canResolve)
                            <button type="button" onclick="setStatus('RESOLVED')"
                                class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono transition flex items-center gap-2
                                    {{ $incident->status === 'RESOLVED'
                                        ? 'bg-emerald-500/20 border border-emerald-500/50 text-emerald-200'
                                        : 'bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-emerald-200 hover:border-emerald-500/30' }}">
                                <span class="w-2 h-2 rounded-full {{ $incident->status === 'RESOLVED' ? 'bg-emerald-300' : 'bg-slate-600' }}"></span>
                                Mark Resolved
                            </button>
                        @else
                            <div class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono flex items-center gap-2 bg-[#11161D] border border-[#2A3440] text-slate-600 cursor-not-allowed select-none">
                                <span class="w-2 h-2 rounded-full bg-slate-700"></span>
                                Mark Resolved
                                <span class="ml-auto text-[10px] text-slate-600 font-normal">awaiting responder</span>
                            </div>
                        @endif

                        {{-- Escalated --}}
                        <button type="button" onclick="setStatus('ESCALATED')"
                            class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono transition flex items-center gap-2
                                {{ $incident->status === 'ESCALATED'
                                    ? 'bg-rose-500/20 border border-rose-500/50 text-rose-300'
                                    : 'bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-rose-300 hover:border-rose-500/30' }}">
                            <span class="w-2 h-2 rounded-full {{ $incident->status === 'ESCALATED' ? 'bg-rose-400' : 'bg-slate-600' }}"></span>
                            Escalated
                        </button>
                    </div>

                    {{-- Investigation Note --}}
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase font-mono">Investigation Note <span class="text-slate-600 font-normal normal-case">— appended to audit trail</span></label>
                        <textarea
                            name="notes"
                            rows="3"
                            class="ecto-input text-xs"
                            placeholder="Add a field note or update summary..."
                        ></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-[#8B5CF6] hover:bg-[#7C3AED] active:scale-[0.99] text-white font-bold rounded-xl transition shadow-md shadow-[#8B5CF6]/20 font-mono text-xs">
                        Commit Update
                    </button>
                </form>

                {{-- Reject (Delete) --}}
                <div class="pt-3 border-t border-[#2A3440]">
                    <button type="button" onclick="openRejectModal()"
                        class="w-full py-2 rounded-xl text-xs font-mono font-bold text-rose-400 border border-rose-500/20 bg-rose-500/5 hover:bg-rose-500/10 hover:border-rose-500/40 transition">
                        Reject &amp; Delete Investigation
                    </button>
                </div>
            </div>

        </div>
    </div>

</main>

{{-- Reject Confirmation Modal --}}
<div id="reject-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 backdrop-blur-sm px-4">
    <div class="w-full max-w-sm bg-[#151B23] border border-rose-500/40 rounded-2xl p-6 space-y-4 shadow-2xl" id="reject-modal-box" style="transform: scale(0.92); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-rose-500/10 border border-rose-500/30 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-white text-sm">Reject Investigation?</h3>
                <p class="text-[11px] text-slate-400 font-mono">{{ $incident->incident_code }}</p>
            </div>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
            This will permanently delete the incident report <span class="font-bold text-white">{{ $incident->incident_code }}</span> and all associated data. This action cannot be undone.
        </p>

        <div class="flex gap-2.5 pt-1">
            <button type="button" onclick="closeRejectModal()"
                class="flex-1 py-2 rounded-xl text-xs font-mono font-bold text-slate-300 bg-[#11161D] border border-[#2A3440] hover:border-[#3A4450] transition">
                Cancel
            </button>
            <form method="POST" action="{{ route('investigator.incidents.reject', $incident->id) }}" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full py-2 rounded-xl text-xs font-mono font-bold text-white bg-rose-600 hover:bg-rose-500 transition">
                    Yes, Reject &amp; Delete
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Mini map
    const lat = {{ $incident->latitude }};
    const lng = {{ $incident->longitude }};

    const map = L.map('investigator-review-mini-map', {
        center: [lat, lng],
        zoom: 14,
        zoomControl: false,
        attributionControl: false,
        dragging: false,
        scrollWheelZoom: false,
    });

    L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 21,
        subdomains: ['0','1','2','3'],
    }).addTo(map);

    L.marker([lat, lng], {
        icon: L.divIcon({
            className: '',
            html: `<div class="gis-marker-wrapper"><div class="marker-incident {{ strtolower($incident->severity) }}"><span style="font-size:11px;font-weight:bold;color:#FFFFFF;">●</span></div></div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        })
    }).addTo(map);

    // Status selection
    window.setStatus = function(status) {
        const input = document.getElementById('status-input');
        if (input) input.value = status;

        document.querySelectorAll('[data-status-btn]').forEach(btn => {
            btn.classList.remove('ring-2', 'ring-[#8B5CF6]');
        });
    };

    // Reject modal
    const modal = document.getElementById('reject-modal');
    const box   = document.getElementById('reject-modal-box');

    window.openRejectModal = function() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            box.style.transform = 'scale(1)';
            box.style.opacity   = '1';
        }, 10);
    };

    window.closeRejectModal = function() {
        box.style.transform = 'scale(0.92)';
        box.style.opacity   = '0';
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    };

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeRejectModal();
    });
});
</script>
@endpush
