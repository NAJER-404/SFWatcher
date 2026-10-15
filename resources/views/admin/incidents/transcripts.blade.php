@extends('layouts.admin')

@section('title', 'Transcript — ' . $incident->incident_code)

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-6 max-w-7xl mx-auto w-full">

    <!-- ═════ Header & Breadcrumbs ═════ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <!-- Simplified Breadcrumb -->
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-400" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#A78BFA] transition">Dashboard</a>
                <span class="text-slate-600">/</span>
                <span class="font-mono text-slate-200">{{ $incident->incident_code }}</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1.5">Incident Transcript</h1>
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

    <!-- ═════ Incident Summary Card ═════ -->
    <div class="rounded-2xl border border-[#2A3440] bg-[#151B23] p-5 sm:p-6 shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-5">
            <div>
                <span class="inline-block rounded-md bg-[#8B5CF6]/10 px-2.5 py-1 text-[10px] font-bold tracking-wider text-[#A78BFA] uppercase mb-2">
                    Official Defense Transcript
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-white leading-tight">
                    {{ $incident->incident_code }}: {{ $incident->title }}
                </h2>
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
                @endif
            </div>
        </div>

        <!-- Clean Metadata Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 rounded-xl border border-[#2A3440] bg-[#11161D] p-4 text-xs font-mono mb-5">
            <div>
                <dt class="text-slate-500 mb-1">Anomaly Type</dt>
                <dd class="font-semibold text-slate-200">{{ ucfirst(strtolower($incident->incident_type)) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 mb-1">Confirmed Severity</dt>
                <dd class="font-semibold text-rose-400 uppercase">{{ $incident->severity }}</dd>
            </div>
            <div>
                <dt class="text-slate-500 mb-1">Location / Sector</dt>
                <dd class="font-semibold text-slate-200">Brgy. {{ $incident->barangay?->name ?? 'San Francisco' }}</dd>
            </div>
        </div>

        <!-- Description Block -->
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Incident Summary</h3>
            <div class="rounded-xl border border-[#2A3440] bg-[#11161D] p-4 text-sm text-slate-300 leading-relaxed">
                {{ $incident->description ?? 'No description provided.' }}
            </div>
        </div>
    </div>

    <!-- ═════ Chronological Timeline ═════ -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">
                Chronological Defense Timeline <span class="text-slate-500 font-mono font-normal">({{ $timeline->count() }} Recorded Events)</span>
            </h3>
            <span class="text-[10px] text-slate-500 font-mono uppercase">Sorted from Inception to Resolution</span>
        </div>

        <!-- Timeline Container (The vertical line) -->
        <div class="relative space-y-4 before:absolute before:left-[11px] before:top-2 before:h-[calc(100%-1rem)] before:w-px before:bg-[#2A3440]">

            @foreach($timeline as $event)
                <div class="relative flex gap-4">

                    <!-- Glowing Timeline Dot -->
                    <div class="relative z-10 mt-1.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-[#2A3440] bg-[#151B23]">
                        <div class="h-2.5 w-2.5 rounded-full bg-[#8B5CF6] shadow-[0_0_8px_rgba(139,92,246,0.6)]"></div>
                    </div>

                    <!-- Event Card -->
                    <div class="flex-1 rounded-xl border border-[#2A3440] bg-[#151B23] p-4 transition hover:border-[#8B5CF6]/40">

                        <!-- Event Header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2.5">
                                <!-- Clean Event Badge (No brackets) -->
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border
                                    @if($event->type === 'REPORTED') bg-sky-500/10 text-sky-400 border-sky-500/30
                                    @elseif($event->type === 'INVESTIGATION') bg-amber-500/10 text-amber-400 border-amber-500/30
                                    @elseif($event->type === 'RESOLVED') bg-emerald-500/10 text-emerald-400 border-emerald-500/30
                                    @else bg-slate-500/10 text-slate-400 border-slate-500/30
                                    @endif">
                                    {{ $event->type }}
                                </span>
                                <h4 class="text-sm font-semibold text-white">{{ $event->title }}</h4>
                            </div>

                            <time class="text-[11px] font-mono text-slate-500 whitespace-nowrap">
                                {{ $event->created_at->format('Y-m-d H:i:s') }}
                                <span class="text-slate-600 ml-1">({{ $event->created_at->diffForHumans() }})</span>
                            </time>
                        </div>

                        <!-- Event Body (Cleaner font) -->
                        <p class="text-sm text-slate-300 leading-relaxed font-sans">
                            {{ $event->description }}
                        </p>

                        <!-- Event Metadata Footer (Cleaner layout) -->
                        @if(isset($event->actor) || isset($event->coordinates) || isset($event->severity))
                            <div class="mt-4 pt-3 border-t border-[#2A3440] flex flex-wrap items-center gap-x-6 gap-y-2 text-[11px] font-mono text-slate-500">
                                @if(isset($event->actor))
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        <span class="text-slate-400">Actor:</span> <span class="text-slate-300">{{ $event->actor }}</span>
                                    </div>
                                @endif
                                @if(isset($event->coordinates))
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <span class="text-slate-400">Coords:</span> <span class="text-slate-300">{{ $event->coordinates }}</span>
                                    </div>
                                @endif
                                @if(isset($event->severity))
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>
                                        <span class="text-slate-400">Severity:</span> <span class="text-rose-400 font-bold">{{ $event->severity }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @if($timeline->isEmpty())
                <div class="rounded-xl border border-[#2A3440] bg-[#151B23] p-8 text-center text-sm text-slate-500">
                    No timeline events recorded for this incident.
                </div>
            @endif
        </div>
    </div>

</main>
@endsection
