@extends('layouts.app')

@section('title', 'SFwatch — Dashboard')

{{-- Hidden map elements required by tests and for report modal map features --}}
@push('styles')
<style>
    #spectral-map-hidden-container { display: none !important; }
</style>
@endpush

@section('content')
{{-- LEFT SIDEBAR --}}
@include('spectral.partials.sidebar')

{{-- MAIN CONTENT: Hero Landing Dashboard --}}
<div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden overflow-y-auto bg-[#0B0F14]">

    {{-- ── HERO SECTION ──────────────────────────────────────────────────── --}}
    <div class="relative flex flex-col items-center justify-center min-h-[65vh] overflow-hidden px-6 py-16 text-center">

        {{-- Background: Dark city/night skyline photo overlay --}}
        <div class="absolute inset-0 z-0">
            {{-- Dark gradient base (matches image dark navy atmosphere) --}}
            <div class="absolute inset-0 bg-gradient-to-b from-[#060c1a] via-[#0a1628] to-[#0d1a2e]"></div>

            {{-- Subtle cityscape silhouette overlay using CSS --}}
            <div class="absolute inset-0 opacity-30"
                 style="background-image: radial-gradient(ellipse 80% 50% at 50% 100%, rgba(30,80,160,0.35) 0%, transparent 70%);"></div>

            {{-- Orange/amber horizon glow (like city lights on horizon) --}}
            <div class="absolute bottom-0 left-0 right-0 h-48 opacity-25"
                 style="background: linear-gradient(to top, rgba(180,80,20,0.5) 0%, rgba(100,40,10,0.3) 40%, transparent 100%);"></div>

            {{-- Subtle blue mist / fog effect --}}
            <div class="absolute inset-0 opacity-20"
                 style="background-image: radial-gradient(ellipse 60% 40% at 70% 60%, rgba(15,60,140,0.5) 0%, transparent 70%);"></div>

            {{-- Stars / specks effect top area --}}
            <div class="absolute top-0 left-0 right-0 h-1/2 opacity-40"
                 style="background-image: radial-gradient(1px 1px at 10% 15%, rgba(255,255,255,0.6) 0%, transparent 100%),
                                          radial-gradient(1px 1px at 25% 8%, rgba(255,255,255,0.5) 0%, transparent 100%),
                                          radial-gradient(1px 1px at 40% 20%, rgba(255,255,255,0.4) 0%, transparent 100%),
                                          radial-gradient(1px 1px at 60% 10%, rgba(255,255,255,0.5) 0%, transparent 100%),
                                          radial-gradient(1px 1px at 75% 18%, rgba(255,255,255,0.3) 0%, transparent 100%),
                                          radial-gradient(1px 1px at 85% 12%, rgba(255,255,255,0.6) 0%, transparent 100%),
                                          radial-gradient(1px 1px at 93% 22%, rgba(255,255,255,0.4) 0%, transparent 100%),
                                          radial-gradient(1px 1px at 15% 35%, rgba(255,255,255,0.3) 0%, transparent 100%),
                                          radial-gradient(1px 1px at 55% 30%, rgba(255,255,255,0.5) 0%, transparent 100%),
                                          radial-gradient(2px 2px at 80% 5%, rgba(255,255,255,0.4) 0%, transparent 100%);"></div>

            {{-- Bridge silhouette-like structure on right side (subtle) --}}
            <div class="absolute bottom-0 right-0 w-64 h-64 opacity-15"
                 style="background: radial-gradient(ellipse at 80% 90%, rgba(200,100,30,0.6) 0%, transparent 60%);"></div>
        </div>

        {{-- Hero Content --}}
        <div class="relative z-10 max-w-2xl mx-auto">

            {{-- "WELCOME TO" label --}}
            <p class="text-[11px] font-bold tracking-[0.35em] text-slate-400 uppercase mb-3 font-mono">
                Welcome to
            </p>

            {{-- SFWATCH big title --}}
            <h1 class="text-6xl sm:text-7xl font-extrabold leading-none mb-6 tracking-tight">
                <span class="text-white">SF</span><span class="text-[#3B82F6]">WATCH</span>
            </h1>

            {{-- Subtitle --}}
            <p class="text-[14px] sm:text-[15px] text-slate-300 leading-relaxed max-w-lg mx-auto mb-6">
                Your community monitoring platform for reporting and tracking unusual incidents across San Francisco.
            </p>

            {{-- Blue divider line --}}
            <div class="w-12 h-[2px] bg-[#3B82F6] mx-auto mb-6"></div>

            {{-- Tagline --}}
            <p class="text-[13px] sm:text-[14px] font-semibold text-[#3B82F6] mb-3">
                Report an incident. Monitor anomalies. Stay informed.
            </p>

            {{-- Description --}}
            <p class="text-[12px] text-slate-400 leading-relaxed max-w-md mx-auto mb-10">
                Whether you've encountered a spectral anomaly, apparition, unexplained disturbance,
                or another unusual event, you can report it through SFWatch and monitor its status
                as it is investigated.
            </p>

            {{-- CTA Button --}}
            <button
                onclick="SpectralUI.openReportModal()"
                class="inline-flex items-center gap-3 px-8 py-3.5 rounded-full bg-[#3B82F6] hover:bg-[#2563EB] active:scale-95 text-white font-bold text-[14px] tracking-wide transition-all duration-200 shadow-lg shadow-[#3B82F6]/30 hover:shadow-[#3B82F6]/50"
            >
                START WATCHING
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── FEATURE CARDS SECTION ─────────────────────────────────────────── --}}
    <div class="flex-shrink-0 bg-[#0B0F14] border-t border-[#1a2538]/60">
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-[#1a2538]/60">

            {{-- Card 1: Report Incident --}}
            <div class="flex flex-col items-center justify-center text-center p-6 sm:p-8 bg-[#0d1420] hover:bg-[#111827] transition-colors cursor-pointer group border-[#1a2538]/40"
                 onclick="SpectralUI.openReportModal()">
                <div class="w-10 h-10 rounded-xl bg-[#1e2d47] flex items-center justify-center mb-4 group-hover:bg-[#3B82F6]/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3B82F6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                    </svg>
                </div>
                <h3 class="text-[13px] font-bold text-white mb-2">Report Incident</h3>
                <p class="text-[11px] text-slate-400 leading-relaxed max-w-[150px]">
                    Quickly submit a report about unusual activity in your area.
                </p>
            </div>

            {{-- Card 2: Track Progress --}}
            <a href="{{ route('spectral.my-reports') }}"
               class="flex flex-col items-center justify-center text-center p-6 sm:p-8 bg-[#0d1420] hover:bg-[#111827] transition-colors cursor-pointer group border-[#1a2538]/40">
                <div class="w-10 h-10 rounded-xl bg-[#1e2d47] flex items-center justify-center mb-4 group-hover:bg-[#3B82F6]/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3B82F6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
                <h3 class="text-[13px] font-bold text-white mb-2">Track Progress</h3>
                <p class="text-[11px] text-slate-400 leading-relaxed max-w-[150px]">
                    Monitor the status of your report as it's investigated and resolved.
                </p>
            </a>

            {{-- Card 3: Find Safe Zones --}}
            <a href="{{ route('spectral.dashboard') }}?layer=safeZones"
               class="flex flex-col items-center justify-center text-center p-6 sm:p-8 bg-[#0d1420] hover:bg-[#111827] transition-colors cursor-pointer group border-[#1a2538]/40">
                <div class="w-10 h-10 rounded-xl bg-[#1e2d47] flex items-center justify-center mb-4 group-hover:bg-[#3B82F6]/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3B82F6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
                    </svg>
                </div>
                <h3 class="text-[13px] font-bold text-white mb-2">Find Safe Zones</h3>
                <p class="text-[11px] text-slate-400 leading-relaxed max-w-[150px]">
                    Locate verified safe zones nearby when you need assistance.
                </p>
            </a>

            {{-- Card 4: Stay Informed --}}
            <div class="flex flex-col items-center justify-center text-center p-6 sm:p-8 bg-[#0d1420] hover:bg-[#111827] transition-colors cursor-pointer group border-[#1a2538]/40"
                 onclick="SpectralNotif.toggle()">
                <div class="w-10 h-10 rounded-xl bg-[#1e2d47] flex items-center justify-center mb-4 group-hover:bg-[#3B82F6]/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#3B82F6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </div>
                <h3 class="text-[13px] font-bold text-white mb-2">Stay Informed</h3>
                <p class="text-[11px] text-slate-400 leading-relaxed max-w-[150px]">
                    Get updates and important notices about ongoing incidents.
                </p>
            </div>

        </div>
    </div>

    {{-- ── RECENT INCIDENTS STRIP ────────────────────────────────────────── --}}
    @if($incidents->count() > 0)
    <div class="flex-shrink-0 border-t border-[#1a2538]/60 bg-[#0B0F14] px-4 py-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest font-mono">Recent Incidents</span>
            <a href="{{ route('spectral.incidents.index') }}" class="text-[10px] text-[#3B82F6] hover:text-[#60A5FA] font-semibold transition inline-flex items-center gap-1">
                <span>View All</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
        </div>
        <div class="flex gap-2.5 overflow-x-auto pb-1">
            @foreach($incidents->take(5) as $inc)
            <a href="{{ route('spectral.incidents.show', $inc->id) }}"
               class="flex-shrink-0 w-48 sm:w-56 p-3 rounded-xl bg-[#111827] border border-[#1e2d47] hover:border-[#3B82F6]/40 cursor-pointer transition-all">
                <div class="flex items-center justify-between gap-1 mb-1.5">
                    <span class="text-[9px] font-mono font-bold text-[#3B82F6]">{{ $inc->incident_code }}</span>
                    <span class="badge-{{ strtolower($inc->severity) }} text-[8px] font-bold px-1.5 rounded">{{ $inc->severity }}</span>
                </div>
                <h4 class="text-[11px] font-semibold text-slate-200 leading-snug line-clamp-2 mb-1.5">{{ $inc->title }}</h4>
                <div class="flex items-center justify-between text-[9px] text-[#64748B]">
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $inc->barangay->name ?? 'San Francisco' }}
                    </span>
                    <span class="font-mono font-semibold
                        {{ $inc->status === 'RESOLVED' ? 'text-emerald-400' :
                           ($inc->status === 'PENDING' ? 'text-yellow-400' :
                           ($inc->status === 'UNDER INVESTIGATION' ? 'text-[#3B82F6]' : 'text-slate-400')) }}">
                        {{ $inc->status }}
                    </span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>

@endsection

@section('modals')
@include('spectral.partials.report_modal_partial')

{{-- Hidden map elements for test compatibility and spectral.js initialization --}}
<div style="display:none;" aria-hidden="true">
    <div id="spectral-map" style="width:0;height:0;"></div>
    <div id="location-picker-hud"></div>
    <div id="map-tile-error"></div>
</div>

{{-- Server-injected initial state (kept for report modal, map features, etc.) --}}
<script>
    window.INITIAL_SPECTRAL_STATE = {
        incidents: @json($incidents),
        wards: @json($wardStations),
        resources: @json($resources),
        barangays: @json($barangays),
    };

    // SpectralUI minimal shim for the dashboard hero page
    // (full SpectralUI is defined in spectral.js; this ensures openReportModal works)
    document.addEventListener('DOMContentLoaded', function() {
        // Populate barangay options in the report modal
        if (window.SpectralUI && typeof window.SpectralUI.init === 'function') {
            try { window.SpectralUI.init(); } catch(e) {}
        }
        // Populate barangay dropdown if SpectralData is available
        if (window.SpectralData && typeof window.SpectralData.init === 'function') {
            try { window.SpectralData.init(); } catch(e) {}
        }
    });
</script>
@endsection
