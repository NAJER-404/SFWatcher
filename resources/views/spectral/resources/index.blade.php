@extends('layouts.app')

@section('title', 'Resources — Spectra')

@section('content')
<div class="flex-1 overflow-y-auto p-6 bg-[#0B0F14] space-y-6 max-w-6xl mx-auto w-full">

    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-[#2A3440]">
        <div>
            <a href="{{ route('spectral.dashboard') }}" class="text-xs font-mono text-[#8B5CF6] hover:underline inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                <span>Return to Map</span>
            </a>
            <h1 class="text-xl font-bold text-white mt-1">Resource Deposits</h1>
            <p class="text-xs text-[#9CA3AF]">San Francisco, Agusan del Sur</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-semibold bg-[#38BDF8]/10 text-[#38BDF8] border border-[#38BDF8]/30">
                Reserves
            </span>
        </div>
    </div>

    <!-- Resources Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($resources as $r)
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] hover:border-[#38BDF8]/40 space-y-4 shadow-xl transition flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#38BDF8]">{{ $r->resource_type }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                            {{ $r->status }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-sm font-extrabold text-white">{{ $r->name }}</h3>
                        <p class="text-xs text-[#9CA3AF]">Brgy. {{ $r->barangay->name ?? 'San Francisco' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[#1E2631] text-xs">
                        <div class="p-2.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[9px] font-mono text-[#64748B] uppercase">Current Quantity</span>
                            <p class="text-base font-extrabold text-white font-mono mt-0.5">{{ $r->quantity }} {{ $r->unit }}</p>
                        </div>
                        <div class="p-2.5 rounded-xl bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[9px] font-mono text-[#64748B] uppercase">Resonance Purity</span>
                            <p class="text-base font-extrabold text-[#8B5CF6] font-mono mt-0.5">{{ $r->purity }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] font-mono text-[10px] space-y-1 text-slate-400">
                    <div class="flex justify-between">
                        <span>Harvest Yield:</span>
                        <span class="text-slate-200">{{ $r->yield_rate ?? 'Standard' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Coordinates:</span>
                        <span class="text-slate-200">{{ number_format($r->latitude, 4) }}, {{ number_format($r->longitude, 4) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
