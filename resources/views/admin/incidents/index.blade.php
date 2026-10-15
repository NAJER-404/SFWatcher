@extends('layouts.admin')

@section('title', 'All Incidents — SFWatch Admin Panel')

@php
    // ---- Filters ----
    $activeStatus = request('status');
    if (!$activeStatus || $activeStatus === 'ALL') { $activeStatus = null; }
    $keep = request()->except(['status', 'page']);          // keep search / severity / dates when switching status

    $severityFilter = request('severity');
    $hasFilters = request()->hasAny(['search', 'date_from', 'date_to'])
        || $activeStatus
        || ($severityFilter && $severityFilter !== 'ALL');

    // Removed the 'Archived' pill from here
    $statusPills = [
        ['All',           null,                  $statusCounts['total']],
        ['Pending',       'PENDING',             $statusCounts['pending']],
        ['Investigating', 'UNDER INVESTIGATION', $statusCounts['under_investigation']],
        ['Verified',      'VERIFIED',            $statusCounts['verified']],
        ['Resolved',      'RESOLVED',            $statusCounts['resolved']],
    ];

    // ---- Badge styles ----
    $statusStyle = [
        'RESOLVED'            => ['Resolved',            'bg-emerald-500/15 text-emerald-300 border-emerald-500/40', 'bg-emerald-400'],
        'VERIFIED'            => ['Verified',            'bg-blue-500/15 text-blue-300 border-blue-500/40',          'bg-blue-400'],
        'UNDER INVESTIGATION' => ['Under investigation', 'bg-sky-500/15 text-sky-300 border-sky-500/40',             'bg-sky-400'],
        'PENDING'             => ['Pending',             'bg-amber-500/15 text-amber-300 border-amber-500/40',       'bg-amber-400'],
        'ARCHIVED'            => ['Archived',            'bg-slate-500/15 text-slate-300 border-slate-500/40',       'bg-slate-400'],
    ];
    $severityStyle = [
        'CRITICAL' => ['Critical', 'bg-rose-600/25 text-rose-200 border-rose-500/60'],
        'HIGH'     => ['High',     'bg-orange-500/15 text-orange-300 border-orange-500/40'],
        'MEDIUM'   => ['Medium',   'bg-yellow-500/15 text-yellow-300 border-yellow-500/40'],
        'LOW'      => ['Low',      'bg-green-500/15 text-green-300 border-green-500/40'],
    ];
    $fieldClass = 'w-full rounded-lg bg-[#11161D] border border-[#2A3440] px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:border-[#8B5CF6] focus:outline-none focus:ring-1 focus:ring-[#8B5CF6]/40';
