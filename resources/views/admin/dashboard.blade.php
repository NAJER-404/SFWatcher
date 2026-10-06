@extends('layouts.admin')

@section('title', 'Dashboard — SpectraWatch')

@php
    // ---- Incident pipeline (a real sequence) ----
    // label, stats key, bar color, text color
    $pipeline = [
        ['Pending',        'pending_incidents',   'bg-slate-300',   'text-slate-200'],
        ['Investigating',  'under_investigation', 'bg-sky-400',     'text-sky-300'],
        ['Confirmed',      'confirmed_incidents', 'bg-[#A78BFA]',   'text-[#C4B5FD]'],
        ['Under response', 'under_response',      'bg-[#2DD4BF]',   'text-[#5EEAD4]'],
        ['Resolved',       'resolved_incidents',  'bg-emerald-400', 'text-emerald-300'],
    ];
    $incidentTotal = max((int) $incidentStats['total_incidents'], 1);
    $pct = fn ($value) => (int) round(($value / $incidentTotal) * 100);


    // ---- Users ----
    $userRoles = [
        ['Reporters',      'total_reporters',     'admin.users.reporters',     'bg-cyan-400'],
        ['Investigators',  'total_investigators', 'admin.users.investigators', 'bg-[#A78BFA]'],
        ['Responders',     'total_responders',    'admin.users.responders',    'bg-emerald-400'],
        ['Administrators', 'total_admins',        null,                        'bg-slate-400'],
    ];
    $userTotal = max((int) $userStats['total_users'], 1);

    // ---- Responders ----
    $responderClasses = [
        ['Class A', 'class_a', 'Elite',       170],
        ['Class B', 'class_b', 'Advanced',    145],
        ['Class C', 'class_c', 'Experienced', 120],
        ['Class D', 'class_d', 'Beginner',    100],
    ];
    $maxHp          = 170;
    $responderTotal = (int) $userStats['total_responders'];
    $available      = (int) $responderStats['available'];
    $readiness      = $responderTotal > 0 ? (int) round($available / $responderTotal * 100) : 0;

    // ---- Activity: type => [dot, badge] ----
    $activityStyles = [
        'ADMIN ACTION'      => ['bg-purple-400',  'bg-purple-500/10 text-purple-300 border-purple-500/30'],
        'PROMOTION'         => ['bg-emerald-400', 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30'],
        'USER REGISTRATION' => ['bg-cyan-400',    'bg-cyan-500/10 text-cyan-300 border-cyan-500/30'],
        'INCIDENT REPORT'   => ['bg-orange-400',  'bg-orange-500/10 text-orange-300 border-orange-500/30'],
        'DISPATCH'          => ['bg-blue-400',    'bg-blue-500/10 text-blue-300 border-blue-500/30'],
    ];
    $defaultActivityStyle = ['bg-slate-400', 'bg-slate-500/10 text-slate-300 border-slate-500/30'];

    $panel = 'rounded-2xl bg-[#151B23] border border-[#2A3440]';
    $link  = 'text-sm text-[#A78BFA] hover:text-white transition rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#2DD4BF]';
@endphp

@section('content')
<main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 space-y-6 max-w-7xl mx-auto w-full">

    {{-- Header --}}
    <header>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-white">Dashboard</h1>
            <p class="text-sm text-slate-400 mt-1">
                Welcome back, {{ $admin->name ?? 'Administrator' }}. Here is where things stand across the network.
            </p>
        </div>
    </header>


    {{-- Incident pipeline --}}
    <section class="{{ $panel }} p-5 sm:p-6" aria-labelledby="incidents-heading">
        <div class="flex items-start justify-between gap-4">
            <div>
                <span class="text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider block">INCIDENT STATISTICS</span>
                <h2 id="incidents-heading" class="text-sm font-semibold text-slate-300">Incident pipeline</h2>
                <p class="mt-2 flex items-baseline gap-2">
                    <span class="text-4xl font-bold text-white font-mono tabular-nums">{{ $incidentStats['total_incidents'] }}</span>
                    <span class="text-sm text-slate-500">incidents reported</span>
                </p>
            </div>
            <a href="{{ route('admin.incidents.index') }}" class="{{ $link }}">View all</a>
        </div>

        <ol class="mt-6 grid grid-cols-2 sm:grid-cols-5 gap-3">
            @foreach($pipeline as [$label, $key, $bar, $text])
                @php $value = (int) $incidentStats[$key]; @endphp
                <li class="relative rounded-xl border border-[#2A3440] bg-[#11161D] p-4">
                    <span class="absolute inset-x-0 top-0 h-0.5 rounded-t-xl {{ $bar }}" aria-hidden="true"></span>
                    <p class="text-xs text-slate-400">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-bold font-mono tabular-nums {{ $text }}">{{ $value }}</p>
                    <div class="mt-3 h-1 w-full rounded-full bg-[#1B222C] overflow-hidden" aria-hidden="true">
                        <div class="h-full rounded-full {{ $bar }}" style="width: {{ $pct($value) }}%"></div>
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-500">{{ $pct($value) }}% of total</p>

                    @unless($loop->last)
                        <svg class="hidden sm:block absolute -right-[11px] top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-slate-600"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 5l7 7-7 7"/>
                        </svg>
                    @endunless
                </li>
            @endforeach
        </ol>

    </section>

    {{-- Responders + Users --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Responders --}}
        <section class="{{ $panel }} p-5 sm:p-6" aria-labelledby="responders-heading">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider block">RESPONDER STATISTICS</span>
                    <h2 id="responders-heading" class="text-sm font-semibold text-slate-300">Responder readiness</h2>
                    <p class="mt-2 flex items-baseline gap-2">
                        <span class="text-4xl font-bold text-[#5EEAD4] font-mono tabular-nums">{{ $available }}</span>
                        <span class="text-sm text-slate-500">of {{ $responderTotal }} available for callout</span>
                    </p>
                </div>
                <a href="{{ route('admin.users.responders') }}" class="{{ $link }}">Manage</a>
            </div>

            <div class="mt-4 h-2 w-full rounded-full bg-[#11161D] overflow-hidden" role="img"
                 aria-label="{{ $readiness }} percent of responders available">
                <div class="h-full rounded-full bg-[#2DD4BF]" style="width: {{ $readiness }}%"></div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full text-sm">
                    <caption class="sr-only">Responders by class</caption>
                    <thead>
                        <tr class="text-left text-xs text-slate-500 border-b border-[#2A3440]">
                            <th scope="col" class="py-2 font-medium">Class</th>
                            <th scope="col" class="py-2 font-medium">Level</th>
                            <th scope="col" class="py-2 font-medium w-1/3">Health</th>
                            <th scope="col" class="py-2 font-medium text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#2A3440]">
                        @foreach($responderClasses as [$label, $key, $rank, $hp])
                            <tr>
                                <td class="py-3 font-medium text-slate-200">{{ $label }}</td>
                                <td class="py-3 text-slate-400">{{ $rank }}</td>
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1.5 flex-1 rounded-full bg-[#11161D] overflow-hidden" aria-hidden="true">
                                            <div class="h-full rounded-full bg-[#2DD4BF]/70" style="width: {{ round($hp / $maxHp * 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-400 font-mono tabular-nums w-14 text-right">{{ $hp }} HP</span>
                                    </div>
                                </td>
                                <td class="py-3 text-right font-mono font-bold tabular-nums text-white">{{ $responderStats[$key] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Users --}}
        <section class="{{ $panel }} p-5 sm:p-6" aria-labelledby="users-heading">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider block">USER STATISTICS</span>
                    <h2 id="users-heading" class="text-sm font-semibold text-slate-300">Users</h2>
                    <p class="mt-2 flex items-baseline gap-2">
                        <span class="text-4xl font-bold text-white font-mono tabular-nums">{{ $userStats['total_users'] }}</span>
                        <span class="text-sm text-slate-500">registered accounts</span>
                    </p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="{{ $link }}">View all</a>
            </div>

            <div class="mt-4 flex h-2 w-full overflow-hidden rounded-full bg-[#11161D]" role="img" aria-label="Users by role">
                @foreach($userRoles as [$label, $key, $routeName, $dot])
                    @if($userStats[$key] > 0)
                        <div class="{{ $dot }}" style="width: {{ round($userStats[$key] / $userTotal * 100, 2) }}%" title="{{ $label }}: {{ $userStats[$key] }}"></div>
                    @endif
                @endforeach
            </div>

            <ul class="mt-5 divide-y divide-[#2A3440] border-t border-[#2A3440]">
                @foreach($userRoles as [$label, $key, $routeName, $dot])
                    <li>
                        @if($routeName)
                            <a href="{{ route($routeName) }}"
                               class="group flex items-center justify-between py-3 text-sm rounded focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#2DD4BF]">
                                <span class="flex items-center gap-3 text-slate-300 group-hover:text-white transition">
                                    <span class="h-2 w-2 rounded-full {{ $dot }}" aria-hidden="true"></span>
                                    {{ $label }}
                                </span>
                                <span class="flex items-center gap-3">
                                    <span class="font-mono font-bold tabular-nums text-white">{{ $userStats[$key] }}</span>
                                    <svg class="h-4 w-4 text-slate-600 group-hover:text-slate-300 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </a>
                        @else
                            <div class="flex items-center justify-between py-3 text-sm">
                                <span class="flex items-center gap-3 text-slate-300">
                                    <span class="h-2 w-2 rounded-full {{ $dot }}" aria-hidden="true"></span>
                                    {{ $label }}
                                </span>
                                <span class="flex items-center gap-3">
                                    <span class="font-mono font-bold tabular-nums text-white">{{ $userStats[$key] }}</span>
                                    <span class="w-4" aria-hidden="true"></span>
                                </span>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    {{-- Recent activity (collapsible, scrolls after ~6 rows) --}}
    <details open class="group {{ $panel }}">
        <summary class="flex cursor-pointer list-none items-center justify-between px-5 sm:px-6 py-4 rounded-2xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#2DD4BF] [&::-webkit-details-marker]:hidden">
            <span class="flex flex-col">
                <span class="text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider block">RECENT SYSTEM ACTIVITY</span>
                <span class="flex items-center gap-3 mt-0.5">
                    <h2 class="text-sm font-semibold text-slate-300">Recent activity</h2>
                    <span class="text-xs text-slate-500">
                        {{ $recentActivity->count() }} {{ \Illuminate\Support\Str::plural('event', $recentActivity->count()) }}
                    </span>
                </span>
            </span>
            <svg class="h-4 w-4 text-slate-500 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </summary>

        @if($recentActivity->isNotEmpty())
            <ol class="max-h-[26rem] overflow-y-auto overscroll-contain border-t border-[#2A3440] px-5 sm:px-6 py-2"
                tabindex="0" aria-label="Recent activity list">
                @foreach($recentActivity as $event)
                    @php [$dot, $badge] = $activityStyles[$event['type']] ?? $defaultActivityStyle; @endphp
                    <li class="flex gap-4 py-4 {{ $loop->last ? '' : 'border-b border-[#2A3440]/60' }}">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>

                        <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-start justify-between gap-1 sm:gap-6">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-medium text-white">{{ $event['title'] }}</p>
                                    <span class="rounded border px-1.5 py-0.5 text-[11px] font-medium {{ $badge }}">
                                        {{ \Illuminate\Support\Str::ucfirst(strtolower($event['type'])) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400">{{ $event['description'] }}</p>
                            </div>

                            <div class="shrink-0 text-xs text-slate-500 sm:text-right">
                                <p class="text-slate-400">{{ $event['actor'] }}</p>
                                <p>
                                    @if($event['timestamp'])
                                        <time datetime="{{ \Carbon\Carbon::parse($event['timestamp'])->toIso8601String() }}">
                                            {{ \Carbon\Carbon::parse($event['timestamp'])->diffForHumans() }}
                                        </time>
                                    @else
                                        —
                                    @endif
                                </p>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="border-t border-[#2A3440] px-6 py-10 text-center text-sm text-slate-500">
                No activity yet. Registrations, incident reports, and dispatches will show up here.
            </p>
        @endif
    </details>

</main>
@endsection
