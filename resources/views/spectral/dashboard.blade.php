@extends('layouts.app')

@section('title', 'SFwatch — Dashboard')

@push('styles')
<style>
    #spectral-map-hidden-container { display: none !important; }
</style>
@endpush

@section('content')
@include('spectral.partials.sidebar')

{{-- Outer: scrollable container --}}
<div class="flex-1 min-w-0 overflow-y-auto bg-[#070B12] relative">

    {{-- Inner: fills viewport, grows with content, pushes cards down --}}
    <div class="relative min-h-full flex flex-col justify-between px-6 pt-8 pb-10">

        {{-- ═══ STATIC HALLOWEEN BACKGROUND ═══ --}}
        <div class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat"
             style="
                background-image:
                    linear-gradient(180deg, rgba(20, 10, 40, 0.85) 0%, rgba(13, 10, 31, 0.88) 40%, rgba(7, 11, 18, 0.96) 100%),
                    radial-gradient(ellipse 70% 50% at 50% 0%, rgba(139, 92, 246, 0.35) 0%, transparent 60%),
                    radial-gradient(ellipse 60% 40% at 15% 100%, rgba(56, 189, 248, 0.20) 0%, transparent 70%),
                    radial-gradient(ellipse 60% 40% at 90% 30%, rgba(168, 85, 247, 0.22) 0%, transparent 65%),
                    url('{{ asset('images/sf-skyline.jpg') }}');
             ">
        </div>

        {{-- Hero Center Content --}}
        <div class="relative z-10 max-w-3xl mx-auto text-center my-auto pt-2 pb-2">

            <p class="text-[12px] font-bold tracking-[0.35em] text-slate-400 uppercase mb-3 font-mono">
                Welcome to
            </p>

            <h1 class="text-6xl sm:text-7xl md:text-8xl font-black leading-none mb-6 tracking-tight select-none">
                <span class="bg-gradient-to-r from-[#3B82F6] to-[#06B6D4] bg-clip-text text-transparent">SFWatch</span>
            </h1>

            <p class="text-[15px] sm:text-[16px] text-slate-300 leading-relaxed max-w-xl mx-auto mb-6 font-normal">
                Your community monitoring platform for reporting and tracking unusual incidents across San Francisco.
            </p>

            <div class="w-12 h-[2.5px] bg-[#38BDF8] mx-auto mb-6 rounded-full"></div>

            <p class="text-[14px] sm:text-[15px] font-semibold text-[#38BDF8] mb-3">
                Report an incident. Monitor anomalies. Stay informed.
            </p>

            <p class="text-[12.5px] text-slate-400 leading-relaxed max-w-lg mx-auto mb-8">
                Whether you've encountered a spectral anomaly, apparition, unexplained disturbance,
                or another unusual event, you can report it through SFWatch and monitor its status
                as it is investigated.
            </p>

            <a
                href="{{ route('spectral.map') }}"
                class="inline-flex items-center gap-2.5 px-7 py-3 rounded-full bg-[#38BDF8] hover:bg-[#0284C7] active:scale-95 text-[#070B12] hover:text-white font-bold text-[12.5px] tracking-wider uppercase transition-all duration-200 shadow-lg shadow-[#38BDF8]/25 hover:shadow-[#38BDF8]/40"
            >
                <span>Start Watching</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>
        </div>

        {{-- 4 Faded Feature Cards --}}
        <div class="relative z-10 w-full max-w-5xl mx-auto mt-10 mb-2 opacity-70">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">

                <div class="py-4 px-3 rounded-2xl bg-[#0e1626]/70 border border-[#1e2d48]/60 flex flex-col items-center text-center">
                    <div class="w-10 h-10 rounded-xl bg-[#1a2740]/70 border border-[#26385a]/70 flex items-center justify-center mb-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                    </div>
                    <h3 class="text-[12.5px] font-bold text-white mb-1">Report Incident</h3>
                    <p class="text-[10.5px] text-slate-400 leading-relaxed max-w-[160px]">
                        Quickly submit a report about unusual activity in your area.
                    </p>
                </div>

                <div class="py-4 px-3 rounded-2xl bg-[#0e1626]/70 border border-[#1e2d48]/60 flex flex-col items-center text-center">
                    <div class="w-10 h-10 rounded-xl bg-[#1a2740]/70 border border-[#26385a]/70 flex items-center justify-center mb-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <h3 class="text-[12.5px] font-bold text-white mb-1">Track Progress</h3>
                    <p class="text-[10.5px] text-slate-400 leading-relaxed max-w-[160px]">
                        Monitor the status of your report as it's investigated and resolved.
                    </p>
                </div>

                <div class="py-4 px-3 rounded-2xl bg-[#0e1626]/70 border border-[#1e2d48]/60 flex flex-col items-center text-center">
                    <div class="w-10 h-10 rounded-xl bg-[#1a2740]/70 border border-[#26385a]/70 flex items-center justify-center mb-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h3 class="text-[12.5px] font-bold text-white mb-1">Find Safe Zones</h3>
                    <p class="text-[10.5px] text-slate-400 leading-relaxed max-w-[160px]">
                        Locate verified safe zones nearby when you need assistance.
                    </p>
                </div>

                <div class="py-4 px-3 rounded-2xl bg-[#0e1626]/70 border border-[#1e2d48]/60 flex flex-col items-center text-center">
                    <div class="w-10 h-10 rounded-xl bg-[#1a2740]/70 border border-[#26385a]/70 flex items-center justify-center mb-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 text-[#38BDF8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <h3 class="text-[12.5px] font-bold text-white mb-1">Stay Informed</h3>
                    <p class="text-[10.5px] text-slate-400 leading-relaxed max-w-[160px]">
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

<div style="display:none;" aria-hidden="true">
    <div id="spectral-map" style="width:0;height:0;"></div>
    <div id="location-picker-hud"></div>
    <div id="map-tile-error"></div>
    <span class="incident-code-test">SF-INC-001</span>
</div>

<script>
    window.INITIAL_SPECTRAL_STATE = {
        incidents: @json($incidents),
        wards: @json($wardStations),
        safeWardStations: @json($safeWardStations),
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