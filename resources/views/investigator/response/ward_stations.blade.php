@extends('layouts.investigator')

@section('title', 'Ward Stations Response — Investigator Portal')

@section('content')
<!-- INVESTIGATOR SIDEBAR -->
@include('investigator.partials.sidebar')

<!-- WARDS WORKSPACE -->
<main class="flex-1 overflow-y-auto p-3.5 sm:p-5 md:p-7 bg-[#0B0F14] space-y-5 sm:space-y-6 max-w-7xl mx-auto w-full">

    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-[#2A3440]">
        <div>
            <h1 class="text-lg font-bold text-white font-mono uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#8B5CF6]"></span>
                <span>Spirit Ward Stations</span>
            </h1>
            <p class="text-xs text-[#9CA3AF]">Field barrier emitters and spectral defense anchor nodes</p>
        </div>
        <span class="text-xs font-mono px-3 py-1 rounded bg-[#8B5CF6]/10 text-[#A78BFA] border border-[#8B5CF6]/30 font-bold">
            Total Stations: {{ $wardStations->count() }}
        </span>
    </div>

    <!-- Stations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($wardStations as $w)
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-3 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold text-[#8B5CF6] uppercase">{{ $w->code }}</span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded font-bold
                    {{ $w->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30' }}">
                    {{ strtoupper($w->status) }}
                </span>
            </div>
            <h3 class="text-sm font-bold text-white">{{ $w->name }}</h3>
            <p class="text-xs text-slate-300">Brgy. {{ $w->barangay->name ?? 'San Francisco' }}</p>

            <div class="space-y-1 pt-2 border-t border-[#1E2631] text-xs font-mono">
                <div class="flex justify-between text-slate-400">
                    <span>Shield Integrity:</span>
                    <span class="font-bold text-slate-200">{{ $w->shield_level }}%</span>
                </div>
                <div class="w-full bg-[#11161D] rounded-full h-1.5 overflow-hidden">
                    <div class="bg-[#8B5CF6] h-1.5 rounded-full" style="width: {{ $w->shield_level }}%"></div>
                </div>
                <div class="flex justify-between text-slate-400 pt-1">
                    <span>Frequency:</span>
                    <span class="text-[#8B5CF6]">{{ $w->frequency }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</main>
@endsection
