@extends('layouts.admin')

@section('title', 'Live Map — SpectraWatch Admin')

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<main class="flex-1 relative" style="min-height: calc(100vh - 3.5rem);">
    <div id="admin-map" style="position: absolute; inset: 0;"></div>

    {{-- ── TOP-LEFT: Stats Strip ──────────────────────────── --}}
    <div class="absolute top-3 left-3 right-3 sm:right-auto z-[500] flex gap-2 overflow-x-auto pb-1 sm:overflow-visible sm:flex-wrap" style="scrollbar-width: none;">
        <div class="shrink-0 bg-[#151B23]/95 backdrop-blur border border-[#2A3440] rounded-lg px-3 py-2 shadow-lg">
            <p class="text-[9px] font-mono text-slate-500 uppercase tracking-wider">Active</p>
            <p class="text-lg font-extrabold text-[#EF4444] leading-none">{{ str_pad($stats['active_incidents'], 2, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="shrink-0 bg-[#151B23]/95 backdrop-blur border border-[#2A3440] rounded-lg px-3 py-2 shadow-lg">
            <p class="text-[9px] font-mono text-slate-500 uppercase tracking-wider">Critical</p>
            <p class="text-lg font-extrabold text-rose-400 leading-none">{{ str_pad($stats['critical'], 2, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="shrink-0 bg-[#151B23]/95 backdrop-blur border border-[#2A3440] rounded-lg px-3 py-2 shadow-lg">
            <p class="text-[9px] font-mono text-slate-500 uppercase tracking-wider">High</p>
            <p class="text-lg font-extrabold text-orange-400 leading-none">{{ str_pad($stats['high'], 2, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="shrink-0 bg-[#151B23]/95 backdrop-blur border border-[#2A3440] rounded-lg px-3 py-2 shadow-lg">
            <p class="text-[9px] font-mono text-slate-500 uppercase tracking-wider">Resolved</p>
            <p class="text-lg font-extrabold text-emerald-400 leading-none">{{ str_pad($stats['resolved'], 2, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="shrink-0 bg-[#151B23]/95 backdrop-blur border border-[#2A3440] rounded-lg px-3 py-2 shadow-lg">
            <p class="text-[9px] font-mono text-slate-500 uppercase tracking-wider">Safe Zones</p>
            <p class="text-lg font-extrabold text-[#2DD4BF] leading-none">{{ str_pad($stats['safe_zones'], 2, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="shrink-0 bg-[#151B23]/95 backdrop-blur border border-[#2A3440] rounded-lg px-3 py-2 shadow-lg">
            <p class="text-[9px] font-mono text-slate-500 uppercase tracking-wider">Resp. Ready</p>
            <p class="text-lg font-extrabold text-cyan-400 leading-none">{{ str_pad($stats['responders_available'], 2, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>

    {{-- ── RIGHT PANEL ──────────────────────────── --}}
    <div class="absolute top-20 sm:top-3 right-3 left-3 sm:left-auto z-[500] sm:w-80 space-y-2 max-h-[calc(100vh-6rem)] sm:max-h-[calc(100vh-1.5rem)] overflow-y-auto">

        {{-- Create Safe Zone Button --}}
        <button type="button" onclick="openSafeZoneModal()"
                class="w-full flex items-center justify-between gap-2 px-4 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-[12px] transition shadow-lg shadow-emerald-500/30">
            <span class="flex items-center gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Create Safe Zone
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>

        {{-- Layers + Filters --}}
        <details open class="group bg-[#151B23]/95 backdrop-blur border border-[#2A3440] rounded-xl shadow-lg overflow-hidden">
            <summary class="flex cursor-pointer list-none items-center justify-between px-4 py-3 bg-[#11161D] hover:bg-[#151B23] transition [&::-webkit-details-marker]:hidden">
                <span class="text-[11px] font-bold text-white flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filters &amp; Layers
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 group-open:rotate-180 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </summary>

            <div class="border-t border-[#2A3440]">
                <div class="p-3 border-b border-[#2A3440]">
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-2">Layers</p>
                    <div class="space-y-1 text-xs">
                        <label class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg hover:bg-[#1B222C] cursor-pointer">
                            <span class="flex items-center gap-2">
                                <input type="checkbox" id="lyr-incidents" checked onchange="toggleAdminLayer('incidents')" class="rounded accent-[#EF4444] cursor-pointer">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span>
                                <span class="text-slate-200">Incidents</span>
                            </span>
                            <span class="text-[#EF4444] font-bold font-mono text-[10px]" id="lyr-count-incidents">{{ $stats['total_incidents'] }}</span>
                        </label>
                        <label class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-lg hover:bg-[#1B222C] cursor-pointer">
                            <span class="flex items-center gap-2">
                                <input type="checkbox" id="lyr-safeZones" checked onchange="toggleAdminLayer('safeZones')" class="rounded accent-[#22C55E] cursor-pointer">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#22C55E]"></span>
                                <span class="text-slate-200">Safe Zones</span>
                            </span>
                            <span class="text-[#22C55E] font-bold font-mono text-[10px]" id="lyr-count-safeZones">{{ $stats['safe_zones'] }}</span>
                        </label>
                    </div>
                </div>

                <div class="p-3 space-y-3">
                    <div class="relative">
                        <input type="text" id="map-search" placeholder="Search code, title, barangay..." oninput="applyFilters()"
                               class="w-full pl-8 pr-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-xs text-white placeholder-slate-500 focus:border-[#8B5CF6] focus:outline-none transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-500 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>

                    <div>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-2">Status</p>
                        <div class="flex flex-wrap gap-1.5" id="status-pills">
                            <button type="button" data-status="ALL" onclick="setStatusFilter('ALL')" class="status-pill px-2.5 py-1 rounded-lg bg-[#8B5CF6] text-white text-[10px] font-bold transition">All <span class="ml-1 font-mono opacity-80">{{ $stats['total_incidents'] }}</span></button>
                            <button type="button" data-status="ACTIVE" onclick="setStatusFilter('ACTIVE')" class="status-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-red-300 hover:border-red-500/50 text-[10px] font-bold transition">Active <span class="ml-1 font-mono opacity-70">{{ $stats['active_incidents'] }}</span></button>
                            <button type="button" data-status="RESOLVED" onclick="setStatusFilter('RESOLVED')" class="status-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-emerald-300 hover:border-emerald-500/50 text-[10px] font-bold transition">Resolved <span class="ml-1 font-mono opacity-70">{{ $stats['resolved'] }}</span></button>
                            <button type="button" data-status="ARCHIVED" onclick="setStatusFilter('ARCHIVED')" class="status-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-amber-300 hover:border-amber-500/50 text-[10px] font-bold transition">Archived <span class="ml-1 font-mono opacity-70">{{ $stats['archived'] }}</span></button>
                        </div>
                    </div>

                    <div>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-2">Severity</p>
                        <div class="flex flex-wrap gap-1.5" id="severity-pills">
                            <button type="button" data-severity="ALL" onclick="setSeverityFilter('ALL')" class="severity-pill px-2.5 py-1 rounded-lg bg-[#8B5CF6] text-white text-[10px] font-bold transition">All</button>
                            <button type="button" data-severity="CRITICAL" onclick="setSeverityFilter('CRITICAL')" class="severity-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-rose-300 hover:border-rose-500/50 text-[10px] font-bold transition">Critical <span class="ml-1 font-mono opacity-70">{{ $stats['critical'] }}</span></button>
                            <button type="button" data-severity="HIGH" onclick="setSeverityFilter('HIGH')" class="severity-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-orange-300 hover:border-orange-500/50 text-[10px] font-bold transition">High <span class="ml-1 font-mono opacity-70">{{ $stats['high'] }}</span></button>
                            <button type="button" data-severity="MEDIUM" onclick="setSeverityFilter('MEDIUM')" class="severity-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-yellow-300 hover:border-yellow-500/50 text-[10px] font-bold transition">Medium</button>
                            <button type="button" data-severity="LOW" onclick="setSeverityFilter('LOW')" class="severity-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] text-green-300 hover:border-green-500/50 text-[10px] font-bold transition">Low</button>
                        </div>
                    </div>

                    <button type="button" onclick="resetAllFilters()" class="w-full py-1.5 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-white text-[10px] font-semibold transition">↺ Reset filters</button>
                </div>

                <div class="px-4 py-3 bg-[#11161D] border-t border-[#2A3440] flex items-center justify-between">
                    <span class="text-[10px] text-slate-500 uppercase tracking-wider font-bold">Showing</span>
                    <span id="filter-summary" class="text-[12px] font-bold text-white font-mono">{{ $stats['total_incidents'] }} / {{ $stats['total_incidents'] }}</span>
                </div>
            </div>
        </details>
    </div>

    {{-- Safe Zone Picking HUD --}}
    <div id="safe-zone-picker-hud" style="display: none;" class="absolute top-3 inset-x-0 mx-auto max-w-md z-[600] px-4 py-2.5 rounded-lg bg-[#1B222C] border border-emerald-500 text-white text-xs shadow-2xl flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="font-medium text-slate-100">Click on the map to place the safe zone</span>
        </div>
        <button type="button" onclick="cancelSafeZonePicking()" class="px-2 py-1 bg-[#151B23] hover:bg-[#222B38] text-[#9CA3AF] hover:text-white border border-[#2A3440] rounded text-[10px] font-semibold transition">Cancel</button>
    </div>

</main>

{{-- ═══════════════════════════════════════════════════════════════════
     SAFE ZONE CREATE MODAL
═══════════════════════════════════════════════════════════════════════ --}}
<div id="safe-zone-modal" class="hidden fixed inset-0 z-[1200] items-start justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeSafeZoneModal()"></div>

    <div class="relative w-full max-w-2xl my-4 sm:my-8 rounded-2xl bg-[#151B23] border border-[#2A3440] shadow-2xl">
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-[#2A3440] bg-[#11161D] rounded-t-2xl">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/40 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm sm:text-base font-bold text-white truncate">Create Safe Zone</h3>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 truncate">Protected area where incidents cannot be reported</p>
                </div>
            </div>
            <button type="button" onclick="closeSafeZoneModal()" class="w-8 h-8 rounded-lg hover:bg-[#1B222C] flex items-center justify-center text-slate-400 hover:text-white transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="sz-create-form" method="POST" action="{{ route('admin.safe-zones.store') }}" onsubmit="handleSafeZoneCreate(event)" class="p-4 sm:p-6 space-y-5 text-xs max-h-[80vh] overflow-y-auto">
            @csrf
            <input type="hidden" name="status" value="active">
            <input type="hidden" name="is_safe_zone" value="1">

            {{-- Location Section --}}
            <div class="rounded-xl border border-[#2A3440] bg-[#11161D] p-3.5 sm:p-4 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-semibold text-slate-200 flex items-center gap-2">
                        Safe Zone Location
                        <span id="sz-geocode-status" class="text-[10px] font-mono text-emerald-400"></span>
                    </span>
                    <button type="button" onclick="startSafeZonePicking()"
                            class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-white font-semibold text-[11px] rounded-lg transition flex items-center gap-1.5 shadow-sm shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        <span>Select on Map</span>
                    </button>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] text-slate-500 font-mono uppercase tracking-wider">Readable Address</label>
                        <span id="sz-geocode-badge" class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-slate-500/10 text-slate-400 border border-slate-500/20">Not selected</span>
                    </div>
                    <input type="text" id="sz-address" readonly placeholder="Click 'Select on Map' to pick a location..."
                           class="w-full px-3 py-2 rounded-lg bg-[#0e1319] border border-[#2A3440] text-slate-200 text-xs focus:outline-none cursor-default">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                    <div class="bg-[#0e1319] p-2.5 rounded-lg border border-[#2A3440]">
                        <span class="text-[9px] text-slate-500 block font-mono uppercase">Barangay</span>
                        <span id="sz-review-barangay" class="text-slate-300 font-medium block truncate mt-0.5">—</span>
                    </div>
                    <div class="bg-[#0e1319] p-2.5 rounded-lg border border-[#2A3440]">
                        <span class="text-[9px] text-slate-500 block font-mono uppercase">Municipality &amp; Province</span>
                        <span id="sz-review-municipality" class="text-slate-300 font-medium block truncate mt-0.5">San Francisco, Agusan del Sur</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-[#2A3440] flex flex-wrap items-center justify-between gap-2">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="sz-location-confirmed" class="w-3.5 h-3.5 rounded bg-[#11161D] border-[#2A3440] text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0">
                        <span class="text-[11px] text-slate-300 font-medium">I have reviewed and confirmed this location</span>
                    </label>
                    <span id="sz-review-coords" class="text-[10px] font-mono text-slate-500">—</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-500 font-mono">Barangay</label>
                        <select name="barangay_id" id="sz-barangay" class="w-full px-2.5 py-1.5 rounded-lg bg-[#0e1319] border border-[#2A3440] text-slate-200 text-xs focus:border-emerald-500 focus:outline-none">
                            <option value="">— Select barangay —</option>
                            @foreach($barangays as $b)
                                <option value="{{ $b->id }}">Brgy. {{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-500 font-mono">Latitude</label>
                        <input type="number" step="0.0000001" name="latitude" id="sz-lat" required placeholder="Click map to pick..."
                               class="w-full px-2.5 py-1.5 rounded-lg bg-[#0e1319] border border-[#2A3440] text-slate-200 text-xs font-mono focus:border-emerald-500 focus:outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-slate-500 font-mono">Longitude</label>
                        <input type="number" step="0.0000001" name="longitude" id="sz-lng" required placeholder="Click map to pick..."
                               class="w-full px-2.5 py-1.5 rounded-lg bg-[#0e1319] border border-[#2A3440] text-slate-200 text-xs font-mono focus:border-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>

            {{-- Basic Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Name <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. San Francisco Central Shelter"
                           class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Code (optional)</label>
                    <input type="text" name="code" placeholder="WS-XXX"
                           class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Radius (meters) <span class="text-rose-400">*</span></label>
                    <input type="number" name="safe_zone_radius_meters" required min="50" max="5000" value="200"
                           class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none font-mono">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Description</label>
                    <input type="text" name="description" placeholder="e.g. Regional safe haven"
                           class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none">
                </div>
            </div>

            {{-- 🕐 Ward Protection Duration — Days / Hours / Mins / Secs --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Ward Protection Duration</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">

                    <div>
                        <div class="relative">
                            <input type="number" id="sz-protection-days" name="protection_days" min="0" max="30" value="0"
                                   oninput="validateProtectionField(this, 'days')"
                                   class="w-full px-3 py-2 pr-12 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none font-mono text-sm">
                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px] text-slate-500 font-mono tracking-wider pointer-events-none">DAYS</span>
                        </div>
                        <p id="sz-days-error" class="hidden text-[10px] text-rose-400 mt-1 font-semibold">Max 30</p>
                    </div>

                    <div>
                        <div class="relative">
                            <input type="number" id="sz-protection-hours" name="protection_hours" min="0" max="24" value="0"
                                   oninput="validateProtectionField(this, 'hours')"
                                   class="w-full px-3 py-2 pr-14 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none font-mono text-sm">
                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px] text-slate-500 font-mono tracking-wider pointer-events-none">HRS</span>
                        </div>
                        <p id="sz-hours-error" class="hidden text-[10px] text-rose-400 mt-1 font-semibold">Max 24</p>
                    </div>

                    <div>
                        <div class="relative">
                            <input type="number" id="sz-protection-minutes" name="protection_minutes" min="0" max="59" value="0"
                                   oninput="validateProtectionField(this, 'minutes')"
                                   class="w-full px-3 py-2 pr-14 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none font-mono text-sm">
                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px] text-slate-500 font-mono tracking-wider pointer-events-none">MIN</span>
                        </div>
                        <p id="sz-minutes-error" class="hidden text-[10px] text-rose-400 mt-1 font-semibold">Max 59</p>
                    </div>

                    <div>
                        <div class="relative">
                            <input type="number" id="sz-protection-seconds" name="protection_seconds" min="0" max="59" value="0"
                                   oninput="validateProtectionField(this, 'seconds')"
                                   class="w-full px-3 py-2 pr-14 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none font-mono text-sm">
                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[9px] text-slate-500 font-mono tracking-wider pointer-events-none">SEC</span>
                        </div>
                        <p id="sz-seconds-error" class="hidden text-[10px] text-rose-400 mt-1 font-semibold">Max 59</p>
                    </div>
                </div>
                <p class="text-[10px] text-slate-500 mt-2 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    Set all to 0 to disable the timer. Example: 1 day 2 hrs 30 min 0 sec.
                </p>
            </div>

            {{-- Relief Supplies --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Relief Supplies</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <select name="relief_food" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none">
                        <option value="available">Food: Available</option>
                        <option value="limited">Food: Limited</option>
                        <option value="out_of_stock">Food: Out</option>
                    </select>
                    <select name="relief_water" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none">
                        <option value="available">Water: Available</option>
                        <option value="limited">Water: Limited</option>
                        <option value="out_of_stock">Water: Out</option>
                    </select>
                    <select name="relief_first_aid" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-emerald-500 focus:outline-none">
                        <option value="available">First-aid: Available</option>
                        <option value="limited">First-aid: Limited</option>
                        <option value="out_of_stock">First-aid: Out</option>
                    </select>
                </div>
            </div>

            {{-- Free Assistance --}}
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Free Assistance Services</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach(['Temporary shelter', 'Basic medical assistance', 'Evacuation support', 'Food distribution', 'Water supply', 'Counseling / support', 'Communication services', 'Child care'] as $svc)
                        <label class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] hover:border-emerald-500/50 cursor-pointer transition">
                            <input type="checkbox" name="assistance_services[]" value="{{ $svc }}" class="w-3.5 h-3.5 rounded accent-emerald-500 cursor-pointer">
                            <span class="text-slate-300">{{ $svc }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-4 border-t border-[#2A3440]">
                <button type="button" onclick="closeSafeZoneModal()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white font-bold transition">Cancel</button>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold transition shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Create Safe Zone
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══ DELETE SAFE ZONE CONFIRMATION MODAL ═══ --}}
<div id="delete-safe-zone-modal" class="hidden fixed inset-0 z-[1300] items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeDeleteSafeZoneModal()"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-[#151B23] border-2 border-rose-500/40 shadow-2xl">
        <div class="p-6 space-y-5 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-rose-500/10 border-2 border-rose-500/40 flex items-center justify-center">
                <svg class="w-7 h-7 text-rose-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white">Delete Safe Zone?</h3>
                <p class="text-sm text-slate-400 mt-2 leading-relaxed">
                    Are you sure you want to delete
                    <strong id="delete-sz-name" class="text-white"></strong>?
                    This action cannot be undone.
                </p>
            </div>
            <div class="flex flex-col-reverse sm:flex-row gap-2">
                <button type="button" onclick="closeDeleteSafeZoneModal()"
                        class="flex-1 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white font-bold transition text-sm">
                    Cancel
                </button>
                <button type="button" onclick="executeSafeZoneDelete()"
                        class="flex-1 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold transition text-sm shadow-lg shadow-rose-500/20">
                    Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══ PROCESSING OVERLAY (blocks duplicate submit) ═══ --}}
<div id="creating-overlay" class="hidden fixed inset-0 z-[1400] items-center justify-center bg-black/80 backdrop-blur-sm">
    <div id="creating-overlay-box" class="flex flex-col items-center gap-4 px-8 py-7 rounded-2xl bg-[#151B23] border border-emerald-500/40 shadow-2xl">
        <div class="relative flex items-center justify-center w-14 h-14">
            <div id="creating-overlay-spinner" class="absolute inset-0 rounded-full border-4 border-emerald-500/20 border-t-emerald-500 animate-spin"></div>
            <div id="creating-overlay-dot" class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
        </div>
        <div class="text-center">
            <p id="creating-overlay-title" class="text-sm font-bold text-white">Creating Safe Zone...</p>
            <p id="creating-overlay-subtitle" class="text-[11px] text-slate-400 mt-1">Please wait</p>
        </div>
    </div>
</div>

{{-- Hidden delete form --}}
<form id="sz-delete-form" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    .gis-leaflet-popup .leaflet-popup-content-wrapper { background: #151B23 !important; border: 1px solid #2A3440 !important; border-radius: 12px !important; padding: 0 !important; box-shadow: 0 16px 36px rgba(0,0,0,0.8) !important; }
    .gis-leaflet-popup .leaflet-popup-content { margin: 0 !important; }
    .gis-leaflet-popup .leaflet-popup-tip { background: #151B23 !important; border: 1px solid #2A3440 !important; }

    .badge-critical { background: #000; color: #fff; border: 1px solid #4B5563; }
    .badge-high     { background: rgba(239,68,68,0.2); color: #EF4444; border: 1px solid rgba(239,68,68,0.4); }
    .badge-medium   { background: rgba(234,179,8,0.2); color: #EAB308; border: 1px solid rgba(234,179,8,0.4); }
    .badge-low      { background: rgba(34,197,94,0.2); color: #22C55E; border: 1px solid rgba(34,197,94,0.4); }

    .leaflet-control-zoom { border: 1px solid #2A3440 !important; border-radius: 8px !important; overflow: hidden !important; }
    .leaflet-control-zoom a { background-color: #151B23 !important; color: #F3F4F6 !important; border-bottom: 1px solid #2A3440 !important; }
    .leaflet-control-zoom a:hover { background-color: #1E293B !important; color: #10B981 !important; }
    .leaflet-control-zoom a:last-child { border-bottom: none !important; }

    .leaflet-control-scale-line { background: rgba(21,27,35,0.9) !important; border-color: #2A3440 !important; color: #9CA3AF !important; }

    #admin-map { background: #06090D; }
    #admin-map.picking-safe-zone { cursor: crosshair; }

    .overflow-y-auto::-webkit-scrollbar { width: 4px; }
    .overflow-y-auto::-webkit-scrollbar-thumb { background: #2A3440; border-radius: 2px; }
    .overflow-y-auto::-webkit-scrollbar-track { background: transparent; }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type=number] { -moz-appearance: textfield; }
</style>

<script>
// ═══════════════════════════════════════════════════════════════════
// CREATE SAFE ZONE — prevent duplicates with 1s delay
// ═══════════════════════════════════════════════════════════════════
window.handleSafeZoneCreate = function(e) {
    e.preventDefault();
    const form = e.target;

    // Get overlay elements
    const overlay = document.getElementById('creating-overlay');
    const box = document.getElementById('creating-overlay-box');
    const spinner = document.getElementById('creating-overlay-spinner');
    const dot = document.getElementById('creating-overlay-dot');
    const title = document.getElementById('creating-overlay-title');
    const subtitle = document.getElementById('creating-overlay-subtitle');

    // Disable submit to block double-click
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    // Set text & colors to Creating (Emerald/Green)
    if (title) title.textContent = 'Creating Safe Zone...';
    if (subtitle) subtitle.textContent = 'Please wait';
    if (box) box.className = 'flex flex-col items-center gap-4 px-8 py-7 rounded-2xl bg-[#151B23] border border-emerald-500/40 shadow-2xl';
    if (spinner) spinner.className = 'absolute inset-0 rounded-full border-4 border-emerald-500/20 border-t-emerald-500 animate-spin';
    if (dot) dot.className = 'w-3 h-3 rounded-full bg-emerald-500 animate-pulse';

    // Show creating overlay
    if (overlay) { overlay.classList.remove('hidden'); overlay.classList.add('flex'); }

    // Wait 1 second, then submit
    setTimeout(() => {
        form.submit(); // native submit — bypasses this handler
    }, 1000);
};

// ═══════════════════════════════════════════════════════════════════
// PROTECTION DURATION VALIDATION (Days: 0-30, Hours: 0-24, Mins/Secs: 0-59)
// ═══════════════════════════════════════════════════════════════════
window.validateProtectionField = function(input, type) {
    const maxMap = { days: 30, hours: 24, minutes: 59, seconds: 59 };
    const max = maxMap[type] || 59;
    const errorEl = document.getElementById('sz-' + type + '-error');

    let val = parseInt(input.value, 10);

    if (isNaN(val)) {
        input.value = '';
        if (errorEl) errorEl.classList.add('hidden');
        return;
    }

    if (val < 0) {
        input.value = 0;
        if (errorEl) {
            errorEl.textContent = 'Cannot be negative';
            errorEl.classList.remove('hidden');
            setTimeout(() => errorEl.classList.add('hidden'), 2500);
        }
        return;
    }

    if (val > max) {
        input.value = max;
        if (errorEl) {
            errorEl.textContent = `Max is ${max}`;
            errorEl.classList.remove('hidden');
            setTimeout(() => errorEl.classList.add('hidden'), 2500);
        }
        return;
    }

    if (errorEl) errorEl.classList.add('hidden');
};

// ═══════════════════════════════════════════════════════════════════
// SAFE ZONE MODAL + MAP PICKER
// ═══════════════════════════════════════════════════════════════════
let _szPickerMarker = null;
let _szPickingMode = false;

window.openSafeZoneModal = function() {
    const modal = document.getElementById('safe-zone-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
};

window.closeSafeZoneModal = function() {
    const modal = document.getElementById('safe-zone-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
};

window.startSafeZonePicking = function() {
    _szPickingMode = true;
    const modal = document.getElementById('safe-zone-modal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
    document.body.style.overflow = '';
    const hud = document.getElementById('safe-zone-picker-hud');
    if (hud) hud.style.display = 'flex';
    const mapEl = document.getElementById('admin-map');
    if (mapEl) mapEl.classList.add('picking-safe-zone');
};

window.cancelSafeZonePicking = function() {
    _szPickingMode = false;
    const hud = document.getElementById('safe-zone-picker-hud');
    if (hud) hud.style.display = 'none';
    const mapEl = document.getElementById('admin-map');
    if (mapEl) mapEl.classList.remove('picking-safe-zone');
    if (_szPickerMarker && window._adminMap) {
        try { window._adminMap.removeLayer(_szPickerMarker); } catch(_) {}
        _szPickerMarker = null;
    }
    window.openSafeZoneModal();
};

function pickSafeZoneLocation(lat, lng) {
    if (_szPickerMarker && window._adminMap) {
        try { window._adminMap.removeLayer(_szPickerMarker); } catch(_) {}
    }

    const pinHtml = `
        <div style="width:28px;height:28px;background:#10B981;border-radius:50%;border:2px solid #FFFFFF;display:flex;align-items:center;justify-content:center;box-shadow:0 0 20px rgba(16,185,129,0.8);">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#fff"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>`;

    if (window._adminMap) {
        _szPickerMarker = L.marker([lat, lng], {
            icon: L.divIcon({ className: '', html: pinHtml, iconSize: [28, 28], iconAnchor: [14, 14] })
        }).addTo(window._adminMap);
    }

    const latInput = document.getElementById('sz-lat');
    const lngInput = document.getElementById('sz-lng');
    const coordsEl = document.getElementById('sz-review-coords');

    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);
    if (coordsEl) coordsEl.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);

    fetch(`/api/spectral/reverse-geocode?lat=${lat}&lng=${lng}`, { headers: { 'Accept': 'application/json' } })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
        if (data && data.success) {
            const addrEl = document.getElementById('sz-address');
            const brgyEl = document.getElementById('sz-review-barangay');
            const muniEl = document.getElementById('sz-review-municipality');
            const badgeEl = document.getElementById('sz-geocode-badge');
            const statusEl = document.getElementById('sz-geocode-status');
            const bSelect = document.getElementById('sz-barangay');
            const confirmCb = document.getElementById('sz-location-confirmed');

            if (addrEl) addrEl.value = data.address || '';
            if (brgyEl) brgyEl.textContent = data.barangay_name ? 'Brgy. ' + data.barangay_name : '—';
            if (muniEl) muniEl.textContent = (data.municipality || 'San Francisco') + ', ' + (data.province || 'Agusan del Sur');
            if (badgeEl) { badgeEl.textContent = 'Verified'; badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'; }
            if (statusEl) statusEl.textContent = '✓ Located';
            if (confirmCb) confirmCb.checked = true;

            if (bSelect && data.barangay_id) {
                for (let opt of bSelect.options) { if (opt.value == data.barangay_id) { bSelect.value = opt.value; break; } }
            }
        }
    })
    .catch(() => { const addrEl = document.getElementById('sz-address'); if (addrEl) addrEl.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`; });

    _szPickingMode = false;
    const hud = document.getElementById('safe-zone-picker-hud');
    if (hud) hud.style.display = 'none';
    const mapEl = document.getElementById('admin-map');
    if (mapEl) mapEl.classList.remove('picking-safe-zone');
    window.openSafeZoneModal();
}

// ═══════════════════════════════════════════════════════════════════
// DELETE SAFE ZONE — confirmation modal
// ═══════════════════════════════════════════════════════════════════
let _pendingDeleteId = null;

window.confirmDeleteSafeZone = function(id, name) {
    _pendingDeleteId = id;
    const nameEl = document.getElementById('delete-sz-name');
    if (nameEl) nameEl.textContent = name;
    const modal = document.getElementById('delete-safe-zone-modal');
    if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
};

window.closeDeleteSafeZoneModal = function() {
    _pendingDeleteId = null;
    const modal = document.getElementById('delete-safe-zone-modal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
};

window.executeSafeZoneDelete = function() {
    if (!_pendingDeleteId) return;
    const form = document.getElementById('sz-delete-form');
    form.action = `/admin/safe-zones/${_pendingDeleteId}`;

    // Close delete confirmation modal
    closeDeleteSafeZoneModal();

    // Get overlay elements
    const overlay = document.getElementById('creating-overlay');
    const box = document.getElementById('creating-overlay-box');
    const spinner = document.getElementById('creating-overlay-spinner');
    const dot = document.getElementById('creating-overlay-dot');
    const title = document.getElementById('creating-overlay-title');
    const subtitle = document.getElementById('creating-overlay-subtitle');

    // Set text & colors to Deleting (Rose/Red)
    if (title) title.textContent = 'Deleting Safe Zone...';
    if (subtitle) subtitle.textContent = 'Please wait';
    if (box) box.className = 'flex flex-col items-center gap-4 px-8 py-7 rounded-2xl bg-[#151B23] border border-rose-500/40 shadow-2xl';
    if (spinner) spinner.className = 'absolute inset-0 rounded-full border-4 border-rose-500/20 border-t-rose-500 animate-spin';
    if (dot) dot.className = 'w-3 h-3 rounded-full bg-rose-500 animate-pulse';

    // Show overlay
    if (overlay) { overlay.classList.remove('hidden'); overlay.classList.add('flex'); }

    // Submit form
    setTimeout(() => { form.submit(); }, 500);
};

// ESC handler
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const createModal = document.getElementById('safe-zone-modal');
        if (createModal && !createModal.classList.contains('hidden')) closeSafeZoneModal();
        const deleteModal = document.getElementById('delete-safe-zone-modal');
        if (deleteModal && !deleteModal.classList.contains('hidden')) closeDeleteSafeZoneModal();
        if (_szPickingMode) cancelSafeZonePicking();
    }
});

// Event delegation for the delete buttons inside popups
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.sz-delete-btn');
    if (btn) {
        confirmDeleteSafeZone(btn.dataset.szId, btn.dataset.szName);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    if (typeof L === 'undefined') { console.error('[Admin Map] Leaflet failed'); return; }

    const RAW_INCIDENTS  = @json($incidents);
    const RAW_SAFE_ZONES = @json($safeZones);

    const map = L.map('admin-map', { center: [8.5100, 125.9750], zoom: 14, zoomControl: false, attributionControl: true });
    window._adminMap = map;

    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.control.scale({ imperial: true, metric: true, position: 'bottomleft' }).addTo(map);

    L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { attribution: 'OpenStreetMap', subdomains: ['0','1','2','3'], maxZoom: 20 }).addTo(map);

    map.on('click', function(e) { if (_szPickingMode) pickSafeZoneLocation(e.latlng.lat, e.latlng.lng); });

    const layers = { incidents: L.layerGroup().addTo(map), safeZones: L.layerGroup().addTo(map) };
    const activeFilters = { severity: 'ALL', status: 'ALL', search: '' };
    const incidentMarkers = {};

    function formatProtectionTime(isoString) {
        if (!isoString) return { text: 'No protection set', expired: true };
        const expires = new Date(isoString);
        const now = new Date();
        const diffMs = expires - now;
        if (diffMs <= 0) return { text: 'Protection Expired', expired: true };

        const totalSeconds = Math.floor(diffMs / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        if (days > 0) return { text: `${days}d ${String(hours).padStart(2,'0')}h ${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
        if (hours > 0) return { text: `${String(hours).padStart(2,'0')}h ${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
        return { text: `${String(minutes).padStart(2,'0')}m ${String(seconds).padStart(2,'0')}s`, expired: false };
    }

    function suppliesBadge(status) {
        const map = {
            'available':    { color: '#22C55E', bg: 'rgba(34,197,94,0.15)',  border: 'rgba(34,197,94,0.4)',  label: 'Available' },
            'limited':      { color: '#EAB308', bg: 'rgba(234,179,8,0.15)',  border: 'rgba(234,179,8,0.4)',  label: 'Limited' },
            'out_of_stock': { color: '#EF4444', bg: 'rgba(239,68,68,0.15)',  border: 'rgba(239,68,68,0.4)',  label: 'Out of stock' }
        };
        const s = map[status] || map['available'];
        return `<span style="display:inline-flex;align-items:center;justify-content:center;background:${s.bg};color:${s.color};border:1px solid ${s.border};font-size:10px;font-weight:700;padding:3px 10px;border-radius:5px;min-width:84px;height:22px;box-sizing:border-box;">${s.label}</span>`;
    }

    function icon(name, color, size = 12) {
        const icons = {
            clock:   `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
            box:     `<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>`,
            coffee:  `<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>`,
            droplet: `<path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>`,
            firstaid:`<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="14"/><line x1="9" y1="11" x2="15" y2="11"/>`,
            heart:   `<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>`,
            trash:   `<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>`
        };
        return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">${icons[name] || ''}</svg>`;
    }

    function buildSafeZonePopup(sz) {
        const time = formatProtectionTime(sz.protection_expires_at);
        const timeColor = time.expired ? '#EF4444' : '#22C55E';
        const statusLabel = time.expired ? 'WARD OFFLINE' : 'WARD ACTIVE';
        const statusDot = `<span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:${timeColor};box-shadow:0 0 6px ${timeColor};margin-right:5px;vertical-align:middle;"></span>`;
        const services = sz.assistance_services || [];

        const servicesHtml = services.length > 0
            ? services.map(s => `<li style="display:flex;align-items:center;gap:7px;font-size:11px;color:#CBD5E1;margin-bottom:5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>${s}</span>
                </li>`).join('')
            : '<li style="font-size:11px;color:#64748B;font-style:italic;">No assistance listed</li>';

        // 🔒 Safely escape name for data attribute
        const safeName = (sz.name || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

        return `
            <div style="padding:14px 16px;min-width:290px;max-width:340px;font-family:'Plus Jakarta Sans',sans-serif;color:#F3F4F6;">
                <div style="margin-bottom:12px;">
                    <span style="display:inline-flex;align-items:center;color:${timeColor};font-size:9px;font-weight:800;font-family:'JetBrains Mono',monospace;letter-spacing:0.08em;">
                        ${statusDot}${statusLabel}
                    </span>
                    <h4 style="font-size:14px;font-weight:800;color:#FFFFFF;margin:5px 0 3px 0;line-height:1.25;">${sz.name}</h4>
                    <p style="font-size:10.5px;color:#9CA3AF;margin:0;">${sz.description || sz.address}</p>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:5px;">
                        ${icon('clock', '#A78BFA', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Protection Remaining</span>
                    </div>
                    <p style="font-size:16px;font-weight:800;color:${timeColor};font-family:'JetBrains Mono',monospace;margin:0;line-height:1;">${time.text}</p>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
                        ${icon('box', '#2DD4BF', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Relief Supplies</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('coffee', '#9CA3AF', 12)}<span>Food &amp; Water</span></span>
                        ${suppliesBadge(sz.relief_food)}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('droplet', '#9CA3AF', 12)}<span>Drinking Water</span></span>
                        ${suppliesBadge(sz.relief_water)}
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="display:flex;align-items:center;gap:7px;font-size:11px;color:#9CA3AF;">${icon('firstaid', '#9CA3AF', 12)}<span>First-aid Kits</span></span>
                        ${suppliesBadge(sz.relief_first_aid)}
                    </div>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:11px;margin-bottom:11px;">
                    <div style="display:flex;align-items:center;gap:7px;margin-bottom:8px;">
                        ${icon('heart', '#22C55E', 12)}
                        <span style="font-size:10px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:0.06em;">Free Assistance</span>
                    </div>
                    <ul style="list-style:none;padding:0;margin:0;">${servicesHtml}</ul>
                </div>

                <div style="border-top:1px solid #2A3440;padding-top:9px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:10px;color:#64748B;font-family:'JetBrains Mono',monospace;">${sz.code || ''}</span>
                    <button type="button"
                            class="sz-delete-btn"
                            data-sz-id="${sz.id}"
                            data-sz-name="${safeName}"
                            style="background:#EF4444;color:#FFFFFF;padding:5px 11px;border-radius:6px;font-size:10px;font-weight:700;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                        ${icon('trash', '#FFFFFF', 10)}
                        Delete
                    </button>
                </div>
            </div>
        `;
    }

    RAW_SAFE_ZONES.forEach(sz => {
        const isActive = sz.protection_active !== false;
        const zoneColor = isActive ? '#22C55E' : '#EF4444';

        const circle = L.circle([sz.lat, sz.lng], { radius: Math.round(sz.radius), color: zoneColor, weight: 1.5, fillColor: zoneColor, fillOpacity: 0.08 });

        const markerIcon = L.divIcon({
            className: '',
            html: `<div style="width:28px;height:28px;border-radius:50%;background:${isActive ? '#10B981' : '#DC2626'};border:2px solid ${isActive ? '#34D399' : '#F87171'};box-shadow:0 0 10px ${isActive ? 'rgba(16,185,129,0.8)' : 'rgba(220,38,38,0.8)'};display:flex;align-items:center;justify-content:center;cursor:pointer;" title="${sz.name}">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#FFFFFF" stroke="${isActive ? '#10B981' : '#DC2626'}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>`,
            iconSize: [28, 28], iconAnchor: [14, 14]
        });

        const marker = L.marker([sz.lat, sz.lng], { icon: markerIcon, zIndexOffset: 700 });
        const popupHtml = buildSafeZonePopup(sz);

        marker.bindPopup(popupHtml, { className: 'gis-leaflet-popup', maxWidth: 360 });
        marker.bindTooltip(sz.name, { direction: 'top', offset: [0, -14] });
        circle.bindPopup(popupHtml, { className: 'gis-leaflet-popup', maxWidth: 360 });

        circle.on('click', () => marker.openPopup());
        marker.on('click', () => map.flyTo([sz.lat, sz.lng], Math.max(map.getZoom(), 16), { duration: 0.7 }));

        layers.safeZones.addLayer(circle);
        layers.safeZones.addLayer(marker);
    });

    const sevColors = { CRITICAL: '#000000', HIGH: '#EF4444', MEDIUM: '#EAB308', LOW: '#22C55E' };

    function createIncidentMarker(inc) {
        if (!inc.latitude || !inc.longitude) return null;
        const lat = parseFloat(inc.latitude);
        const lng = parseFloat(inc.longitude);
        if (isNaN(lat) || isNaN(lng)) return null;

        const color = sevColors[inc.severity] || '#22C55E';
        const isHigh = inc.severity === 'HIGH' || inc.severity === 'CRITICAL';
        const isCritical = inc.severity === 'CRITICAL';
        const isArchived = inc.archived_from_map_at !== null && inc.archived_from_map_at !== undefined;
        const isResolved = inc.status === 'RESOLVED';
        const pulseBg = isCritical ? 'rgba(0,0,0,0.6)' : `${color}44`;
        const border = isCritical ? '#9CA3AF' : (isArchived ? '#EAB308' : '#FFFFFF');
        const shadow = isCritical ? '0 0 12px rgba(0,0,0,0.95)' : `0 0 10px ${color}`;

        const markerInner = isArchived
            ? `<div style="width:6px;height:6px;border-radius:50%;background:#EAB308;"></div>`
            : (isResolved
                ? `<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`
                : `<div style="width:6px;height:6px;border-radius:50%;background:#FFFFFF;"></div>`);

        const icon = L.divIcon({
            className: '',
            html: `<div style="position:relative;width:26px;height:26px;display:flex;align-items:center;justify-content:center;cursor:pointer;${isArchived ? 'opacity:0.55;' : ''}">
                ${isHigh && !isArchived ? `<div style="position:absolute;inset:-4px;border-radius:50%;background:${pulseBg};animation:ping 2s cubic-bezier(0,0,0.2,1) infinite;"></div>` : ''}
                <div style="position:relative;width:20px;height:20px;border-radius:50%;background:${color};border:2px solid ${border};box-shadow:${shadow};display:flex;align-items:center;justify-content:center;">${markerInner}</div>
            </div>`,
            iconSize: [26, 26], iconAnchor: [13, 13]
        });

        const marker = L.marker([lat, lng], { icon, zIndexOffset: isArchived ? 400 : 800 });
        const incCode = inc.incident_code || `SF-INC-${inc.id}`;
        const bName = inc.barangay?.name || 'San Francisco';

        const statusBadges = [];
        if (isResolved) statusBadges.push(`<span style="background:rgba(34,197,94,0.15);color:#22C55E;border:1px solid rgba(34,197,94,0.4);font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;font-family:'JetBrains Mono',monospace;">RESOLVED</span>`);
        else statusBadges.push(`<span style="background:rgba(139,92,246,0.15);color:#A78BFA;border:1px solid rgba(139,92,246,0.4);font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;font-family:'JetBrains Mono',monospace;">${inc.status}</span>`);
        if (isArchived) statusBadges.push(`<span style="background:rgba(234,179,8,0.15);color:#EAB308;border:1px solid rgba(234,179,8,0.4);font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;">ARCHIVED</span>`);

        marker.bindPopup(`
            <div style="padding:12px 14px;min-width:260px;font-family:'Plus Jakarta Sans',sans-serif;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;gap:8px;padding-right:24px;">
                    <span style="font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:700;color:#8B5CF6;">${incCode}</span>
                    <span class="badge-${(inc.severity||'low').toLowerCase()}" style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;">${inc.severity}</span>
                </div>
                <h4 style="font-size:12px;font-weight:800;color:#FFFFFF;line-height:1.3;margin-bottom:4px;">${inc.title}</h4>
                <p style="font-size:11px;color:#9CA3AF;margin-bottom:6px;">Brgy. ${bName}</p>
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">${statusBadges.join('')}</div>
                <div style="display:flex;justify-content:flex-end;padding-top:8px;border-top:1px solid #2A3440;">
                    <a href="/admin/incidents/${inc.id}" style="background:#8B5CF6;color:#FFFFFF;padding:6px 12px;border-radius:6px;font-size:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Inspect
                    </svg></a>
                </div>
            </div>
        `);

        return marker;
    }

    RAW_INCIDENTS.forEach(inc => {
        const marker = createIncidentMarker(inc);
        if (marker) {
            incidentMarkers[inc.id] = marker;
            if (inc.incident_code) incidentMarkers[inc.incident_code] = marker;
        }
    });

    function matchesFilters(inc) {
        const search = activeFilters.search.trim().toLowerCase();
        if (search) {
            const code = (inc.incident_code || '').toLowerCase();
            const title = (inc.title || '').toLowerCase();
            const brgy = (inc.barangay?.name || '').toLowerCase();
            if (!code.includes(search) && !title.includes(search) && !brgy.includes(search)) return false;
        }
        if (activeFilters.severity !== 'ALL' && inc.severity !== activeFilters.severity) return false;
        const isArchived = inc.archived_from_map_at !== null && inc.archived_from_map_at !== undefined;
        if (activeFilters.status === 'ACTIVE') {
            if (!['PENDING', 'UNDER INVESTIGATION', 'VERIFIED'].includes(inc.status)) return false;
            if (isArchived) return false;
        } else if (activeFilters.status === 'RESOLVED') {
            if (inc.status !== 'RESOLVED') return false;
            if (isArchived) return false;
        } else if (activeFilters.status === 'ARCHIVED') {
            if (!isArchived) return false;
        }
        return true;
    }

    function applyFilters() {
        const searchEl = document.getElementById('map-search');
        activeFilters.search = searchEl ? searchEl.value : '';
        layers.incidents.clearLayers();
        let visible = 0;
        RAW_INCIDENTS.forEach(inc => {
            if (!matchesFilters(inc)) return;
            const m = incidentMarkers[inc.id];
            if (m) { layers.incidents.addLayer(m); visible++; }
        });
        const summaryEl = document.getElementById('filter-summary');
        if (summaryEl) summaryEl.textContent = visible + ' / ' + RAW_INCIDENTS.length;
        const lyrCount = document.getElementById('lyr-count-incidents');
        if (lyrCount) lyrCount.textContent = visible;
    }
    window.applyFilters = applyFilters;

    window.toggleAdminLayer = function(name) {
        if (!layers[name]) return;
        const nowOn = !map.hasLayer(layers[name]);
        if (nowOn) map.addLayer(layers[name]); else map.removeLayer(layers[name]);
    };

    window.setStatusFilter = function(status) {
        activeFilters.status = status;
        document.querySelectorAll('.status-pill').forEach(btn => {
            const isActive = btn.dataset.status === status;
            const colorMap = { ALL: 'text-white', ACTIVE: 'text-red-300', RESOLVED: 'text-emerald-300', ARCHIVED: 'text-amber-300' };
            const color = colorMap[btn.dataset.status] || 'text-slate-300';
            btn.className = isActive ? `status-pill px-2.5 py-1 rounded-lg bg-[#8B5CF6] text-white text-[10px] font-bold transition` : `status-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] ${color} hover:border-[#8B5CF6]/50 text-[10px] font-bold transition`;
        });
        applyFilters();
    };

    window.setSeverityFilter = function(sev) {
        activeFilters.severity = sev;
        document.querySelectorAll('.severity-pill').forEach(btn => {
            const isActive = btn.dataset.severity === sev;
            const colorMap = { ALL: 'text-white', CRITICAL: 'text-rose-300', HIGH: 'text-orange-300', MEDIUM: 'text-yellow-300', LOW: 'text-green-300' };
            const color = colorMap[btn.dataset.severity] || 'text-slate-300';
            btn.className = isActive ? `severity-pill px-2.5 py-1 rounded-lg bg-[#8B5CF6] text-white text-[10px] font-bold transition` : `severity-pill px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] ${color} hover:border-[#8B5CF6]/50 text-[10px] font-bold transition`;
        });
        applyFilters();
    };

    window.resetAllFilters = function() {
        activeFilters.severity = 'ALL';
        activeFilters.status = 'ALL';
        activeFilters.search = '';
        const searchEl = document.getElementById('map-search');
        if (searchEl) searchEl.value = '';
        setStatusFilter('ALL');
        setSeverityFilter('ALL');
        map.flyTo([8.5100, 125.9750], 14, { duration: 1.2 });
    };

    applyFilters();
    setTimeout(() => map.invalidateSize(), 200);
    window.addEventListener('resize', () => map.invalidateSize());
});
</script>

@endsection
