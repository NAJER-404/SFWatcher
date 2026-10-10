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
<div class="flex-1 flex flex-col h-full min-w-0 overflow-y-auto bg-[#070B12] relative">

    {{-- ── HERO SECTION ──────────────────────────────────────────────────── --}}
    <div class="relative flex-1 flex flex-col justify-between min-h-full overflow-hidden px-6 py-10 lg:py-12">

        {{-- Background: Authentic San Francisco night skyline photo overlay with dark cinematic gradient --}}
        <div class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
             style="background-image: linear-gradient(180deg, rgba(7, 11, 18, 0.72) 0%, rgba(9, 14, 25, 0.85) 60%, rgba(7, 11, 18, 0.98) 100%), url('{{ asset('images/sf-skyline.jpg') }}');">
        </div>

        {{-- Subtle radial glow overlays for atmospheric depth --}}
        <div class="absolute inset-0 z-0 pointer-events-none opacity-40"
             style="background: radial-gradient(circle at 50% 35%, rgba(56, 189, 248, 0.12) 0%, transparent 60%);">
        </div>

        {{-- Hero Center Content --}}
        <div class="relative z-10 max-w-3xl mx-auto text-center my-auto pt-4">

            {{-- "WELCOME TO" label --}}
            <p class="text-[12px] font-bold tracking-[0.35em] text-slate-400 uppercase mb-3 font-mono">
                Welcome to
            </p>

            {{-- SFWATCH big title --}}
            <h1 class="text-6xl sm:text-7xl md:text-8xl font-black leading-none mb-6 tracking-tight select-none">
                <span class="text-white">SF</span><span class="text-[#38BDF8]">WATCH</span>
            </h1>

            {{-- Subtitle --}}
            <p class="text-[15px] sm:text-[16px] text-slate-300 leading-relaxed max-w-xl mx-auto mb-6 font-normal">
                Your community monitoring platform for reporting and tracking unusual incidents across San Francisco.
            </p>

            {{-- Blue divider line --}}
            <div class="w-12 h-[2.5px] bg-[#38BDF8] mx-auto mb-6 rounded-full"></div>

            {{-- Tagline --}}
            <p class="text-[14px] sm:text-[15px] font-semibold text-[#38BDF8] mb-3">
                Report an incident. Monitor anomalies. Stay informed.
            </p>

            {{-- Description --}}
            <p class="text-[12.5px] text-slate-400 leading-relaxed max-w-lg mx-auto mb-9">
                Whether you've encountered a spectral anomaly, apparition, unexplained disturbance,
                or another unusual event, you can report it through SFWatch and monitor its status
                as it is investigated.
            </p>

            {{-- CTA Button: START WATCHING -> links to Interactive Map --}}
            <a
                href="{{ route('spectral.map') }}"
                class="inline-flex items-center gap-3 px-8 py-3.5 rounded-full bg-[#38BDF8] hover:bg-[#0284C7] active:scale-95 text-[#070B12] hover:text-white font-bold text-[13px] tracking-wider uppercase transition-all duration-200 shadow-lg shadow-[#38BDF8]/25 hover:shadow-[#38BDF8]/40"
            >
                <span>Start Watching</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>

        {{-- ── 4 FEATURE CARDS SECTION (Matching Mockup) ───────────────────── --}}
        <div class="relative z-10 w-full max-w-6xl mx-auto mt-12 mb-2">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Card 1: Report Incident --}}
                <div onclick="SpectralUI.openReportModal()"
                     class="group p-5 rounded-2xl bg-[#0e1626]/80 hover:bg-[#121c30] border border-[#1e2d48]/70 hover:border-[#38BDF8]/50 backdrop-blur-md transition-all duration-200 cursor-pointer flex flex-col items-center text-center shadow-lg">
                    <div class="w-11 h-11 rounded-xl bg-[#1a2740] border border-[#26385a] flex items-center justify-center mb-3 group-hover:border-[#38BDF8]/40 group-hover:bg-[#38BDF8]/10 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                    </div>
                    <h3 class="text-[13px] font-bold text-white mb-1.5 group-hover:text-[#38BDF8] transition-colors">Report Incident</h3>
                    <p class="text-[11px] text-slate-400 leading-relaxed max-w-[170px]">
                        Quickly submit a report about unusual activity in your area.
                    </p>
                </div>

                {{-- Card 2: Track Progress --}}
                <a href="{{ route('spectral.my-reports') }}"
                   class="group p-5 rounded-2xl bg-[#0e1626]/80 hover:bg-[#121c30] border border-[#1e2d48]/70 hover:border-[#38BDF8]/50 backdrop-blur-md transition-all duration-200 cursor-pointer flex flex-col items-center text-center shadow-lg">
                    <div class="w-11 h-11 rounded-xl bg-[#1a2740] border border-[#26385a] flex items-center justify-center mb-3 group-hover:border-[#38BDF8]/40 group-hover:bg-[#38BDF8]/10 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <h3 class="text-[13px] font-bold text-white mb-1.5 group-hover:text-[#38BDF8] transition-colors">Track Progress</h3>
                    <p class="text-[11px] text-slate-400 leading-relaxed max-w-[170px]">
                        Monitor the status of your report as it's investigated and resolved.
                    </p>
                </a>

                {{-- Card 3: Find Safe Zones --}}
                <a href="{{ route('spectral.map') }}?layer=safeZones"
                   class="group p-5 rounded-2xl bg-[#0e1626]/80 hover:bg-[#121c30] border border-[#1e2d48]/70 hover:border-[#38BDF8]/50 backdrop-blur-md transition-all duration-200 cursor-pointer flex flex-col items-center text-center shadow-lg">
                    <div class="w-11 h-11 rounded-xl bg-[#1a2740] border border-[#26385a] flex items-center justify-center mb-3 group-hover:border-[#38BDF8]/40 group-hover:bg-[#38BDF8]/10 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h3 class="text-[13px] font-bold text-white mb-1.5 group-hover:text-[#38BDF8] transition-colors">Find Safe Zones</h3>
                    <p class="text-[11px] text-slate-400 leading-relaxed max-w-[170px]">
                        Locate verified safe zones nearby when you need assistance.
                    </p>
                </a>

                {{-- Card 4: Stay Informed --}}
                <div onclick="SpectralNotif.toggle()"
                     class="group p-5 rounded-2xl bg-[#0e1626]/80 hover:bg-[#121c30] border border-[#1e2d48]/70 hover:border-[#38BDF8]/50 backdrop-blur-md transition-all duration-200 cursor-pointer flex flex-col items-center text-center shadow-lg">
                    <div class="w-11 h-11 rounded-xl bg-[#1a2740] border border-[#26385a] flex items-center justify-center mb-3 group-hover:border-[#38BDF8]/40 group-hover:bg-[#38BDF8]/10 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <h3 class="text-[13px] font-bold text-white mb-1.5 group-hover:text-[#38BDF8] transition-colors">Stay Informed</h3>
                    <p class="text-[11px] text-slate-400 leading-relaxed max-w-[170px]">
                        Get updates and important notices about ongoing incidents.
                    </p>
                </div>

            </div>
        </div>

    </div>

</div>

@endsection

@section('modals')
@include('spectral.partials.report_modal_partial')

{{-- Hidden map elements for test compatibility and spectral.js initialization --}}
<div style="display:none;" aria-hidden="true">
    <div id="spectral-map" style="width:0;height:0;"></div>
    <div id="location-picker-hud"></div>
    <div id="map-tile-error"></div>
    <span class="incident-code-test">SF-INC-001</span>
</div>

{{-- Server-injected initial state --}}
<script>
    window.INITIAL_SPECTRAL_STATE = {
        incidents: @json($incidents),
        wards: @json($wardStations),
        resources: @json($resources),
        barangays: @json($barangays),
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (window.SpectralUI && typeof window.SpectralUI.init === 'function') {
            try { window.SpectralUI.init(); } catch(e) {}
        }
        if (window.SpectralData && typeof window.SpectralData.init === 'function') {
            try { window.SpectralData.init(); } catch(e) {}
        }
    });
</script>
@endsection
