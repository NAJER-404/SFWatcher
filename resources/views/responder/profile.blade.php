@extends('layouts.responder')
@section('title', 'My Profile — Responder Portal')

@php
    $user = $user ?? auth('responder')->user() ?? auth()->user();

    $nameParts = preg_split('/\s+/', trim($user->name));
    $initials  = strtoupper(mb_substr($nameParts[0] ?? 'R', 0, 1) . mb_substr($nameParts[1] ?? '', 0, 1));

    $currentStatus = strtoupper($user->responder_status ?? 'AVAILABLE');

    // Status options the responder can switch between
    $statusOptions = [
        'AVAILABLE' => [
            'label' => 'Available',
            'hint'  => 'Can be assigned to new incidents',
            'dot'   => 'bg-emerald-400',
            'text'  => 'text-emerald-300',
            'ring'  => 'border-emerald-400/60 bg-emerald-400/10',
        ],
        'BUSY' => [
            'label' => 'Busy',
            'hint'  => 'Not taking new assignments',
            'dot'   => 'bg-amber-400',
            'text'  => 'text-amber-300',
            'ring'  => 'border-amber-400/60 bg-amber-400/10',
        ],
        'ON_DUTY' => [
            'label' => 'On duty',
            'hint'  => 'Currently on shift',
            'dot'   => 'bg-[#8B5CF6]',
            'text'  => 'text-[#C4B5FD]',
            'ring'  => 'border-[#8B5CF6]/60 bg-[#8B5CF6]/10',
        ],
    ];
    $current = $statusOptions[$currentStatus] ?? $statusOptions['AVAILABLE'];

    $stats = $stats ?? ['total' => 0, 'new' => 0, 'active' => 0, 'completed' => 0];
    $total = $stats['total'];
@endphp

