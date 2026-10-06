@extends('layouts.responder')

@section('title', 'Responder Dashboard — Spectra')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-5 md:p-8 bg-[#0B0F14] max-w-6xl mx-auto w-full space-y-5 sm:space-y-7">

    <!-- Top Header & Stats -->
    <div class="space-y-4">
        <div>
            <p class="text-xs font-mono font-bold text-[#A78BFA] uppercase tracking-wider">SPECTRAWATCH DEFENSE GRID</p>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-0.5">Tactical Responder Dashboard</h1>
            <p class="text-xs text-slate-400">Manage incident response assignments and active anomaly containment.</p>
        </div>

        <!-- 4-Stat Metric Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440]">
                <p class="text-[11px] font-mono text-[#64748B] uppercase font-bold">Current Class</p>
                <p class="text-2xl font-extrabold text-[#A78BFA] font-mono mt-1">Class {{ $stats['current_class'] }}</p>
                <p class="text-[10px] text-slate-400 font-mono mt-0.5">Status: {{ $stats['responder_status'] }}</p>
            </div>

            <div class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440]">
                <p class="text-[11px] font-mono text-[#64748B] uppercase font-bold">Experience (XP)</p>
                <p class="text-2xl font-extrabold text-white font-mono mt-1">{{ $stats['xp'] }}</p>
                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $stats['successful_responses'] }} successful</p>
            </div>

            <div class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440]">
                <p class="text-[11px] font-mono text-[#64748B] uppercase font-bold">Responder HP</p>
                <p class="text-2xl font-extrabold text-emerald-400 font-mono mt-1">{{ $stats['responder_hp'] }} / {{ $stats['responder_hp'] }}</p>
                <p class="text-[10px] text-slate-400 font-mono mt-0.5">Max Tactical Capacity</p>
            </div>

            <div class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440]">
                <p class="text-[11px] font-mono text-[#64748B] uppercase font-bold">Total Assignments</p>
                <p class="text-2xl font-extrabold text-white font-mono mt-1">{{ $stats['total_assigned'] }}</p>
                <p class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $stats['active_responses_count'] }} in progress</p>
            </div>
        </div>
    </div>

    <!-- 1. NEW RESPONSE ASSIGNMENTS -->
    <div class="space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-[#2A3440]">
            <h2 class="text-sm font-bold font-mono text-white uppercase tracking-wider">
                NEW RESPONSE ASSIGNMENTS
            </h2>
            <span class="px-2.5 py-0.5 rounded bg-[#8B5CF6]/20 border border-[#8B5CF6]/40 text-[#A78BFA] font-mono text-xs font-bold">
                {{ $newAssignments->count() }} PENDING
            </span>
        </div>

        @forelse($newAssignments as $assignment)
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#8B5CF6]/40 hover:border-[#8B5CF6] transition space-y-4 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#1E2631] pb-3">
                <div>
                    <span class="text-xs font-mono font-bold text-[#8B5CF6]">Incident #{{ $assignment->incident->incident_code }}</span>
                    <h3 class="text-base font-bold text-white mt-0.5">{{ $assignment->incident->title }}</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded bg-[#1B222C] border border-[#2A3440] text-xs font-mono font-bold text-slate-300">
                        {{ $assignment->incident->severity }}
                    </span>
                    <span class="px-2.5 py-1 rounded bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-mono font-bold">
                        Awaiting Acceptance
                    </span>
                </div>
            </div>

            <!-- Metadata List -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs font-mono">
                <div>
                    <span class="block text-[#64748B] text-[10px] uppercase">Incident ID</span>
                    <strong class="text-[#A78BFA]">{{ $assignment->incident->incident_code }}</strong>
                </div>
                <div>
                    <span class="block text-[#64748B] text-[10px] uppercase">Incident Type</span>
                    <strong class="text-slate-200">{{ $assignment->incident->incident_type }}</strong>
                </div>
                <div>
                    <span class="block text-[#64748B] text-[10px] uppercase">Severity</span>
                    <strong class="text-rose-400">{{ $assignment->incident->severity }}</strong>
                </div>
                <div>
                    <span class="block text-[#64748B] text-[10px] uppercase">Location</span>
                    <strong class="text-slate-200">Brgy. {{ $assignment->incident->barangay->name ?? 'San Francisco' }}</strong>
                </div>
                <div>
                    <span class="block text-[#64748B] text-[10px] uppercase">Assigned Time</span>
                    <strong class="text-slate-200">{{ $assignment->assigned_at ? $assignment->assigned_at->format('Y-m-d h:i A') : '—' }}</strong>
                </div>
                <div>
                    <span class="block text-[#64748B] text-[10px] uppercase">Status</span>
                    <strong class="text-amber-300">{{ $assignment->status }}</strong>
                </div>
            </div>

            @if($assignment->incident->description)
            <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] text-xs text-slate-300 leading-relaxed">
                {{ $assignment->incident->description }}
            </div>
            @endif

            <!-- Action Button: Accept Assignment -->
            <div class="flex items-center justify-end pt-2">
                <form method="POST" action="{{ route('responder.assignments.accept', $assignment) }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold text-xs rounded-lg transition font-mono shadow-md shadow-[#8B5CF6]/30">
                        ACCEPT ASSIGNMENT
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="p-8 text-center rounded-xl bg-[#151B23] border border-[#2A3440] text-slate-400 text-xs font-mono">
            No new response assignments awaiting acceptance. System automatically assigns confirmed investigations.
        </div>
        @endforelse
    </div>

    <!-- 2. ACTIVE RESPONSES -->
    <div class="space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-[#2A3440]">
            <h2 class="text-sm font-bold font-mono text-white uppercase tracking-wider">
                ACTIVE RESPONSES
            </h2>
            <span class="px-2.5 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-mono text-xs font-bold">
                {{ $activeResponses->count() }} ENGAGED
            </span>
        </div>

        @forelse($activeResponses as $assignment)
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-4 shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#1E2631] pb-3">
                <div>
                    <span class="text-xs font-mono font-bold text-[#8B5CF6]">Incident #{{ $assignment->incident->incident_code }}</span>
                    <h3 class="text-base font-bold text-white mt-0.5">{{ $assignment->incident->title }}</h3>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">
                        {{ $assignment->incident->incident_type }} &bull; Brgy. {{ $assignment->incident->barangay->name ?? 'San Francisco' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded font-mono text-xs font-bold
                        {{ $assignment->status === 'ACTIVE' ? 'bg-[#8B5CF6]/20 text-[#A78BFA] border border-[#8B5CF6]/40' :
                           ($assignment->status === 'ACCEPTED' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/40' :
                           'bg-rose-500/20 text-rose-400 border border-rose-500/40') }}">
                        {{ $assignment->status }}
                    </span>
                </div>
            </div>

            <!-- Visible HP and Mechanics for Active Responses -->
            @if($assignment->status === 'ACTIVE')
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-mono">
                <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440]">
                    <span class="block text-[#64748B] text-[10px] uppercase">Anomaly Condition</span>
                    <strong class="text-rose-400 text-sm">{{ $assignment->anomaly_hp }} / {{ $assignment->anomaly_max_hp }} HP</strong>
                </div>
                <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440]">
                    <span class="block text-[#64748B] text-[10px] uppercase">Responder Condition</span>
                    <strong class="text-emerald-400 text-sm">{{ $assignment->responder_hp }} / {{ $assignment->responder_max_hp }} HP</strong>
                </div>
                <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440]">
                    <span class="block text-[#64748B] text-[10px] uppercase">Response Progress</span>
                    <strong class="text-white text-sm">{{ $assignment->response_progress }}%</strong>
                </div>
                <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440]">
                    <span class="block text-[#64748B] text-[10px] uppercase">Time Deadline</span>
                    <strong class="text-amber-400 text-sm">{{ $assignment->response_deadline?->format('h:i A') ?? '—' }}</strong>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-end pt-2">
                <a href="{{ route('responder.assignments.show', $assignment) }}" class="w-full sm:w-auto text-center px-5 py-2.5 bg-[#1B222C] hover:bg-[#222B38] text-white border border-[#2A3440] hover:border-[#8B5CF6] font-bold text-xs rounded-lg transition font-mono">
                    OPEN RESPONSE SCREEN
                </a>
            </div>
        </div>
        @empty
        <div class="p-8 text-center rounded-xl bg-[#151B23] border border-[#2A3440] text-slate-400 text-xs font-mono">
            No active responses in progress. Accept an assignment above to begin containment.
        </div>
        @endforelse
    </div>

    <!-- 3. COMPLETED RESPONSES -->
    <div class="space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-[#2A3440]">
            <h2 class="text-sm font-bold font-mono text-white uppercase tracking-wider">
                COMPLETED RESPONSES
            </h2>
            <span class="px-2.5 py-0.5 rounded bg-[#1B222C] border border-[#2A3440] text-slate-400 font-mono text-xs">
                {{ $completedResponses->count() }} ARCHIVED
            </span>
        </div>

        @forelse($completedResponses as $assignment)
        <div class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-mono font-bold text-emerald-400">{{ $assignment->incident->incident_code }}</span>
                    <span class="font-semibold text-white">{{ $assignment->incident->title }}</span>
                </div>
                <p class="text-slate-400 font-mono text-[11px] mt-0.5">
                    Neutralized &bull; Brgy. {{ $assignment->incident->barangay->name ?? 'San Francisco' }} &bull; Completed {{ $assignment->response_completed_at?->format('M j, Y h:i A') ?? '—' }}
                </p>
            </div>
            <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                <span class="px-2.5 py-1 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 font-mono font-bold text-xs">
                    NEUTRALIZED
                </span>
                <a href="{{ route('responder.assignments.show', $assignment) }}" class="px-3 py-1.5 rounded bg-[#11161D] border border-[#2A3440] hover:border-slate-400 text-slate-300 font-mono text-xs transition">
                    View Record
                </a>
            </div>
        </div>
        @empty
        <div class="p-6 text-center rounded-xl bg-[#151B23] border border-[#2A3440] text-slate-400 text-xs font-mono">
            No completed response records archived yet.
        </div>
        @endforelse
    </div>

</main>
@endsection
