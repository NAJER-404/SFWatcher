@extends('layouts.responder')

@section('title', 'Response Screen — ' . $assignment->incident->incident_code . ' — Spectra')

@section('content')
<main class="flex-1 overflow-y-auto p-5 md:p-8 bg-[#0B0F14] max-w-6xl mx-auto w-full space-y-6">

    <!-- Top Navigation Breadcrumb -->
    <div class="flex items-center justify-between pb-3 border-b border-[#2A3440]">
        <div class="flex items-center gap-2 text-xs font-mono">
            <a href="{{ route('responder.dashboard') }}" class="text-[#8B5CF6] hover:underline">
                Responder Dashboard
            </a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold">Response Screen</span>
            <span class="text-slate-600">/</span>
            <span class="text-[#A78BFA] font-bold">{{ $assignment->incident->incident_code }}</span>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded bg-[#151B23] border border-[#2A3440] font-mono text-xs font-bold text-slate-300">
                SEVERITY: {{ $assignment->incident->severity }}
            </span>
            <span class="px-2.5 py-1 rounded font-mono text-xs font-bold
                {{ $assignment->status === 'COMPLETED' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' :
                   ($assignment->status === 'ACTIVE' ? 'bg-[#8B5CF6]/20 text-[#A78BFA] border border-[#8B5CF6]/40' :
                   ($assignment->status === 'SUPPORT_REQUIRED' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/40' :
                   'bg-amber-500/20 text-amber-400 border border-amber-500/40')) }}">
                ASSIGNMENT: {{ $assignment->status }}
            </span>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         4 REQUIRED VISIBLE RESPONSE MECHANICS CARDS
         - ANOMALY CONDITION (HP: 100 / 100)
         - RESPONDER CONDITION (HP: 170 / 170)
         - RESPONSE PROGRESS (0%)
         - TIME REMAINING (30:00)
    ════════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- 1. ANOMALY CONDITION -->
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#64748B]">ANOMALY CONDITION</span>
                <span class="text-[10px] font-mono text-rose-400 font-bold">{{ $assignment->incident->severity }} THREAT</span>
            </div>
            <p class="text-2xl font-extrabold font-mono text-rose-400">
                HP: {{ $displayAnomalyHp }} / {{ $displayAnomalyMax }}
            </p>
            <!-- Progress Bar -->
            <div class="w-full bg-[#11161D] h-2 rounded-full overflow-hidden border border-[#2A3440]">
                @php
                    $anomalyPct = $displayAnomalyMax > 0 ? max(0, min(100, round(($displayAnomalyHp / $displayAnomalyMax) * 100))) : 0;
                @endphp
                <div class="bg-rose-500 h-full transition-all duration-500" style="width: {{ $anomalyPct }}%;"></div>
            </div>
            <span class="text-[10px] font-mono text-slate-500">Spectral disturbance integrity</span>
        </div>

        <!-- 2. RESPONDER CONDITION -->
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#64748B]">RESPONDER CONDITION</span>
                <span class="text-[10px] font-mono text-emerald-400 font-bold">CLASS {{ $assignment->responder->responder_class ?? 'D' }}</span>
            </div>
            <p class="text-2xl font-extrabold font-mono text-emerald-400">
                HP: {{ $displayResponderHp }} / {{ $displayResponderMax }}
            </p>
            <!-- Progress Bar -->
            <div class="w-full bg-[#11161D] h-2 rounded-full overflow-hidden border border-[#2A3440]">
                @php
                    $respPct = $displayResponderMax > 0 ? max(0, min(100, round(($displayResponderHp / $displayResponderMax) * 100))) : 0;
                @endphp
                <div class="bg-emerald-500 h-full transition-all duration-500" style="width: {{ $respPct }}%;"></div>
            </div>
            <span class="text-[10px] font-mono text-slate-500">Tactical shielding stability</span>
        </div>

        <!-- 3. RESPONSE PROGRESS -->
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#64748B]">RESPONSE PROGRESS</span>
                <span class="text-[10px] font-mono text-[#8B5CF6] font-bold">CONTAINMENT</span>
            </div>
            <p class="text-2xl font-extrabold font-mono text-white">
                {{ $assignment->response_progress ?? 0 }}%
            </p>
            <!-- Progress Bar -->
            <div class="w-full bg-[#11161D] h-2 rounded-full overflow-hidden border border-[#2A3440]">
                <div class="bg-[#8B5CF6] h-full transition-all duration-500" style="width: {{ $assignment->response_progress ?? 0 }}%;"></div>
            </div>
            <span class="text-[10px] font-mono text-slate-500">Neutralization completion rate</span>
        </div>

        <!-- 4. TIME REMAINING -->
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#64748B]">TIME REMAINING</span>
                <span class="text-[10px] font-mono text-amber-400 font-bold">
                    {{ $assignment->status === 'ACTIVE' ? 'COUNTDOWN' : 'ESTIMATED' }}
                </span>
            </div>
            <p id="countdown-display" class="text-2xl font-extrabold font-mono text-amber-400">
                {{ $timeDisplay }}
            </p>
            <div class="w-full bg-[#11161D] h-2 rounded-full overflow-hidden border border-[#2A3440]">
                <div id="countdown-bar" class="bg-amber-500 h-full transition-all duration-500" style="width: {{ $assignment->status === 'ACTIVE' ? '100' : '0' }}%;"></div>
            </div>
            <span class="text-[10px] font-mono text-slate-500">
                @if($assignment->status === 'ACTIVE')
                    Active operational window
                @else
                    Begins upon response start
                @endif
            </span>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════
         ACTION CONTROLS BAR: START RESPONSE / COMPLETE RESPONSE / REQUEST SUPPORT
    ════════════════════════════════════════════════════════════════ -->
    <div class="p-6 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#1E2631]">
            <div>
                <h3 class="text-sm font-mono font-bold text-white uppercase tracking-wider">RESPONSE EXECUTION</h3>
                <p class="text-xs text-slate-400 mt-0.5">Control containment procedure and execute operational directives.</p>
            </div>
            <span class="px-2.5 py-1 rounded bg-[#11161D] border border-[#2A3440] font-mono text-xs text-slate-300">
                Current Status: {{ $assignment->status }}
            </span>
        </div>

        @if($assignment->status === 'ASSIGNED')
            <div class="flex items-center justify-between gap-4 p-4 rounded-lg bg-[#11161D] border border-amber-500/30">
                <div>
                    <p class="text-xs font-bold text-white">Assignment Awaiting Acceptance</p>
                    <p class="text-[11px] text-slate-400">Accept this response assignment to take tactical ownership of the site.</p>
                </div>
                <form method="POST" action="{{ route('responder.assignments.accept', $assignment) }}">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold text-xs rounded-lg transition font-mono shadow-md shadow-[#8B5CF6]/30">
                        ACCEPT ASSIGNMENT
                    </button>
                </form>
            </div>
        @elseif($assignment->status === 'ACCEPTED')
            <div class="flex items-center justify-between gap-4 p-4 rounded-lg bg-[#11161D] border border-[#8B5CF6]/30">
                <div>
                    <p class="text-xs font-bold text-white">Ready for Deployment</p>
                    <p class="text-[11px] text-slate-400">Click below to initialize anomaly containment, start tactical monitoring, and begin the operational timer.</p>
                </div>
                <form method="POST" action="{{ route('responder.assignments.start', $assignment) }}">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold text-xs rounded-lg transition font-mono shadow-md shadow-[#8B5CF6]/30">
                        START RESPONSE
                    </button>
                </form>
            </div>
        @elseif($assignment->status === 'ACTIVE')
            <div class="space-y-4">
                <!-- Direct Action Buttons: Complete Response & Request Support -->
                <div class="flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('responder.assignments.complete', $assignment) }}">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg transition font-mono shadow-md shadow-emerald-600/30">
                            COMPLETE RESPONSE
                        </button>
                    </form>

                    <form method="POST" action="{{ route('responder.assignments.request-support', $assignment) }}">
                        @csrf
                        <button type="submit" class="px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-lg transition font-mono shadow-md shadow-rose-600/30">
                            REQUEST SUPPORT
                        </button>
                    </form>
                </div>

                <!-- Containment Monitoring Increment Update -->
                <form method="POST" action="{{ route('responder.assignments.progress', $assignment) }}" class="p-4 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono font-bold text-slate-300 uppercase">Step Containment Adjustment</span>
                        <span class="text-[10px] text-slate-500 font-mono">Effectiveness: {{ $assignment->responder->responder_class ?? 'D' }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Containment Applied (HP Reduction)</label>
                            <input type="number" name="containment" min="1" max="{{ $displayAnomalyHp }}" value="{{ min(25, $displayAnomalyHp) }}" class="ecto-input font-mono" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Condition Cost (Responder HP)</label>
                            <input type="number" name="condition_cost" min="0" max="{{ $displayResponderHp }}" value="0" class="ecto-input font-mono" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Monitoring Update Log</label>
                        <textarea name="notes" rows="2" class="ecto-input" placeholder="Enter stabilization log or tactical remarks..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-[#1B222C] hover:bg-[#222B38] text-white border border-[#2A3440] hover:border-[#8B5CF6] font-bold text-xs rounded-lg transition font-mono">
                            RECORD PROGRESS UPDATE
                        </button>
                    </div>
                </form>
            </div>
        @elseif($assignment->status === 'COMPLETED')
            <div class="p-4 rounded-lg bg-emerald-950/40 border border-emerald-500/40 text-xs space-y-1 font-mono">
                <p class="font-bold text-emerald-300 text-sm">RESPONSE COMPLETED — INCIDENT RESOLVED</p>
                <p class="text-slate-300">Anomaly successfully stabilized and neutralized. Incident code {{ $assignment->incident->incident_code }} archived in defense registry.</p>
                <p class="text-slate-400 text-[11px] mt-1">Completed: {{ $assignment->response_completed_at?->format('Y-m-d h:i A') ?? '—' }}</p>
            </div>
        @elseif($assignment->status === 'SUPPORT_REQUIRED')
            <div class="p-4 rounded-lg bg-rose-950/40 border border-rose-500/40 text-xs space-y-1 font-mono">
                <p class="font-bold text-rose-300 text-sm">RESPONDER CRITICAL — SUPPORT REQUESTED</p>
                <p class="text-slate-300">Active containment halted. Incident has been escalated to defense command for additional responder dispatch.</p>
            </div>
        @endif
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         INCIDENT DETAILS + TACTICAL GIS INTEL GRID
    ════════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Left Column: Incident Specifications -->
        <div class="p-6 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-4">
            <div class="pb-2 border-b border-[#1E2631]">
                <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">INCIDENT SPECIFICATIONS</h3>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="block text-[10px] font-mono text-[#64748B] uppercase">Title &amp; Code</span>
                    <p class="text-base font-bold text-white mt-0.5">{{ $incident->title }}</p>
                    <span class="font-mono text-xs text-[#8B5CF6] font-bold">{{ $incident->incident_code }}</span>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-[#1E2631] font-mono">
                    <div>
                        <span class="block text-[10px] text-[#64748B] uppercase">Type</span>
                        <strong class="text-slate-200">{{ $incident->incident_type }}</strong>
                    </div>
                    <div>
                        <span class="block text-[10px] text-[#64748B] uppercase">Severity</span>
                        <strong class="text-rose-400">{{ $incident->severity }}</strong>
                    </div>
                    <div>
                        <span class="block text-[10px] text-[#64748B] uppercase">Location</span>
                        <strong class="text-slate-200">Brgy. {{ $incident->barangay->name ?? 'San Francisco' }}</strong>
                    </div>
                    <div>
                        <span class="block text-[10px] text-[#64748B] uppercase">Incident Status</span>
                        <strong class="text-emerald-400">{{ $incident->status }}</strong>
                    </div>
                </div>

                <div class="pt-2 border-t border-[#1E2631]">
                    <span class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Reported Disturbance</span>
                    <p class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 leading-relaxed">
                        {{ $incident->description }}
                    </p>
                </div>

                <!-- Evidence Thumbnails -->
                @if($incident->evidence->isNotEmpty())
                <div class="pt-2 border-t border-[#1E2631] space-y-2">
                    <span class="block text-[10px] font-mono text-[#64748B] uppercase">Field Evidence Attachments ({{ $incident->evidence->count() }})</span>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($incident->evidence as $ev)
                            <div class="rounded-lg overflow-hidden border border-[#2A3440] bg-[#11161D]">
                                <img src="{{ $ev->url }}" alt="{{ $ev->file_name }}" class="w-full h-24 object-cover">
                                <div class="p-2 text-[10px] font-mono text-[#9CA3AF] truncate">
                                    {{ $ev->file_name }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Nearby Ward Station, Safe Zone, Resources & Investigation Summary -->
        <div class="space-y-6">

            <!-- Nearby Ward Station & Safe Zone -->
            <div class="p-6 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-4">
                <div class="pb-2 border-b border-[#1E2631]">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">TACTICAL GIS GRID</h3>
                </div>

                <div class="space-y-3 text-xs font-mono">
                    <!-- Nearby Ward Station -->
                    <div class="p-3.5 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-1">
                        <span class="text-[10px] text-[#64748B] uppercase font-bold">NEARBY WARD STATION</span>
                        @if($nearestWard)
                            <p class="font-bold text-white text-sm">{{ $nearestWard->name }}</p>
                            <p class="text-[11px] text-slate-400">
                                Code: {{ $nearestWard->code }} &bull; Shield: {{ $nearestWard->shield_level }}% &bull; Status: {{ strtoupper($nearestWard->status) }}
                            </p>
                        @else
                            <p class="text-slate-400">No primary ward station in immediate vicinity.</p>
                        @endif
                    </div>

                    <!-- Nearby Safe Zone -->
                    <div class="p-3.5 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-1">
                        <span class="text-[10px] text-emerald-400 uppercase font-bold">NEARBY SAFE ZONE</span>
                        @if($nearestSafeZone)
                            <p class="font-bold text-white text-sm">{{ $nearestSafeZone->name }} Safe Zone</p>
                            <p class="text-[11px] text-slate-400">
                                Radius: {{ $nearestSafeZone->radius_meters }}m &bull; Energy: {{ $nearestSafeZone->energy_level }}%
                            </p>
                        @else
                            <p class="text-slate-400">Active ward perimeter currently maintaining localized boundary.</p>
                        @endif
                    </div>

                    <!-- Available Resources -->
                    <div class="p-3.5 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-1.5">
                        <span class="text-[10px] text-[#64748B] uppercase font-bold">AVAILABLE RESOURCES IN NODE</span>
                        @if($availableResources->isNotEmpty())
                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                @foreach($availableResources as $res)
                                    <div class="p-2 rounded bg-[#151B23] border border-[#2A3440]">
                                        <p class="font-bold text-slate-200 truncate">{{ $res->name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $res->resource_type }} &bull; {{ $res->quantity }} units</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-slate-400">Standard containment field kit active.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Investigation Result / Evidence Summary -->
            <div class="p-6 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-3">
                <div class="pb-2 border-b border-[#1E2631]">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">INVESTIGATION SUMMARY</h3>
                </div>

                <div class="text-xs space-y-2">
                    <div class="flex items-center justify-between font-mono">
                        <span class="text-[10px] text-[#64748B] uppercase">Result</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 font-bold">
                            {{ $incident->investigation_result ?? 'CONFIRMED' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between font-mono">
                        <span class="text-[10px] text-[#64748B] uppercase">Investigated By</span>
                        <span class="text-slate-200 font-bold">{{ $assignment->investigator->name ?? 'Lead Investigator' }}</span>
                    </div>
                    @if($incident->investigation_completed_at)
                    <div class="flex items-center justify-between font-mono">
                        <span class="text-[10px] text-[#64748B] uppercase">Completed Date</span>
                        <span class="text-slate-400">{{ $incident->investigation_completed_at->format('Y-m-d h:i A') }}</span>
                    </div>
                    @endif

                    @if($incident->notes)
                    <div class="pt-2 border-t border-[#1E2631]">
                        <span class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Investigator Findings</span>
                        <p class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 font-mono text-[11px] leading-relaxed">
                            {{ $incident->notes }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Dynamic countdown timer when response is ACTIVE
    const isResponseActive = {{ $assignment->status === 'ACTIVE' ? 'true' : 'false' }};
    const deadlineIso = '{{ $assignment->response_deadline ? $assignment->response_deadline->toISOString() : "" }}';

    if (isResponseActive && deadlineIso) {
        const deadline = new Date(deadlineIso).getTime();

        const timerInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = deadline - now;

            if (distance <= 0) {
                clearInterval(timerInterval);
                const display = document.getElementById('countdown-display');
                if (display) display.textContent = '00:00';
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const display = document.getElementById('countdown-display');
            if (display) {
                display.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
        }, 1000);
    }
});
</script>
@endpush
