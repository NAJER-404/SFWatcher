@extends('layouts.app')

@section('title', 'Incident Reports — Spectra')

@section('content')
@include('spectral.partials.sidebar')
<main class="flex-1 h-full overflow-y-auto bg-[#0B0F14] p-3.5 sm:p-6">
    <div class="max-w-7xl mx-auto space-y-5 sm:space-y-6">

        <!-- Top Navigation & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#2A3440]">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('spectral.dashboard') }}" class="text-xs font-mono text-[#8B5CF6] hover:underline inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        <span>Return to Map</span>
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-xs font-mono text-slate-400">Registry</span>
                </div>
                <h1 class="text-lg sm:text-xl font-bold text-white mt-1">Incident Reports</h1>
                <p class="text-xs text-[#9CA3AF]">San Francisco, Agusan del Sur</p>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('spectral.incidents.index') }}" class="p-3.5 sm:p-4 rounded-xl bg-[#151B23] border border-[#2A3440] grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Severity</label>
                <select name="severity" onchange="this.form.submit()" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none">
                    <option class="bg-[#151B23] text-slate-200" value="ALL">All Severities</option>
                    <option class="bg-[#151B23] text-slate-200" value="LOW" {{ request('severity') == 'LOW' ? 'selected' : '' }}>LOW</option>
                    <option class="bg-[#151B23] text-slate-200" value="MEDIUM" {{ request('severity') == 'MEDIUM' ? 'selected' : '' }}>MEDIUM</option>
                    <option class="bg-[#151B23] text-slate-200" value="HIGH" {{ request('severity') == 'HIGH' ? 'selected' : '' }}>HIGH</option>
                    <option class="bg-[#151B23] text-slate-200" value="CRITICAL" {{ request('severity') == 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none">
                    <option class="bg-[#151B23] text-slate-200" value="ALL">All Statuses</option>
                    <option class="bg-[#151B23] text-slate-200" value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                    <option class="bg-[#151B23] text-slate-200" value="UNDER INVESTIGATION" {{ request('status') == 'UNDER INVESTIGATION' ? 'selected' : '' }}>UNDER INVESTIGATION</option>
                    <option class="bg-[#151B23] text-slate-200" value="VERIFIED" {{ request('status') == 'VERIFIED' ? 'selected' : '' }}>VERIFIED</option>
                    <option class="bg-[#151B23] text-slate-200" value="RESOLVED" {{ request('status') == 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Barangay</label>
                <select name="barangay_id" onchange="this.form.submit()" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none">
                    <option class="bg-[#151B23] text-slate-200" value="">All Barangays</option>
                    @foreach($barangays as $b)
                        <option class="bg-[#151B23] text-slate-200" value="{{ $b->id }}" {{ request('barangay_id') == $b->id ? 'selected' : '' }}>
                            Brgy. {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <a href="{{ route('spectral.incidents.index') }}" class="w-full py-2 bg-[#1B222C] hover:bg-[#222B38] text-slate-300 font-bold rounded-lg text-center transition">
                    Reset Filters
                </a>
            </div>
        </form>

        <!-- Incidents List (Mobile Cards: sm:hidden) -->
        <div class="block sm:hidden space-y-3">
            @forelse($incidents as $inc)
            <div class="p-3.5 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-2.5 shadow-lg">
                <div class="flex items-center justify-between">
                    <a href="{{ route('spectral.incidents.show', $inc->id) }}" class="text-xs font-mono font-bold text-[#8B5CF6] hover:underline">
                        {{ $inc->incident_code }}
                    </a>
                    <div class="flex items-center gap-1.5">
                        <span class="badge-{{ strtolower($inc->severity) }} px-2 py-0.5 rounded-full text-[9px] font-bold font-mono">
                            {{ $inc->severity }}
                        </span>
                        <span class="{{ $inc->status_badge_class }} px-2 py-0.5 rounded-full text-[9px] font-bold font-mono">
                            {{ $inc->status }}
                        </span>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold text-white leading-snug">{{ $inc->title }}</h3>
                    <p class="text-[10px] text-[#9CA3AF] mt-0.5 font-mono">{{ $inc->incident_type }}</p>
                </div>

                <div class="flex items-center justify-between text-[10px] font-mono text-[#64748B] pt-2 border-t border-[#1E2631]">
                    <span class="truncate">{{ $inc->barangay->name ?? 'San Francisco' }}</span>
                    <span>{{ $inc->incident_date ? $inc->incident_date->format('M d, H:i') : $inc->created_at->format('M d, H:i') }}</span>
                </div>

                <div class="pt-1">
                    <a href="{{ route('spectral.incidents.show', $inc->id) }}" class="w-full py-2 bg-[#1B222C] hover:bg-[#8B5CF6] hover:text-white text-slate-200 font-bold rounded-lg transition border border-[#2A3440] flex items-center justify-center gap-1.5 text-xs font-mono">
                        <span>Inspect Incident</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-500 font-mono text-xs rounded-xl bg-[#151B23] border border-[#2A3440]">
                No supernatural incidents matching your filter criteria.
            </div>
            @endforelse
        </div>

        <!-- Incidents Table (Tablet & Desktop: hidden sm:block) -->
        <div class="hidden sm:block rounded-xl border border-[#2A3440] bg-[#151B23] overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#11161D] text-[#64748B] font-mono uppercase text-[10px] border-b border-[#2A3440]">
                        <tr>
                            <th class="p-3.5">Code</th>
                            <th class="p-3.5">Incident Title & Type</th>
                            <th class="p-3.5">Barangay</th>
                            <th class="p-3.5">Coordinates</th>
                            <th class="p-3.5">Severity</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5">Reported</th>
                            <th class="p-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1E2631]">
                        @forelse($incidents as $inc)
                            <tr class="hover:bg-[#1B222C]/70 transition">
                                <td class="p-3.5 font-mono font-bold text-[#8B5CF6]">
                                    <a href="{{ route('spectral.incidents.show', $inc->id) }}" class="hover:underline">
                                        {{ $inc->incident_code }}
                                    </a>
                                </td>
                                <td class="p-3.5">
                                    <p class="font-bold text-white">{{ $inc->title }}</p>
                                    <p class="text-[11px] text-[#9CA3AF]">{{ $inc->incident_type }}</p>
                                </td>
                                <td class="p-3.5 text-slate-300 font-medium">{{ $inc->barangay->name ?? 'San Francisco' }}</td>
                                <td class="p-3.5 font-mono text-[11px] text-slate-400">{{ number_format($inc->latitude, 4) }}, {{ number_format($inc->longitude, 4) }}</td>
                                <td class="p-3.5">
                                    <span class="badge-{{ strtolower($inc->severity) }} px-2 py-0.5 rounded-full text-[10px] font-bold font-mono">
                                        {{ $inc->severity }}
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    <span class="{{ $inc->status_badge_class }} px-2 py-0.5 rounded-full text-[10px] font-bold font-mono">
                                        {{ $inc->status }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-400 font-mono text-[11px]">
                                    {{ $inc->incident_date ? $inc->incident_date->format('Y-m-d H:i') : $inc->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="p-3.5 text-right">
                                    <a href="{{ route('spectral.incidents.show', $inc->id) }}" class="px-3 py-1.5 bg-[#1B222C] hover:bg-[#8B5CF6] hover:text-white text-slate-200 font-bold rounded-lg transition border border-[#2A3440] inline-flex items-center gap-1.5 text-xs font-mono">
                                        <span>Inspect</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-500 font-mono">
                                    No supernatural incidents matching your filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($incidents->hasPages())
                <div class="p-4 border-t border-[#2A3440] bg-[#11161D]">
                    {{ $incidents->links() }}
                </div>
            @endif
        </div>

    </div>
</main>
@endsection
