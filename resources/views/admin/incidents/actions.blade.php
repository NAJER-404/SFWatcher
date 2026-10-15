{{--
    Row actions for one incident.
    Usage: @include('admin.incidents.actions', ['inc' => $inc])

    - Resolved incidents: Details + Remove from map (or Restore to map if already removed). No delete.
    - Every other status:  Details + Delete.
--}}
@php
    $isResolved = $inc->status === 'RESOLVED';
    $isRemoved  = $inc->isArchivedFromMap();
@endphp

<div class="flex flex-wrap items-center gap-2">
    {{-- Details (always) --}}
    <a href="{{ route('admin.incidents.show', $inc) }}"
       class="inline-flex min-h-[36px] items-center gap-1.5 rounded-lg border border-[#2A3440] bg-[#11161D] px-3 py-1.5 text-xs font-semibold text-slate-100 transition hover:border-[#8B5CF6] hover:text-white">
        <svg class="h-3.5 w-3.5 text-[#A78BFA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Details
    </a>

    @if($isResolved)
        @if($isRemoved)
            {{-- Already removed from the active map: allow restoring --}}
            <button type="button"
                    data-modal-open="restore"
                    data-code="{{ $inc->incident_code }}"
                    data-title="{{ $inc->title }}"
                    data-action="{{ route('admin.incidents.restore', $inc) }}"
                    class="inline-flex min-h-[36px] items-center gap-1.5 rounded-lg border border-emerald-500/50 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-300 transition hover:bg-emerald-500/20">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Restore to map
            </button>
        @else
            {{-- Visible on the map: allow removing (yellow) --}}
            <button type="button"
                    data-modal-open="archive"
                    data-code="{{ $inc->incident_code }}"
                    data-title="{{ $inc->title }}"
                    data-action="{{ route('admin.incidents.archive', $inc) }}"
                    class="inline-flex min-h-[36px] items-center gap-1.5 rounded-lg border border-amber-400/60 bg-amber-400/10 px-3 py-1.5 text-xs font-semibold text-amber-300 transition hover:bg-amber-400/20">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                Remove from map
            </button>
        @endif
    @else
        {{-- Not resolved yet: delete is still allowed --}}
        <button type="button"
                data-modal-open="delete"
                data-code="{{ $inc->incident_code }}"
                data-title="{{ $inc->title }}"
                data-action="{{ route('admin.incidents.destroy', $inc) }}"
                class="inline-flex min-h-[36px] items-center gap-1.5 rounded-lg border border-rose-500/40 bg-[#11161D] px-3 py-1.5 text-xs font-semibold text-rose-400 transition hover:border-rose-500 hover:bg-rose-500/10">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            Delete
        </button>
    @endif
</div>
