@extends('layouts.admin')

@section('title', 'Incident Transcripts Registry — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-5 max-w-7xl mx-auto w-full">

    <!-- Header + Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#8B5CF6]">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 font-bold">Incident Transcripts</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">Incident Transcripts &amp; Historical Timelines</h1>
            <p class="text-xs text-slate-400 font-mono mt-0.5">
                Complete chronological audit log of all defense events, field investigations, responder actions, and map visibility states.
            </p>
        </div>

        <div class="flex items-center gap-2 text-xs font-mono">
            <span class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-[#2A3440] text-slate-300">
                Audited Incidents: <strong>{{ $incidents->total() }}</strong>
            </span>
        </div>
    </div>

    <!-- Search & Filter Form -->
    <form method="GET" action="{{ route('admin.incidents.transcripts') }}" class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between text-xs font-mono">
        <div class="flex-1 flex flex-col sm:flex-row gap-2.5">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search by incident code or title..."
                   class="flex-1 px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-500 focus:border-[#8B5CF6] focus:outline-none">

            <select name="status" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 focus:border-[#8B5CF6] focus:outline-none">
                <option value="ALL">Status: All</option>
                <option value="PENDING" @selected(request('status') === 'PENDING')>PENDING</option>
                <option value="UNDER INVESTIGATION" @selected(request('status') === 'UNDER INVESTIGATION')>UNDER INVESTIGATION</option>
                <option value="VERIFIED" @selected(request('status') === 'VERIFIED')>VERIFIED</option>
                <option value="RESOLVED" @selected(request('status') === 'RESOLVED')>RESOLVED</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition">
                [ Filter ]
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.incidents.transcripts') }}" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-white transition">
                    [ Reset ]
                </a>
            @endif
        </div>
    </form>

    <!-- Incidents Transcript Registry Table -->
    <div class="overflow-hidden rounded-xl border border-[#2A3440] bg-[#151B23]">
        <table class="w-full text-left text-xs font-mono">
            <thead class="bg-[#11161D] text-slate-400 border-b border-[#2A3440] uppercase">
                <tr>
                    <th class="p-3.5">Code / Anomaly</th>
                    <th class="p-3.5">Status</th>
                    <th class="p-3.5">Severity</th>
                    <th class="p-3.5">Sector</th>
                    <th class="p-3.5">Event Count</th>
                    <th class="p-3.5">Map Visibility</th>
                    <th class="p-3.5 text-right">Transcript Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3440]">
                @forelse($incidents as $inc)
                    @php
                        $eventCount = 1 + $inc->evidence->count() + $inc->investigations->count() + ($inc->responderAssignments->count() * 2) + ($inc->status === 'RESOLVED' ? 1 : 0) + ($inc->isArchivedFromMap() ? 1 : 0);
                    @endphp
                    <tr class="hover:bg-[#1B222C]/70 transition">
                        <td class="p-3.5">
                            <span class="font-bold text-white block">{{ $inc->incident_code }}</span>
                            <span class="text-slate-400 text-[11px] block truncate max-w-[200px]">{{ $inc->title }}</span>
                        </td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase
                                @if($inc->status === 'RESOLVED') bg-emerald-500/20 text-emerald-300 border-emerald-500/40
                                @elseif($inc->status === 'VERIFIED') bg-blue-500/20 text-blue-300 border-blue-500/40
                                @elseif($inc->status === 'UNDER INVESTIGATION') bg-sky-500/20 text-sky-300 border-sky-500/40
                                @else bg-amber-500/20 text-amber-300 border-amber-500/40
                                @endif">
                                {{ $inc->status }}
                            </span>
                        </td>
                        <td class="p-3.5 font-bold text-white">
                            {{ $inc->severity }}
                        </td>
                        <td class="p-3.5 text-slate-300">
                            Brgy. {{ $inc->barangay?->name ?? 'San Francisco' }}
                        </td>
                        <td class="p-3.5 text-slate-400">
                            <span class="text-white font-bold">{{ $eventCount }}+</span> chronological events
                        </td>
                        <td class="p-3.5">
                            @if($inc->isArchivedFromMap())
                                <span class="text-amber-400 font-bold text-[10px]">[ ARCHIVED FROM MAP ]</span>
                            @else
                                <span class="text-emerald-400 font-bold text-[10px]">[ ACTIVE ON MAP ]</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-right">
                            <a href="{{ route('admin.incidents.transcript', $inc) }}"
                               class="px-3 py-1.5 rounded bg-[#11161D] border border-[#8B5CF6]/50 hover:bg-[#8B5CF6] text-white font-bold transition inline-block">
                                [ INSPECT TRANSCRIPT &rarr; ]
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500 font-mono text-xs">
                            No incident transcripts found.
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
