@extends('layouts.app')

@section('title', 'Equipment — Spectra')

@section('content')
<div class="flex-1 overflow-y-auto p-6 bg-[#0B0F14] space-y-6 max-w-6xl mx-auto w-full">

    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-[#2A3440]">
        <div>
            <a href="{{ route('spectral.dashboard') }}" class="text-xs font-mono text-[#8B5CF6] hover:underline">&larr; Return to Map</a>
            <h1 class="text-xl font-bold text-white mt-1">Equipment Catalog</h1>
            <p class="text-xs text-[#9CA3AF]">San Francisco, Agusan del Sur</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-semibold bg-[#8B5CF6]/10 text-[#8B5CF6] border border-[#8B5CF6]/30">
                Inventory
            </span>
        </div>
    </div>

    <!-- Equipment Catalog Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($equipment as $eq)
            <div class="p-5 rounded-2xl bg-[#151B23] border border-[#2A3440] hover:border-[#8B5CF6]/40 space-y-4 shadow-xl transition flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#8B5CF6]">{{ $eq->category }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                            {{ str_replace('_', ' ', $eq->status) }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-sm font-extrabold text-white">{{ $eq->name }}</h3>
                        <p class="text-xs text-[#9CA3AF] mt-1 leading-relaxed">{{ $eq->description }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-[#1E2631] flex items-center justify-between text-xs font-mono">
                    <div>
                        <span class="text-[9px] text-[#64748B] uppercase block">Price</span>
                        <span class="text-sm font-extrabold text-[#FACC15]">₱{{ number_format($eq->price, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] text-[#64748B] uppercase block text-right">Available Stock</span>
                        <span class="text-sm font-extrabold text-white text-right block">{{ $eq->stock }} Units</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
