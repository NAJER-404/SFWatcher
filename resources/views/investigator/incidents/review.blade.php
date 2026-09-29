@extends('layouts.investigator')

@section('title', 'Review ' . $incident->incident_code . ' — Investigator Portal')

@section('content')
@include('investigator.partials.sidebar')

<main class="flex-1 overflow-y-auto p-5 md:p-7 bg-[#0B0F14] space-y-6 max-w-5xl mx-auto w-full">

    {{-- Flash Messages removed from review page --}}

    {{-- Breadcrumb + Status Badges --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs font-mono">
            <a href="{{ route('investigator.dashboard') }}" class="text-[#8B5CF6] hover:underline inline-flex items-center gap-1">
                Dashboard
            </a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('investigator.queue') }}" class="text-[#8B5CF6] hover:underline">Queue</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold">{{ $incident->incident_code }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="badge-{{ strtolower($incident->severity) }} px-3 py-1 rounded-full text-xs font-bold font-mono border border-slate-700">
                SEVERITY: {{ strtoupper($incident->severity) }}
            </span>
            <span class="{{ $incident->status_badge_class }} px-3 py-1 rounded-full text-xs font-bold font-mono">
                {{ $incident->status }}
            </span>
        </div>
    </div>

    {{-- 2-Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">

        {{-- LEFT: Incident Details + Evidence + History --}}
        <div class="lg:col-span-2 space-y-5 flex flex-col">

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

                {{-- Metric Row: Severity, Anomaly HP, Required Class --}}
                <div class="grid grid-cols-3 gap-3 p-3 rounded-xl bg-[#11161D] border border-[#2A3440] text-center">
                    <div>
                        <p class="text-[10px] font-mono uppercase text-[#64748B]">Confirmed Severity</p>
                        <p class="text-xs font-mono font-bold text-amber-400 mt-0.5">{{ strtoupper($incident->severity) }}</p>
                    </div>
                    <div class="border-x border-[#1E2631]">
                        <p class="text-[10px] font-mono uppercase text-[#64748B]">Anomaly Max HP</p>
                        <p class="text-xs font-mono font-bold text-rose-400 mt-0.5">{{ $incident->anomaly_max_hp ?? config("spectral_response.anomaly_hp." . strtoupper($incident->severity), 80) }} HP</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-mono uppercase text-[#64748B]">Eligible Classes</p>
                        <p class="text-xs font-mono font-bold text-emerald-400 mt-0.5">
                            Class {{ implode('/', config("spectral_response.severity_eligibility." . strtoupper($incident->severity), ['D','C','B','A'])) }}
                        </p>
                    </div>
                </div>

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
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">
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

            {{-- Investigation History (formerly the "Audit Trail" block — now grouped here with Core Details + Evidence) --}}
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3 flex-1 flex flex-col">
                <div class="flex items-center justify-between pb-3 border-b border-[#1E2631]">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                        Investigation History
                    </h3>
                    <span class="text-[10px] font-mono text-slate-500">
                        {{ min($incident->investigations->count(), 3) }} of {{ min($incident->investigations->count(), 3) }} record(s)
                    </span>
                </div>
                <div class="grid grid-cols-1 gap-2.5 flex-1 content-center">
                    @forelse($incident->investigations->sortByDesc('investigation_date')->take(3) as $inv)
                        <div class="px-3 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-[11px] font-mono space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#1B222C] text-[#A78BFA] border border-[#2A3440]">{{ $inv->result ?? 'UPDATE' }}</span>
                                    <span class="text-white font-bold truncate">{{ $inv->investigator->name ?? 'Investigator' }}</span>
                                </div>
                                <span class="shrink-0 text-[10px] text-[#64748B]">
                                    {{ $inv->investigation_date ? $inv->investigation_date->format('m-d h:i A') : ($inv->created_at?->format('m-d h:i A') ?? '—') }}
                                </span>
                            </div>
                            <p class="text-slate-400 truncate pl-0.5">{{ $inv->notes }}</p>
                        </div>
                    @empty
                        <div class="py-4 text-center text-slate-600 text-[11px] font-mono">No audit records yet.</div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- RIGHT: Map + Response Assignment + Investigator Action Panel --}}
        <div class="space-y-5">

            {{-- Mini Map --}}
            <div class="p-4 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-slate-300">Location</span>
                    <span class="text-[11px] font-mono text-[#8B5CF6]">Brgy. {{ $incident->barangay->name ?? 'N/A' }}</span>
                </div>
                <div id="investigator-review-mini-map" class="w-full h-44 rounded-xl border border-[#2A3440] overflow-hidden"></div>
                <div class="font-mono text-[11px] text-slate-400 flex justify-between">
                    <span>{{ number_format($incident->latitude, 5) }}° N</span>
                    <span>{{ number_format($incident->longitude, 5) }}° E</span>
                </div>
            </div>

            {{-- Investigator Action Panel --}}
            @php
                $latestAssignment = $incident->responderAssignments->sortByDesc('assigned_at')->first();
                $assignedResponder = $latestAssignment?->responder;
                $canResolve = $incident->responderAssignments->where('status', 'COMPLETED')->count() > 0;
            @endphp

            <div class="p-4 rounded-2xl bg-[#1B222C] border border-[#8B5CF6]/40 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Investigator Actions</h3>
                    <span class="px-2 py-0.5 rounded bg-[#8B5CF6]/20 border border-[#8B5CF6]/40 text-[#A78BFA] font-mono text-[10px] font-bold">AUTHORIZED</span>
                </div>

                {{-- Currently Assigned Responder Card (Compact) --}}
                <div id="assigned-responder-card" class="p-3 rounded-xl bg-[#11161D] border border-emerald-500/30 text-xs font-mono {{ $assignedResponder ? '' : 'hidden' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Currently Assigned:</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-bold text-[10px]" id="card-assignment-status">
                            {{ $latestAssignment?->status ?? 'ASSIGNED' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs mt-1.5">
                        <span class="font-bold text-white" id="card-responder-name">{{ $assignedResponder?->name ?? 'None' }}</span>
                        <span class="text-emerald-400 font-bold" id="card-responder-class">Class {{ $assignedResponder?->responder_class ?? '—' }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('investigator.incidents.update', $incident->id) }}" class="space-y-4" id="action-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" id="status-input" value="{{ $incident->status }}">
                    <input type="hidden" name="responder_id" id="form-responder-id" value="{{ $latestAssignment?->responder_id ?? '' }}">

                    {{-- Severity Display (Read-Only) + HP --}}
                    <div class="space-y-1 p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                        <div class="flex items-center justify-between text-[10px] font-mono uppercase text-slate-400 font-bold">
                            <span>Incident Severity Level</span>
                            <span class="badge-{{ strtolower($incident->severity) }} px-2 py-0.5 rounded text-[10px] font-bold border border-slate-700">
                                {{ strtoupper($incident->severity) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between font-mono pt-1">
                            <span class="text-xs text-white font-bold">{{ strtoupper($incident->severity) }} ANOMALY</span>
                            <span class="text-xs font-bold text-rose-400" id="hp-display">
                                {{ $incident->anomaly_max_hp ?? config("spectral_response.anomaly_hp." . strtoupper($incident->severity), 80) }} HP
                            </span>
                        </div>
                        <input type="hidden" name="severity" id="severity-input" value="{{ strtoupper($incident->severity ?? 'MEDIUM') }}">
                    </div>

                    {{-- Workflow Action Buttons --}}
                    <div class="space-y-1.5">
                        <p class="text-[10px] font-mono text-slate-400 uppercase font-bold">Workflow Stage Selection</p>

                        {{-- Under Investigation --}}
                        <button type="button" onclick="setStatus('UNDER INVESTIGATION')" id="btn-status-under-investigation"
                            class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono transition flex items-center justify-between
                                {{ $incident->status === 'UNDER INVESTIGATION'
                                    ? 'bg-[#8B5CF6]/20 border border-[#8B5CF6]/50 text-[#C4B5FD]'
                                    : 'bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-slate-200 hover:border-[#3A4450]' }}">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $incident->status === 'UNDER INVESTIGATION' ? 'bg-[#8B5CF6]' : 'bg-slate-600' }}"></span>
                                <span>Under Investigation</span>
                            </div>
                        </button>

                        {{-- Verify Incident — disabled once already VERIFIED or RESOLVED to prevent re-verifying --}}
                        @php $alreadyVerifiedOrResolved = in_array($incident->status, ['VERIFIED', 'RESOLVED']); @endphp
                        @if($alreadyVerifiedOrResolved)
                            <div id="btn-status-verified"
                                class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono flex items-center justify-between bg-[#11161D] border border-[#2A3440] text-slate-500 cursor-not-allowed select-none opacity-60">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                    <span id="label-verified">Verify Incident</span>
                                </div>
                                <span class="text-[10px] text-slate-600 font-normal">completed</span>
                            </div>
                        @else
                            <button type="button" onclick="setStatus('VERIFIED')" id="btn-status-verified"
                                class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono transition flex items-center justify-between bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-emerald-300 hover:border-emerald-500/30">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                    <span id="label-verified">Verify Incident</span>
                                </div>
                            </button>
                        @endif

                        {{-- Assign Responder (Opens Modal) - Unlocked ONLY if already committed as VERIFIED/RESOLVED --}}
                        @php
                            $isVerifiedIncident = in_array($incident->status, ['VERIFIED', 'RESOLVED']);
                        @endphp
                        @if($isVerifiedIncident)
                            <button type="button" onclick="openAssignModal()" id="btn-assign-responder"
                                class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono transition flex items-center justify-between
                                    {{ ($assignedResponder && $incident->status === 'VERIFIED')
                                        ? 'bg-cyan-500/20 border border-cyan-500/50 text-cyan-200'
                                        : 'bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-cyan-300 hover:border-cyan-500/30' }}">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ ($assignedResponder && $incident->status === 'VERIFIED') ? 'bg-cyan-400' : 'bg-slate-600' }}"></span>
                                    <span id="label-assign-responder">
                                        Assign Responder{{ $assignedResponder ? ' → ' . $assignedResponder->name : '' }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-cyan-400 font-normal">→ modal</span>
                            </button>
                        @else
                            <div title="Commit 'Verify Incident' first to unlock responder assignment"
                                class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono flex items-center justify-between bg-[#11161D] border border-[#2A3440] text-slate-600 cursor-not-allowed select-none opacity-60">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-700"></span>
                                    <span id="label-assign-responder">Assign Responder</span>
                                </div>
                                <span class="text-[10px] text-amber-500/80 font-normal">commit verify incident first</span>
                            </div>
                        @endif

                        {{-- Mark Resolved --}}
                        @if($canResolve)
                            <button type="button" onclick="setStatus('RESOLVED')" id="btn-status-resolved"
                                class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono transition flex items-center justify-between
                                    {{ $incident->status === 'RESOLVED'
                                        ? 'bg-emerald-500/20 border border-emerald-500/50 text-emerald-200'
                                        : 'bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-emerald-200 hover:border-emerald-500/30' }}">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $incident->status === 'RESOLVED' ? 'bg-emerald-300' : 'bg-slate-600' }}"></span>
                                    <span id="label-resolved">
                                        Mark Resolved{{ $assignedResponder ? ' → ' . $assignedResponder->name : '' }}
                                    </span>
                                </div>
                            </button>
                        @else
                            <div class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-semibold font-mono flex items-center justify-between bg-[#11161D] border border-[#2A3440] text-slate-600 cursor-not-allowed select-none">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-slate-700"></span>
                                    <span id="label-resolved">
                                        Mark Resolved{{ $assignedResponder ? ' → ' . $assignedResponder->name : '' }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-slate-600 font-normal">awaiting completed response</span>
                            </div>
                        @endif
                    </div>

                    {{-- Investigation Note --}}
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase font-mono">Investigation Note <span class="text-slate-600 font-normal normal-case">— recorded to audit trail</span></label>
                        <textarea
                            name="notes"
                            rows="2"
                            class="ecto-input text-xs w-full bg-[#11161D] border border-[#2A3440] rounded-lg p-2 text-slate-200"
                            placeholder="Add field note or rationale for update..."
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

    {{-- Audit Trail — Full Width Below Grid --}}
    <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3">
        <div class="flex items-center justify-between pb-3 border-b border-[#1E2631]">
            <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#8B5CF6]"></span>
                Audit Trail
            </h3>
            <span class="text-[10px] font-mono text-slate-500">
                {{ min($incident->investigations->count(), 3) }} of {{ $incident->investigations->count() }} record(s)
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
            @forelse($incident->investigations->sortByDesc('investigation_date')->take(3) as $inv)
                <div class="px-3 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-[11px] font-mono space-y-1">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#1B222C] text-[#A78BFA] border border-[#2A3440]">{{ $inv->result ?? 'UPDATE' }}</span>
                            <span class="text-white font-bold truncate">{{ $inv->investigator->name ?? 'Investigator' }}</span>
                        </div>
                        <span class="shrink-0 text-[10px] text-[#64748B]">
                            {{ $inv->investigation_date ? $inv->investigation_date->format('m-d h:i A') : ($inv->created_at?->format('m-d h:i A') ?? '—') }}
                        </span>
                    </div>
                    <p class="text-slate-400 truncate pl-0.5">{{ $inv->notes }}</p>
                </div>
            @empty
                <div class="sm:col-span-3 py-4 text-center text-slate-600 text-[11px] font-mono">No audit records yet.</div>
            @endforelse
        </div>
    </div>

</main>

{{-- Assign Responder Modal (AJAX) --}}
<div id="assign-modal" style="display: none;" class="fixed inset-0 z-50 items-center justify-center bg-black/80 backdrop-blur-sm px-4 pointer-events-none">
    <div class="w-full max-w-md bg-[#151B23] border border-[#2A3440] rounded-2xl p-6 space-y-4 shadow-2xl pointer-events-auto" id="assign-modal-box" style="transform: scale(0.92); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;">
        <div class="flex items-start justify-between gap-3 border-b border-[#2A3440] pb-3">
            <div>
                <h3 class="font-bold text-white text-sm font-mono flex items-center gap-2" id="modal-title">
                    Assign Responder
                </h3>
                <p class="text-[11px] text-slate-400 font-mono mt-0.5" id="modal-subtitle">
                    Select an eligible responder for {{ $incident->incident_code }}
                </p>
            </div>
            <button type="button" onclick="closeAssignModal()" class="text-slate-500 hover:text-white font-mono text-sm px-1.5 py-0.5 rounded border border-transparent hover:border-slate-700">
                ✕
            </button>
        </div>

        {{-- Incident Anomaly Target Badge --}}
        <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] text-xs font-mono flex items-center justify-between">
            <span class="text-slate-400 text-[11px]">Anomaly Target HP:</span>
            <span class="font-bold text-rose-400" id="modal-anomaly-hp">
                {{ $incident->anomaly_max_hp ?? 80 }} HP
            </span>
        </div>

        {{-- Error Alert --}}
        <div id="modal-error-alert" class="hidden p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-mono">
        </div>

        {{-- Loading Skeleton --}}
        <div id="modal-loading" class="py-8 text-center text-xs font-mono text-slate-400 space-y-2">
            <div class="inline-block w-5 h-5 border-2 border-[#8B5CF6] border-t-transparent rounded-full animate-spin"></div>
            <p>Loading eligible responders...</p>
        </div>

        {{-- Responder Selection List --}}
        <div id="modal-responder-list" class="space-y-2 max-h-60 overflow-y-auto pr-1 hidden">
            <!-- Populated dynamically via AJAX -->
        </div>

        {{-- Empty State --}}
        <div id="modal-empty-state" class="hidden p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-mono text-center space-y-1">
            <p class="font-bold">NO ELIGIBLE RESPONDERS AVAILABLE</p>
            <p class="text-[11px] text-amber-400/80">No active responder with eligible class found.</p>
        </div>

        {{-- Modal Actions --}}
        <div class="flex gap-2.5 pt-2 border-t border-[#2A3440]">
            <button type="button" onclick="closeAssignModal()"
                class="flex-1 py-2.5 rounded-xl text-xs font-mono font-bold text-slate-300 bg-[#11161D] border border-[#2A3440] hover:border-[#3A4450] transition">
                Cancel
            </button>
            <button type="button" id="modal-confirm-btn" onclick="submitResponderAssignment()" disabled
                class="flex-1 py-2.5 rounded-xl text-xs font-mono font-bold text-white bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-md shadow-emerald-900/30">
                Confirm &amp; Assign
            </button>
        </div>
    </div>
</div>

{{-- Reject Confirmation Modal (Fixed pointer-events when hidden) --}}
<div id="reject-modal" style="display: none;" class="fixed inset-0 z-50 items-center justify-center bg-black/75 backdrop-blur-sm px-4 pointer-events-none">
    <div class="w-full max-w-sm bg-[#151B23] border border-rose-500/40 rounded-2xl p-6 space-y-4 shadow-2xl pointer-events-auto" id="reject-modal-box" style="transform: scale(0.92); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-rose-500/10 border border-rose-500/30 flex items-center justify-center flex-shrink-0 text-rose-400 font-bold font-mono">
                !
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

    if (typeof L !== 'undefined' && document.getElementById('investigator-review-mini-map')) {
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
    }

    // Status selection
    window.setStatus = function(status) {
        const input = document.getElementById('status-input');
        if (input) input.value = status;

        // Reset visual styles of buttons
        const btnMap = {
            'UNDER INVESTIGATION': 'btn-status-under-investigation',
            'VERIFIED': 'btn-status-verified',
            'RESOLVED': 'btn-status-resolved'
        };

        ['UNDER INVESTIGATION', 'VERIFIED', 'RESOLVED'].forEach(st => {
            const btn = document.getElementById(btnMap[st]);
            if (btn) {
                if (st === status) {
                    btn.classList.remove('bg-[#11161D]', 'border-[#2A3440]', 'text-slate-400');
                    if (st === 'VERIFIED') {
                        btn.classList.add('bg-emerald-500/20', 'border-emerald-500/50', 'text-emerald-300');
                    } else if (st === 'RESOLVED') {
                        btn.classList.add('bg-emerald-500/20', 'border-emerald-500/50', 'text-emerald-200');
                    } else {
                        btn.classList.add('bg-[#8B5CF6]/20', 'border-[#8B5CF6]/50', 'text-[#C4B5FD]');
                    }
                } else {
                    btn.classList.remove(
                        'bg-emerald-500/20', 'border-emerald-500/50', 'text-emerald-300', 'text-emerald-200',
                        'bg-[#8B5CF6]/20', 'border-[#8B5CF6]/50', 'text-[#C4B5FD]'
                    );
                    btn.classList.add('bg-[#11161D]', 'border-[#2A3440]', 'text-slate-400');
                }
            }
        });
    };

    // Assign Responder Modal System (AJAX)
    const assignModal    = document.getElementById('assign-modal');
    const assignModalBox = document.getElementById('assign-modal-box');
    const modalTitle     = document.getElementById('modal-title');
    const modalSubtitle  = document.getElementById('modal-subtitle');
    const modalLoading   = document.getElementById('modal-loading');
    const modalList      = document.getElementById('modal-responder-list');
    const modalEmpty     = document.getElementById('modal-empty-state');
    const modalError     = document.getElementById('modal-error-alert');
    const modalConfirmBtn= document.getElementById('modal-confirm-btn');
    const modalAnomalyHp = document.getElementById('modal-anomaly-hp');

    let selectedResponderId = null;

    window.openAssignModal = function() {
        const dbStatus = '{{ $incident->status }}';
        if (dbStatus !== 'VERIFIED' && dbStatus !== 'RESOLVED') {
            alert('You must commit "Verify Incident" first before assigning a responder.');
            return;
        }

        selectedResponderId = null;
        modalConfirmBtn.disabled = true;
        modalError.classList.add('hidden');
        modalError.textContent = '';
        modalList.classList.add('hidden');
        modalEmpty.classList.add('hidden');
        modalLoading.classList.remove('hidden');

        modalTitle.innerHTML = '<span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span> Assign Responder';
        modalSubtitle.textContent = 'Select an eligible responder for {{ $incident->incident_code }}';
        modalConfirmBtn.className = 'flex-1 py-2.5 rounded-xl text-xs font-mono font-bold text-white bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-md shadow-cyan-900/30';
        modalConfirmBtn.textContent = 'Confirm & Assign';

        assignModal.style.display = 'flex';
        assignModal.classList.remove('pointer-events-none');
        setTimeout(() => {
            assignModalBox.style.transform = 'scale(1)';
            assignModalBox.style.opacity   = '1';
        }, 10);

        // Fetch eligible responders via AJAX
        fetch(`/investigator/incidents/{{ $incident->id }}/eligible-responders`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            modalLoading.classList.add('hidden');
            modalAnomalyHp.textContent = data.anomalyMaxHp + ' HP (' + data.severity + ')';

            if (!data.responders || data.responders.length === 0) {
                modalEmpty.classList.remove('hidden');
                return;
            }

            modalList.innerHTML = '';
            modalList.classList.remove('hidden');

            const classColors = {
                'A': 'bg-rose-500/20 text-rose-300 border-rose-500/40',
                'B': 'bg-purple-500/20 text-purple-300 border-purple-500/40',
                'C': 'bg-cyan-500/20 text-cyan-300 border-cyan-500/40',
                'D': 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40'
            };

            data.responders.forEach(resp => {
                const card = document.createElement('div');
                const isCurrent = (data.currentResponderId == resp.id);
                card.className = `p-3 rounded-xl bg-[#11161D] border border-[#2A3440] hover:border-cyan-500/50 cursor-pointer transition flex items-center justify-between responder-option-card ${isCurrent ? 'ring-1 ring-cyan-500/50' : ''}`;
                card.dataset.id = resp.id;

                const colorClass = classColors[resp.responder_class] || 'bg-slate-700 text-slate-300 border-slate-600';

                card.innerHTML = `
                    <div class="flex items-center gap-3">
                        <input type="radio" name="modal_resp_radio" value="${resp.id}" class="accent-cyan-500" ${isCurrent ? 'checked' : ''}>
                        <div>
                            <div class="text-xs font-bold text-white flex items-center gap-2">
                                <span>${resp.name}</span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-mono border ${colorClass}">
                                    Class ${resp.responder_class}
                                </span>
                            </div>
                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                Health: <span class="text-emerald-400 font-bold">${resp.hp} HP</span> &bull; Status: <span class="text-slate-300">${resp.responder_status}</span>
                            </div>
                        </div>
                    </div>
                    ${isCurrent ? '<span class="text-[10px] font-mono text-cyan-400 uppercase font-bold">Current</span>' : ''}
                `;

                card.addEventListener('click', () => {
                    document.querySelectorAll('.responder-option-card').forEach(c => {
                        c.classList.remove('border-cyan-500', 'bg-[#151B23]');
                        c.classList.add('border-[#2A3440]', 'bg-[#11161D]');
                    });
                    card.classList.remove('border-[#2A3440]', 'bg-[#11161D]');
                    card.classList.add('border-cyan-500', 'bg-[#151B23]');
                    const radio = card.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                    selectedResponderId = resp.id;
                    modalConfirmBtn.disabled = false;
                });

                if (isCurrent) {
                    selectedResponderId = resp.id;
                    modalConfirmBtn.disabled = false;
                }

                modalList.appendChild(card);
            });
        })
        .catch(err => {
            modalLoading.classList.add('hidden');
            modalError.textContent = 'Failed to load responders. Please try again.';
            modalError.classList.remove('hidden');
        });
    };

    window.closeAssignModal = function() {
        assignModalBox.style.transform = 'scale(0.92)';
        assignModalBox.style.opacity   = '0';
        setTimeout(() => {
            assignModal.style.display = 'none';
            assignModal.classList.add('pointer-events-none');
        }, 200);
    };

    assignModal.addEventListener('click', function(e) {
        if (e.target === assignModal) closeAssignModal();
    });

    // Submit responder assignment via AJAX
    window.submitResponderAssignment = function() {
        if (!selectedResponderId) return;

        modalConfirmBtn.disabled = true;
        const originalText = modalConfirmBtn.textContent;
        modalConfirmBtn.textContent = 'Assigning...';
        modalError.classList.add('hidden');

        fetch(`/investigator/incidents/{{ $incident->id }}/assign-responder-ajax`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                responder_id: selectedResponderId
            })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.error || 'Assignment failed');
            }
            return data;
        })
        .then(data => {
            // Update UI with newly assigned responder — name only
            const responderLabel = data.responderName;

            // Update Assign Responder button label
            const assignLabel = document.getElementById('label-assign-responder');
            if (assignLabel) assignLabel.textContent = `Assign Responder → ${responderLabel}`;

            const assignBtn = document.getElementById('btn-assign-responder');
            if (assignBtn) {
                assignBtn.classList.remove('text-slate-400', 'border-[#2A3440]');
                assignBtn.classList.add('bg-cyan-500/20', 'border-cyan-500/50', 'text-cyan-200');
            }

            // Also update Mark Resolved label if assigned
            const resolvedLabel = document.getElementById('label-resolved');
            if (resolvedLabel) resolvedLabel.textContent = `Mark Resolved → ${responderLabel}`;

            // Update hidden form inputs
            const formRespId = document.getElementById('form-responder-id');
            if (formRespId) formRespId.value = data.responderId;

            // Update compact card
            const card = document.getElementById('assigned-responder-card');
            const cardName = document.getElementById('card-responder-name');
            const cardClass = document.getElementById('card-responder-class');
            if (card && cardName && cardClass) {
                cardName.textContent = data.responderName;
                cardClass.textContent = `Class ${data.responderClass}`;
                card.classList.remove('hidden');
            }

            closeAssignModal();
        })
        .catch(err => {
            modalConfirmBtn.disabled = false;
            modalConfirmBtn.textContent = originalText;
            modalError.textContent = err.message;
            modalError.classList.remove('hidden');
        });
    };

    // Reject modal
    const modal = document.getElementById('reject-modal');
    const box   = document.getElementById('reject-modal-box');

    window.openRejectModal = function() {
        modal.style.display = 'flex';
        modal.classList.remove('pointer-events-none');
        setTimeout(() => {
            box.style.transform = 'scale(1)';
            box.style.opacity   = '1';
        }, 10);
    };

    window.closeRejectModal = function() {
        box.style.transform = 'scale(0.92)';
        box.style.opacity   = '0';
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.add('pointer-events-none');
        }, 200);
    };

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeRejectModal();
    });
});
</script>
@endpush
