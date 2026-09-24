@extends('layouts.investigator')

@section('title', 'Safe Ward Stations — Investigator Portal')

@section('content')
<!-- INVESTIGATOR SIDEBAR -->
@include('investigator.partials.sidebar')

<!-- SAFE WARD STATIONS WORKSPACE -->
<main class="flex-1 overflow-y-auto p-5 md:p-7 bg-[#0B0F14] space-y-6 max-w-7xl mx-auto w-full">

    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-[#2A3440]">
        <div>
            <h1 class="text-lg font-bold text-white font-mono uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#22C55E]"></span>
                <span>Safe Ward Stations &amp; Sanctuaries</span>
            </h1>
            <p class="text-xs text-[#9CA3AF]">Designated fortified shelters and spiritual protection perimeters</p>
        </div>
        <span class="text-xs font-mono px-3 py-1 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-bold">
            Operational
        </span>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-3 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold text-[#22C55E] uppercase">SAFE WARD STATION #01 &bull; Active</span>
                <span class="text-xs font-mono text-slate-400 font-bold">Capacity: 2,500</span>
            </div>
            <h3 class="text-base font-bold text-white">San Francisco Municipal Gymnasium Sanctuary</h3>
            <p class="text-xs text-slate-300">Reinforced municipal sports complex equipped with secondary Class-3 ectoplasmic displacement wards.</p>
            <div class="pt-2 border-t border-[#1E2631] flex items-center justify-between text-xs font-mono text-[#64748B]">
                <span>Barangay 1, San Francisco</span>
                <span class="text-slate-400">8.5098° N, 125.9780° E</span>
            </div>
        </div>

        <div class="p-5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-3 shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold text-[#22C55E] uppercase">SAFE WARD STATION #02 &bull; Active</span>
                <span class="text-xs font-mono text-slate-400 font-bold">Capacity: 1,200</span>
            </div>
            <h3 class="text-base font-bold text-white">Hubang Transport Safe Haven</h3>
            <p class="text-xs text-slate-300">Central transit hub secure perimeter with backup divine resonance generators and evacuation staging area.</p>
            <div class="pt-2 border-t border-[#1E2631] flex items-center justify-between text-xs font-mono text-[#64748B]">
                <span>Hubang, San Francisco</span>
                <span class="text-slate-400">8.5310° N, 125.9730° E</span>
            </div>
        </div>
    </div>

</main>
@endsection