@endphp

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8">
<div class="mx-auto w-full max-w-7xl space-y-5">

    {{-- ═════ Header ═════ --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs text-[#A78BFA]" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="text-slate-600">/</span>
                <span class="font-semibold text-slate-300">All incidents</span>
            </nav>
            <h1 class="mt-1 text-xl font-extrabold text-white sm:text-2xl">All incidents</h1>
            <p class="mt-0.5 text-sm text-slate-400">Track every report from pending to resolved, and manage what stays on the live map.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-lg border border-[#2A3440] bg-[#151B23] px-3 py-1.5 text-slate-300">
                Total <strong class="ml-1 font-mono text-white">{{ $statusCounts['total'] }}</strong>
            </span>
            <span class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-1.5 text-emerald-300">
                Resolved <strong class="ml-1 font-mono">{{ $statusCounts['resolved'] }}</strong>
            </span>

            {{-- NEW BULK ARCHIVE BUTTON --}}
            @if($statusCounts['resolved'] > 0)
                <button type="button"
                        data-modal-open="archive-resolved"
                        data-action="{{ route('admin.incidents.archive-resolved') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-1.5 font-semibold text-amber-300 transition hover:bg-amber-500 hover:text-[#1B1405]">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                        <line x1="8" y1="2" x2="8" y2="18"/>
                        <line x1="16" y1="6" x2="16" y2="22"/>
                    </svg>
                    Archive All Resolved
                </button>
            @endif
        </div>
    </div>

    {{-- ═════ Status quick filters ═════ --}}
    <div class="-mx-3.5 overflow-x-auto px-3.5 sm:mx-0 sm:px-0" style="scrollbar-width: none;">
        <div class="inline-flex min-w-full gap-1.5 rounded-xl border border-[#2A3440] bg-[#151B23] p-1.5 text-sm sm:min-w-0">
            @foreach($statusPills as [$label, $value, $count])
                @php $isOn = $activeStatus === $value; @endphp
                <a href="{{ route('admin.incidents.index', $value ? array_merge($keep, ['status' => $value]) : $keep) }}"
                   @if($isOn) aria-current="true" @endif
                   class="inline-flex shrink-0 items-center gap-2 rounded-lg px-3 py-1.5 font-medium transition
                          {{ $isOn ? 'bg-[#8B5CF6] text-white' : 'text-slate-400 hover:bg-[#1B222C] hover:text-white' }}">
                    {{ $label }}
                    <span class="rounded-full px-1.5 py-0.5 font-mono text-[11px] {{ $isOn ? 'bg-white/20 text-white' : 'bg-[#11161D] text-slate-400' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ═════ Search + filters ═════ --}}
    <form method="GET" action="{{ route('admin.incidents.index') }}" class="rounded-xl border border-[#2A3440] bg-[#151B23] p-4">
        @if($activeStatus)
            <input type="hidden" name="status" value="{{ $activeStatus }}">
        @endif

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12">
            <div class="sm:col-span-2 lg:col-span-4">
                <label for="f-search" class="mb-1 block text-xs font-medium text-slate-400">Search</label>
                <input id="f-search" type="text" name="search" value="{{ request('search') }}"
                       placeholder="Code, title or reporter" class="{{ $fieldClass }}">
            </div>

            <div class="lg:col-span-2">
                <label for="f-severity" class="mb-1 block text-xs font-medium text-slate-400">Severity</label>
                <select id="f-severity" name="severity" class="{{ $fieldClass }}">
                    <option value="ALL">All severities</option>
                    @foreach($severityStyle as $key => [$sevLabel])
                        <option value="{{ $key }}" @selected($severityFilter === $key)>{{ $sevLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="f-from" class="mb-1 block text-xs font-medium text-slate-400">Reported from</label>
                <input id="f-from" type="date" name="date_from" value="{{ request('date_from') }}" class="{{ $fieldClass }}">
            </div>

            <div class="lg:col-span-2">
                <label for="f-to" class="mb-1 block text-xs font-medium text-slate-400">Reported to</label>
                <input id="f-to" type="date" name="date_to" value="{{ request('date_to') }}" class="{{ $fieldClass }}">
            </div>

            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-2">
                <button type="submit" class="min-h-[38px] flex-1 rounded-lg bg-[#8B5CF6] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#7C3AED]">
                    Apply filters
                </button>
                @if($hasFilters)
                    <a href="{{ route('admin.incidents.index') }}" class="inline-flex min-h-[38px] items-center rounded-lg border border-[#2A3440] bg-[#11161D] px-3 py-2 text-sm text-slate-400 transition hover:text-white">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ═════ Desktop table (xl and up) ═════ --}}
    <div class="hidden overflow-hidden rounded-xl border border-[#2A3440] bg-[#151B23] xl:block">
        <table class="w-full text-left text-xs">
            <thead class="border-b border-[#2A3440] bg-[#11161D] text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                <tr>
                    <th class="px-3 py-2.5">Incident</th>
                    <th class="px-3 py-2.5">Severity</th>
                    <th class="px-3 py-2.5">Location</th>
                    <th class="px-3 py-2.5">People</th>
                    <th class="px-3 py-2.5">Status</th>
                    <th class="px-3 py-2.5">Dates</th>
                    <th class="px-3 py-2.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3440]">
                @forelse($incidents as $inc)
                    @php
                        $assignment   = $inc->responderAssignments->sortByDesc('assigned_at')->first();
                        $investigator = $inc->investigations->sortByDesc('investigation_date')->first()?->investigator;
                        [$stLabel, $stClass, $stDot] = $statusStyle[$inc->status] ?? [ucfirst(strtolower($inc->status)), 'bg-amber-500/15 text-amber-300 border-amber-500/40', 'bg-amber-400'];
                        [$sevLabel, $sevClass] = $severityStyle[$inc->severity] ?? [ucfirst(strtolower($inc->severity)), 'bg-slate-500/15 text-slate-300 border-slate-500/40'];
                    @endphp
                    <tr class="align-middle transition hover:bg-[#1B222C]/60">
                        {{-- Incident --}}
                        <td class="px-3 py-2.5">
                            <span class="block font-mono text-[10px] font-bold text-[#A78BFA]">{{ $inc->incident_code }}</span>
                            <span class="mt-0.5 block max-w-[200px] truncate font-semibold text-white" title="{{ $inc->title }}">{{ $inc->title }}</span>
                            <span class="block text-[10px] text-slate-500">{{ ucfirst(strtolower($inc->incident_type)) }}</span>
                        </td>

                        {{-- Severity --}}
                        <td class="px-3 py-2.5">
                            <span class="inline-block rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $sevClass }}">{{ $sevLabel }}</span>
                        </td>

                        {{-- Location --}}
                        <td class="px-3 py-2.5 text-slate-300">
                            Brgy. {{ $inc->barangay?->name ?? 'San Francisco' }}
                        </td>

                        {{-- People (Compact Grid) --}}
                        <td class="px-3 py-2.5">
                            <div class="grid grid-cols-2 gap-x-2 gap-y-0.5 text-[11px]">
                                <div class="text-slate-500">Reporter</div>
                                <div class="truncate text-slate-300">{{ $inc->reporter?->name ?? 'Field scout' }}</div>
                                <div class="text-slate-500">Investigator</div>
                                <div class="truncate text-slate-300">{{ $investigator?->name ?? '—' }}</div>
                                <div class="text-slate-500">Responder</div>
                                <div class="truncate font-semibold {{ $assignment?->responder ? 'text-emerald-300' : 'text-slate-500' }}">{{ $assignment?->responder?->name ?? '—' }}</div>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-3 py-2.5">
                            <div class="flex flex-col gap-1 items-start">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $stClass }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $stDot }}"></span>{{ $stLabel }}
                                </span>
                                @if($inc->isArchivedFromMap())
                                    <span class="inline-flex items-center gap-1 rounded-full border border-amber-400/40 bg-amber-400/10 px-1.5 py-0.5 text-[9px] font-medium text-amber-300">
                                        Removed from map
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Dates --}}
                        <td class="px-3 py-2.5 text-[11px]">
                            <p class="text-slate-400"><span class="text-slate-500">Reported</span> {{ $inc->created_at?->format('M j, Y') ?? '—' }}</p>
                            @if($inc->status === 'RESOLVED')
                                <p class="mt-0.5 text-emerald-300"><span class="text-slate-500">Resolved</span> {{ ($inc->investigation_completed_at ?? $inc->updated_at)?->format('M j, Y') }}</p>
                            @endif
                        </td>

                        {{-- Actions (Compact Icon Buttons) --}}
                        <td class="px-3 py-2.5">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- View Details --}}
                                <a href="{{ route('admin.incidents.show', $inc) }}"
                                   class="flex h-7 w-7 items-center justify-center rounded-md border border-[#2A3440] bg-[#11161D] text-slate-400 transition hover:border-[#8B5CF6] hover:text-[#A78BFA]"
                                   title="View Details">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                {{-- Delete Action --}}
                                <button type="button"
                                        data-modal-open="delete"
                                        data-code="{{ $inc->incident_code }}"
                                        data-title="{{ $inc->title }}"
                                        data-action="{{ route('admin.incidents.destroy', $inc) }}"
                                        class="flex h-7 w-7 items-center justify-center rounded-md border border-rose-500/30 bg-rose-500/10 text-rose-400 transition hover:bg-rose-500 hover:text-white"
                                        title="Delete Incident">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        <line x1="10" y1="11" x2="10" y2="17"/>
                                        <line x1="14" y1="11" x2="14" y2="17"/>
                                    </svg>
                                </button>

                                {{-- Archive / Restore Action --}}
                                @if($inc->isArchivedFromMap())
                                    <button type="button"
                                            data-modal-open="restore"
                                            data-code="{{ $inc->incident_code }}"
                                            data-title="{{ $inc->title }}"
                                            data-action="{{ route('admin.incidents.restore', $inc) }}"
                                            class="flex h-7 w-7 items-center justify-center rounded-md border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 transition hover:bg-emerald-500 hover:text-white"
                                            title="Restore to Map">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="1 4 1 10 7 10"/>
                                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                                        </svg>
                                    </button>
                                @else
                                    <button type="button"
                                            data-modal-open="archive"
                                            data-code="{{ $inc->incident_code }}"
                                            data-title="{{ $inc->title }}"
                                            data-action="{{ route('admin.incidents.archive', $inc) }}"
                                            class="flex h-7 w-7 items-center justify-center rounded-md border border-amber-500/30 bg-amber-500/10 text-amber-400 transition hover:bg-amber-500 hover:text-[#1B1405]"
                                            title="Remove from Map">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                                            <line x1="8" y1="2" x2="8" y2="18"/>
                                            <line x1="16" y1="6" x2="16" y2="22"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">
                            No incidents match your filters.
                            @if($hasFilters) <a href="{{ route('admin.incidents.index') }}" class="ml-1 text-[#A78BFA] hover:underline">Reset filters</a> @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═════ Card list (below xl: tablets and phones) ═════ --}}
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:hidden">
        @forelse($incidents as $inc)
            @php
                $assignment   = $inc->responderAssignments->sortByDesc('assigned_at')->first();
                $investigator = $inc->investigations->sortByDesc('investigation_date')->first()?->investigator;
                [$stLabel, $stClass, $stDot] = $statusStyle[$inc->status] ?? [ucfirst(strtolower($inc->status)), 'bg-amber-500/15 text-amber-300 border-amber-500/40', 'bg-amber-400'];
                [$sevLabel, $sevClass] = $severityStyle[$inc->severity] ?? [ucfirst(strtolower($inc->severity)), 'bg-slate-500/15 text-slate-300 border-slate-500/40'];
            @endphp
            <article class="flex flex-col gap-3 rounded-xl border border-[#2A3440] bg-[#151B23] p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <span class="block font-mono text-[10px] font-bold text-[#A78BFA]">{{ $inc->incident_code }}</span>
                        <h3 class="mt-0.5 break-words text-sm font-bold leading-snug text-white">{{ $inc->title }}</h3>
                        <p class="mt-0.5 text-[11px] text-slate-400">{{ ucfirst(strtolower($inc->incident_type)) }} &middot; Brgy. {{ $inc->barangay?->name ?? 'San Francisco' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $sevClass }}">{{ $sevLabel }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $stClass }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $stDot }}"></span>{{ $stLabel }}
                    </span>
                    @if($inc->isArchivedFromMap())
                        <span class="inline-flex items-center rounded-full border border-amber-400/40 bg-amber-400/10 px-1.5 py-0.5 text-[9px] font-medium text-amber-300">Removed from map</span>
                    @endif
                </div>

                <dl class="grid grid-cols-2 gap-x-3 gap-y-1.5 rounded-lg border border-[#2A3440] bg-[#11161D] p-2.5 text-[11px]">
                    <div class="min-w-0"><dt class="text-slate-500">Reporter</dt><dd class="truncate text-slate-200">{{ $inc->reporter?->name ?? 'Field scout' }}</dd></div>
                    <div class="min-w-0"><dt class="text-slate-500">Investigator</dt><dd class="truncate text-slate-200">{{ $investigator?->name ?? '—' }}</dd></div>
                    <div class="min-w-0"><dt class="text-slate-500">Responder</dt><dd class="truncate {{ $assignment?->responder ? 'font-semibold text-emerald-300' : 'text-slate-500' }}">{{ $assignment?->responder?->name ?? '—' }}</dd></div>
                    <div class="min-w-0">
                        <dt class="text-slate-500">{{ $inc->status === 'RESOLVED' ? 'Resolved' : 'Reported' }}</dt>
                        <dd class="text-slate-200">
                            @if($inc->status === 'RESOLVED')
                                {{ ($inc->investigation_completed_at ?? $inc->updated_at)?->format('M j, Y') }}
                            @else
                                {{ $inc->created_at?->format('M j, Y') ?? '—' }}
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="mt-auto flex justify-end border-t border-[#2A3440] pt-3">
                    <div class="flex items-center gap-1.5">
                        {{-- View Details --}}
                        <a href="{{ route('admin.incidents.show', $inc) }}"
                           class="flex h-7 w-7 items-center justify-center rounded-md border border-[#2A3440] bg-[#11161D] text-slate-400 transition hover:border-[#8B5CF6] hover:text-[#A78BFA]"
                           title="View Details">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>

                        {{-- Delete Action --}}
                        <button type="button"
                                data-modal-open="delete"
                                data-code="{{ $inc->incident_code }}"
                                data-title="{{ $inc->title }}"
                                data-action="{{ route('admin.incidents.destroy', $inc) }}"
                                class="flex h-7 w-7 items-center justify-center rounded-md border border-rose-500/30 bg-rose-500/10 text-rose-400 transition hover:bg-rose-500 hover:text-white"
                                title="Delete Incident">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                        </button>

                        {{-- Archive / Restore Action --}}
                        @if($inc->isArchivedFromMap())
                            <button type="button"
                                    data-modal-open="restore"
                                    data-code="{{ $inc->incident_code }}"
                                    data-title="{{ $inc->title }}"
                                    data-action="{{ route('admin.incidents.restore', $inc) }}"
                                    class="flex h-7 w-7 items-center justify-center rounded-md border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 transition hover:bg-emerald-500 hover:text-white"
                                    title="Restore to Map">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="1 4 1 10 7 10"/>
                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                                </svg>
                            </button>
                        @else
                            <button type="button"
                                    data-modal-open="archive"
                                    data-code="{{ $inc->incident_code }}"
                                    data-title="{{ $inc->title }}"
                                    data-action="{{ route('admin.incidents.archive', $inc) }}"
                                    class="flex h-7 w-7 items-center justify-center rounded-md border border-amber-500/30 bg-amber-500/10 text-amber-400 transition hover:bg-amber-500 hover:text-[#1B1405]"
                                    title="Remove from Map">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                                    <line x1="8" y1="2" x2="8" y2="18"/>
                                    <line x1="16" y1="6" x2="16" y2="22"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-xl border border-[#2A3440] bg-[#151B23] p-10 text-center text-sm text-slate-500">
                No incidents match your filters.
                @if($hasFilters) <a href="{{ route('admin.incidents.index') }}" class="ml-1 text-[#A78BFA] hover:underline">Reset filters</a> @endif
            </div>
        @endforelse
    </div>

    {{-- ═════ Pagination ═════ --}}
    <div class="pt-1 text-sm">
        {{ $incidents->withQueryString()->links() }}
    </div>

</div>

{{-- ═════════════════ MODALS ═════════════════ --}}

{{-- NEW: Archive All Resolved (yellow) --}}
<div id="modal-archive-resolved" data-modal-backdrop class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="archive-resolved-title">
    <div class="w-full max-w-md space-y-4 rounded-2xl border border-amber-400/40 bg-[#151B23] p-5 shadow-2xl sm:p-6">
        <div class="flex items-start gap-3">
            <div class="shrink-0 rounded-xl border border-amber-400/30 bg-amber-400/10 p-2.5 text-amber-300">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
            </div>
            <div>
                <h3 id="archive-resolved-title" class="text-base font-bold text-white">Archive all resolved incidents?</h3>
                <p class="mt-1 text-sm text-slate-400">This will remove <strong>all</strong> currently resolved incidents from the live GIS map. They will still be accessible in the database and can be restored individually.</p>
            </div>
        </div>
        <form method="POST" action="" data-modal-form>
            @csrf
            <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                <button type="button" data-modal-close class="min-h-[40px] rounded-xl border border-[#2A3440] bg-[#11161D] px-4 py-2 text-sm font-semibold text-slate-300 transition hover:text-white">Cancel</button>
                <button type="submit" class="min-h-[40px] rounded-xl bg-amber-400 px-5 py-2 text-sm font-bold text-[#1B1405] transition hover:bg-amber-300">Archive All Resolved</button>
            </div>
        </form>
    </div>
</div>

{{-- Remove from map (yellow) --}}
<div id="modal-archive" data-modal-backdrop class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="archive-title">
    <div class="w-full max-w-md space-y-4 rounded-2xl border border-amber-400/40 bg-[#151B23] p-5 shadow-2xl sm:p-6">
        <div class="flex items-start gap-3">
            <div class="shrink-0 rounded-xl border border-amber-400/30 bg-amber-400/10 p-2.5 text-amber-300">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
            </div>
            <div>
                <h3 id="archive-title" class="text-base font-bold text-white">Remove from active map?</h3>
                <p class="mt-1 text-sm text-slate-400">This resolved incident will no longer appear on the live GIS maps. The record, evidence and transcripts stay saved, and you can restore it any time.</p>
            </div>
        </div>
        <div class="space-y-1 rounded-xl border border-[#2A3440] bg-[#11161D] p-3 text-sm">
            <p class="text-slate-400">Code <strong data-fill="code" class="ml-1 font-mono text-white"></strong></p>
            <p class="text-slate-400">Title <span data-fill="title" class="ml-1 text-slate-200"></span></p>
        </div>
        <form method="POST" action="" data-modal-form>
            @csrf
            <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                <button type="button" data-modal-close class="min-h-[40px] rounded-xl border border-[#2A3440] bg-[#11161D] px-4 py-2 text-sm font-semibold text-slate-300 transition hover:text-white">Cancel</button>
                <button type="submit" class="min-h-[40px] rounded-xl bg-amber-400 px-5 py-2 text-sm font-bold text-[#1B1405] transition hover:bg-amber-300">Remove from map</button>
            </div>
        </form>
    </div>
</div>

{{-- Restore to map (green) --}}
<div id="modal-restore" data-modal-backdrop class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="restore-title">
    <div class="w-full max-w-md space-y-4 rounded-2xl border border-emerald-500/40 bg-[#151B23] p-5 shadow-2xl sm:p-6">
        <div class="flex items-start gap-3">
            <div class="shrink-0 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-2.5 text-emerald-300">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            </div>
            <div>
                <h3 id="restore-title" class="text-base font-bold text-white">Restore to active map?</h3>
                <p class="mt-1 text-sm text-slate-400">This incident will show on the live GIS maps again for investigators and responders.</p>
            </div>
        </div>
        <div class="space-y-1 rounded-xl border border-[#2A3440] bg-[#11161D] p-3 text-sm">
            <p class="text-slate-400">Code <strong data-fill="code" class="ml-1 font-mono text-white"></strong></p>
            <p class="text-slate-400">Title <span data-fill="title" class="ml-1 text-slate-200"></span></p>
        </div>
        <form method="POST" action="" data-modal-form>
            @csrf
            <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                <button type="button" data-modal-close class="min-h-[40px] rounded-xl border border-[#2A3440] bg-[#11161D] px-4 py-2 text-sm font-semibold text-slate-300 transition hover:text-white">Cancel</button>
                <button type="submit" class="min-h-[40px] rounded-xl bg-emerald-500 px-5 py-2 text-sm font-bold text-white transition hover:bg-emerald-400">Restore to map</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete (red) --}}
