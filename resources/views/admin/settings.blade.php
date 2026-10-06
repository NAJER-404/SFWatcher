@extends('layouts.admin')

@section('title', 'Account & System Settings — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-6 max-w-4xl mx-auto w-full font-mono">

    <!-- Header + Breadcrumb -->
    <div>
        <div class="flex items-center gap-2 text-xs text-[#8B5CF6]">
            <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold">Account Settings</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">System &amp; Defense Settings</h1>
        <p class="text-xs text-slate-400 mt-0.5">Configuration policies for archiving, responder progression, and network security.</p>
    </div>

    <!-- System Parameters Policy Card -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 text-xs">
        <div class="border-b border-[#2A3440] pb-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-white">Active Operational Policies</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">System defaults configured in the defense core.</p>
        </div>

        <div class="space-y-3">
            <!-- Map Archiving Policy -->
            <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-white">Resolved Incident Map Archiving Policy</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">ENABLED</span>
                </div>
                <p class="text-slate-400 text-[11px]">
                    Allows Administrators to hide resolved anomalies from public civilian and field investigator maps while permanently preserving database records, transcripts, and evidence.
                </p>
            </div>

            <!-- Responder Class Thresholds -->
            <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-white">Tactical Class Progression Standards</span>
                    <span class="text-slate-500 text-[10px]">CONFIG // spectral_response</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px] text-center">
                    <div class="p-2 rounded bg-[#151B23] border border-[#2A3440]">
                        <span class="text-emerald-400 font-bold block">Class D</span>
                        <span class="text-slate-300">100 HP</span>
                        <span class="text-slate-500 text-[10px] block mt-0.5">Req 100 XP &rarr; C</span>
                    </div>
                    <div class="p-2 rounded bg-[#151B23] border border-[#2A3440]">
                        <span class="text-cyan-400 font-bold block">Class C</span>
                        <span class="text-slate-300">120 HP</span>
                        <span class="text-slate-500 text-[10px] block mt-0.5">Req 250 XP &rarr; B</span>
                    </div>
                    <div class="p-2 rounded bg-[#151B23] border border-[#2A3440]">
                        <span class="text-purple-400 font-bold block">Class B</span>
                        <span class="text-slate-300">145 HP</span>
                        <span class="text-slate-500 text-[10px] block mt-0.5">Req 500 XP &rarr; A</span>
                    </div>
                    <div class="p-2 rounded bg-[#151B23] border border-[#2A3440]">
                        <span class="text-rose-400 font-bold block">Class A</span>
                        <span class="text-slate-300">170 HP</span>
                        <span class="text-slate-500 text-[10px] block mt-0.5">Elite Max Tier</span>
                    </div>
                </div>
            </div>

            <!-- Security Isolation -->
            <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-white">Multi-Portal Authentication Guard Architecture</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/40">ISOLATED GUARDS</span>
                </div>
                <p class="text-slate-400 text-[11px]">
                    Separate session scopes for <code>admin</code>, <code>investigator</code>, <code>responder</code>, and <code>web</code> (reporter) guards prevent session collisions.
                </p>
            </div>
        </div>
    </div>

    <!-- Administrative Preferences Form -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 text-xs">
        <div class="border-b border-[#2A3440] pb-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-white">Administrative Preferences</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Interface and audit retention options.</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf

            <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="audit_logging" checked class="accent-[#8B5CF6]">
                    <span class="text-slate-200">Log all map archiving and restoration actions to AdminActivityLog</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="promotion_confirm" checked class="accent-[#8B5CF6]">
                    <span class="text-slate-200">Require explicit confirmation before executing responder promotions</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="strict_security" checked class="accent-[#8B5CF6]">
                    <span class="text-slate-200">Prevent self-demotion and primary commander account removal</span>
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition">
                    [ Save Administrative Preferences ]
                </button>
            </div>
        </form>
    </div>

</main>
@endsection
