@extends('layouts.app')
@section('title', 'My Reports — SFWatch')

@section('content')
@include('spectral.partials.sidebar')

<main class="flex-1 h-full overflow-y-auto bg-[#06090D] p-4 sm:p-6 lg:p-8">
    <div class="max-w-5xl mx-auto space-y-5">

        {{-- ── Page Header ── --}}
        <div class="flex items-center justify-between pb-4 border-b border-[#1E2631]">
            <div>
                <h1 class="text-base font-bold text-white tracking-tight">My Incident Reports</h1>
                <p class="text-[11px] text-[#64748B] mt-0.5 font-mono">
                    San Francisco Node &mdash; {{ $incidents->count() }} {{ $incidents->count() === 1 ? 'record' : 'records' }} on file
                </p>
            </div>
            {{-- Summary pills --}}
            <div class="hidden sm:flex items-center gap-2">
                @php
                    $pendingCount  = $incidents->whereIn('status', ['PENDING'])->count();
                    $activeCount   = $incidents->whereIn('status', ['UNDER INVESTIGATION', 'VERIFIED'])->count();
                    $resolvedCount = $incidents->where('status', 'RESOLVED')->count();
                @endphp
                @if($pendingCount > 0)
                <span class="text-[10px] font-mono font-bold px-2.5 py-1 rounded-lg bg-yellow-500/10 text-yellow-400 border border-yellow-500/25">
                    {{ $pendingCount }} Pending
                </span>
                @endif
                @if($activeCount > 0)
                <span class="text-[10px] font-mono font-bold px-2.5 py-1 rounded-lg bg-[#8B5CF6]/10 text-[#8B5CF6] border border-[#8B5CF6]/25">
                    {{ $activeCount }} Active
                </span>
                @endif
                @if($resolvedCount > 0)
                <span class="text-[10px] font-mono font-bold px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                    {{ $resolvedCount }} Resolved
                </span>
                @endif
            </div>
        </div>

        {{-- ── Filter Bar ── --}}
        <div class="flex items-center gap-2 flex-wrap" id="filter-bar">
            @php
                $filterOptions = [
                    'ALL'                 => 'All Reports',
                    'PENDING'             => 'Pending',
                    'UNDER INVESTIGATION' => 'Under Investigation',
                    'VERIFIED'            => 'Verified',
                    'RESOLVED'            => 'Resolved',
                ];
            @endphp
            @foreach($filterOptions as $val => $label)
            @php
                $cnt = $val === 'ALL' ? $incidents->count() : $incidents->where('status', $val)->count();
            @endphp
            <button
                type="button"
                data-filter="{{ $val }}"
                onclick="filterReports('{{ $val }}')"
                class="filter-btn text-[10px] font-mono font-semibold px-3 py-1.5 rounded-lg border transition-all
                    {{ $val === 'ALL'
                        ? 'bg-[#8B5CF6] text-white border-[#8B5CF6]'
                        : 'bg-[#151B23] text-[#64748B] border-[#2A3440] hover:border-[#8B5CF6]/50 hover:text-white' }}">
                {{ $label }}
                @if($cnt > 0)
                <span class="ml-1 opacity-75">({{ $cnt }})</span>
                @endif
            </button>
            @endforeach
        </div>

        {{-- ── Incident Cards ── --}}
        <div class="space-y-4" id="incidents-list">
        @forelse($incidents as $inc)
        @php
            $latestInv      = $inc->investigations->sortByDesc('investigation_date')->first();
            $thumbEv        = $inc->evidence->first();
            $cardAssignment = $inc->responderAssignments->sortByDesc('assigned_at')->first();

            // HP / Progress
            $cardDefaultHp  = ['CRITICAL' => 150, 'HIGH' => 100, 'MEDIUM' => 60, 'LOW' => 30][$inc->severity] ?? 100;
            $cardMaxHp      = $cardAssignment?->anomaly_max_hp ?: ($inc->anomaly_max_hp ?: $cardDefaultHp);
            $cardHp         = ($cardAssignment && $cardAssignment->anomaly_hp !== null)
                                ? $cardAssignment->anomaly_hp
                                : ($inc->anomaly_hp ?? $cardMaxHp);
            $cardPct        = $cardMaxHp > 0 ? max(0, min(100, round(($cardHp / $cardMaxHp) * 100))) : 100;
            $cardProgress   = $cardAssignment?->response_progress ?? ($inc->response_progress ?? 0);
            $hpColor        = $cardPct <= 25 ? 'bg-emerald-500' : ($cardPct <= 60 ? 'bg-yellow-500' : 'bg-rose-500');
            $hpTextColor    = $cardPct <= 25 ? 'text-emerald-400' : ($cardPct <= 60 ? 'text-yellow-400' : 'text-rose-400');

            $showAnomaly = in_array($inc->status, ['VERIFIED', 'RESOLVED']);

            $sevClass = match($inc->severity) {
                'CRITICAL' => 'bg-black text-white border-gray-600',
                'HIGH'     => 'bg-red-500/15 text-red-500 border-red-500/30',
                'MEDIUM'   => 'bg-yellow-500/15 text-yellow-400 border-yellow-500/30',
                default    => 'bg-green-500/15 text-green-500 border-green-500/30',
            };

            $statusClass = match($inc->status) {
                'RESOLVED'            => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                'PENDING'             => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                'VERIFIED'            => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                'UNDER INVESTIGATION' => 'bg-[#8B5CF6]/10 text-[#8B5CF6] border-[#8B5CF6]/30',
                default               => 'bg-[#1E2631] text-[#9CA3AF] border-[#2A3440]',
            };
        @endphp
        <article
            data-status="{{ $inc->status }}"
            class="incident-card rounded-xl bg-[#151B23] border border-[#2A3440] hover:border-[#8B5CF6]/40 transition-all overflow-hidden shadow-sm">

            {{-- ── Card Header Strip ── --}}
            <div class="flex items-center justify-between px-4 py-2.5 bg-[#11161D] border-b border-[#1E2631]">
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('spectral.incidents.show', $inc->id) }}"
                       class="text-[11px] font-mono font-bold text-[#8B5CF6] hover:underline underline-offset-2">
                        {{ $inc->incident_code }}
                    </a>
                    <span class="hidden sm:inline text-[10px] text-[#64748B] font-mono">{{ $inc->incident_type }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-mono font-bold px-2 py-0.5 rounded border {{ $sevClass }}">
                        {{ $inc->severity }}
                    </span>
                    <span class="text-[9px] font-mono font-bold px-2 py-0.5 rounded border {{ $statusClass }}">
                        {{ $inc->status }}
                    </span>
                </div>
            </div>

            {{-- ── Card Body ── --}}
            <div class="flex flex-col sm:flex-row items-stretch gap-0">

                @if($thumbEv)
                @php
                    $evUrl = str_starts_with($thumbEv->file_path, 'http')
                        ? $thumbEv->file_path
                        : asset('storage/' . $thumbEv->file_path);
                @endphp
                <a href="{{ route('spectral.incidents.show', $inc->id) }}"
                   class="flex-shrink-0 w-full sm:w-48 h-44 relative overflow-hidden group block border-b sm:border-b-0 sm:border-r border-[#2A3440] bg-[#0E131A]">
                    <img src="{{ $evUrl }}" alt="Evidence" loading="lazy" decoding="async"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                </a>
                @else
                <a href="{{ route('spectral.incidents.show', $inc->id) }}"
                   class="flex-shrink-0 w-full sm:w-48 h-44 bg-[#0E131A] border-b sm:border-b-0 sm:border-r border-[#2A3440] flex items-center justify-center group hover:bg-[#11161D] transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-[#2A3440] group-hover:text-[#64748B] transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
                    </svg>
                </a>
                @endif

                {{-- Main Info --}}
                <div class="flex-1 min-w-0 p-3.5 sm:p-4 flex flex-col justify-between gap-2.5">

                    <div>
                        <a href="{{ route('spectral.incidents.show', $inc->id) }}"
                           class="block text-sm font-semibold text-white hover:text-[#8B5CF6] transition leading-snug truncate">
                            {{ $inc->title }}
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[10px] font-mono text-[#64748B]">
                        <div class="flex items-center gap-1.5 truncate">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span class="truncate">{{ $inc->barangay->name ?? 'San Francisco' }}, San Francisco</span>
                        </div>
                        <div class="flex items-center gap-1.5 truncate">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>{{ $inc->created_at->format('M j, Y \a\t g:i A') }}</span>
                        </div>
                    </div>

                    @if($latestInv)
                    <div class="rounded-lg bg-[#0E131A] border border-[#1E2631] px-3 py-2 space-y-1">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                <span class="text-[10px] font-mono font-bold text-[#8B5CF6]">Investigator Update</span>
                                <span class="text-[9px] text-[#64748B] font-mono hidden sm:inline">&bull; by {{ $latestInv->investigator?->name ?? 'System' }}</span>
                            </div>
                            <span class="text-[9px] text-[#64748B] font-mono">{{ \Carbon\Carbon::parse($latestInv->investigation_date)->diffForHumans() }}</span>
                        </div>
                        <p class="text-[11px] text-[#9CA3AF] leading-relaxed line-clamp-1 font-mono">{{ $latestInv->notes }}</p>
                    </div>
                    @else
                    <div class="rounded-lg bg-[#0E131A] border border-[#1E2631] px-3 py-2 flex items-center">
                        <div class="flex items-center gap-2 text-[10px] font-mono text-[#64748B]">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400/80 animate-pulse"></span>
                            <span class="text-slate-300 font-semibold">Investigation Status:</span>
                            <span>Awaiting review by lead investigator</span>
                        </div>
                    </div>
                    @endif

                </div>

                {{-- View & Delete Action --}}
                <div class="flex-shrink-0 flex items-center justify-between sm:justify-center gap-2 px-4 py-2.5 sm:px-3 sm:py-0 border-t sm:border-t-0 sm:border-l border-[#1E2631] bg-[#11161D]/40 sm:bg-transparent">
                    <a href="{{ route('spectral.incidents.show', $inc->id) }}"
                       class="flex items-center gap-1.5 text-[10px] font-mono font-bold text-[#8B5CF6] hover:text-white transition px-2.5 py-1.5 rounded bg-[#1B222C] sm:bg-transparent hover:bg-[#1B222C]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>View Details</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-[#64748B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    <button type="button"
                            onclick="openDeleteModal('{{ route('spectral.incidents.destroy', $inc->id) }}', '{{ $inc->incident_code }}', '{{ addslashes($inc->title) }}')"
                            title="Delete incident report"
                            class="flex items-center gap-1.5 px-2.5 py-1.5 text-xs text-[#64748B] hover:text-rose-400 hover:bg-rose-500/10 rounded transition font-mono">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        <span class="sm:hidden text-[10px]">Delete</span>
                    </button>
                </div>
            </div>

            {{-- ── Anomaly Condition ── --}}
            @if($showAnomaly)
            <div class="border-t border-[#1E2631] px-4 py-3 bg-[#0A0E14] space-y-3">
                <p class="text-[9px] font-mono font-bold text-[#64748B] uppercase tracking-widest">Anomaly Condition</p>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[10px] font-mono">
                        <span class="text-[#64748B]">Threat Level (HP)</span>
                        <span class="{{ $hpTextColor }} font-bold">{{ $cardHp }} / {{ $cardMaxHp }} HP</span>
                    </div>
                    <div class="w-full bg-[#151B23] h-2 rounded-full overflow-hidden border border-[#2A3440]">
                        <div class="{{ $hpColor }} h-full rounded-full transition-all duration-500" style="width: {{ $cardPct }}%;"></div>
                    </div>
                    <div class="flex justify-between text-[9px] font-mono text-[#3B4A5A]">
                        <span>Neutralized</span>
                        <span>{{ $cardPct }}%</span>
                        <span>Full Threat</span>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-[10px] font-mono">
                        <span class="text-[#64748B]">Response Progress</span>
                        <span class="text-emerald-400 font-bold">{{ $cardProgress }}%</span>
                    </div>
                    <div class="w-full bg-[#151B23] h-1.5 rounded-full overflow-hidden border border-[#2A3440]">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $cardProgress }}%;"></div>
                    </div>
                </div>
            </div>
            @endif

        </article>
        @empty
        <div class="text-center py-24">
            <div class="w-14 h-14 rounded-xl bg-[#151B23] border border-[#2A3440] flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-[#2A3440]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-white mb-1">No reports on file</p>
            <p class="text-xs text-[#64748B]">Submit an incident report using the button in the top navigation bar.</p>
        </div>
        @endforelse
        </div>

        <div id="filter-empty" class="hidden text-center py-16">
            <p class="text-sm font-semibold text-white mb-1">No records match this filter</p>
            <p class="text-xs text-[#64748B]">Try selecting a different status above.</p>
        </div>

    </div>
</main>

<script>
function filterReports(status) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        const isActive = btn.getAttribute('data-filter') === status;
        btn.classList.toggle('bg-[#8B5CF6]',   isActive);
        btn.classList.toggle('text-white',      isActive);
        btn.classList.toggle('border-[#8B5CF6]',isActive);
        btn.classList.toggle('bg-[#151B23]',    !isActive);
        btn.classList.toggle('text-[#64748B]',  !isActive);
        btn.classList.toggle('border-[#2A3440]',!isActive);
    });

    const cards   = document.querySelectorAll('.incident-card');
    let   visible = 0;

    cards.forEach(card => {
        const match = status === 'ALL' || card.getAttribute('data-status') === status;
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    const empty = document.getElementById('filter-empty');
    if (empty) empty.classList.toggle('hidden', visible > 0);
}
</script>
@endsection

@section('modals')
@include('spectral.partials.report_modal_partial')
@include('spectral.partials.delete_modal_partial')
@endsection