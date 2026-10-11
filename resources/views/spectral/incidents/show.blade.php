@extends('layouts.app')

@section('title', 'Incident ' . $incident->incident_code . ' — Spectra')

@php
    $latestAssignment = $incident->responderAssignments->sortByDesc('assigned_at')->first();
    $evidence         = $incident->evidence;
    $mainEvidence     = $evidence->first();
    // Route of the Interactive Map page (map.blade.php). Run `php artisan route:list --name=spectral`
    // and put the real name FIRST in this list if it is different.
    $mapRouteName = collect(['spectral.map', 'spectral.interactive-map', 'spectral.interactive_map', 'spectral.gis'])
        ->first(fn ($n) => \Illuminate\Support\Facades\Route::has($n), 'spectral.dashboard');

    $fmt              = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('M j, h:i A') : null;

    // ── Shared look: same palette as the rest of the app ──
    $card  = 'rounded-2xl bg-[#151B23] border border-[#2A3440] shadow-xl';
    $inner = 'rounded-xl bg-[#11161D] border border-[#2A3440]';

    $severityClass = match ($incident->severity) {
        'CRITICAL' => 'bg-black text-white border-slate-500',
        'HIGH'     => 'bg-red-500/15 text-red-400 border-red-500/40',
        'MEDIUM'   => 'bg-yellow-500/15 text-yellow-400 border-yellow-500/40',
        default    => 'bg-green-500/15 text-green-400 border-green-500/40',
    };
    $statusClass = match ($incident->status) {
        'RESOLVED'            => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
        'VERIFIED'            => 'bg-teal-500/10 text-teal-300 border-teal-500/30',
        'UNDER INVESTIGATION' => 'bg-[#8B5CF6]/10 text-[#C4B5FD] border-[#8B5CF6]/30',
        'PENDING'             => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        default               => 'bg-[#1E2631] text-slate-300 border-[#2A3440]',
    };

    // ── Investigation progress (reference naming, vertical layout) ──
    $statusKey = $incident->status;

    $steps = [
        [
            'label' => 'Report Submitted',
            'done'  => true,
            'meta'  => $fmt($incident->incident_date),
        ],
        [
            'label' => 'Under Investigation',
            'done'  => in_array($statusKey, ['UNDER INVESTIGATION', 'VERIFIED', 'RESOLVED']) || $incident->investigations->isNotEmpty(),
            'meta'  => $fmt($incident->investigations->where('result', 'UNDER INVESTIGATION')->sortBy('investigation_date')->first()?->investigation_date),
        ],
        [
            'label' => 'Verified',
            'done'  => $incident->investigation_result === 'CONFIRMED' || in_array($statusKey, ['VERIFIED', 'RESOLVED']) || $latestAssignment !== null,
            'meta'  => $fmt($incident->investigation_completed_at),
        ],
        [
            'label' => 'Responder Assigned',
            'done'  => (bool) ($latestAssignment && $latestAssignment->responder_id),
            'meta'  => ($latestAssignment && $latestAssignment->responder)
                        ? $latestAssignment->responder->name . ' (Class ' . $latestAssignment->responder->responder_class . ')'
                        : null,
        ],
        [
            'label' => 'Resolved',
            'done'  => $statusKey === 'RESOLVED',
            'meta'  => $statusKey === 'RESOLVED' ? $fmt($latestAssignment?->response_completed_at ?? $incident->updated_at) : null,
        ],
    ];

    $currentIndex = 0;
    foreach ($steps as $i => $s) {
        if ($s['done']) { $currentIndex = $i; }
    }
    $currentLabel = $steps[$currentIndex]['label'];
@endphp

