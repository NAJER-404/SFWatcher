@extends('layouts.investigator')

@section('title', 'Field Responders — Investigator Portal')

@section('content')
<!-- INVESTIGATOR SIDEBAR -->
@include('investigator.partials.sidebar')

<!-- RESPONDERS ROSTER WORKSPACE -->
<main class="flex-1 overflow-y-auto p-3.5 sm:p-5 md:p-7 bg-[#0B0F14] space-y-5 sm:space-y-6 max-w-7xl mx-auto w-full">

    <!-- Top Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-[#2A3440]">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('investigator.dashboard') }}" class="text-xs font-mono text-[#8B5CF6] hover:underline inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    <span>Dashboard</span>
                </a>
                <span class="text-slate-600">/</span>
                <span class="text-xs font-mono text-slate-400">Response Division</span>
            </div>
            <h1 class="text-lg font-bold text-white font-mono uppercase tracking-wider flex items-center gap-2.5 mt-1">
                <span class="w-2.5 h-2.5 rounded-full bg-[#38BDF8] animate-pulse"></span>
                <span>Tactical Field Responders</span>
            </h1>
            <p class="text-xs text-[#9CA3AF]">Active operatives, tactical combat classes, and vital signs monitoring</p>
        </div>

        <!-- Summary Badges: How many responders are active / available -->
        <div class="flex items-center gap-2 flex-wrap font-mono text-xs">
            <span class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-[#2A3440] text-slate-300">
                Total Operatives: <strong class="text-white">{{ $stats['total'] }}</strong>
            </span>
            <span class="px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-bold">
                {{ $stats['available'] }} Available
            </span>
            @if($stats['on_duty'] > 0)
            <span class="px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 font-bold">
                {{ $stats['on_duty'] }} On Duty
            </span>
            @endif
        </div>
    </div>

    <!-- Filter Buttons by Status / Class -->
    <div class="flex items-center gap-2 flex-wrap" id="responder-filters">
        <button type="button" onclick="filterResponders('ALL')" data-filter="ALL"
                class="resp-filter-btn text-[11px] font-mono font-semibold px-3 py-1.5 rounded-lg border transition-all bg-[#8B5CF6] text-white border-[#8B5CF6]">
            All Responders ({{ $responders->count() }})
        </button>
        <button type="button" onclick="filterResponders('AVAILABLE')" data-filter="AVAILABLE"
                class="resp-filter-btn text-[11px] font-mono font-semibold px-3 py-1.5 rounded-lg border transition-all bg-[#151B23] text-[#64748B] border-[#2A3440] hover:border-emerald-500/50 hover:text-emerald-400">
            Available ({{ $stats['available'] }})
        </button>
        <button type="button" onclick="filterResponders('ON_DUTY')" data-filter="ON_DUTY"
                class="resp-filter-btn text-[11px] font-mono font-semibold px-3 py-1.5 rounded-lg border transition-all bg-[#151B23] text-[#64748B] border-[#2A3440] hover:border-amber-500/50 hover:text-amber-400">
            On Duty ({{ $stats['on_duty'] }})
        </button>
        <div class="h-4 w-[1px] bg-[#2A3440] mx-1"></div>
        <button type="button" onclick="filterResponders('CLASS_A')" data-filter="CLASS_A"
                class="resp-filter-btn text-[10px] font-mono font-semibold px-2.5 py-1.5 rounded-lg border transition-all bg-[#151B23] text-[#64748B] border-[#2A3440] hover:text-amber-400">
            Class A ({{ $stats['class_a'] }})
        </button>
        <button type="button" onclick="filterResponders('CLASS_B')" data-filter="CLASS_B"
                class="resp-filter-btn text-[10px] font-mono font-semibold px-2.5 py-1.5 rounded-lg border transition-all bg-[#151B23] text-[#64748B] border-[#2A3440] hover:text-blue-400">
            Class B ({{ $stats['class_b'] }})
        </button>
        <button type="button" onclick="filterResponders('CLASS_C')" data-filter="CLASS_C"
                class="resp-filter-btn text-[10px] font-mono font-semibold px-2.5 py-1.5 rounded-lg border transition-all bg-[#151B23] text-[#64748B] border-[#2A3440] hover:text-emerald-400">
            Class C ({{ $stats['class_c'] }})
        </button>
        <button type="button" onclick="filterResponders('CLASS_D')" data-filter="CLASS_D"
                class="resp-filter-btn text-[10px] font-mono font-semibold px-2.5 py-1.5 rounded-lg border transition-all bg-[#151B23] text-[#64748B] border-[#2A3440] hover:text-violet-400">
            Class D ({{ $stats['class_d'] }})
        </button>
    </div>

    <!-- Responders Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4" id="responders-grid">
        @forelse($responders as $resp)
        @php
            $class = $resp->responder_class ?? 'D';
            $cfg = $classConfig[$class] ?? ['responder_hp' => 100, 'effectiveness' => 10];
            $maxHp = $cfg['responder_hp'] ?? 100;
            
            // Active assignment if currently on duty
            $activeAssignment = $resp->responderAssignments->sortByDesc('assigned_at')->first();
            $currentHp = ($activeAssignment && $activeAssignment->responder_hp !== null) ? $activeAssignment->responder_hp : $maxHp;
            $hpPercent = $maxHp > 0 ? max(0, min(100, round(($currentHp / $maxHp) * 100))) : 100;

            // Class Badge Styles
            $classLabel = match($class) {
                'A' => 'Class A &bull; Vanguard',
                'B' => 'Class B &bull; Field Operative',
                'C' => 'Class C &bull; Strike Specialist',
                default => 'Class D &bull; Apprentice',
            };
            $classBadgeClass = match($class) {
                'A' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                'B' => 'bg-blue-500/15 text-blue-300 border-blue-500/30',
                'C' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                default => 'bg-violet-500/15 text-violet-300 border-violet-500/30',
            };

            // Status Styles
            $status = $resp->responder_status ?? 'AVAILABLE';
            $isAvailable = $status === 'AVAILABLE';
            $isOnDuty = $status === 'ON_DUTY' || $activeAssignment !== null;

            // HP Color
            $hpBarColor = $hpPercent <= 25 ? 'bg-rose-500' : ($hpPercent <= 60 ? 'bg-amber-500' : 'bg-emerald-500');
            $hpTextColor = $hpPercent <= 25 ? 'text-rose-400' : ($hpPercent <= 60 ? 'text-amber-400' : 'text-emerald-400');
        @endphp
        <div class="responder-card p-5 rounded-xl bg-[#151B23] border border-[#2A3440] hover:border-[#8B5CF6]/40 transition-all space-y-4 shadow-lg"
             data-status="{{ $isOnDuty ? 'ON_DUTY' : 'AVAILABLE' }}"
             data-class="CLASS_{{ $class }}">

            {{-- Card Header: Name + Class Badge + Status --}}
            <div class="flex items-start justify-between gap-3 pb-3 border-b border-[#1E2631]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#1B222C] border border-[#2A3440] flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-wide">{{ $resp->name }}</h3>
                        <p class="text-[11px] font-mono text-[#64748B]">{{ $resp->email }}</p>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-1.5">
                    {{-- Class Badge --}}
                    <span class="text-[10px] font-mono font-bold px-2.5 py-0.5 rounded border {{ $classBadgeClass }}">
                        {!! $classLabel !!}
                    </span>
                    {{-- Activity Status Badge --}}
                    @if($isOnDuty)
                    <span class="inline-flex items-center gap-1.5 text-[9px] font-mono font-bold px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <span>ON ACTIVE RESPONSE</span>
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 text-[9px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>AVAILABLE</span>
                    </span>
                    @endif
                </div>
            </div>

            {{-- Vital Signs: HEALTH (HP) & Combat Attributes --}}
            <div class="space-y-2 rounded-lg bg-[#0E131A] border border-[#1E2631] p-3">
                <div class="flex items-center justify-between text-xs font-mono">
                    <span class="text-[#64748B] inline-flex items-center gap-1.5 font-bold uppercase text-[10px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        <span>Health (HP)</span>
                    </span>
                    <span class="{{ $hpTextColor }} font-bold text-xs">{{ $currentHp }} / {{ $maxHp }} HP</span>
                </div>

                {{-- Health Bar --}}
                <div class="w-full bg-[#151B23] h-2.5 rounded-full overflow-hidden border border-[#2A3440]">
                    <div class="{{ $hpBarColor }} h-full rounded-full transition-all duration-500" style="width: {{ $hpPercent }}%;"></div>
                </div>

                <div class="flex justify-between items-center text-[10px] font-mono text-[#64748B] pt-1">
                    <span>Vitality: <strong class="{{ $hpTextColor }}">{{ $hpPercent }}%</strong></span>
                    <span>Effectiveness: <strong class="text-white">+{{ $cfg['effectiveness'] ?? 10 }} Combat Power</strong></span>
                </div>
            </div>

            {{-- Current Deployment / Activity Details --}}
            <div class="pt-1 flex items-center justify-between text-xs font-mono">
                @if($activeAssignment && $activeAssignment->incident)
                <div class="flex items-center gap-2 truncate">
                    <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                    <span class="text-[#64748B] text-[11px] truncate">
                        Assigned to:
                        <a href="{{ route('investigator.incidents.review', $activeAssignment->incident_id) }}" class="text-[#8B5CF6] hover:underline font-bold">
                            {{ $activeAssignment->incident->incident_code }} &mdash; {{ $activeAssignment->incident->title }}
                        </a>
                    </span>
                </div>
                <a href="{{ route('investigator.incidents.review', $activeAssignment->incident_id) }}"
                   class="px-2.5 py-1 text-[10px] font-bold rounded bg-[#8B5CF6]/15 text-[#C4B5FD] hover:bg-[#8B5CF6] hover:text-white border border-[#8B5CF6]/30 transition flex-shrink-0">
                    Review Mission &rsaquo;
                </a>
                @else
                <div class="flex items-center gap-2 text-slate-400 text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Ready for rapid dispatch &bull; Standby mode</span>
                </div>
                <a href="{{ route('investigator.queue') }}"
                   class="px-2.5 py-1 text-[10px] font-bold rounded bg-[#1B222C] text-slate-300 hover:text-white hover:bg-[#2A3440] border border-[#2A3440] transition flex-shrink-0">
                    Dispatch Operative &rsaquo;
                </a>
                @endif
            </div>

        </div>
        @empty
        <div class="col-span-2 text-center py-16 bg-[#151B23] border border-[#2A3440] rounded-xl">
            <p class="text-sm font-semibold text-white">No field responders registered</p>
            <p class="text-xs text-[#64748B] mt-1">Responder operatives will appear here once registered.</p>
        </div>
        @endforelse
    </div>

    {{-- Empty filtered state --}}
    <div id="filter-empty" class="hidden text-center py-16">
        <p class="text-sm font-semibold text-white mb-1">No operatives match this filter</p>
        <p class="text-xs text-[#64748B]">Try selecting a different status or class filter above.</p>
    </div>

</main>

<script>
function filterResponders(filter) {
    document.querySelectorAll('.resp-filter-btn').forEach(btn => {
        const isActive = btn.getAttribute('data-filter') === filter;
        btn.classList.toggle('bg-[#8B5CF6]', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('border-[#8B5CF6]', isActive);
        btn.classList.toggle('bg-[#151B23]', !isActive);
        btn.classList.toggle('text-[#64748B]', !isActive);
        btn.classList.toggle('border-[#2A3440]', !isActive);
    });

    const cards = document.querySelectorAll('.responder-card');
    let visible = 0;

    cards.forEach(card => {
        let match = false;
        if (filter === 'ALL') {
            match = true;
        } else if (filter === 'AVAILABLE' || filter === 'ON_DUTY') {
            match = card.getAttribute('data-status') === filter;
        } else if (filter.startsWith('CLASS_')) {
            match = card.getAttribute('data-class') === filter;
        }

        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    const empty = document.getElementById('filter-empty');
    if (empty) empty.classList.toggle('hidden', visible > 0);
}
</script>
@endsection
