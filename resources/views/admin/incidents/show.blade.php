@extends('layouts.admin')

@section('title', 'Incident ' . $incident->incident_code . ' Details — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-6 max-w-6xl mx-auto w-full">

    <!-- Breadcrumb + Navigation -->
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
        <div class="flex items-center gap-2 text-[#8B5CF6]">
            <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('admin.incidents.index') }}" class="hover:underline">Incidents</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold">{{ $incident->incident_code }}</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.incidents.transcript', $incident) }}" class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-[#8B5CF6]/50 text-[#C4B5FD] hover:text-white transition">
                Transcript
            </a>
            <a href="{{ route('admin.incidents.index') }}" class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-[#2A3440] text-slate-300 hover:text-white transition">
                &larr; Back to Incidents
            </a>
        </div>
    </div>

    <!-- Header Card -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-mono text-xs font-bold text-slate-400 bg-[#11161D] px-2.5 py-1 rounded border border-[#2A3440]">
                    {{ $incident->incident_code }}
                </span>
                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#8B5CF6]">
                    {{ $incident->incident_type }}
                </span>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold font-mono border uppercase
                    @if($incident->status === 'RESOLVED') bg-emerald-500/20 text-emerald-300 border-emerald-500/40
                    @elseif($incident->status === 'VERIFIED') bg-blue-500/20 text-blue-300 border-blue-500/40
                    @elseif($incident->status === 'UNDER INVESTIGATION') bg-sky-500/20 text-sky-300 border-sky-500/40
                    @else bg-amber-500/20 text-amber-300 border-amber-500/40
                    @endif">
                    STATUS: {{ $incident->status }}
                </span>

                @if($incident->isArchivedFromMap())
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-amber-500/20 text-amber-300 border border-amber-500/50">
                        [ ARCHIVED FROM ACTIVE MAP ]
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        [ ACTIVE ON GIS MAP ]
                    </span>
                @endif
            </div>

            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-2">{{ $incident->title }}</h1>
            <p class="text-xs text-slate-400 font-mono mt-1">
                Reported by <span class="text-slate-200 font-bold">{{ $incident->reporter?->name ?? 'Civilian Field Scout' }}</span>
                on {{ $incident->incident_date ? $incident->incident_date->format('Y-m-d h:i A') : $incident->created_at->format('Y-m-d h:i A') }}
            </p>
        </div>

        <!-- Archiving Action Box -->
        @if($incident->status === 'RESOLVED')
            <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] font-mono text-xs space-y-2 shrink-0 self-start">
                <span class="text-[10px] text-slate-400 uppercase font-bold block">Map Visibility</span>
                @if($incident->isArchivedFromMap())
                    <p class="text-[10px] text-amber-400">Archived: {{ $incident->archived_from_map_at?->format('Y-m-d H:i') }}</p>
                    <form method="POST" action="{{ route('admin.incidents.restore', $incident) }}"
                          onsubmit="return confirm('Restore this incident to the active map?');">
                        @csrf
                        <button type="submit" class="w-full py-1.5 px-3 rounded bg-emerald-950/40 border border-emerald-500/50 hover:bg-emerald-500/20 text-emerald-300 font-bold transition">
                            Restore to Map
                        </button>
                    </form>
                @else
                    <p class="text-[10px] text-emerald-400">Visible on active maps</p>
                    <form method="POST" action="{{ route('admin.incidents.archive', $incident) }}"
                          onsubmit="return confirm('Remove from active maps? Records and transcripts remain preserved.');">
                        @csrf
                        <button type="submit" class="w-full py-1.5 px-3 rounded bg-amber-950/40 border border-amber-500/50 hover:bg-amber-500/20 text-amber-300 font-bold transition">
                            Remove from Map
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <!-- Description & Anomaly Telemetry -->
    <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 font-mono text-xs">
        <h2 class="text-xs uppercase font-bold text-slate-400 tracking-wider">Incident Briefing &amp; Telemetry</h2>
        <p class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-200 leading-relaxed whitespace-pre-line">{{ $incident->description }}</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                <span class="text-[10px] text-slate-500 uppercase block">Severity</span>
                <span class="font-bold text-white block mt-1">{{ $incident->severity }}</span>
            </div>
            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                <span class="text-[10px] text-slate-500 uppercase block">Anomaly HP</span>
                <span class="font-bold text-rose-400 block mt-1">{{ $incident->anomaly_hp ?? $incident->anomaly_max_hp }} / {{ $incident->anomaly_max_hp ?? 80 }} HP</span>
            </div>
            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                <span class="text-[10px] text-slate-500 uppercase block">Location Sector</span>
                <span class="font-bold text-slate-200 block mt-1">Brgy. {{ $incident->barangay?->name ?? 'San Francisco' }}</span>
            </div>
            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                <span class="text-[10px] text-slate-500 uppercase block">Coordinates</span>
                <span class="font-bold text-slate-300 block mt-1">{{ number_format($incident->latitude, 4) }}°, {{ number_format($incident->longitude, 4) }}°</span>
            </div>
        </div>
    </div>

    <!-- Evidence Gallery -->
    <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3 font-mono text-xs">
        <div class="flex items-center justify-between border-b border-[#2A3440] pb-2.5">
            <h2 class="text-xs uppercase font-bold text-white tracking-wider">
                Field Evidence Files ({{ $incident->evidence->count() }})
            </h2>
            <span class="text-[10px] text-slate-500">Read-Only Archive</span>
        </div>

        @if($incident->evidence->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($incident->evidence as $ev)
                    <div class="p-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-2">
                        <a href="{{ $ev->url }}" target="_blank" class="block aspect-video overflow-hidden rounded-lg bg-black/50 relative border border-[#2A3440]">
                            <img src="{{ $ev->url }}" alt="{{ $ev->file_name }}" class="w-full h-full object-cover hover:scale-105 transition">
                        </a>
                        <div class="text-[11px] text-slate-400 flex justify-between">
                            <span class="truncate max-w-[150px] font-bold text-white">{{ $ev->file_name }}</span>
                            <span class="text-[10px] text-slate-500">{{ $ev->created_at?->format('M d') }}</span>
                        </div>
                        <a href="{{ $ev->url }}" target="_blank" class="text-[10px] text-[#A78BFA] hover:underline block text-center">
                            [ View Full Resolution ]
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-slate-500 text-center py-4">No field evidence documents attached to this report.</p>
        @endif
    </div>

    <!-- 2-Column: Investigations and Responder Assignments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 font-mono text-xs">

        <!-- Investigation History -->
        <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3">
            <h2 class="text-xs uppercase font-bold text-white tracking-wider border-b border-[#2A3440] pb-2">
                Investigation Assessment History ({{ $incident->investigations->count() }})
            </h2>

            @forelse($incident->investigations->sortByDesc('investigation_date') as $inv)
                <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="font-bold text-white">{{ $inv->investigator?->name ?? 'Investigator' }}</span>
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#1B222C] text-emerald-300 border border-[#2A3440]">{{ $inv->result }}</span>
                    </div>
                    <p class="text-slate-400 text-[11px]">{{ $inv->notes }}</p>
                    <span class="text-[10px] text-slate-500 block">Logged: {{ $inv->investigation_date ? $inv->investigation_date->format('Y-m-d H:i') : '—' }}</span>
                </div>
            @empty
                <p class="text-slate-500 text-center py-4">No investigator notes logged.</p>
            @endforelse
        </div>

        <!-- Responder Deployment History -->
        <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3">
            <h2 class="text-xs uppercase font-bold text-white tracking-wider border-b border-[#2A3440] pb-2">
                Responder Deployments ({{ $incident->responderAssignments->count() }})
            </h2>

            @forelse($incident->responderAssignments->sortByDesc('assigned_at') as $as)
                <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1.5">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="font-bold text-white">{{ $as->responder?->name ?? 'Responder' }}</span>
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold border
                            {{ $as->status === 'COMPLETED' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-slate-700 text-slate-300 border-slate-600' }}">
                            {{ $as->status }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-[10px] text-slate-400">
                        <div>Assigned: {{ $as->assigned_at ? $as->assigned_at->format('Y-m-d H:i') : '—' }}</div>
                        <div>Completed: {{ $as->response_completed_at ? $as->response_completed_at->format('Y-m-d H:i') : '—' }}</div>
                        <div>Anomaly HP: <strong class="text-rose-400">{{ $as->anomaly_hp }}/{{ $as->anomaly_max_hp }}</strong></div>
                        <div>Responder HP: <strong class="text-emerald-400">{{ $as->responder_hp }}/{{ $as->responder_max_hp }}</strong></div>
                    </div>
                </div>
            @empty
                <p class="text-slate-500 text-center py-4">No responder assignments dispatched.</p>
            @endforelse
        </div>

    </div>

</main>
@endsection