@section('content')
<div class="flex-1 overflow-y-auto p-3.5 sm:p-6 space-y-5 sm:space-y-6 max-w-6xl mx-auto w-full">

    <!-- Breadcrumb & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#2A3440]">
        <div class="flex items-center gap-2 text-xs font-mono">
            <a href="{{ route($mapRouteName) }}" class="text-[#A78BFA] hover:underline inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                <span>GIS Map</span>
            </a>
            <span class="text-slate-600">/</span>
            <a href="{{ route('spectral.incidents.index') }}" class="text-[#A78BFA] hover:underline">Registry</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-400">{{ $incident->incident_code }}</span>
        </div>

        @if(($incident->reported_by === auth()->id() || auth()->user()?->isAdmin()) && ! in_array($incident->status, ['VERIFIED', 'RESOLVED']))
        <button type="button"
                onclick="openDeleteModal('{{ route('spectral.incidents.destroy', $incident->id) }}', '{{ $incident->incident_code }}', '{{ addslashes($incident->title) }}')"
                class="self-start sm:self-auto flex items-center gap-1.5 px-3 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 border border-rose-500/30 hover:border-rose-500/50 rounded-full text-xs font-mono font-bold transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            <span>Delete</span>
        </button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6 items-start">

        <!-- ═════ LEFT (2 cols) ═════ -->
        <div class="lg:col-span-2 space-y-5 sm:space-y-6">

            <!-- Hero -->
            <section class="{{ $card }} p-4 sm:p-6" aria-labelledby="incident-title">
                <div class="flex flex-col-reverse sm:flex-row gap-5">

                    <div class="flex-1 min-w-0 space-y-3.5">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-[11px] font-bold font-mono {{ $severityClass }}">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                {{ $incident->severity }}
                            </span>
                            <span class="text-xs font-mono font-bold text-[#A78BFA]">{{ $incident->incident_code }}</span>
                        </div>

                        <h1 id="incident-title" class="text-xl sm:text-2xl font-extrabold text-white leading-snug">{{ $incident->title }}</h1>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-300">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $incident->incident_date?->format('M j, Y') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {{ $incident->incident_date?->format('h:i A') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-bold font-mono {{ $statusClass }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>{{ $incident->status }}
                            </span>
                        </div>

                        <p class="text-xs sm:text-[13px] text-slate-300 leading-relaxed whitespace-pre-line">{{ $incident->description }}</p>
                    </div>

                    {{-- Hero photo: fixed box so any photo size looks the same --}}
                    <div class="sm:w-56 shrink-0">
                        @if($mainEvidence)
                            <a href="{{ $mainEvidence->url }}" target="_blank" rel="noopener"
                               class="group relative block h-44 w-full overflow-hidden rounded-xl border border-[#2A3440] bg-[#11161D]">
                                <img src="{{ $mainEvidence->url }}" alt="Evidence: {{ $mainEvidence->file_name }}" loading="lazy" decoding="async"
                                     class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            </a>
                        @else
                            <div class="grid h-44 w-full place-items-center rounded-xl border border-dashed border-[#2A3440] bg-[#11161D] text-[11px] text-slate-500">No photo attached</div>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Investigation Progress -->
            <section class="{{ $card }} p-5 space-y-3 font-mono" aria-labelledby="progress-heading">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#1E2631] pb-2.5">
                    <h2 id="progress-heading" class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white">
                        <svg class="h-4 w-4 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Investigation Progress
                    </h2>
                    <span class="rounded-md border border-[#8B5CF6]/40 bg-[#8B5CF6]/10 px-2 py-0.5 text-[10px] font-bold text-[#C4B5FD]">{{ $currentLabel }}</span>
                </div>

                <ol class="space-y-2 pt-1 text-xs">
                    @foreach($steps as $i => $step)
                        @php
                            $state   = $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'current' : 'pending');
                            $isFinal = $state === 'current' && $statusKey === 'RESOLVED';
                        @endphp

                        <li class="flex items-center gap-3" @if($state === 'current') aria-current="step" @endif>
                            @if($state === 'done' || $isFinal)
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-400 ring-2 ring-emerald-500/20" aria-hidden="true"></span>
                            @elseif($state === 'current')
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-[#8B5CF6] ring-2 ring-[#8B5CF6]/30 motion-safe:animate-pulse" aria-hidden="true"></span>
                            @else
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-slate-600" aria-hidden="true"></span>
                            @endif

                            <span class="{{ $state === 'pending' ? 'text-slate-600' : ($isFinal ? 'text-emerald-400 font-bold' : ($state === 'current' ? 'text-[#C4B5FD] font-bold' : 'text-white font-bold')) }}">
                                {{ $step['label'] }}
                            </span>

                            @if($step['meta'] && $state !== 'pending')
                                <span class="ml-auto text-right text-[10px] {{ $state === 'current' && ! $isFinal ? 'text-[#A78BFA]' : 'text-slate-500' }}">{{ $step['meta'] }}</span>
                            @endif
                        </li>

                        @unless($loop->last)
                            <li class="pl-1 text-[10px] leading-none text-slate-700" aria-hidden="true">↓</li>
                        @endunless
                    @endforeach
                </ol>
            </section>

            <!-- Attached Evidence + Details (side by side, like the reference) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">

                <section class="{{ $card }} p-5 space-y-3" aria-labelledby="evidence-heading">
                    <h2 id="evidence-heading" class="flex items-center gap-2 text-xs font-mono font-bold uppercase tracking-wider text-white">
                        <svg class="h-4 w-4 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        Attached Evidence ({{ $evidence->count() }})
                    </h2>

                    @forelse($evidence as $ev)
                        <a href="{{ $ev->url }}" target="_blank" rel="noopener" class="group block {{ $inner }} p-2">
                            <span class="relative block h-36 w-full overflow-hidden rounded-lg bg-[#0E131A]">
                                <img src="{{ $ev->url }}" alt="{{ $ev->file_name }}" loading="lazy" decoding="async"
                                     class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            </span>
                            <span class="mt-2 flex items-center justify-between gap-2 px-1 text-[11px]">
                                <span class="truncate font-mono text-slate-300">{{ $ev->file_name }}</span>
                                <span class="shrink-0 font-mono text-[10px] text-slate-500">{{ $ev->created_at?->format('M j, h:i A') }}</span>
                            </span>
                        </a>
                    @empty
                        <p class="{{ $inner }} px-3 py-8 text-center text-xs text-slate-500">No photographic evidence submitted with this report.</p>
                    @endforelse
                </section>

                <section class="{{ $card }} p-5 space-y-3" aria-labelledby="details-heading">
                    <h2 id="details-heading" class="flex items-center gap-2 text-xs font-mono font-bold uppercase tracking-wider text-white">
                        <svg class="h-4 w-4 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Details
                    </h2>

                    <dl class="divide-y divide-[#1E2631] text-xs">
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="flex items-center gap-2 text-slate-500">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 1v3M12 20v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M1 12h3M20 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/></svg>
                                Incident type
                            </dt>
                            <dd class="text-right font-medium text-white">{{ $incident->incident_type }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="flex items-center gap-2 text-slate-500">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Reported by
                            </dt>
                            <dd class="text-right font-medium text-white">{{ $incident->reporter->name ?? 'Civilian Observer' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 py-3">
                            <dt class="flex items-center gap-2 text-slate-500">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                Last updated
                            </dt>
                            <dd class="text-right font-medium text-white">{{ $incident->updated_at?->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            {{-- Anomaly condition — only once the report is confirmed --}}
            @if(in_array($incident->status, ['VERIFIED', 'RESOLVED']))
            @php
                $anomalyMaxVal  = $latestAssignment?->anomaly_max_hp ?: ($incident->anomaly_max_hp ?: config('spectral_response.anomaly_hp.' . $incident->severity, 100));
                $anomalyHpVal   = $latestAssignment?->anomaly_hp !== null ? $latestAssignment->anomaly_hp : ($incident->anomaly_hp !== null ? $incident->anomaly_hp : $anomalyMaxVal);
                $anomalyPctLeft = $anomalyMaxVal > 0 ? max(0, min(100, round(($anomalyHpVal / $anomalyMaxVal) * 100))) : 100;
                $hpText         = $anomalyPctLeft <= 25 ? 'text-emerald-400' : ($anomalyPctLeft <= 60 ? 'text-yellow-400' : 'text-rose-400');
                $hpBar          = $anomalyPctLeft <= 25 ? 'bg-emerald-500' : ($anomalyPctLeft <= 60 ? 'bg-yellow-500' : 'bg-rose-500');
                $responseProgress = $latestAssignment?->response_progress ?? ($incident->response_progress ?? 0);

                $responseTimeLeft = null;
                if ($latestAssignment?->response_deadline) {
                    $secsLeft = max(0, now()->diffInSeconds($latestAssignment->response_deadline, false));
                    $responseTimeLeft = $secsLeft > 0 ? sprintf('%d:%02d', intdiv($secsLeft, 60), $secsLeft % 60) : '00:00';
                }

                $responseStatusText = $latestAssignment
                    ? match ($latestAssignment->status) {
                        'ACTIVE'    => 'Under Response',
                        'ASSIGNED'  => 'Responder Assigned',
                        'ACCEPTED'  => 'Responder En Route',
                        'COMPLETED' => 'Neutralized',
                        default     => $latestAssignment->status,
                    }
                    : ($incident->status === 'VERIFIED' ? 'Confirmed — Awaiting Responder' : $incident->status);
            @endphp
            <section class="{{ $card }} p-5 space-y-4 font-mono" aria-labelledby="anomaly-heading">
                <div class="flex items-center justify-between border-b border-[#1E2631] pb-2.5">
                    <h2 id="anomaly-heading" class="text-xs font-bold uppercase tracking-wider text-white">Anomaly Condition</h2>
                    <span class="text-[10px] text-slate-500">{{ $responseStatusText }}</span>
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase text-slate-500">HP</span>
                        <span class="text-lg font-extrabold {{ $hpText }}">{{ $anomalyHpVal }} / {{ $anomalyMaxVal }}</span>
                    </div>
                    <div class="h-3 w-full overflow-hidden rounded-full border border-[#2A3440] bg-[#11161D]">
                        <div class="h-full {{ $hpBar }} transition-all duration-500" style="width: {{ $anomalyPctLeft }}%;"></div>
                    </div>
                </div>

                <div class="space-y-2 border-t border-[#1E2631] pt-3 text-[11px]">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Response progress</span>
                        <strong class="text-emerald-400">{{ $responseProgress }}%</strong>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full border border-[#2A3440] bg-[#11161D]">
                        <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ $responseProgress }}%;"></div>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-500">Time remaining</span>
                        <strong class="text-[#A78BFA]">
                            @if($responseTimeLeft) {{ $responseTimeLeft }}
                            @elseif($latestAssignment && in_array($latestAssignment->status, ['ASSIGNED', 'ACCEPTED'])) Begins on deployment
                            @else --:--
                            @endif
                        </strong>
                    </div>
                </div>
            </section>
            @endif

            <!-- Activity log -->
            <section class="{{ $card }} p-5 space-y-3" aria-labelledby="log-heading">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 id="log-heading" class="text-xs font-mono font-bold uppercase tracking-wider text-white">Investigation &amp; Action Log</h2>
                    <span class="text-[10px] text-slate-500">Investigator notes are private</span>
                </div>

                <ul class="divide-y divide-[#1E2631]">
                    @forelse($incident->investigations->sortByDesc('investigation_date') as $inv)
                        <li class="flex items-center justify-between gap-3 py-2.5 text-xs">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="shrink-0 rounded border border-[#2A3440] bg-[#11161D] px-2 py-0.5 text-[10px] font-mono font-bold text-[#A78BFA]">{{ $inv->result ?? 'UPDATE' }}</span>
                                <span class="truncate font-semibold text-white">{{ $inv->investigator->name ?? 'Administrator' }}</span>
                            </div>
                            <span class="shrink-0 font-mono text-[10px] text-slate-500">{{ $inv->investigation_date?->format('M j, H:i') }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-center text-xs text-slate-500">Pending initial review.</li>
                    @endforelse
                </ul>
            </section>

        </div>

        <!-- ═════ RIGHT (1 col) ═════ -->
        <div class="space-y-5 sm:space-y-6">

            <!-- Incident Location -->
            <section class="{{ $card }} p-5 space-y-3" aria-labelledby="location-heading">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="location-heading" class="flex items-center gap-2 text-xs font-mono font-bold uppercase tracking-wider text-white">
                        <svg class="h-4 w-4 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Incident Location
                    </h2>
                    <span class="rounded-md border border-[#2A3440] bg-[#11161D] px-2 py-0.5 text-[10px] font-mono text-slate-300">Brgy. {{ $incident->barangay->name ?? 'San Francisco' }}</span>
                </div>

                <div id="mini-incident-map" class="h-56 w-full overflow-hidden rounded-xl border border-[#2A3440]"></div>

                <a href="{{ route($mapRouteName) }}?zoom_lat={{ $incident->latitude }}&zoom_lng={{ $incident->longitude }}&zoom_incident={{ $incident->id }}"
                   class="group flex w-full items-center justify-center gap-2 rounded-xl border border-[#2A3440] bg-[#11161D] py-2.5 text-[11px] font-mono font-bold text-slate-400 transition hover:border-[#8B5CF6]/60 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-[#A78BFA] transition-transform group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>View on GIS Map</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-500 transition-colors group-hover:text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </section>

            <!-- Location Information -->
            <section class="{{ $card }} p-5" aria-labelledby="locinfo-heading">
                <h2 id="locinfo-heading" class="flex items-center gap-2 text-xs font-mono font-bold uppercase tracking-wider text-white">
                    <svg class="h-4 w-4 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Location Information
                </h2>

                <dl class="mt-3 divide-y divide-[#1E2631] text-xs">
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <dt class="text-slate-500">Barangay</dt>
                        <dd class="text-right font-medium text-white">{{ $incident->barangay->name ?? 'San Francisco' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <dt class="text-slate-500">Municipality</dt>
                        <dd class="text-right font-medium text-white">{{ $incident->barangay->municipality ?? 'San Francisco' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <dt class="text-slate-500">Province</dt>
                        <dd class="text-right font-medium text-white">{{ $incident->barangay->province ?? 'Agusan del Sur' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2.5">
                        <dt class="text-slate-500">Coordinates</dt>
                        <dd class="text-right font-mono font-medium text-white">{{ number_format($incident->latitude, 4) }}, {{ number_format($incident->longitude, 4) }}</dd>
                    </div>
                </dl>
            </section>

            @if(auth()->user()?->isAdmin())
            <!-- Administrator Action Workflow Box -->
            <section class="rounded-2xl bg-[#1B222C] border border-[#8B5CF6]/40 p-5 space-y-4 shadow-xl">
                <div class="pb-2 border-b border-[#2A3440]">
                    <h2 class="text-xs font-mono font-bold uppercase tracking-wider text-white">Administrator Action</h2>
                </div>

                <form method="POST" action="{{ route('spectral.incidents.update-status', $incident->id) }}" class="space-y-3 text-xs">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300 uppercase">Advance Workflow Status</label>
                        <select name="status" class="ecto-select">
                            <option value="PENDING" {{ $incident->status === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            <option value="UNDER INVESTIGATION" {{ $incident->status === 'UNDER INVESTIGATION' ? 'selected' : '' }}>UNDER INVESTIGATION</option>
                            <option value="VERIFIED" {{ $incident->status === 'VERIFIED' ? 'selected' : '' }}>VERIFIED</option>
                            <option value="RESOLVED" {{ $incident->status === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300 uppercase">Update Severity</label>
                        <select name="severity" class="ecto-select">
                            <option value="LOW" {{ $incident->severity === 'LOW' ? 'selected' : '' }}>LOW</option>
                            <option value="MEDIUM" {{ $incident->severity === 'MEDIUM' ? 'selected' : '' }}>MEDIUM</option>
                            <option value="HIGH" {{ $incident->severity === 'HIGH' ? 'selected' : '' }}>HIGH</option>
                            <option value="CRITICAL" {{ $incident->severity === 'CRITICAL' ? 'selected' : '' }}>CRITICAL</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-300 uppercase">Field Investigation Notes</label>
                        <textarea name="notes" rows="3" class="ecto-input" placeholder="Enter findings, ward stabilization notes, or dispatch directives..." required></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold rounded-xl transition shadow-md shadow-[#8B5CF6]/30">
                        Commit Investigation Record
                    </button>
                </form>
            </section>
            @endif

        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lat = {{ $incident->latitude }};
        const lng = {{ $incident->longitude }};

        const miniMap = L.map('mini-incident-map', {
            center: [lat, lng],
            zoom: 16,
            zoomControl: false,
            attributionControl: false
        });

        // Google Satellite Tile Layer — identical to dashboard map API
        L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            attribution: 'Imagery &copy; Google',
            maxZoom: 21,
            subdomains: ['0','1','2','3'],
            tileSize: 256
        }).addTo(miniMap);

        const markerHtml = `
            <div class="gis-marker-wrapper">
                <div class="marker-incident {{ strtolower($incident->severity) }}">
                    <div class="marker-incident-pulse"></div>
                    <span style="font-size:11px;font-weight:bold;color:#FFFFFF;">●</span>
                </div>
            </div>`;

        L.marker([lat, lng], {
            icon: L.divIcon({
                className: '',
                html: markerHtml,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            })
        }).addTo(miniMap);
    });
</script>
@endpush
@endsection

@section('modals')
@include('spectral.partials.delete_modal_partial')
@endsection
