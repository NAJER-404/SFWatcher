@extends('layouts.admin')

@section('title', 'All Incidents Registry — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-5 max-w-7xl mx-auto w-full font-mono">

    <!-- Header + Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#8B5CF6]">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 font-bold">All Incidents</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">All Incidents Registry</h1>
            <p class="text-xs text-slate-400 mt-0.5">Comprehensive audit and status tracking across all anomaly stages.</p>
        </div>

        <div class="flex items-center gap-2 text-xs">
            <span class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-[#2A3440] text-slate-300">
                Total: <strong class="text-white">{{ $statusCounts['total'] }}</strong>
            </span>
        </div>
    </div>

    <!-- Quick Status Filter Pills -->
    <div class="flex flex-wrap items-center gap-1.5 p-1.5 rounded-xl bg-[#151B23] border border-[#2A3440] text-xs">
        <a href="{{ route('admin.incidents.index') }}"
           class="px-3 py-1.5 rounded-lg transition {{ !request('status') ? 'bg-[#8B5CF6] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
            All ({{ $statusCounts['total'] }})
        </a>
        <a href="{{ route('admin.incidents.index', ['status' => 'PENDING']) }}"
           class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'PENDING' ? 'bg-[#8B5CF6] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
            Pending ({{ $statusCounts['pending'] }})
        </a>
        <a href="{{ route('admin.incidents.index', ['status' => 'UNDER INVESTIGATION']) }}"
           class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'UNDER INVESTIGATION' ? 'bg-[#8B5CF6] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
            Investigating ({{ $statusCounts['under_investigation'] }})
        </a>
        <a href="{{ route('admin.incidents.index', ['status' => 'VERIFIED']) }}"
           class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'VERIFIED' ? 'bg-[#8B5CF6] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
            Confirmed ({{ $statusCounts['verified'] }})
        </a>
        <a href="{{ route('admin.incidents.index', ['status' => 'RESOLVED']) }}"
           class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'RESOLVED' ? 'bg-[#8B5CF6] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
            Resolved ({{ $statusCounts['resolved'] }})
        </a>
        <a href="{{ route('admin.incidents.index', ['status' => 'ARCHIVED']) }}"
           class="px-3 py-1.5 rounded-lg transition {{ request('status') === 'ARCHIVED' ? 'bg-[#8B5CF6] text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
            Archived ({{ $statusCounts['archived'] }})
        </a>
    </div>

    <!-- Redesigned Clean Filtering Toolbar -->
    <form method="GET" action="{{ route('admin.incidents.index') }}" class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between text-xs">
        <div class="flex-1 flex flex-col sm:flex-row gap-2.5">
            <!-- Search -->
            <div class="relative flex-1">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search code, title, reporter..."
                       class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-500 focus:border-[#8B5CF6] focus:outline-none">
            </div>

            <!-- Severity Filter -->
            <select name="severity" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 focus:border-[#8B5CF6] focus:outline-none">
                <option value="ALL">Severity: All</option>
                <option value="LOW" @selected(request('severity') === 'LOW')>LOW</option>
                <option value="MEDIUM" @selected(request('severity') === 'MEDIUM')>MEDIUM</option>
                <option value="HIGH" @selected(request('severity') === 'HIGH')>HIGH</option>
                <option value="CRITICAL" @selected(request('severity') === 'CRITICAL')>CRITICAL</option>
            </select>

            <!-- Status Filter -->
            <select name="status" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 focus:border-[#8B5CF6] focus:outline-none">
                <option value="ALL">Status: All</option>
                <option value="PENDING" @selected(request('status') === 'PENDING')>Pending</option>
                <option value="UNDER INVESTIGATION" @selected(request('status') === 'UNDER INVESTIGATION')>Under Investigation</option>
                <option value="VERIFIED" @selected(request('status') === 'VERIFIED')>Verified</option>
                <option value="RESOLVED" @selected(request('status') === 'RESOLVED')>Resolved</option>
                <option value="ARCHIVED" @selected(request('status') === 'ARCHIVED')>Archived</option>
            </select>

            <!-- Date Range -->
            <input type="date"
                   name="date_from"
                   value="{{ request('date_from') }}"
                   title="Start Date"
                   class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 focus:border-[#8B5CF6] focus:outline-none">
            <input type="date"
                   name="date_to"
                   value="{{ request('date_to') }}"
                   title="End Date"
                   class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 focus:border-[#8B5CF6] focus:outline-none">
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'severity', 'status', 'date_from', 'date_to']))
                <a href="{{ route('admin.incidents.index') }}" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-white transition">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Desktop Table View -->
    <div class="hidden lg:block overflow-hidden rounded-xl border border-[#2A3440] bg-[#151B23]">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#11161D] text-slate-400 border-b border-[#2A3440] uppercase">
                <tr>
                    <th class="p-3">Code / Type</th>
                    <th class="p-3">Severity</th>
                    <th class="p-3">Location</th>
                    <th class="p-3">Reporter</th>
                    <th class="p-3">Investigator</th>
                    <th class="p-3">Responder</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Reported / Resolved</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3440]">
                @forelse($incidents as $inc)
                    @php
                        $latestAssignment = $inc->responderAssignments->sortByDesc('assigned_at')->first();
                        $latestInvestigator = $inc->investigations->sortByDesc('investigation_date')->first()?->investigator;
                    @endphp
                    <tr class="hover:bg-[#1B222C]/70 transition">
                        <!-- Incident ID & Type -->
                        <td class="p-3">
                            <span class="font-bold text-white text-xs block">{{ $inc->incident_code }}</span>
                            <span class="text-[10px] text-slate-400 block truncate max-w-[150px]">{{ $inc->title }}</span>
                            <span class="text-[9px] text-[#A78BFA] block uppercase">{{ $inc->incident_type }}</span>
                        </td>

                        <!-- Severity -->
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase
                                @if($inc->severity === 'CRITICAL') bg-black text-white border-slate-600
                                @elseif($inc->severity === 'HIGH') bg-red-500/20 text-red-400 border-red-500/40
                                @elseif($inc->severity === 'MEDIUM') bg-yellow-500/20 text-yellow-300 border-yellow-500/40
                                @else bg-green-500/20 text-green-300 border-green-500/40
                                @endif">
                                {{ $inc->severity }}
                            </span>
                        </td>

                        <!-- Location (No coordinates) -->
                        <td class="p-3">
                            <span class="text-slate-200 block font-bold">Brgy. {{ $inc->barangay?->name ?? 'San Francisco' }}</span>
                        </td>

                        <!-- Reporter -->
                        <td class="p-3 text-slate-300">
                            {{ $inc->reporter?->name ?? 'Field Scout' }}
                        </td>

                        <!-- Assigned Investigator -->
                        <td class="p-3 text-slate-400">
                            {{ $latestInvestigator?->name ?? '—' }}
                        </td>

                        <!-- Assigned Responder -->
                        <td class="p-3">
                            @if($latestAssignment && $latestAssignment->responder)
                                <span class="text-white font-bold block">{{ $latestAssignment->responder->name }}</span>
                                <span class="text-[10px] text-emerald-400 block">Class {{ $latestAssignment->responder->responder_class }} [{{ $latestAssignment->status }}]</span>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>

                        <!-- Status + Map Status -->
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase block text-center max-w-[130px]
                                @if($inc->status === 'RESOLVED') bg-emerald-500/20 text-emerald-300 border-emerald-500/40
                                @elseif($inc->status === 'VERIFIED') bg-blue-500/20 text-blue-300 border-blue-500/40
                                @elseif($inc->status === 'UNDER INVESTIGATION') bg-sky-500/20 text-sky-300 border-sky-500/40
                                @else bg-amber-500/20 text-amber-300 border-amber-500/40
                                @endif">
                                {{ $inc->status }}
                            </span>
                            @if($inc->isArchivedFromMap())
                                <span class="text-[9px] text-amber-400 block mt-1 font-bold text-center">[ MAP ARCHIVED ]</span>
                            @endif
                        </td>

                        <!-- Report / Resolution Dates -->
                        <td class="p-3 text-[11px] text-slate-400">
                            <div>Rep: {{ $inc->created_at ? $inc->created_at->format('Y-m-d') : '—' }}</div>
                            @if($inc->status === 'RESOLVED')
                                <div class="text-emerald-400 text-[10px]">Res: {{ $inc->investigation_completed_at ? $inc->investigation_completed_at->format('Y-m-d') : $inc->updated_at->format('Y-m-d') }}</div>
                            @endif
                        </td>

                        <!-- Actions: Eye icon details, Transcript, Delete -->
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.incidents.show', $inc) }}"
                                   title="View Details"
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[#11161D] border border-[#2A3440] hover:border-[#8B5CF6] text-white hover:text-[#C4B5FD] font-bold transition">
                                    <svg class="h-3.5 w-3.5 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Details</span>
                                </a>

                                <button type="button"
                                        onclick="openDeleteIncidentModal({{ $inc->id }}, '{{ addslashes($inc->incident_code) }}', '{{ addslashes($inc->title) }}', '{{ route('admin.incidents.destroy', $inc) }}')"
                                        title="Delete Incident"
                                        class="px-2 py-1 rounded bg-[#11161D] border border-rose-500/30 hover:border-rose-500 text-rose-400 hover:bg-rose-500/10 font-bold transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-slate-500">
                            No incident records match current criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="block lg:hidden space-y-3">
        @forelse($incidents as $inc)
            @php
                $latestAssignment = $inc->responderAssignments->sortByDesc('assigned_at')->first();
                $latestInvestigator = $inc->investigations->sortByDesc('investigation_date')->first()?->investigator;
            @endphp
            <div class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-3 font-mono text-xs">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="text-[10px] text-[#8B5CF6] uppercase font-bold">{{ $inc->incident_code }}</span>
                        <h3 class="font-bold text-white text-sm">{{ $inc->title }}</h3>
                        <p class="text-[11px] text-slate-400">{{ $inc->incident_type }} &bull; Brgy. {{ $inc->barangay?->name ?? 'San Francisco' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase shrink-0
                        @if($inc->status === 'RESOLVED') bg-emerald-500/20 text-emerald-300 border-emerald-500/40
                        @elseif($inc->status === 'VERIFIED') bg-blue-500/20 text-blue-300 border-blue-500/40
                        @elseif($inc->status === 'UNDER INVESTIGATION') bg-sky-500/20 text-sky-300 border-sky-500/40
                        @else bg-amber-500/20 text-amber-300 border-amber-500/40
                        @endif">
                        {{ $inc->status }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[11px] p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440]">
                    <div>
                        <span class="text-slate-500 block">Severity:</span>
                        <span class="text-white font-bold">{{ $inc->severity }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Reporter:</span>
                        <span class="text-slate-300 truncate block">{{ $inc->reporter?->name ?? 'Field Scout' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Investigator:</span>
                        <span class="text-slate-300 truncate block">{{ $latestInvestigator?->name ?? 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Responder:</span>
                        <span class="text-emerald-400 font-bold truncate block">{{ $latestAssignment?->responder?->name ?? 'None' }}</span>
                    </div>
                </div>

                @if($inc->isArchivedFromMap())
                    <p class="text-amber-400 text-[10px] font-bold">[ ARCHIVED FROM ACTIVE MAP ]</p>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#2A3440] text-[11px]">
                    <span class="text-slate-500">Rep: {{ $inc->created_at ? $inc->created_at->format('Y-m-d') : '—' }}</span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.incidents.show', $inc) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-[#11161D] border border-[#8B5CF6]/50 text-white font-bold">
                            <svg class="h-3.5 w-3.5 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Details</span>
                        </a>
                        <button type="button"
                                onclick="openDeleteIncidentModal({{ $inc->id }}, '{{ addslashes($inc->incident_code) }}', '{{ addslashes($inc->title) }}', '{{ route('admin.incidents.destroy', $inc) }}')"
                                class="px-2.5 py-1 rounded bg-[#11161D] border border-rose-500/40 text-rose-400 font-bold">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-500 font-mono text-xs bg-[#151B23] border border-[#2A3440] rounded-xl">
                No incident records found.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2 font-mono text-xs">
        {{ $incidents->links() }}
    </div>

    <!-- Delete Incident Alert Modal -->
    <div id="delete-incident-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/75 backdrop-blur-xs font-mono">
        <div class="w-full max-w-md rounded-2xl bg-[#151B23] border border-rose-500/40 p-6 space-y-4 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 shrink-0">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Delete Incident Record</h3>
                    <p class="text-xs text-slate-400 mt-1">This will permanently delete this incident, evidence files, and investigations.</p>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1 text-xs">
                <p class="text-slate-300"><span class="text-slate-500">Incident Code:</span> <strong id="delete-incident-code" class="text-white"></strong></p>
                <p class="text-slate-300"><span class="text-slate-500">Title:</span> <span id="delete-incident-title" class="text-slate-400"></span></p>
            </div>

            <form id="delete-incident-form" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button"
                            onclick="closeDeleteIncidentModal()"
                            class="px-4 py-2 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white transition text-xs font-bold">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition text-xs shadow-lg shadow-rose-950/40">
                        Delete Incident
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

<script>
    function openDeleteIncidentModal(id, code, title, actionUrl) {
        document.getElementById('delete-incident-code').textContent = code;
        document.getElementById('delete-incident-title').textContent = title;
        document.getElementById('delete-incident-form').action = actionUrl;
        const modal = document.getElementById('delete-incident-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteIncidentModal() {
        const modal = document.getElementById('delete-incident-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDeleteIncidentModal();
    });
</script>
@endsection
