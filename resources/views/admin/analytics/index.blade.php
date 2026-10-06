@extends('layouts.admin')

@section('title', 'System Analytics & Performance — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-6 max-w-7xl mx-auto w-full font-mono">

    <!-- Header + Timeframe Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#8B5CF6]">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 font-bold">Analytics &amp; Statistics</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">System Statistics &amp; Telemetry</h1>
            <p class="text-xs text-slate-400 mt-0.5">Empirical performance benchmarks derived from database records.</p>
        </div>

        <!-- Modern Timeframe Filter Buttons -->
        <div class="inline-flex flex-wrap items-center p-1 rounded-xl bg-[#151B23] border border-[#2A3440] text-xs">
            <a href="{{ route('admin.analytics', ['timeframe' => 'all']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $timeframe === 'all' ? 'bg-[#8B5CF6] text-white font-bold shadow-md shadow-purple-900/30' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
                All Time
            </a>
            <a href="{{ route('admin.analytics', ['timeframe' => '7d']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $timeframe === '7d' ? 'bg-[#8B5CF6] text-white font-bold shadow-md shadow-purple-900/30' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
                7 Days
            </a>
            <a href="{{ route('admin.analytics', ['timeframe' => '30d']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $timeframe === '30d' ? 'bg-[#8B5CF6] text-white font-bold shadow-md shadow-purple-900/30' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
                30 Days
            </a>
            <a href="{{ route('admin.analytics', ['timeframe' => '90d']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $timeframe === '90d' ? 'bg-[#8B5CF6] text-white font-bold shadow-md shadow-purple-900/30' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
                90 Days
            </a>
            <a href="{{ route('admin.analytics', ['timeframe' => '1y']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $timeframe === '1y' ? 'bg-[#8B5CF6] text-white font-bold shadow-md shadow-purple-900/30' : 'text-slate-400 hover:text-white hover:bg-[#1B222C]' }}">
                1 Year
            </a>
        </div>
    </div>

    <!-- 1. SYSTEM PERFORMANCE KPIS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- Incident Resolution Rate -->
        <div class="p-4 sm:p-5 rounded-xl bg-[#151B23] border border-[#2A3440]">
            <span class="text-[10px] sm:text-xs text-slate-400 uppercase font-semibold block">Incident Resolution Rate</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-emerald-400">{{ $resolutionRate }}%</span>
                <span class="text-[10px] text-slate-500">{{ $resolvedTotal }}/{{ $totalIncidents }}</span>
            </div>
            <div class="w-full h-1.5 bg-[#11161D] rounded-full overflow-hidden mt-3">
                <div class="h-full bg-emerald-500" style="width: {{ $resolutionRate }}%;"></div>
            </div>
        </div>

        <!-- Average Investigation Time -->
        <div class="p-4 sm:p-5 rounded-xl bg-[#151B23] border border-[#2A3440]">
            <span class="text-[10px] sm:text-xs text-slate-400 uppercase font-semibold block">Avg Investigation Time</span>
            <div class="mt-2 flex items-baseline justify-between">
                @if($avgInvestigationMinutes !== null)
                    <span class="text-2xl sm:text-3xl font-extrabold text-sky-400">
                        {{ $avgInvestigationMinutes >= 60 ? round($avgInvestigationMinutes / 60, 1) . ' hrs' : $avgInvestigationMinutes . ' mins' }}
                    </span>
                    <span class="text-[10px] text-slate-500">elapsed</span>
                @else
                    <span class="text-lg font-bold text-slate-500">Unavailable</span>
                    <span class="text-[10px] text-slate-600">no data</span>
                @endif
            </div>
            <p class="text-[10px] text-slate-500 mt-3">From report to assessment</p>
        </div>

        <!-- Average Response Time -->
        <div class="p-4 sm:p-5 rounded-xl bg-[#151B23] border border-[#2A3440]">
            <span class="text-[10px] sm:text-xs text-slate-400 uppercase font-semibold block">Avg Response Containment</span>
            <div class="mt-2 flex items-baseline justify-between">
                @if($avgResponseMinutes !== null)
                    <span class="text-2xl sm:text-3xl font-extrabold text-purple-400">
                        {{ $avgResponseMinutes >= 60 ? round($avgResponseMinutes / 60, 1) . ' hrs' : $avgResponseMinutes . ' mins' }}
                    </span>
                    <span class="text-[10px] text-slate-500">elapsed</span>
                @else
                    <span class="text-lg font-bold text-slate-500">Unavailable</span>
                    <span class="text-[10px] text-slate-600">no data</span>
                @endif
            </div>
            <p class="text-[10px] text-slate-500 mt-3">From dispatch to complete</p>
        </div>

        <!-- Total Escalated Incidents -->
        <div class="p-4 sm:p-5 rounded-xl bg-[#151B23] border border-[#2A3440]">
            <span class="text-[10px] sm:text-xs text-slate-400 uppercase font-semibold block">Critical Threats</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-extrabold text-rose-400">{{ $escalatedIncidents }}</span>
                <span class="text-[10px] text-rose-500/80">critical</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-3">Severity A or escalated</p>
        </div>
    </div>

    <!-- 2-Column Analytics: Incident Telemetry & User Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">

        <!-- INCIDENT ANALYTICS -->
        <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-5">
            <div class="border-b border-[#2A3440] pb-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">INCIDENT ANALYTICS</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Distribution by threat severity and lifecycle state.</p>
            </div>

            <!-- Severity Breakdown -->
            <div class="space-y-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Incidents by Severity</span>
                @foreach($severityCounts as $sev => $cnt)
                    @php
                        $pct = $totalIncidents > 0 ? round(($cnt / $totalIncidents) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-[11px]">
                            <span class="font-bold text-slate-300">{{ $sev }}</span>
                            <span class="text-slate-400">{{ $cnt }} incidents ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-[#11161D] overflow-hidden border border-[#2A3440]">
                            <div class="h-full
                                @if($sev === 'CRITICAL') bg-rose-500
                                @elseif($sev === 'HIGH') bg-orange-500
                                @elseif($sev === 'MEDIUM') bg-amber-500
                                @else bg-emerald-500
                                @endif"
                                style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Lifecycle Status Breakdown -->
            <div class="space-y-3 border-t border-[#2A3440] pt-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Incidents by Status</span>
                @foreach($statusCounts as $st => $cnt)
                    @php
                        $pct = $totalIncidents > 0 ? round(($cnt / $totalIncidents) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-[11px]">
                            <span class="font-bold text-slate-300">{{ $st }}</span>
                            <span class="text-slate-400">{{ $cnt }} incidents ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-[#11161D] overflow-hidden border border-[#2A3440]">
                            <div class="h-full
                                @if($st === 'RESOLVED') bg-emerald-500
                                @elseif($st === 'VERIFIED') bg-blue-500
                                @elseif($st === 'UNDER INVESTIGATION') bg-sky-500
                                @else bg-amber-500
                                @endif"
                                style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Map Visibility Comparison: Archived vs Visible Resolved -->
            <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-2">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Map Visibility of Resolved Anomalies</span>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-2.5 rounded-lg bg-[#151B23] border border-emerald-500/30">
                        <span class="text-[10px] text-emerald-400 block font-bold">Active on Field Maps</span>
                        <span class="text-xl font-bold text-white block mt-0.5">{{ $visibleResolved }}</span>
                    </div>
                    <div class="p-2.5 rounded-lg bg-[#151B23] border border-amber-500/30">
                        <span class="text-[10px] text-amber-400 block font-bold">Archived from Map</span>
                        <span class="text-xl font-bold text-white block mt-0.5">{{ $archivedResolved }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- USER & RESPONDER ANALYTICS -->
        <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-5">
            <div class="border-b border-[#2A3440] pb-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">USER &amp; RESPONDER ANALYTICS</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Personnel deployment, class tier breakdown, and experience levels.</p>
            </div>

            <!-- User Role Distribution -->
            <div class="space-y-3">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Users by System Role</span>
                @foreach($userRoleBreakdown as $roleName => $cnt)
                    @php
                        $pct = $totalUsers > 0 ? round(($cnt / $totalUsers) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-[11px]">
                            <span class="font-bold text-slate-300">{{ $roleName }}</span>
                            <span class="text-slate-400">{{ $cnt }} users ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-[#11161D] overflow-hidden border border-[#2A3440]">
                            <div class="h-full
                                @if($roleName === 'Admins') bg-amber-500
                                @elseif($roleName === 'Investigators') bg-purple-500
                                @elseif($roleName === 'Responders') bg-emerald-500
                                @else bg-cyan-500
                                @endif"
                                style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Responder Class Distribution -->
            <div class="space-y-3 border-t border-[#2A3440] pt-4">
                <span class="text-[10px] font-bold text-slate-400 uppercase block">Tactical Responder Class Breakdown</span>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                    @foreach($classBreakdown as $cName => $cCount)
                        <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440]">
                            <span class="text-[10px] text-slate-400 block font-bold">{{ $cName }}</span>
                            <span class="text-lg font-bold text-white block mt-0.5">{{ $cCount }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Responder Experience & Availability Stats -->
            <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-2">
                <div class="flex justify-between text-[11px]">
                    <span class="text-slate-400">Total Responder Experience Pool:</span>
                    <span class="text-amber-400 font-bold">{{ $totalXp }} XP</span>
                </div>
                <div class="flex justify-between text-[11px]">
                    <span class="text-slate-400">Average Responder XP:</span>
                    <span class="text-white font-bold">{{ $avgXp }} XP / responder</span>
                </div>
                <div class="flex justify-between text-[11px]">
                    <span class="text-slate-400">Available Responders:</span>
                    <span class="text-emerald-400 font-bold">{{ $availabilityBreakdown['Available'] }} Available</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. MONTHLY REPORT & RESOLUTION TRENDS -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-[#2A3440] pb-3">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wider text-white">HISTORICAL MONTHLY TRENDS</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Real incident filings versus resolved outcomes over the last 6 months.</p>
            </div>
            <span class="text-[10px] text-slate-500">6-Month Window</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($monthlyTrends as $monthLabel => $mData)
                <div class="p-3.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-center space-y-2">
                    <span class="text-xs font-bold text-white block">{{ $monthLabel }}</span>
                    <div class="space-y-1">
                        <div class="flex justify-between text-[10px] text-slate-400">
                            <span>Logged:</span>
                            <span class="font-bold text-cyan-400">{{ $mData['total'] }}</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-400">
                            <span>Resolved:</span>
                            <span class="font-bold text-emerald-400">{{ $mData['resolved'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</main>
@endsection
