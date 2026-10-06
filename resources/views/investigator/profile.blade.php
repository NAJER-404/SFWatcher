@extends('layouts.investigator')

@section('title', 'Investigator Profile — SpectraWatch')

@section('content')
<!-- INVESTIGATOR SIDEBAR -->
@include('investigator.partials.sidebar')

<!-- PROFILE WORKSPACE -->
<main class="flex-1 overflow-y-auto p-3.5 sm:p-5 md:p-7 bg-[#0B0F14] space-y-5 sm:space-y-6 max-w-4xl mx-auto w-full">

    <div class="pb-3 border-b border-[#2A3440]">
        <h1 class="text-lg font-bold text-white font-mono uppercase tracking-wider flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-[#8B5CF6]"></span>
            <span>Investigator Credentials</span>
        </h1>
        <p class="text-xs text-[#9CA3AF]">San Francisco Node Field Agent Identity &amp; Activity Log</p>
    </div>

    <!-- Profile Details Card -->
    <div class="p-4 sm:p-6 rounded-xl bg-[#151B23] border border-[#2A3440] shadow-xl space-y-5">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-[#1B222C] border-2 border-[#8B5CF6]/50 flex items-center justify-center text-xl font-bold text-[#8B5CF6] font-mono">
                {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strrchr($user->name, ' '), 1, 1) ?: substr($user->name, 1, 1)) }}
            </div>
            <div>
                <h2 class="text-base font-bold text-white">{{ $user->name }}</h2>
                <p class="text-xs text-[#9CA3AF] font-mono">{{ $user->email }}</p>
                <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-md bg-[#8B5CF6]/15 border border-[#8B5CF6]/40 text-[10px] font-mono font-bold text-[#A78BFA] uppercase">
                    Role: Investigator &bull; Authorized
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-[#1E2631] text-xs font-mono">
            <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440]">
                <span class="text-[#64748B] block text-[10px] uppercase">Jurisdiction</span>
                <span class="text-white font-bold mt-0.5 block">San Francisco, Agusan del Sur</span>
            </div>
            <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440]">
                <span class="text-[#64748B] block text-[10px] uppercase">Account Security</span>
                <span class="text-emerald-400 font-bold mt-0.5 block">Active Field Token Verified</span>
            </div>
        </div>
    </div>

    <!-- Recent Investigation Entries -->
    <div class="rounded-xl bg-[#151B23] border border-[#2A3440] shadow-xl overflow-hidden">
        <div class="px-5 py-3.5 border-b border-[#2A3440] bg-[#11161D]">
            <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Your Recent Investigation Log</h3>
        </div>
        <div class="p-4 space-y-3">
            @forelse($recentInvestigations as $inv)
                <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-[#8B5CF6]">{{ $inv->incident->incident_code ?? 'INCIDENT' }}</span>
                        <span class="text-[10px] font-mono text-[#64748B]">{{ $inv->investigation_date ? $inv->investigation_date->format('Y-m-d h:i A') : '—' }}</span>
                    </div>
                    <p class="text-slate-300 font-sans">{{ $inv->notes }}</p>
                </div>
            @empty
                <p class="text-slate-500 text-xs text-center py-4">No logged investigations under this account yet.</p>
            @endforelse
        </div>
    </div>

</main>
@endsection
