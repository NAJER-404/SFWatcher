@extends('layouts.admin')

@section('title', 'User Profile: ' . $user->name . ' — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-6 max-w-6xl mx-auto w-full font-mono">

    <!-- Breadcrumb + Navigation -->
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2 text-[#8B5CF6]">
            <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('admin.users.index') }}" class="hover:underline">Users</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold">{{ $user->name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-amber-500/40 text-amber-400 hover:text-white font-bold transition">
                Update User
            </a>
            <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-lg bg-[#151B23] border border-[#2A3440] text-slate-300 hover:text-white transition">
                &larr; Back to Users
            </a>
        </div>
    </div>

    <!-- User Header Card -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border
                    @if($user->role === 'admin') bg-amber-500/15 text-amber-300 border-amber-500/40
                    @elseif($user->role === 'investigator') bg-purple-500/15 text-purple-300 border-purple-500/40
                    @elseif($user->role === 'responder') bg-emerald-500/15 text-emerald-300 border-emerald-500/40
                    @else bg-cyan-500/15 text-cyan-300 border-cyan-500/40
                    @endif">
                    {{ strtoupper($user->role) }}
                </span>
                <span class="text-xs text-slate-500">ID #{{ $user->id }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-white mt-1">{{ $user->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 text-xs">
            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] text-center min-w-[120px]">
                <span class="text-[10px] text-slate-500 uppercase block">Registered</span>
                <span class="font-bold text-slate-200 block mt-0.5">{{ $user->created_at ? $user->created_at->format('Y-m-d') : '—' }}</span>
            </div>
            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] text-center min-w-[120px]">
                <span class="text-[10px] text-slate-500 uppercase block">Account State</span>
                <span class="font-bold text-emerald-400 block mt-0.5">ACTIVE</span>
            </div>
        </div>
    </div>

    <!-- 2-Column Grid: Role Settings + Role-Specific Perspective -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Role Configuration & Account Info -->
        <div class="space-y-6">


            <!-- Account Summary Card -->
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3 text-xs">
                <h3 class="font-bold text-white uppercase tracking-wider text-xs border-b border-[#2A3440] pb-2">
                    Account Metadata
                </h3>
                <div class="space-y-2 text-[11px]">
                    <div class="flex justify-between py-1 border-b border-[#2A3440]/50">
                        <span class="text-slate-400">User ID</span>
                        <span class="text-white font-bold">#{{ $user->id }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[#2A3440]/50">
                        <span class="text-slate-400">Registration Date</span>
                        <span class="text-slate-300">{{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[#2A3440]/50">
                        <span class="text-slate-400">Auth Method</span>
                        <span class="text-slate-300">{{ $user->google_id ? 'Google OAuth' : 'Standard Password' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400">Email Verified</span>
                        <span class="text-emerald-400 font-bold">Verified</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Tailored Perspective According to User Role -->
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. INVESTIGATOR PERSPECTIVE --}}
            @if($user->isInvestigator())
                @php
                    $investigations = $user->investigations->sortByDesc('investigation_date');
                    $verifiedCount = $investigations->where('result', 'VERIFIED')->count();
                    $underInvCount = $investigations->where('result', 'UNDER INVESTIGATION')->count();
                    $resolvedCount = $investigations->where('result', 'RESOLVED')->count();
                    $totalInvs = $investigations->count();
                @endphp

                <!-- Investigator Performance Metrics -->
                <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-purple-500/40 space-y-5 text-xs">
                    <div class="border-b border-[#2A3440] pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold text-[#A78BFA] uppercase tracking-wider">INVESTIGATOR PERSPECTIVE</span>
                            <h2 class="text-base font-extrabold text-white mt-0.5">Field Investigation Command</h2>
                        </div>
                        <span class="px-2.5 py-1 rounded bg-[#11161D] border border-[#2A3440] text-purple-300 font-bold self-start">
                            LEAD INVESTIGATOR
                        </span>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Total Cases</span>
                            <span class="text-2xl font-bold text-white block mt-1">{{ $totalInvs }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Threats Verified</span>
                            <span class="text-2xl font-bold text-[#A78BFA] block mt-1">{{ $verifiedCount }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Under Review</span>
                            <span class="text-2xl font-bold text-sky-400 block mt-1">{{ $underInvCount }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Cases Resolved</span>
                            <span class="text-2xl font-bold text-emerald-400 block mt-1">{{ $resolvedCount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Field Investigations Logged with Dropdown Filter & Accordion -->
                <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#2A3440] pb-3">
                        <div>
                            <h3 class="font-bold text-white uppercase tracking-wider text-xs">
                                Field Investigations Logged ({{ $totalInvs }})
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Click any case to expand detailed findings and forensic notes.</p>
                        </div>

                        <!-- Dropdown Filter for Investigation Result -->
                        <div class="flex items-center gap-2">
                            <label for="investigation-result-filter" class="text-[11px] text-slate-400">Filter:</label>
                            <select id="investigation-result-filter"
                                    onchange="filterInvestigations(this.value)"
                                    class="px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 text-xs focus:border-[#8B5CF6] focus:outline-none">
                                <option value="ALL">All Outcomes ({{ $totalInvs }})</option>
                                <option value="VERIFIED">VERIFIED ({{ $verifiedCount }})</option>
                                <option value="UNDER INVESTIGATION">UNDER INVESTIGATION ({{ $underInvCount }})</option>
                                <option value="RESOLVED">RESOLVED ({{ $resolvedCount }})</option>
                            </select>
                        </div>
                    </div>

                    @if($investigations->isNotEmpty())
                        <div class="space-y-2.5" id="investigations-list">
                            @php $shownInvestigations = $investigations->take(5); @endphp
                            @if($investigations->count() > 5)
                                <p class="text-[10px] text-slate-500 text-right">Showing 5 of {{ $investigations->count() }} — use filter to narrow results</p>
                            @endif
                            @foreach($shownInvestigations as $inv)
                                <details class="investigation-item group p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] hover:border-[#3A4654] transition"
                                         data-result="{{ $inv->result }}">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 [&::-webkit-details-marker]:hidden">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border shrink-0
                                                @if($inv->result === 'VERIFIED') bg-purple-500/15 text-purple-300 border-purple-500/40
                                                @elseif($inv->result === 'UNDER INVESTIGATION') bg-sky-500/15 text-sky-300 border-sky-500/40
                                                @elseif($inv->result === 'RESOLVED') bg-emerald-500/15 text-emerald-300 border-emerald-500/40
                                                @else bg-slate-700 text-slate-300 border-slate-600
                                                @endif">
                                                {{ $inv->result }}
                                            </span>
                                            <span class="font-bold text-white text-xs truncate">
                                                {{ $inv->incident?->incident_code ?? 'SF-INC' }}: {{ $inv->incident?->title }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-[10px] text-slate-500">
                                                {{ $inv->investigation_date ? $inv->investigation_date->format('Y-m-d') : '—' }}
                                            </span>
                                            <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform">&darr;</span>
                                        </div>
                                    </summary>

                                    <!-- Dropdown / Collapsible Expanded Content -->
                                    <div class="pt-3 mt-3 border-t border-[#2A3440]/60 space-y-2 text-[11px]">
                                        <div>
                                            <span class="text-slate-500 block uppercase text-[10px]">Forensic Assessment Notes:</span>
                                            <p class="text-slate-300 mt-1 leading-relaxed bg-[#151B23] p-2.5 rounded-lg border border-[#2A3440]">
                                                {{ $inv->notes ?: 'No additional qualitative notes filed for this inspection.' }}
                                            </p>
                                        </div>

                                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-[10px] text-slate-400">
                                            <span>Logged: {{ $inv->investigation_date ? $inv->investigation_date->format('Y-m-d H:i:s') : '—' }}</span>
                                            @if($inv->incident)
                                                <a href="{{ route('admin.incidents.show', $inv->incident) }}" class="text-[#A78BFA] hover:underline font-bold">
                                                    [ Inspect Incident &rarr; ]
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-6">No field investigations on record for this investigator.</p>
                    @endif
                </div>

            {{-- 2. RESPONDER PERSPECTIVE --}}
            @elseif($user->isResponder())
                <!-- SECTION: RESPONDER MANAGEMENT AND CLASS PROMOTION -->
                <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-emerald-500/40 space-y-5 text-xs">
                    <div class="border-b border-[#2A3440] pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Tactical Profile</span>
                            <h2 class="text-base font-extrabold text-white mt-0.5">Responder Profile &amp; Tactical Command</h2>
                        </div>
                        <span class="px-2.5 py-1 rounded bg-[#11161D] border border-[#2A3440] text-emerald-400 font-bold self-start">
                            STATUS: {{ $user->responder_status ?? 'AVAILABLE' }}
                        </span>
                    </div>

                    <!-- Tactical Metrics Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Current Class</span>
                            <span class="text-2xl font-bold text-white block mt-1">Class {{ $user->responder_class ?? 'D' }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Maximum HP</span>
                            <span class="text-2xl font-bold text-rose-400 block mt-1">{{ $user->max_hp }} HP</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Current XP</span>
                            <span class="text-2xl font-bold text-amber-400 block mt-1">{{ $user->xp }} XP</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Completed Missions</span>
                            <span class="text-2xl font-bold text-emerald-400 block mt-1">{{ $user->successful_responses }}</span>
                        </div>
                    </div>

                    <!-- Promotion Eligibility Card: Class Progression Trajectory -->
                    @php
                        $nextClass = $user->next_class;
                        $reqXp = $user->required_promotion_xp;
                        $isQualified = $user->isPromotionEligible();
                    @endphp

                    <div class="p-4 sm:p-5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#2A3440] pb-3">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400">Class Progression Trajectory</span>
                                <h3 class="font-bold text-white text-sm mt-0.5">
                                    Next Tier:
                                    @if($nextClass)
                                        <span class="text-[#8B5CF6]">Class {{ $nextClass }}</span>
                                        <span class="text-slate-400 text-xs font-normal">({{ config("spectral_response.classes.{$nextClass}.responder_hp") }} Max HP)</span>
                                    @else
                                        <span class="text-emerald-400">MAXIMUM CLASS TIER (Class A)</span>
                                    @endif
                                </h3>
                            </div>

                            @if($nextClass)
                                <span class="px-2.5 py-1 rounded text-xs font-bold border self-start
                                    {{ $isQualified ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/50' : 'bg-slate-800 text-slate-400 border-slate-700' }}">
                                    {{ $isQualified ? 'QUALIFIED FOR PROMOTION' : 'IN PROGRESS' }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/50 self-start">
                                    MAX TIER
                                </span>
                            @endif
                        </div>

                        <!-- XP Requirement Bar -->
                        @if($nextClass)
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-slate-400">XP Progress: <strong class="text-white">{{ $user->xp }}</strong> / {{ $reqXp }} XP required</span>
                                    <span class="text-amber-400 font-bold">
                                        {{ $reqXp > 0 ? min(100, round(($user->xp / $reqXp) * 100)) : 100 }}%
                                    </span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-[#1B222C] overflow-hidden border border-[#2A3440]">
                                    <div class="h-full bg-gradient-to-r from-[#8B5CF6] to-emerald-400"
                                         style="width: {{ $reqXp > 0 ? min(100, round(($user->xp / $reqXp) * 100)) : 100 }}%;"></div>
                                </div>
                            </div>

                            <!-- Action: Approve Promotion -->
                            <div class="pt-2 flex flex-col sm:flex-row gap-3">
                                <form method="POST" action="{{ route('admin.users.promote', $user) }}" class="flex-1"
                                      onsubmit="return confirm('Promote {{ $user->name }} from Class {{ $user->responder_class }} to Class {{ $nextClass }}?');">
                                    @csrf
                                    <button type="submit"
                                            {{ !$isQualified ? 'disabled' : '' }}
                                            class="w-full py-2.5 rounded-xl font-bold transition text-xs shadow-lg
                                                {{ $isQualified
                                                    ? 'bg-emerald-600 hover:bg-emerald-500 text-white cursor-pointer shadow-emerald-900/30'
                                                    : 'bg-[#1B222C] text-slate-500 border border-[#2A3440] cursor-not-allowed opacity-60' }}">
                                        [ PROMOTE TO CLASS {{ $nextClass }} ]
                                    </button>
                                </form>

                                <button type="button"
                                        onclick="document.getElementById('manual-class-override').classList.toggle('hidden')"
                                        class="px-3 py-2 rounded-xl bg-[#1B222C] border border-[#2A3440] text-slate-400 hover:text-white transition text-xs">
                                    Manual Class Setting
                                </button>
                            </div>

                            <!-- Manual Override (Collapsible) -->
                            <div id="manual-class-override" class="hidden p-3 rounded-lg bg-[#151B23] border border-[#2A3440] space-y-2">
                                <p class="text-[11px] text-slate-400">Administrative Manual Class Adjustment:</p>
                                <form method="POST" action="{{ route('admin.users.class', $user) }}" class="flex gap-2"
                                      onsubmit="return confirm('Manually change class for {{ $user->name }}?');">
                                    @csrf
                                    @method('PUT')
                                    <select name="responder_class" class="px-2.5 py-1.5 rounded bg-[#11161D] border border-[#2A3440] text-white">
                                        <option value="D" @selected($user->responder_class === 'D')>Class D (100 HP)</option>
                                        <option value="C" @selected($user->responder_class === 'C')>Class C (120 HP)</option>
                                        <option value="B" @selected($user->responder_class === 'B')>Class B (145 HP)</option>
                                        <option value="A" @selected($user->responder_class === 'A')>Class A (170 HP)</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 rounded bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold">
                                        Apply
                                    </button>
                                </form>
                            </div>
                        @else
                            <p class="text-xs text-slate-400">
                                This responder has achieved the maximum Class A rank. Authorized for all containment operations.
                            </p>
                        @endif
                    </div>

                    <!-- Tactical Standards Reference -->
                    <div class="border-t border-[#2A3440] pt-3">
                        <span class="text-[10px] text-slate-500 uppercase font-bold block mb-2">Tactical Defense Class Standards</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px] text-center">
                            <div class="p-2 rounded bg-[#11161D] border border-[#2A3440]">
                                <span class="font-bold text-emerald-400 block">Class D</span>
                                <span class="text-slate-400 text-[10px]">100 HP &bull; Req 100 XP</span>
                            </div>
                            <div class="p-2 rounded bg-[#11161D] border border-[#2A3440]">
                                <span class="font-bold text-cyan-400 block">Class C</span>
                                <span class="text-slate-400 text-[10px]">120 HP &bull; Req 250 XP</span>
                            </div>
                            <div class="p-2 rounded bg-[#11161D] border border-[#2A3440]">
                                <span class="font-bold text-purple-400 block">Class B</span>
                                <span class="text-slate-400 text-[10px]">145 HP &bull; Req 500 XP</span>
                            </div>
                            <div class="p-2 rounded bg-[#11161D] border border-[#2A3440]">
                                <span class="font-bold text-rose-400 block">Class A</span>
                                <span class="text-slate-400 text-[10px]">170 HP &bull; Elite Tier</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Class Promotion Log (Inside Responder Perspective) -->
                <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3 text-xs">
                    <h3 class="font-bold text-white uppercase tracking-wider text-xs border-b border-[#2A3440] pb-2">
                        Class Promotion Log ({{ $user->promotionHistory->count() }})
                    </h3>

                    @forelse($user->promotionHistory as $promo)
                        <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-1 text-[11px]">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-emerald-400">Class {{ $promo->from_class }} &rarr; Class {{ $promo->to_class }}</span>
                                <span class="text-[10px] text-slate-500">{{ $promo->created_at?->format('Y-m-d') }}</span>
                            </div>
                            <div class="text-slate-400 flex justify-between text-[10px]">
                                <span>At XP: {{ $promo->xp_at_promotion }}</span>
                                <span>Promoter: {{ $promo->promoter?->name ?? 'Admin Command' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-slate-500 text-[11px] text-center py-2">No promotion records on file for this responder.</p>
                    @endforelse
                </div>

                <!-- Response Mission History with Dropdown / Collapsible Details -->
                <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-[#2A3440] pb-2.5">
                        <h3 class="font-bold text-white uppercase tracking-wider text-xs">
                            Response Mission History ({{ $user->responderAssignments->count() }})
                        </h3>
                    </div>

                    @if($user->responderAssignments->isNotEmpty())
                        <div class="space-y-2">
                            @php $shownAssignments = $user->responderAssignments->sortByDesc('assigned_at')->take(5); @endphp
                            @if($user->responderAssignments->count() > 5)
                                <p class="text-[10px] text-slate-500 text-right">Showing 5 of {{ $user->responderAssignments->count() }} missions</p>
                            @endif
                            @foreach($shownAssignments as $as)
                                <details class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] hover:border-[#3A4654] transition">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 [&::-webkit-details-marker]:hidden">
                                        <div class="flex items-center gap-2">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border
                                                {{ $as->status === 'COMPLETED' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-slate-700 text-slate-300 border-slate-600' }}">
                                                {{ $as->status }}
                                            </span>
                                            <span class="text-[#A78BFA] font-bold">
                                                {{ $as->incident?->incident_code ?? 'SF-INC' }}
                                            </span>
                                            <span class="text-slate-300 truncate max-w-[160px]">{{ $as->incident?->title }}</span>
                                        </div>
                                        <span class="text-[10px] text-slate-500">
                                            {{ $as->assigned_at ? $as->assigned_at->format('Y-m-d') : '—' }} &darr;
                                        </span>
                                    </summary>

                                    <div class="pt-2.5 mt-2.5 border-t border-[#2A3440]/60 grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px] text-slate-400">
                                        <div>Assigned: <span class="text-white">{{ $as->assigned_at ? $as->assigned_at->format('Y-m-d H:i') : '—' }}</span></div>
                                        <div>Completed: <span class="text-white">{{ $as->response_completed_at ? $as->response_completed_at->format('Y-m-d H:i') : '—' }}</span></div>
                                        <div>Anomaly HP: <strong class="text-rose-400">{{ $as->anomaly_hp ?? 0 }}/{{ $as->anomaly_max_hp ?? 0 }}</strong></div>
                                        <div>Responder HP: <strong class="text-emerald-400">{{ $as->responder_hp ?? 0 }}/{{ $as->responder_max_hp ?? 0 }}</strong></div>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-4">No deployment missions recorded for this responder.</p>
                    @endif
                </div>

            {{-- 3. REPORTER PERSPECTIVE --}}
            @elseif($user->isReporter())
                @php
                    $incidents = $user->incidents->sortByDesc('created_at');
                    $totalReports = $incidents->count();
                    $verifiedReports = $incidents->where('status', 'VERIFIED')->count();
                    $resolvedReports = $incidents->where('status', 'RESOLVED')->count();
                    $pendingReports = $incidents->where('status', 'PENDING')->count();
                @endphp

                <!-- Reporter Credibility & Activity Metrics -->
                <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-cyan-500/40 space-y-5 text-xs">
                    <div class="border-b border-[#2A3440] pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-[10px] font-bold text-cyan-400 uppercase tracking-wider">REPORTER PERSPECTIVE</span>
                            <h2 class="text-base font-extrabold text-white mt-0.5">Civilian Field Scout Intel</h2>
                        </div>
                        <span class="px-2.5 py-1 rounded bg-[#11161D] border border-[#2A3440] text-cyan-300 font-bold self-start">
                            COMMUNITY SCOUT
                        </span>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Reports Filed</span>
                            <span class="text-2xl font-bold text-white block mt-1">{{ $totalReports }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Verified</span>
                            <span class="text-2xl font-bold text-[#A78BFA] block mt-1">{{ $verifiedReports }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Pending</span>
                            <span class="text-2xl font-bold text-amber-400 block mt-1">{{ $pendingReports }}</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-500 uppercase block">Resolved</span>
                            <span class="text-2xl font-bold text-emerald-400 block mt-1">{{ $resolvedReports }}</span>
                        </div>
                    </div>
                </div>

                <!-- Civilian Reports Logged with Dropdown Filter -->
                <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 text-xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#2A3440] pb-3">
                        <div>
                            <h3 class="font-bold text-white uppercase tracking-wider text-xs">
                                Incident Reports Logged ({{ $totalReports }})
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Anomalies reported from local community sectors.</p>
                        </div>

                        <!-- Dropdown Filter for Reports -->
                        <div class="flex items-center gap-2">
                            <label for="reporter-status-filter" class="text-[11px] text-slate-400">Filter:</label>
                            <select id="reporter-status-filter"
                                    onchange="filterReports(this.value)"
                                    class="px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 text-xs focus:border-[#8B5CF6] focus:outline-none">
                                <option value="ALL">All Reports ({{ $totalReports }})</option>
                                <option value="PENDING">PENDING ({{ $pendingReports }})</option>
                                <option value="VERIFIED">VERIFIED ({{ $verifiedReports }})</option>
                                <option value="RESOLVED">RESOLVED ({{ $resolvedReports }})</option>
                            </select>
                        </div>
                    </div>

                    @if($incidents->isNotEmpty())
                        <div class="space-y-2.5" id="reports-list">
                            @php $shownIncidents = $incidents->take(5); @endphp
                            @if($incidents->count() > 5)
                                <p class="text-[10px] text-slate-500 text-right">Showing 5 of {{ $incidents->count() }} — use filter to narrow results</p>
                            @endif
                            @foreach($shownIncidents as $inc)
                                <details class="report-item group p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] hover:border-[#3A4654] transition"
                                         data-status="{{ $inc->status }}">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-2 [&::-webkit-details-marker]:hidden">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border shrink-0
                                                @if($inc->status === 'RESOLVED') bg-emerald-500/15 text-emerald-300 border-emerald-500/40
                                                @elseif($inc->status === 'VERIFIED') bg-purple-500/15 text-purple-300 border-purple-500/40
                                                @elseif($inc->status === 'UNDER INVESTIGATION') bg-sky-500/15 text-sky-300 border-sky-500/40
                                                @else bg-amber-500/15 text-amber-300 border-amber-500/40
                                                @endif">
                                                {{ $inc->status }}
                                            </span>
                                            <span class="font-bold text-white text-xs truncate">
                                                {{ $inc->incident_code }}: {{ $inc->title }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-[10px] text-slate-500">
                                                {{ $inc->incident_date ? $inc->incident_date->format('Y-m-d') : $inc->created_at->format('Y-m-d') }}
                                            </span>
                                            <span class="text-xs text-slate-400 group-open:rotate-180 transition-transform">&darr;</span>
                                        </div>
                                    </summary>

                                    <div class="pt-3 mt-3 border-t border-[#2A3440]/60 space-y-2 text-[11px]">
                                        <p class="text-slate-300 leading-relaxed bg-[#151B23] p-2.5 rounded-lg border border-[#2A3440]">
                                            {{ $inc->description }}
                                        </p>
                                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1 text-[10px] text-slate-400">
                                            <span>Sector: Brgy. {{ $inc->barangay?->name ?? 'San Francisco' }}</span>
                                            <a href="{{ route('admin.incidents.show', $inc) }}" class="text-[#A78BFA] hover:underline font-bold">
                                                [ View Full Incident &rarr; ]
                                            </a>
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @else
                        <p class="text-slate-500 text-center py-6">No incident reports filed by this user.</p>
                    @endif
                </div>

            {{-- 4. ADMINISTRATOR PERSPECTIVE --}}
            @else
                <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-amber-500/40 space-y-4 text-xs">
                    <div class="border-b border-[#2A3440] pb-3 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">ADMINISTRATOR PERSPECTIVE</span>
                            <h2 class="text-base font-extrabold text-white mt-0.5">Executive Privileges &amp; Command Log</h2>
                        </div>
                        <span class="px-2.5 py-1 rounded bg-[#11161D] border border-[#2A3440] text-amber-300 font-bold">
                            FULL PRIVILEGES
                        </span>
                    </div>

                    <p class="text-slate-400 text-xs">
                        This account possesses unrestricted administrative authorization over user accounts, incident lifecycles, and tactical defense parameters.
                    </p>
                </div>
            @endif

        </div>

    </div>

</main>

<script>
    function filterInvestigations(selectedResult) {
        const items = document.querySelectorAll('.investigation-item');
        items.forEach(item => {
            const result = item.getAttribute('data-result');
            if (selectedResult === 'ALL' || result === selectedResult) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function filterReports(selectedStatus) {
        const items = document.querySelectorAll('.report-item');
        items.forEach(item => {
            const status = item.getAttribute('data-status');
            if (selectedStatus === 'ALL' || status === selectedStatus) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@endsection
