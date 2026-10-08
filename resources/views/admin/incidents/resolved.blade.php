@extends('layouts.admin')

@section('title', 'Resolved Incidents & Map Archiving — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-5 max-w-7xl mx-auto w-full">

    <!-- Header + Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-[#8B5CF6]">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="text-slate-600">/</span>
                <a href="{{ route('admin.incidents.index') }}" class="hover:underline">Incidents</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 font-bold">Resolved Incidents</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">Resolved Incident Map Archiving</h1>
            <p class="text-xs text-slate-400 font-mono mt-0.5">
                Control tactical GIS map visibility for neutralized anomalies without deleting audit records or transcripts.
            </p>
        </div>

        <!-- Metric Badges -->
        <div class="flex flex-wrap items-center gap-2 text-xs font-mono">
            <span class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-emerald-500/40 text-emerald-300">
                Visible on Active Map: <strong>{{ $activeMapCount }}</strong>
            </span>
            <span class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-amber-500/40 text-amber-300">
                Archived from Map: <strong>{{ $archivedMapCount }}</strong>
            </span>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('admin.incidents.resolved') }}" class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between text-xs font-mono">
        <div class="flex-1 flex flex-col sm:flex-row gap-2.5">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search resolved incident code or title..."
                   class="flex-1 px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-500 focus:border-[#8B5CF6] focus:outline-none">

            <select name="map_status" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 focus:border-[#8B5CF6] focus:outline-none">
                <option value="">Map Visibility: All ({{ $incidents->total() }})</option>
                <option value="ACTIVE" @selected(request('map_status') === 'ACTIVE')>Visible on Active Map ({{ $activeMapCount }})</option>
                <option value="ARCHIVED" @selected(request('map_status') === 'ARCHIVED')>Archived from Active Map ({{ $archivedMapCount }})</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition">
                [ Filter ]
            </button>
            @if(request()->hasAny(['search', 'map_status']))
                <a href="{{ route('admin.incidents.resolved') }}" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-white transition">
                    [ Reset ]
                </a>
            @endif
        </div>
    </form>

    <!-- Table of Resolved Incidents -->
    <div class="overflow-hidden rounded-xl border border-[#2A3440] bg-[#151B23]">
        <table class="w-full text-left text-xs font-mono">
            <thead class="bg-[#11161D] text-slate-400 border-b border-[#2A3440] uppercase">
                <tr>
                    <th class="p-3.5">Incident Code / Anomaly</th>
                    <th class="p-3.5">Severity</th>
                    <th class="p-3.5">Location</th>
                    <th class="p-3.5">Resolution Date</th>
                    <th class="p-3.5">Map Display Status</th>
                    <th class="p-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3440]">
                @forelse($incidents as $inc)
                    <tr class="hover:bg-[#1B222C]/70 transition">
                        <!-- Code / Title -->
                        <td class="p-3.5">
                            <span class="font-bold text-white block">{{ $inc->incident_code }}</span>
                            <span class="text-slate-400 text-[11px] block truncate max-w-[200px]">{{ $inc->title }}</span>
                            <span class="text-[9px] text-[#A78BFA] block uppercase">{{ $inc->incident_type }}</span>
                        </td>

                        <!-- Severity -->
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase
                                @if($inc->severity === 'CRITICAL') bg-black text-white border-slate-600
                                @elseif($inc->severity === 'HIGH') bg-red-500/20 text-red-400 border-red-500/40
                                @elseif($inc->severity === 'MEDIUM') bg-yellow-500/20 text-yellow-300 border-yellow-500/40
                                @else bg-green-500/20 text-green-300 border-green-500/40
                                @endif">
                                {{ $inc->severity }}
                            </span>
                        </td>

                        <!-- Location -->
                        <td class="p-3.5 text-slate-300">
                            Brgy. {{ $inc->barangay?->name ?? 'San Francisco' }}
                        </td>

                        <!-- Resolution Date -->
                        <td class="p-3.5 text-slate-400 text-[11px]">
                            {{ $inc->investigation_completed_at ? $inc->investigation_completed_at->format('Y-m-d H:i') : ($inc->updated_at ? $inc->updated_at->format('Y-m-d H:i') : '—') }}
                        </td>

                        <!-- Active Map Status -->
                        <td class="p-3.5">
                            @if($inc->isArchivedFromMap())
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-amber-500/15 border border-amber-500/40 text-amber-300 inline-block">
                                    Archived
                                </span>
                                <span class="block text-[9px] text-slate-500 mt-0.5">
                                    {{ $inc->archived_from_map_at ? $inc->archived_from_map_at->format('Y-m-d') : '' }}
                                    by {{ $inc->archivedBy?->name ?? 'Admin' }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 inline-block">
                                    Active on Map
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="p-3.5 text-right space-x-2">
                            <a href="{{ route('admin.incidents.show', $inc) }}"
                               class="px-2.5 py-1.5 rounded bg-[#11161D] border border-[#2A3440] hover:border-[#8B5CF6] text-white font-bold transition inline-block text-xs font-mono">
                                View
                            </a>

                            @if($inc->isArchivedFromMap())
                                <form method="POST" action="{{ route('admin.incidents.restore', $inc) }}" class="inline"
                                      onsubmit="return confirm('Restore {{ $inc->incident_code }} to the active map?');">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1.5 rounded bg-emerald-950/40 border border-emerald-500/50 hover:bg-emerald-500/20 text-emerald-300 font-bold transition text-xs font-mono">
                                        Restore to Map
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.incidents.archive', $inc) }}" class="inline"
                                      onsubmit="return confirm('Remove {{ $inc->incident_code }} from active maps? Records and transcripts remain preserved.');">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1.5 rounded bg-amber-950/40 border border-amber-500/50 hover:bg-amber-500/20 text-amber-300 font-bold transition text-xs font-mono">
                                        Remove from Map
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500 font-mono text-xs">
                            No resolved incidents found matching filters.
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
