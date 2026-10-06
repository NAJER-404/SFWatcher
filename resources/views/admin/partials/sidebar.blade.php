@php
    // Feather-style icons (same set/stroke as the reporter sidebar).
    $icons = [
        'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'incidents' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
        'archived'  => '<polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>',
        'analytics' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'profile'   => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    ];

    // Sidebar items as specifically requested:
    // "all users, All incidents, Archived, Analytics,Profile,settings that's it and delete what i didnt mentioned"
    $navItems = [
        ['All users',     'admin.users.index',         ['admin.users.*'],               'users',     \App\Models\User::count()],
        ['All incidents', 'admin.incidents.index',     ['admin.incidents.index', 'admin.incidents.show'], 'incidents', \App\Models\Incident::count()],
        ['Archived',      'admin.incidents.archived',  ['admin.incidents.archived'],    'archived',  \App\Models\Incident::archivedFromMap()->count()],
        ['Analytics',     'admin.analytics',           ['admin.analytics'],             'analytics', null],
        ['Profile',       'admin.profile',             ['admin.profile'],               'profile',   null],
        ['Settings',      'admin.settings',            ['admin.settings'],              'settings',  null],
    ];

    $adminUser = auth()->user() ?? \Illuminate\Support\Facades\Auth::guard('admin')->user();
    $initial   = strtoupper(mb_substr($adminUser->name ?? 'A', 0, 1));
@endphp

<style>
    /* Desktop collapse (icons only). Mobile drawer is unaffected. */
    @media (min-width: 768px) {
        #admin-mobile-drawer[data-collapsed="true"] { width: 4.5rem; }
        #admin-mobile-drawer[data-collapsed="true"] .side-label { display: none; }
        #admin-mobile-drawer[data-collapsed="true"] .side-link { justify-content: center; padding-left: 0; padding-right: 0; }
        #admin-mobile-drawer[data-collapsed="true"] .side-brand { justify-content: center; padding-left: 0.75rem; padding-right: 0.75rem; }
        #admin-mobile-drawer[data-collapsed="true"] .side-toggle-icon { transform: rotate(180deg); }
        #admin-mobile-drawer[data-collapsed="true"] .side-user { justify-content: center; padding-left: 0; padding-right: 0; }
    }
    @media (prefers-reduced-motion: no-preference) {
        #admin-mobile-drawer { transition: width .2s ease, transform .2s ease; }
        .side-toggle-icon { transition: transform .2s ease; }
    }
</style>

<aside id="admin-mobile-drawer"
       data-collapsed="false"
       class="fixed md:static inset-y-0 left-0 z-50 w-72 md:w-64 bg-[#0E1319] border-r border-[#2A3440] flex flex-col flex-shrink-0 -translate-x-full md:translate-x-0 select-none shadow-2xl md:shadow-none">

    {{-- Header --}}
    <div class="side-brand flex items-center justify-between gap-2 pl-[26px] pr-3 py-4 border-b border-[#2A3440]">
        <a href="{{ route('admin.dashboard') }}"
           class="side-label min-w-0 rounded-lg focus-visible:outline focus-visible:outline-2 focus-visible:outline-slate-400"
           aria-label="Admin Panel">
            <span class="block truncate text-sm font-semibold text-slate-400">Admin Panel</span>
        </a>

        {{-- Desktop collapse toggle --}}
        <button type="button" id="admin-sidebar-toggle"
                class="hidden md:inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#2A3440] bg-[#151B23] text-slate-400 hover:text-white hover:border-[#3A4654] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-slate-400"
                aria-label="Collapse sidebar" aria-expanded="true" aria-controls="admin-mobile-drawer">
            <svg class="side-toggle-icon h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        {{-- Mobile close --}}
        <button type="button"
                onclick="AdminDrawer.close()"
                class="md:hidden inline-flex h-8 w-8 items-center justify-center rounded-lg border border-[#2A3440] bg-[#151B23] text-slate-300 hover:text-white transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-slate-400"
                aria-label="Close navigation">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6L6 18"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto p-3 space-y-1.5 text-sm" aria-label="Admin navigation">
        @foreach($navItems as [$label, $routeName, $patterns, $icon, $count])
            @php $active = request()->routeIs(...$patterns); @endphp
            <a href="{{ route($routeName) }}"
               title="{{ $label }}"
               @if($active) aria-current="page" @endif
               class="side-link group flex items-center gap-3 px-3 py-2.5 rounded-xl border-l-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-slate-400
                      {{ $active
                          ? 'bg-[#1B222C] border-slate-400 text-slate-200 font-semibold'
                          : 'border-transparent text-slate-400 hover:text-white hover:bg-[#151B23]' }}">
                <svg class="w-4 h-4 shrink-0 {{ $active ? 'text-slate-300' : 'text-[#64748B] group-hover:text-slate-300' }}"
                     xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    {!! $icons[$icon] !!}
                </svg>
                <span class="side-label flex-1 truncate">{{ $label }}</span>
                @if(!is_null($count))
                    <span class="side-label text-xs font-mono tabular-nums px-2 py-0.5 rounded-full {{ $active ? 'bg-[#2A3440] text-slate-200 font-bold' : 'bg-[#151B23] text-slate-400' }}">{{ $count }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Footer: Signed-in admin info --}}
    <div class="border-t border-[#2A3440] p-3">
        <div class="side-user flex items-center gap-3 px-[14px] py-1.5">
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#1B222C] border border-[#2A3440] text-xs font-semibold text-slate-300"
                  title="{{ $adminUser->name ?? 'Administrator' }}">{{ $initial }}</span>
            <span class="side-label min-w-0">
                <span class="block truncate text-sm text-slate-300 leading-tight font-medium">{{ $adminUser->name ?? 'Administrator' }}</span>
                <span class="block text-xs text-slate-500 leading-tight">Administrator</span>
            </span>
        </div>
    </div>
</aside>

<script>
    (function () {
        var aside  = document.getElementById('admin-mobile-drawer');
        var toggle = document.getElementById('admin-sidebar-toggle');
        var KEY    = 'spectrawatch.admin.sidebar.collapsed';
        if (!aside || !toggle) return;

        function apply(collapsed) {
            aside.setAttribute('data-collapsed', collapsed ? 'true' : 'false');
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }

        var saved = false;
        try { saved = localStorage.getItem(KEY) === '1'; } catch (e) {}
        apply(saved);

        toggle.addEventListener('click', function () {
            var next = aside.getAttribute('data-collapsed') !== 'true';
            apply(next);
            try { localStorage.setItem(KEY, next ? '1' : '0'); } catch (e) {}
        });
    })();
</script>
