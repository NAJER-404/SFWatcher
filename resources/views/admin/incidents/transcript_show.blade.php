@extends('layouts.admin')

@section('title', 'Historical Transcript: ' . $incident->incident_code . ' — SFWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-6 max-w-5xl mx-auto w-full">

    <!-- ═════ Header & Breadcrumbs ═════ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <!-- Simplified Breadcrumb -->
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#A78BFA] transition">Dashboard</a>
                <span class="text-slate-600">/</span>
                <span class="font-mono text-slate-200 font-bold">{{ $incident->incident_code }}</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1.5">Historical Transcript</h1>
        </div>

        <!-- Modernized "Incident Details" Button -->
        <a href="{{ route('admin.incidents.show', $incident) }}"
           class="inline-flex items-center justify-center gap-2 rounded-lg border border-[#2A3440] bg-[#11161D] px-4 py-2 text-xs font-semibold text-slate-300 transition hover:border-[#8B5CF6] hover:bg-[#8B5CF6]/10 hover:text-white">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            Incident Details
        </a>
    </div>

    <!-- ═════ Incident Dossier Briefing Card ═════ -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 shadow-lg">

        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-[#2A3440] pb-4">
            <div>
                <span class="inline-block rounded-md bg-[#8B5CF6]/10 px-2.5 py-1 text-[10px] font-bold tracking-wider text-[#A78BFA] uppercase mb-2">
                    Official Defense Transcript
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-white leading-tight">
                    {{ $incident->incident_code }}: {{ $incident->title }}
                </h1>
                <p class="text-slate-400 text-xs mt-1.5 font-mono">
                    Anomaly Type: <span class="text-slate-200 font-semibold">{{ $incident->incident_type }}</span> &bull;
                    Confirmed Severity: <span class="text-rose-400 font-bold uppercase">{{ $incident->severity }}</span> &bull;
                    Location: <span class="text-slate-200 font-semibold">Brgy. {{ $incident->barangay?->name ?? 'San Francisco' }}</span>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <!-- Status Badge (No brackets) -->
                <span class="px-3 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider
                    @if($incident->status === 'RESOLVED') bg-emerald-500/15 text-emerald-300 border-emerald-500/40
                    @elseif($incident->status === 'VERIFIED') bg-blue-500/15 text-blue-300 border-blue-500/40
                    @elseif($incident->status === 'UNDER INVESTIGATION') bg-sky-500/15 text-sky-300 border-sky-500/40
                    @else bg-amber-500/15 text-amber-300 border-amber-500/40
                    @endif">
                    {{ $incident->status }}
                </span>
                <!-- Archive Badge (No brackets) -->
                @if($incident->isArchivedFromMap())
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold border border-amber-400/40 bg-amber-400/10 text-amber-300 uppercase tracking-wider">
                        Archived from Map
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold border border-emerald-400/40 bg-emerald-400/10 text-emerald-400 uppercase tracking-wider">
                        Active on Map
                    </span>
                @endif
            </div>
        </div>

        <!-- Description Block (Cleaner font) -->
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Incident Summary</h3>
            <div class="rounded-xl border border-[#2A3440] bg-[#11161D] p-4 text-sm text-slate-300 leading-relaxed font-sans whitespace-pre-line">
                {{ $incident->description }}
            </div>
        </div>
    </div>

    <!-- ═════ Complete Chronological Timeline ═════ -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-[#2A3440] pb-3">
            <h2 class="font-bold text-white uppercase tracking-wider text-xs">
                Chronological Defense Timeline <span class="text-slate-500 font-mono font-normal">({{ $events->count() }} Recorded Events)</span>
            </h2>
            <span class="text-[10px] text-slate-500 font-mono uppercase">Sorted from Inception to Resolution</span>
        </div>

        @if($events->isNotEmpty())
            <!-- Timeline Container -->
            <div class="relative pl-6 space-y-4 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-px before:bg-[#2A3440]">

                @foreach($events as $idx => $ev)
                    <div class="relative p-4 rounded-xl bg-[#151B23] border border-[#2A3440] hover:border-[#8B5CF6]/40 transition space-y-3">

                        <!-- Glowing Timeline Dot -->
                        <span class="absolute -left-[23px] top-5 flex h-6 w-6 items-center justify-center rounded-full border border-[#2A3440] bg-[#151B23] z-10">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#8B5CF6] shadow-[0_0_8px_rgba(139,92,246,0.6)]"></span>
                        </span>

                        <!-- Event Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <!-- Clean Event Badge (No brackets) -->
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border
                                    @if($ev['stage'] === 'REPORTED') bg-cyan-500/10 text-cyan-400 border-cyan-500/30
                                    @elseif($ev['stage'] === 'EVIDENCE') bg-blue-500/10 text-blue-400 border-blue-500/30
                                    @elseif($ev['stage'] === 'INVESTIGATION') bg-purple-500/10 text-purple-400 border-purple-500/30
                                    @elseif($ev['stage'] === 'DISPATCH' || $ev['stage'] === 'ACCEPTED') bg-indigo-500/10 text-indigo-400 border-indigo-500/30
                                    @elseif($ev['stage'] === 'RESPONSE_STARTED') bg-amber-500/10 text-amber-400 border-amber-500/30
                                    @elseif($ev['stage'] === 'RESPONSE_COMPLETED' || $ev['stage'] === 'RESOLVED') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                    @elseif($ev['stage'] === 'INCIDENT_ARCHIVED') bg-amber-500/10 text-amber-400 border-amber-500/30
                                    @else bg-slate-500/10 text-slate-400 border-slate-500/30
                                    @endif">
                                    {{ $ev['stage'] }}
                                </span>
                                <h3 class="font-semibold text-white text-sm">{{ $ev['title'] }}</h3>
                            </div>

                            <div class="text-[11px] font-mono text-slate-500 sm:text-right shrink-0">
                                <span>{{ $ev['timestamp'] ? \Carbon\Carbon::parse($ev['timestamp'])->format('Y-m-d H:i:s') : '—' }}</span>
                                <span class="text-[10px] text-slate-600 block">({{ $ev['timestamp'] ? \Carbon\Carbon::parse($ev['timestamp'])->diffForHumans() : '' }})</span>
                            </div>
                        </div>

                        <!-- Event Description (Cleaner font) -->
                        <p class="text-slate-300 text-sm leading-relaxed font-sans">{{ $ev['description'] }}</p>

                        <!-- Event Metadata Footer (Cleaner layout) -->
                        <div class="pt-3 border-t border-[#2A3440] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] font-mono text-slate-500">
                            <div class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span class="text-slate-400">Actor:</span> <span class="text-slate-300">{{ $ev['actor'] }}</span>
                                <span class="text-[10px] text-slate-600">[{{ $ev['role'] }}]</span>
                            </div>
                            @if(!empty($ev['details']))
                                <div class="text-[10px] text-slate-400 font-mono bg-[#11161D] px-2 py-1 rounded border border-[#2A3440]">
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