@section('content')
<main class="flex-1 h-full overflow-y-auto bg-[#06090D] p-4 sm:p-6 lg:p-8 min-w-0">
    <div class="max-w-5xl mx-auto space-y-5">

        {{-- Back link --}}
        <a href="{{ route('responder.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to dashboard
        </a>

        {{-- ── Identity card ── --}}
        <section class="rounded-2xl bg-[#151B23] border border-[#2A3440] overflow-hidden" aria-labelledby="identity-heading">
            <div class="h-16 bg-gradient-to-r from-[#8B5CF6]/30 via-[#2DD4BF]/10 to-transparent border-b border-[#2A3440]" aria-hidden="true"></div>

            <div class="px-5 pb-5 -mt-8">
                <div class="grid h-16 w-16 place-items-center rounded-2xl border-2 border-[#151B23] bg-[#1B222C] ring-1 ring-[#8B5CF6]/50 text-lg font-bold font-mono text-[#C4B5FD]" aria-hidden="true">
                    {{ $initials }}
                </div>

                <h2 id="identity-heading" class="mt-3 text-base font-bold text-white leading-tight">{{ $user->name }}</h2>
                <p class="text-xs text-slate-400 mt-0.5 break-all">{{ $user->email }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-md border border-[#8B5CF6]/40 bg-[#8B5CF6]/10 px-2 py-0.5 text-[10px] font-mono font-bold text-[#A78BFA]">
                        Class {{ $user->responder_class ?? 'D' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-md border border-[#2A3440] bg-[#1B222C] px-2 py-0.5 text-[10px] font-mono font-bold {{ $current['text'] }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $current['dot'] }}"></span>{{ $current['label'] }}
                    </span>
                </div>

                <dl class="mt-4 space-y-3 border-t border-[#2A3440] pt-4 text-xs">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[#64748B]">Member since</dt>
                        <dd class="text-slate-200 font-medium">{{ $user->created_at?->format('F j, Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[#64748B]">Location</dt>
                        <dd class="text-slate-200 font-medium text-right">San Francisco, Agusan del Sur</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[#64748B]">Experience</dt>
                        <dd class="text-slate-200 font-mono font-bold">{{ $user->xp ?? 0 }} XP</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[#64748B]">Successful responses</dt>
                        <dd class="text-slate-200 font-mono font-bold">{{ $user->successful_responses ?? 0 }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        {{-- ── Availability status switcher ── --}}
        <section class="rounded-2xl bg-[#151B23] border border-[#2A3440] p-5" aria-labelledby="status-heading">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="status-heading" class="text-sm font-bold text-white">My status</h2>
                    <p class="mt-1 text-xs text-slate-400">Investigators see this when choosing who to assign. Tap a status to switch.</p>
                </div>
                <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full border border-[#2A3440] bg-[#1B222C] px-2.5 py-1 text-[11px] font-semibold {{ $current['text'] }}">
                    <span class="h-2 w-2 rounded-full {{ $current['dot'] }}"></span>{{ $current['label'] }}
                </span>
            </div>

            <form method="POST" action="{{ route('responder.profile.status') }}" class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                @csrf
                @method('PUT')

                @foreach($statusOptions as $value => $opt)
                    @php $isActive = $currentStatus === $value; @endphp
                    <button type="submit" name="responder_status" value="{{ $value }}"
                            @if($isActive) aria-pressed="true" @else aria-pressed="false" @endif
                            class="group text-left rounded-xl border p-4 transition focus:outline-none focus:ring-2 focus:ring-[#8B5CF6]/50
                                   {{ $isActive ? $opt['ring'] : 'border-[#2A3440] bg-[#11161D] hover:border-[#3A4656] hover:bg-[#1B222C]' }}">
                        <span class="flex items-center justify-between gap-2">
                            <span class="flex items-center gap-2 text-sm font-bold {{ $isActive ? $opt['text'] : 'text-slate-200' }}">
                                <span class="h-2.5 w-2.5 rounded-full {{ $opt['dot'] }}"></span>{{ $opt['label'] }}
                            </span>
                            @if($isActive)
                                <svg class="w-4 h-4 {{ $opt['text'] }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                            @endif
                        </span>
                        <span class="mt-1.5 block text-xs text-slate-400">{{ $opt['hint'] }}</span>
                    </button>
                @endforeach
            </form>
        </section>

        {{-- ── Stats grid ── --}}
        <section aria-label="Response statistics" class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach([
                ['Total assigned', $total,             'text-white',       'bg-slate-400'],
                ['New',            $stats['new'],       'text-yellow-400',  'bg-yellow-400'],
                ['In progress',    $stats['active'],    'text-[#A78BFA]',   'bg-[#8B5CF6]'],
                ['Completed',      $stats['completed'], 'text-emerald-400', 'bg-emerald-400'],
            ] as [$label, $value, $text, $dot])
                <div class="rounded-xl bg-[#151B23] border border-[#2A3440] p-4">
                    <p class="flex items-center gap-2 text-[11px] text-[#64748B]">
                        <span class="h-1.5 w-1.5 rounded-full {{ $dot }}" aria-hidden="true"></span>{{ $label }}
                    </p>
                    <p class="mt-2 text-2xl font-bold font-mono tabular-nums {{ $text }}">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        {{-- ── Progress bar ── --}}
        @if($total > 0)
        <section class="rounded-2xl bg-[#151B23] border border-[#2A3440] p-5" aria-labelledby="dist-heading">
            <h2 id="dist-heading" class="text-xs font-semibold text-slate-300">Where your assignments stand</h2>
            <div class="mt-3 flex h-2 w-full overflow-hidden rounded-full bg-[#11161D]" role="img"
                 aria-label="{{ $stats['new'] }} new, {{ $stats['active'] }} in progress, {{ $stats['completed'] }} completed">
                @if($stats['new'] > 0)       <div class="bg-yellow-400"  style="width: {{ round($stats['new']       / $total * 100, 2) }}%"></div>@endif
                @if($stats['active'] > 0)    <div class="bg-[#8B5CF6]"   style="width: {{ round($stats['active']    / $total * 100, 2) }}%"></div>@endif
                @if($stats['completed'] > 0) <div class="bg-emerald-400" style="width: {{ round($stats['completed'] / $total * 100, 2) }}%"></div>@endif
            </div>
            <p class="mt-2 text-[11px] text-[#64748B]">
                {{ $stats['completed'] }} of {{ $total }} {{ \Illuminate\Support\Str::plural('assignment', $total) }} completed.
            </p>
        </section>
        @endif

    </div>
</main>
@endsection