<div id="modal-delete" data-modal-backdrop class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/75 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="delete-title">
    <div class="w-full max-w-md space-y-4 rounded-2xl border border-rose-500/40 bg-[#151B23] p-5 shadow-2xl sm:p-6">
        <div class="flex items-start gap-3">
            <div class="shrink-0 rounded-xl border border-rose-500/30 bg-rose-500/10 p-2.5 text-rose-400">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <h3 id="delete-title" class="text-base font-bold text-white">Delete this incident?</h3>
                <p class="mt-1 text-sm text-slate-400">This permanently deletes the incident, its evidence files and its investigations. This can't be undone.</p>
            </div>
        </div>
        <div class="space-y-1 rounded-xl border border-[#2A3440] bg-[#11161D] p-3 text-sm">
            <p class="text-slate-400">Code <strong data-fill="code" class="ml-1 font-mono text-white"></strong></p>
            <p class="text-slate-400">Title <span data-fill="title" class="ml-1 text-slate-200"></span></p>
        </div>
        <form method="POST" action="" data-modal-form>
            @csrf
            @method('DELETE')
            <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                <button type="button" data-modal-close class="min-h-[40px] rounded-xl border border-[#2A3440] bg-[#11161D] px-4 py-2 text-sm font-semibold text-slate-300 transition hover:text-white">Cancel</button>
                <button type="submit" class="min-h-[40px] rounded-xl bg-rose-600 px-5 py-2 text-sm font-bold text-white transition hover:bg-rose-500">Delete incident</button>
            </div>
        </form>
    </div>
</div>
</main>

<script>
(function () {
    let openModal = null;

    function show(type, data) {
        const modal = document.getElementById('modal-' + type);
        if (!modal) return;
        modal.querySelectorAll('[data-fill]').forEach(function (el) {
            el.textContent = data[el.dataset.fill] || '';
        });
        modal.querySelector('[data-modal-form]').action = data.action;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        openModal = modal;
        var cancel = modal.querySelector('[data-modal-close]');
        if (cancel) cancel.focus();
    }

    function hide() {
        if (!openModal) return;
        openModal.classList.add('hidden');
        openModal.classList.remove('flex');
        openModal = null;
    }

    document.addEventListener('click', function (e) {
        var opener = e.target.closest('[data-modal-open]');
        if (opener) {
            show(opener.dataset.modalOpen, {
                code: opener.dataset.code,
                title: opener.dataset.title,
                action: opener.dataset.action
            });
            return;
        }
        if (e.target.closest('[data-modal-close]') || e.target.hasAttribute('data-modal-backdrop')) {
            hide();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hide();
    });
})();
</script>
@endsection
