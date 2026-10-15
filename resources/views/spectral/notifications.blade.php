@extends('layouts.app')

@section('title', 'SFwatch — Notifications')

@section('content')
{{-- LEFT SIDEBAR --}}
@include('spectral.partials.sidebar')

{{-- MAIN CONTENT --}}
<div class="flex-1 flex flex-col h-full min-w-0 overflow-y-auto bg-[#070B12]">

    <div class="max-w-5xl mx-auto w-full px-4 sm:px-6 py-6 sm:py-8 space-y-5">

        {{-- ── HEADER ────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div>
                <nav class="flex items-center gap-2 text-[11px] font-mono text-[#38BDF8] mb-2">
                    <a href="{{ route('spectral.dashboard') }}" class="hover:underline">Dashboard</a>
                    <span class="text-slate-600">/</span>
                    <span class="text-slate-400">Notifications</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Notifications</h1>
                <p class="text-[12.5px] text-slate-400 mt-1">
                    Updates on your reports, investigations, and community alerts.
                </p>
            </div>

            <div class="flex items-center gap-2 text-[11px] font-mono">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('spectral.notifications.read') }}">
                        @csrf
                        <input type="hidden" name="all" value="1">
                        <button type="submit"
                                class="px-3 py-1.5 rounded-lg bg-[#38BDF8]/10 hover:bg-[#38BDF8]/20 border border-[#38BDF8]/40 text-[#38BDF8] hover:text-white font-bold transition">
                            ✓ Mark all as read
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ── FILTER TABS ───────────────────────────────────────────── --}}
        @php
            $tabs = [
                ['label' => 'All',    'value' => null,     'count' => $totalCount],
                ['label' => 'Unread', 'value' => 'unread', 'count' => $unreadCount],
                ['label' => 'Read',   'value' => 'read',   'count' => $totalCount - $unreadCount],
            ];
        @endphp

        <div class="flex items-center gap-1.5 p-1.5 rounded-xl bg-[#0e1626] border border-[#1e2d48] w-fit">
            @foreach($tabs as $tab)
                @php $isActive = request('filter') === $tab['value'] || (is_null($tab['value']) && !request('filter')); @endphp
                <a href="{{ route('spectral.notifications.index', $tab['value'] ? ['filter' => $tab['value']] : []) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-[12px] font-semibold transition
                          {{ $isActive ? 'bg-[#38BDF8] text-[#070B12]' : 'text-slate-400 hover:text-white hover:bg-[#151f33]' }}">
                    {{ $tab['label'] }}
                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-full {{ $isActive ? 'bg-[#070B12]/20 text-[#070B12]' : 'bg-[#151f33] text-slate-500' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- ── NOTIFICATIONS LIST ────────────────────────────────────── --}}
        <div class="rounded-2xl border border-[#1e2d48] bg-[#0e1626]/70 backdrop-blur-md overflow-hidden">

            @forelse($notifications as $notif)
                @php
                    // Icon + color mapping per notification type
                    $style = match($notif->type) {
                        'reported'    => ['bg' => 'bg-[#38BDF8]/10', 'border' => 'border-[#38BDF8]/40', 'text' => 'text-[#38BDF8]',  'icon' => 'file'],
                        'investigation' => ['bg' => 'bg-purple-500/10', 'border' => 'border-purple-500/40', 'text' => 'text-purple-300', 'icon' => 'search'],
                        'verified'    => ['bg' => 'bg-blue-500/10', 'border' => 'border-blue-500/40', 'text' => 'text-blue-300',   'icon' => 'check'],
                        'resolved'    => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/40', 'text' => 'text-emerald-300', 'icon' => 'shield'],
                        'response'    => ['bg' => 'bg-amber-500/10', 'border' => 'border-amber-500/40', 'text' => 'text-amber-300',   'icon' => 'zap'],
                        'evidence'    => ['bg' => 'bg-cyan-500/10', 'border' => 'border-cyan-500/40', 'text' => 'text-cyan-300',    'icon' => 'image'],
                        default       => ['bg' => 'bg-slate-500/10', 'border' => 'border-slate-500/40', 'text' => 'text-slate-300',   'icon' => 'bell'],
                    };
                @endphp

                <div class="relative flex items-start gap-4 p-4 sm:p-5 border-b border-[#1e2d48]/60 last:border-b-0 transition
                            {{ $notif->read ? 'hover:bg-[#121c30]/60' : 'bg-[#38BDF8]/[0.03] hover:bg-[#38BDF8]/[0.06]' }}">

                    {{-- Unread indicator dot --}}
                    @if(!$notif->read)
                        <span class="absolute left-1.5 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-[#38BDF8] shadow-[0_0_8px_rgba(56,189,248,0.8)]"></span>
                    @endif

                    {{-- Icon --}}
                    <div class="shrink-0 w-10 h-10 rounded-xl {{ $style['bg'] }} border {{ $style['border'] }} {{ $style['text'] }} flex items-center justify-center">
                        @if($style['icon'] === 'file')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        @elseif($style['icon'] === 'search')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        @elseif($style['icon'] === 'check')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @elseif($style['icon'] === 'shield')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        @elseif($style['icon'] === 'zap')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        @elseif($style['icon'] === 'image')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-[13.5px] font-bold {{ $notif->read ? 'text-slate-300' : 'text-white' }}">
                                {{ $notif->title }}
                            </h3>
                            <time class="shrink-0 text-[10.5px] font-mono text-slate-500 whitespace-nowrap" title="{{ $notif->created_at->format('M j, Y H:i') }}">
                                {{ $notif->created_at->diffForHumans() }}
                            </time>
                        </div>

                        <p class="mt-1 text-[12px] text-slate-400 leading-relaxed">
                            {{ $notif->message }}
                        </p>

                        {{-- Meta row --}}
                        <div class="mt-2.5 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-[11px] font-mono">
                            @if($notif->incident_code)
                                <span class="inline-flex items-center gap-1 text-[#38BDF8]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    {{ $notif->incident_code }}
                                </span>
                            @endif

                            @if($notif->link)
                                <a href="{{ $notif->link }}" class="text-slate-400 hover:text-white underline decoration-dotted transition">
                                    View details →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                {{-- Empty State --}}
                <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-[#151f33] border border-[#1e2d48] flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <h3 class="text-[14px] font-bold text-white mb-1">
                        {{ request('filter') === 'unread' ? 'All caught up!' : (request('filter') === 'read' ? 'No read notifications yet' : 'No notifications yet') }}
                    </h3>
                    <p class="text-[12px] text-slate-500 max-w-xs">
                        @if(request('filter'))
                            Try switching to a different filter.
                        @else
                            When your reports get investigated or resolved, you'll see updates here.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="pt-2">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
