@extends('layouts.admin')

@section('title', 'Historical Transcript: ' . $incident->incident_code . ' — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-6 max-w-5xl mx-auto w-full">

    <!-- Header + Breadcrumb -->
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
        <div class="flex items-center gap-2 text-[#8B5CF6]">
            <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('admin.incidents.transcripts') }}" class="hover:underline">Transcripts</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold">{{ $incident->incident_code }} Timeline</span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.incidents.show', $incident) }}" class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-[#2A3440] text-slate-300 hover:text-white transition">
                [ Incident Details ]
            </a>
            <a href="{{ route('admin.incidents.transcripts') }}" class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-[#2A3440] text-slate-300 hover:text-white transition">
                &larr; [ Back to Transcripts ]
            </a>
        </div>
    </div>

    <!-- Incident Dossier Briefing Card -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3 font-mono text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#2A3440] pb-3">
            <div>
                <span class="text-[10px] text-[#8B5CF6] uppercase font-bold tracking-wider">[ OFFICIAL DEFENSE TRANSCRIPT ]</span>
                <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-0.5">
                    {{ $incident->incident_code }}: {{ $incident->title }}
                </h1>
                <p class="text-slate-400 text-xs mt-0.5">
                    Anomaly Type: <span class="text-slate-200 font-bold">{{ $incident->incident_type }}</span> &bull;
                    Confirmed Severity: <span class="text-white font-bold">{{ $incident->severity }}</span> &bull;
                    Location: <span class="text-slate-200 font-bold">Brgy. {{ $incident->barangay?->name ?? 'San Francisco' }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2 self-start">
                <span class="px-2.5 py-1 rounded text-xs font-bold border uppercase
                    @if($incident->status === 'RESOLVED') bg-emerald-500/20 text-emerald-300 border-emerald-500/40
                    @elseif($incident->status === 'VERIFIED') bg-blue-500/20 text-blue-300 border-blue-500/40
                    @elseif($incident->status === 'UNDER INVESTIGATION') bg-sky-500/20 text-sky-300 border-sky-500/40
                    @else bg-amber-500/20 text-amber-300 border-amber-500/40
                    @endif">
                    {{ $incident->status }}
                </span>
                @if($incident->isArchivedFromMap())
                    <span class="px-2 py-1 rounded text-xs font-bold bg-amber-500/15 border border-amber-500/40 text-amber-300">
                        [ ARCHIVED FROM MAP ]
                    </span>
                @else
                    <span class="px-2 py-1 rounded text-xs font-bold bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                        [ ACTIVE ON MAP ]
                    </span>
                @endif
            </div>
        </div>

        <p class="text-slate-300 text-xs leading-relaxed bg-[#11161D] p-3 rounded-xl border border-[#2A3440] whitespace-pre-line">
            {{ $incident->description }}
        </p>
    </div>

    <!-- Complete Chronological Timeline -->
    <div class="space-y-4 font-mono text-xs">
        <div class="flex items-center justify-between border-b border-[#2A3440] pb-2">
            <h2 class="font-bold text-white uppercase tracking-wider text-xs">
                CHRONOLOGICAL DEFENSE TIMELINE ({{ $events->count() }} RECORDED EVENTS)
            </h2>
            <span class="text-[10px] text-slate-500">Sorted from Inception to Resolution</span>
        </div>

        @if($events->isNotEmpty())
            <div class="relative pl-6 space-y-4 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#2A3440]">
                @foreach($events as $idx => $ev)
                    <div class="relative p-4 rounded-xl bg-[#151B23] border border-[#2A3440] hover:border-[#3A4654] transition space-y-2">
                        <!-- Step Marker Bullet -->
                        <span class="absolute -left-[23px] top-4.5 w-3 h-3 rounded-full bg-[#11161D] border-2 border-[#8B5CF6]"></span>

                        <!-- Event Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase
                                    @if($ev['stage'] === 'REPORTED') bg-cyan-500/15 text-cyan-300 border-cyan-500/30
                                    @elseif($ev['stage'] === 'EVIDENCE') bg-blue-500/15 text-blue-300 border-blue-500/30
                                    @elseif($ev['stage'] === 'INVESTIGATION') bg-purple-500/15 text-purple-300 border-purple-500/30
                                    @elseif($ev['stage'] === 'DISPATCH' || $ev['stage'] === 'ACCEPTED') bg-indigo-500/15 text-indigo-300 border-indigo-500/30
                                    @elseif($ev['stage'] === 'RESPONSE_STARTED') bg-amber-500/15 text-amber-300 border-amber-500/30
                                    @elseif($ev['stage'] === 'RESPONSE_COMPLETED' || $ev['stage'] === 'RESOLVED') bg-emerald-500/15 text-emerald-300 border-emerald-500/30
                                    @elseif($ev['stage'] === 'INCIDENT_ARCHIVED') bg-amber-500/15 text-amber-300 border-amber-500/30
                                    @else bg-slate-700 text-slate-300 border-slate-600
                                    @endif">
                                    [ {{ $ev['stage'] }} ]
                                </span>
                                <h3 class="font-bold text-white text-xs">{{ $ev['title'] }}</h3>
                            </div>

                            <div class="text-[11px] text-slate-400 sm:text-right shrink-0">
                                <span>{{ $ev['timestamp'] ? \Carbon\Carbon::parse($ev['timestamp'])->format('Y-m-d H:i:s') : '—' }}</span>
                                <span class="text-[10px] text-slate-500 block">({{ $ev['timestamp'] ? \Carbon\Carbon::parse($ev['timestamp'])->diffForHumans() : '' }})</span>
                            </div>
                        </div>

                        <!-- Event Description & Details -->
                        <p class="text-slate-300 text-xs leading-relaxed">{{ $ev['description'] }}</p>

                        <div class="pt-2 border-t border-[#2A3440] flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-[11px] text-slate-400">
                            <div>
                                Responsible Actor: <strong class="text-white">{{ $ev['actor'] }}</strong>
                                <span class="text-[10px] text-slate-500">[{{ $ev['role'] }}]</span>
                            </div>
                            @if(!empty($ev['details']))
                                <div class="text-[10px] text-slate-400 font-mono">
                                    {{ $ev['details'] }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-slate-500 bg-[#151B23] border border-[#2A3440] rounded-xl">
                No chronological events recorded for this incident.
            </div>
        @endif
    </div>

</main>
@endsection
