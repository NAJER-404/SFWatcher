@extends('layouts.admin')

@section('title', 'Update User: ' . $user->name . ' — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 space-y-6 max-w-4xl mx-auto w-full font-mono">

    <!-- Breadcrumb: Dashboard / All Users / Update -->
    <nav class="flex items-center gap-2 text-xs text-[#8B5CF6]" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="text-slate-600">/</span>
        <a href="{{ route('admin.users.index') }}" class="hover:underline">All Users</a>
        <span class="text-slate-600">/</span>
        <span class="text-slate-300 font-bold">Update</span>
    </nav>

    <!-- Header Card -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">Update Account: {{ $user->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Modify account credentials, role authorization, or tactical classification.</p>
        </div>

        <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white transition text-xs self-start">
            &larr; Back to Users
        </a>
    </div>

    @php
        // ── Promotion Progress data (for responder role only) ──
        $currentClass = strtoupper($user->responder_class ?? 'D');
        $nextClassMap = ['D' => 'C', 'C' => 'B', 'B' => 'A', 'A' => null];
        $nextClass    = $nextClassMap[$currentClass] ?? null;

        $promotionXpFallback = ['D' => 100, 'C' => 150, 'B' => 250, 'A' => null];
        $requiredXp = $nextClass
            ? config("spectral_response.classes.{$currentClass}.promotion_xp", $promotionXpFallback[$currentClass] ?? null)
            : null;

        $currentXp    = (int) ($user->xp ?? 0);
        $isMaxClass   = $nextClass === null;
        $isEligible   = ! $isMaxClass && $requiredXp !== null && $currentXp >= $requiredXp;
        $xpRemaining  = $isEligible ? 0 : max(0, ($requiredXp ?? 0) - $currentXp);
        $progressPct  = ($requiredXp && $requiredXp > 0) ? min(100, round(($currentXp / $requiredXp) * 100)) : 100;

        // Class tier HP for reference
        $classHpMap = ['D' => 100, 'C' => 120, 'B' => 145, 'A' => 170];
    @endphp

    <!-- Update Form -->
    <form id="update-user-form" method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Primary Account Details -->
        <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-[#2A3440] pb-2.5">
                Profile Credentials
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label for="name" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Full Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition">
                    @error('name')
                        <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition">
                    @error('email')
                        <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Role Assignment -->
            <div class="pt-2">
                <label for="role" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">System Role</label>
                <select id="role"
                        name="role"
                        onchange="toggleResponderFields(this.value)"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition text-xs">
                    <option value="reporter" @selected(old('role', $user->role) === 'reporter')>Reporter (Civilian Incident Reporting)</option>
                    <option value="investigator" @selected(old('role', $user->role) === 'investigator')>Investigator (Field Investigation & Review)</option>
                    <option value="responder" @selected(old('role', $user->role) === 'responder')>Responder (Tactical Anomaly Containment)</option>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator (Full System Administration)</option>
                </select>
                @error('role')
                    <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Responder Tactical Fields (Conditional) -->
        <div id="responder-fields" class="{{ old('role', $user->role) === 'responder' ? '' : 'hidden' }} p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#8B5CF6]/40 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-[#2A3440] pb-2.5 flex items-center justify-between">
                <span>Responder Tactical Specifications</span>
                <span class="text-[10px] text-[#A78BFA] font-normal">Active for Responder Role Only</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label for="responder_class" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Class Tier</label>
                    <select id="responder_class"
                            name="responder_class"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition">
                        <option value="D" @selected(old('responder_class', $user->responder_class ?? 'D') === 'D')>Class D (100 Max HP)</option>
                        <option value="C" @selected(old('responder_class', $user->responder_class) === 'C')>Class C (120 Max HP)</option>
                        <option value="B" @selected(old('responder_class', $user->responder_class) === 'B')>Class B (145 Max HP)</option>
                        <option value="A" @selected(old('responder_class', $user->responder_class) === 'A')>Class A (170 Max HP)</option>
                    </select>
                </div>

                <div>
                    <label for="xp" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Current XP</label>
                    <input type="number"
                           id="xp"
                           name="xp"
                           min="0"
                           value="{{ old('xp', $user->xp ?? 0) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-amber-400 font-bold focus:border-[#8B5CF6] focus:outline-none transition">
                </div>

                <div>
                    <label for="responder_status" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Availability Status</label>
                    <select id="responder_status"
                            name="responder_status"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition">
                        <option value="AVAILABLE" @selected(old('responder_status', $user->responder_status ?? 'AVAILABLE') === 'AVAILABLE')>AVAILABLE (Ready for Callout)</option>
                        <option value="RESPONDING" @selected(old('responder_status', $user->responder_status) === 'RESPONDING')>RESPONDING (Active Mission)</option>
                        <option value="INACTIVE" @selected(old('responder_status', $user->responder_status) === 'INACTIVE')>INACTIVE (Off Duty)</option>
                    </select>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════
                 PROMOTION PROGRESS SECTION
            ═══════════════════════════════════════════════════════════ -->
            <div class="pt-4 mt-2 border-t border-[#2A3440]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="17 11 12 6 7 11"/>
                            <polyline points="17 18 12 13 7 18"/>
                        </svg>
                        Promotion Progress
                    </h3>
                    @if($isMaxClass)
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/40">
                            ⭐ MAX TIER
                        </span>
                    @endif
                </div>

                @if($isMaxClass)
                    {{-- Max tier reached — celebratory card --}}
                    <div class="p-5 rounded-xl bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/40 text-center space-y-1.5">
                        <div class="text-2xl">🏆</div>
                        <p class="text-sm font-bold text-amber-300 uppercase tracking-wider">Class A — Maximum Tier Reached</p>
                        <p class="text-[11px] text-slate-400">This responder has achieved the highest tactical classification. No further promotions are available.</p>
                    </div>
                @else
                    {{-- Stats grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mb-4">
                        <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Current Class</p>
                            <p class="text-lg font-extrabold text-white mt-0.5">{{ $currentClass }}</p>
                            <p class="text-[9px] text-slate-500 font-mono mt-0.5">{{ $classHpMap[$currentClass] ?? 100 }} HP</p>
                        </div>
                        <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Current XP</p>
                            <p class="text-lg font-extrabold text-amber-400 mt-0.5">{{ number_format($currentXp) }}</p>
                            <p class="text-[9px] text-slate-500 font-mono mt-0.5">Total earned</p>
                        </div>
                        <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Target Class</p>
                            <p class="text-lg font-extrabold text-[#A78BFA] mt-0.5">{{ $nextClass }}</p>
                            <p class="text-[9px] text-slate-500 font-mono mt-0.5">{{ $classHpMap[$nextClass] ?? 120 }} HP</p>
                        </div>
                        <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <p class="text-[10px] text-slate-500 uppercase font-bold">Required XP</p>
                            <p class="text-lg font-extrabold {{ $isEligible ? 'text-emerald-400' : 'text-slate-300' }} mt-0.5">
                                {{ number_format((int) $requiredXp) }}
                            </p>
                            <p class="text-[9px] text-slate-500 font-mono mt-0.5">To promote</p>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between text-[11px] mb-1.5">
                            <span class="text-slate-400">Progress to Class {{ $nextClass }}</span>
                            <span class="font-mono font-bold {{ $isEligible ? 'text-emerald-400' : 'text-[#A78BFA]' }}">
                                {{ $isEligible ? '✓ READY TO PROMOTE' : number_format($xpRemaining) . ' XP remaining' }}
                            </span>
                        </div>
                        <div class="h-3 rounded-full bg-[#11161D] border border-[#2A3440] overflow-hidden">
                            <div class="h-full transition-all duration-500 {{ $isEligible ? 'bg-gradient-to-r from-emerald-500 to-emerald-400' : 'bg-gradient-to-r from-[#8B5CF6] to-[#A78BFA]' }}"
                                 style="width: {{ $progressPct }}%;"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono mt-1">
                            <span>{{ number_format($currentXp) }} XP</span>
                            <span>{{ $progressPct }}%</span>
                            <span>{{ number_format((int) $requiredXp) }} XP</span>
                        </div>
                    </div>

                    {{-- Action: Promote button OR waiting message --}}
                    @if($isEligible)
                        {{-- Eligible: show promotion form button (submits the separate promote form outside this form) --}}
                        <button type="submit"
                                form="promote-user-form"
                                onclick="return confirm('Promote {{ addslashes($user->name) }} from Class {{ $currentClass }} to Class {{ $nextClass }}?');"
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-900/40 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="17 11 12 6 7 11"/>
                                <polyline points="17 18 12 13 7 18"/>
                            </svg>
                            Promote to Class {{ $nextClass }}
                        </button>
                        <p class="text-[10px] text-emerald-400/80 text-center mt-2 italic">
                            ✓ This responder meets the XP requirement for Class {{ $nextClass }}.
                        </p>
                    @else
                        {{-- Not eligible yet --}}
                        <div class="p-3 rounded-xl bg-[#11161D] border border-dashed border-[#2A3440] text-center">
                            <p class="text-[11px] text-slate-400">
                                Needs <strong class="text-[#A78BFA] font-mono">{{ number_format($xpRemaining) }} more XP</strong> to become eligible for promotion to Class {{ $nextClass }}.
                            </p>
                            <p class="text-[10px] text-slate-600 mt-1 italic">Update the XP field above and save to re-check eligibility.</p>
                        </div>
                    @endif
                @endif
            </div>
            {{-- ═══════════════════════════════════════════════════════════ --}}
        </div>

        <!-- Form Submit & Cancel Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-white transition text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition text-xs shadow-lg shadow-purple-900/30">
                Update User
            </button>
        </div>
    </form>

    <!-- ═══════════════════════════════════════════════════════════
         SEPARATE PROMOTE FORM (hidden — triggered by the Promote button above)
    ═══════════════════════════════════════════════════════════ -->
    <form id="promote-user-form" method="POST" action="{{ route('admin.users.promote', $user) }}" class="hidden">
        @csrf
    </form>

</main>

<script>
    function toggleResponderFields(role) {
        const el = document.getElementById('responder-fields');
        if (role === 'responder') {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }
</script>
@endsection