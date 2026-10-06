@extends('layouts.admin')

@section('title', 'Archived Incidents — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-5 max-w-7xl mx-auto w-full font-mono">

    <!-- Header + Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#8B5CF6]">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="text-slate-600">/</span>
                <a href="{{ route('admin.incidents.index') }}" class="hover:underline">Incidents</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 font-bold">Archived Incidents</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">Archived Incidents</h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Neutralized anomalies hidden from active field maps while preserved in administrative records.
            </p>
        </div>

        <div class="flex items-center gap-2 text-xs">
            <span class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-amber-500/40 text-amber-300">
                Archived: <strong>{{ $archivedCount }}</strong>
            </span>
        </div>
    </div>

    <!-- Search Toolbar -->
    <form method="GET" action="{{ route('admin.incidents.archived') }}" class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] flex gap-3 items-center justify-between text-xs">
        <input type="text"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search by code, title, or type..."
               class="flex-1 px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-500 focus:border-[#8B5CF6] focus:outline-none">

        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.incidents.archived') }}" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-white transition">
                    Clear
                </a>
            @endif
        </div>
    </form>

    <!-- Table of Archived Incidents with Expandable Dropdowns for History -->
    <div class="overflow-hidden rounded-xl border border-[#2A3440] bg-[#151B23]">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#11161D] text-slate-400 border-b border-[#2A3440] uppercase">
                <tr>
                    <th class="p-3.5">Incident ID / Title</th>
                    <th class="p-3.5">Type</th>
                    <th class="p-3.5">Severity</th>
                    <th class="p-3.5">Location</th>
                    <th class="p-3.5">Archived Date</th>
                    <th class="p-3.5">Archived By</th>
                    <th class="p-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3440]">
                @forelse($incidents as $inc)
                    @php
                        $latestInvestigation = $inc->investigations->sortByDesc('investigation_date')->first();
                        $latestAssignment = $inc->responderAssignments->sortByDesc('assigned_at')->first();
                    @endphp
                    <tr class="hover:bg-[#1B222C]/70 transition">
                        <td class="p-3.5" colspan="7">
                            <details class="group">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden">
                                    <div class="grid grid-cols-6 flex-1 items-center gap-3">
                                        <!-- Code & Title -->
                                        <div class="col-span-2">
                                            <span class="font-bold text-white block">{{ $inc->incident_code }}</span>
                                            <span class="text-slate-400 text-[11px] block truncate max-w-[200px]">{{ $inc->title }}</span>
                                        </div>

                                        <!-- Incident Type -->
                                        <div class="text-slate-300 text-[11px]">
                                            {{ $inc->incident_type }}
                                        </div>

                                        <!-- Severity -->
                                        <div>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase inline-block
                                                @if($inc->severity === 'CRITICAL') bg-rose-500/20 text-rose-300 border-rose-500/40
                                                @elseif($inc->severity === 'HIGH') bg-orange-500/20 text-orange-300 border-orange-500/40
                                                @elseif($inc->severity === 'MEDIUM') bg-amber-500/20 text-amber-300 border-amber-500/40
                                                @else bg-emerald-500/20 text-emerald-300 border-emerald-500/40
                                                @endif">
                                                {{ $inc->severity }}
                                            </span>
                                        </div>

                                        <!-- Location -->
                                        <div class="text-slate-300 text-[11px]">
                                            Brgy. {{ $inc->barangay?->name ?? 'San Francisco' }}
                                        </div>

                                        <!-- Archived Date & By -->
                                        <div class="text-[11px]">
                                            <span class="text-amber-400 font-bold block">{{ $inc->archived_from_map_at ? $inc->archived_from_map_at->format('Y-m-d') : '—' }}</span>
                                            <span class="text-slate-500 text-[10px] block">By {{ $inc->archivedBy?->name ?? 'Admin' }}</span>
                                        </div>
                                    </div>

                                    <!-- Quick Actions + Expand Icon -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a href="{{ route('admin.incidents.show', $inc) }}"
                                           title="View Details"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[#11161D] border border-[#2A3440] hover:border-[#8B5CF6] text-white hover:text-[#C4B5FD] font-bold transition text-xs">
                                            <svg class="h-3.5 w-3.5 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>View</span>
                                        </a>

                                        <form method="POST" action="{{ route('admin.incidents.restore', $inc) }}" class="inline"
                                              onsubmit="return confirm('Restore {{ $inc->incident_code }} to active maps?');">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1 rounded bg-emerald-950/40 border border-emerald-500/50 hover:bg-emerald-500/20 text-emerald-300 font-bold transition text-xs">
                                                Restore to Map
                                            </button>
                                        </form>

                                        <span class="p-1 rounded text-slate-400 group-open:rotate-180 transition-transform">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </span>
                                    </div>
                                </summary>

                                <!-- Dropdown History Details (Expandable) -->
                                <div class="mt-4 pt-3 border-t border-[#2A3440] grid grid-cols-1 sm:grid-cols-3 gap-3 text-[11px] bg-[#11161D] p-3.5 rounded-xl">
                                    <!-- Investigation History -->
                                    <div class="space-y-1">
                                        <span class="text-[10px] text-slate-500 uppercase font-bold block">Investigation Record:</span>
                                        @if($latestInvestigation)
                                            <p class="text-white font-semibold">Investigator: {{ $latestInvestigation->investigator?->name ?? 'Lead Investigator' }}</p>
                                            <p class="text-emerald-300">Result: {{ $latestInvestigation->result }}</p>
                                            <p class="text-slate-400 text-[10px] line-clamp-2">{{ $latestInvestigation->notes ?: 'No notes filed.' }}</p>
                                        @else
                                            <p class="text-slate-500">No investigation log found.</p>
                                        @endif
                                    </div>

                                    <!-- Responder Deployment History -->
                                    <div class="space-y-1">
                                        <span class="text-[10px] text-slate-500 uppercase font-bold block">Response Mission:</span>
                                        @if($latestAssignment)
                                            <p class="text-white font-semibold">Responder: {{ $latestAssignment->responder?->name ?? 'Assigned Responder' }}</p>
                                            <p class="text-emerald-300">Outcome: {{ $latestAssignment->status }}</p>
                                            <p class="text-slate-400 text-[10px]">Anomaly HP: {{ $latestAssignment->anomaly_hp }}/{{ $latestAssignment->anomaly_max_hp }}</p>
                                        @else
                                            <p class="text-slate-500">No responder assignment recorded.</p>
                                        @endif
                                    </div>

                                    <!-- Archive Details -->
                                    <div class="space-y-1">
                                        <span class="text-[10px] text-slate-500 uppercase font-bold block">Archive Telemetry:</span>
                                        <p class="text-slate-300">Archived: {{ $inc->archived_from_map_at ? $inc->archived_from_map_at->format('Y-m-d H:i') : '—' }}</p>
                                        <p class="text-slate-400 text-[10px]">{{ $inc->archive_notes ?: 'Hidden from active GIS map display.' }}</p>
                                        <a href="{{ route('admin.incidents.transcript', $inc) }}" class="text-[#A78BFA] hover:underline text-[10px] font-bold block pt-1">
                                            [ View Complete Transcript &rarr; ]
                                        </a>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            No archived incidents found matching criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pt-2 font-mono text-xs">
        {{ $incidents->links() }}
    </div>

</main>
@endsection
