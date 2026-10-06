@extends('layouts.investigator')

@section('title', 'Resources Response — Investigator Portal')

@section('content')
<!-- INVESTIGATOR SIDEBAR -->
@include('investigator.partials.sidebar')

<!-- RESOURCES WORKSPACE -->
<main class="flex-1 overflow-y-auto p-3.5 sm:p-5 md:p-7 bg-[#0B0F14] space-y-5 sm:space-y-6 max-w-7xl mx-auto w-full">

    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-[#2A3440]">
        <div>
            <h1 class="text-lg font-bold text-white font-mono uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#38BDF8]"></span>
                <span>Spectral Resources &amp; Ectoplasm Wells</span>
            </h1>
            <p class="text-xs text-[#9CA3AF]">Tactical energy nodes and purification reserves for supernatural containment</p>
        </div>
        <span class="text-xs font-mono px-3 py-1 rounded bg-[#38BDF8]/10 text-[#38BDF8] border border-[#38BDF8]/30 font-bold">
            Total Nodes: {{ $resources->count() }}
        </span>
    </div>

    <!-- Resources Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($resources as $r)
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-3 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold text-[#38BDF8] uppercase">{{ $r->resource_type }}</span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                    {{ strtoupper($r->status) }}
                </span>
            </div>
            <h3 class="text-sm font-bold text-white">{{ $r->name }}</h3>
            <p class="text-xs text-slate-300">Brgy. {{ $r->barangay->name ?? 'San Francisco' }}</p>

            <div class="space-y-1.5 pt-2 border-t border-[#1E2631] text-xs font-mono">
                <div class="flex justify-between text-slate-400">
                    <span>Reserve Volume:</span>
                    <span class="font-bold text-white">{{ $r->quantity }} {{ $r->unit }}</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Extraction Yield:</span>
                    <span class="text-[#38BDF8]">{{ $r->yield_rate }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</main>
@endsection
