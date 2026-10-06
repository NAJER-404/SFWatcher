@extends('layouts.app')

@section('title', 'Ward Stations — Spectra')

@section('content')
<div class="flex-1 overflow-y-auto p-3.5 sm:p-6 bg-[#0B0F14] space-y-5 sm:space-y-6 max-w-6xl mx-auto w-full">

    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-[#2A3440]">
        <div>
            <a href="{{ route('spectral.dashboard') }}" class="text-xs font-mono text-[#8B5CF6] hover:underline inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Return to Map</span>
            </a>
            <h1 class="text-xl font-bold text-white mt-1">Ward Stations</h1>
            <p class="text-xs text-[#9CA3AF]">San Francisco, Agusan del Sur</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                Active Stations
            </span>
        </div>
    </div>

    <!-- Ward Stations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($wardStations as $w)
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] hover:border-[#8B5CF6]/50 space-y-4 shadow-xl transition flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono font-bold text-[#8B5CF6]">{{ $w->code }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase {{ $w->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : ($w->status === 'degraded' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30') }}">
                            {{ $w->status }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-sm font-extrabold text-white">{{ $w->name }}</h3>
                        <p class="text-xs text-[#9CA3AF]">Brgy. {{ $w->barangay->name ?? 'San Francisco' }}</p>
                    </div>

                    <!-- Meters -->
                    <div class="space-y-2 pt-2 border-t border-[#1E2631] text-xs">
                        <div>
                            <div class="flex justify-between text-[11px] font-mono text-slate-300 mb-1">
                                <span>Shield Integrity</span>
                                <span class="font-bold text-emerald-400">{{ $w->shield_level }}%</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-[#11161D] overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $w->shield_level }}%;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-[11px] font-mono text-slate-300 mb-1">
                                <span>Resonance Power</span>
                                <span class="font-bold text-[#8B5CF6]">{{ $w->energy_level }}%</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-[#11161D] overflow-hidden">
                                <div class="h-full bg-[#8B5CF6] rounded-full" style="width: {{ $w->energy_level }}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] font-mono text-[10px] space-y-1 text-slate-400">
                    <div class="flex justify-between">
                        <span>Frequency:</span>
                        <span class="text-slate-200">{{ $w->frequency }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Barrier Radius:</span>
                        <span class="text-slate-200">{{ $w->radius_meters }} m</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Coordinates:</span>
                        <span class="text-slate-200">{{ number_format($w->latitude, 4) }}, {{ number_format($w->longitude, 4) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
