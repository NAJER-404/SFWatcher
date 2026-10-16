@php
    // Feather-style icons (same set/stroke as the other portals).
    $icons = [
        'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'incidents' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'archived'  => '<polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>',
        'map'       => '<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/>',
        'analytics' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'profile'   => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    ];

    // Grouped like the investigator sidebar: [label, route, active patterns, icon, icon color, count]
    $sections = [
        'Management' => [
            ['All users',     'admin.users.index',        ['admin.users.*'],                                  'users',     'text-[#38BDF8]', \App\Models\User::count()],
            ['All incidents', 'admin.incidents.index',    ['admin.incidents.index', 'admin.incidents.show'],  'incidents', 'text-[#8B5CF6]', \App\Models\Incident::count()],
            ['Archived',      'admin.incidents.archived', ['admin.incidents.archived'],                       'archived',  'text-[#EAB308]', \App\Models\Incident::archivedFromMap()->count()],
            ['Safe Zones',    'admin.safe-zones.index',   ['admin.safe-zones.*'],                             'shield',    'text-[#22C55E]', \App\Models\WardStation::where('is_safe_zone', true)->count()],
        ],
        'Monitoring' => [
            ['Live Map',      'admin.map',                ['admin.map'],                                      'map',       'text-[#2DD4BF]', null],
        ],
        'Insights' => [
            ['Analytics',     'admin.analytics',          ['admin.analytics'],                                'analytics', 'text-[#22C55E]', null],
        ],
        'Account' => [
            ['Profile',       'admin.profile',            ['admin.profile'],                                  'profile',   'text-[#64748B]', null],
            ['Settings',      'admin.settings',           ['admin.settings'],                                 'settings',  'text-[#64748B]', null],
        ],
    ];
@endphp

<style>
    /* ===== Collapsible admin sidebar ===== */
    #admin-mobile-drawer .sb-item .sb-label,
    #admin-mobile-drawer .sb-footer .sb-label { white-space: nowrap; }

    /* Tablet / desktop: collapse into a slim icon-only rail */
    @media (min-width: 768px) {
        #admin-mobile-drawer { transition: width .3s ease; }
        #admin-mobile-drawer.is-collapsed { width: 4rem; }
        #admin-mobile-drawer.is-collapsed .sb-label,
        #admin-mobile-drawer.is-collapsed .sb-section-title { display: none; }
        #admin-mobile-drawer.is-collapsed .sb-center { justify-content: center; }
        #admin-mobile-drawer.is-collapsed .sb-item { justify-content: center; padding-left: 0; padding-right: 0; }
        #admin-mobile-drawer.is-collapsed .sb-section + .sb-section { border-top: 1px solid #2A3440; padding-top: 0.75rem; }
    }
</style>

{{-- Mobile open/close is handled by AdminDrawer in the admin layout (adds the .open class) --}}
<aside id="admin-mobile-drawer"
       class="fixed md:static inset-y-0 left-0 z-50 w-72 md:w-64 bg-[#151B23] border-r border-[#2A3440] flex flex-col flex-shrink-0 -translate-x-full md:translate-x-0 select-none overflow-hidden shadow-2xl md:shadow-none">

    <!-- Brand Header + Toggle -->
    <div class="sb-header px-4 pt-3.5 pb-3 border-b border-[#2A3440]">
        <div class="sb-center flex items-center justify-between gap-2">
            <a href="{{ route('admin.dashboard') }}" class="sb-label flex items-center gap-2.5 min-w-0" aria-label="SFWatch Admin Panel">
                <img src="{{ asset('images/spectra-logo.png') }}" alt="SFWatch" class="h-8 w-8 shrink-0 object-contain">
                <span class="min-w-0 leading-tight">
                    <span class="block text-sm font-extrabold tracking-wide bg-gradient-to-r from-[#3B82F6] to-[#06B6D4] bg-clip-text text-transparent">SFWatch</span>
                    <span class="block text-[11px] text-slate-400">Admin Panel</span>
                </span>
            </a>

            <!-- Open / Close toggle (desktop) -->
            <button type="button" id="admin-sidebar-toggle"
                    aria-controls="admin-mobile-drawer" aria-expanded="true" aria-label="Collapse sidebar" title="Collapse sidebar"
                    class="hidden md:flex flex-shrink-0 w-8 h-8 items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-[#1B222C] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/>
                </svg>
            </button>

            <!-- Close button (mobile drawer) -->
            <button type="button" onclick="AdminDrawer.close()"
                    class="flex md:hidden flex-shrink-0 w-7 h-7 items-center justify-center rounded-md text-slate-400 hover:text-white hover:bg-[#1B222C] transition"
                    aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Scrollable Navigation -->
    <nav class="sb-scroll flex-1 min-h-0 overflow-y-auto px-3 py-4 space-y-5 text-xs bg-[#151B23]" aria-label="Admin navigation">
        @foreach($sections as $title => $items)
            <div class="sb-section space-y-1">
                <p class="sb-section-title text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">{{ $title }}</p>

                @foreach($items as [$label, $routeName, $patterns, $icon, $iconColor, $count])
                    @php $active = request()->routeIs(...$patterns); @endphp
                    <a href="{{ route($routeName) }}" data-label="{{ $label }}"
                       onclick="if (window.innerWidth < 768) AdminDrawer.close()"
                       @if($active) aria-current="page" @endif
                       class="sb-item w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg transition
                              {{ $active
                                  ? 'bg-[#8B5CF6]/20 text-white border border-[#8B5CF6]/40 font-semibold'
                                  : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }}">
                        <span class="flex items-center gap-2.5 min-w-0">
                            <svg class="w-4 h-4 flex-shrink-0 {{ $iconColor }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                {!! $icons[$icon] !!}
                            </svg>
                            <span class="sb-label truncate">{{ $label }}</span>
                        </span>
                        @if(!is_null($count))
                            <span class="sb-label text-[10px] font-mono tabular-nums px-2 py-0.5 rounded-full {{ $active ? 'bg-[#8B5CF6]/30 text-white font-bold' : 'bg-[#11161D] text-slate-400' }}">{{ $count }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    <!-- Sidebar Footer -->
    <div class="sb-footer px-4 py-3 border-t border-[#2A3440] bg-[#11161D]">
        <div class="sb-center flex items-center gap-2">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="SpectraWatch" class="w-5 h-5 object-contain opacity-70 flex-shrink-0">
            <div class="sb-label leading-tight">
                <p class="text-[10px] font-semibold text-[#8B5CF6]">SFWatch Admin Panel</p>
                <p class="text-[9px] text-[#64748B]">San Francisco, Agusan del Sur</p>
            </div>
        </div>
    </div>
</aside>

<script>
/* ===== Desktop collapse logic (mobile drawer is handled by AdminDrawer) ===== */
(function () {
    const sidebar = document.getElementById('admin-mobile-drawer');
    const toggle  = document.getElementById('admin-sidebar-toggle');
    if (!sidebar || !toggle) return;

    const STORAGE_KEY = 'admin_sidebar_collapsed';

    function applyState(collapsed, animate) {
        if (!animate) sidebar.style.transition = 'none';

        sidebar.classList.toggle('is-collapsed', collapsed);

        const label = collapsed ? 'Expand sidebar' : 'Collapse sidebar';
        toggle.setAttribute('aria-expanded', String(!collapsed));
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);

        sidebar.querySelectorAll('.sb-item[data-label]').forEach(function (el) {
            if (collapsed) el.setAttribute('title', el.dataset.label);
            else el.removeAttribute('title');
        });

        if (!animate) {
            void sidebar.offsetWidth;
            sidebar.style.transition = '';
        } else {
            setTimeout(function () { window.dispatchEvent(new Event('resize')); }, 320);
        }
    }

    let saved = false;
    try { saved = localStorage.getItem(STORAGE_KEY) === '1'; } catch (e) {}

    // Only restore collapsed state on desktop
    if (window.innerWidth >= 768) {
        applyState(saved, false);
    }

    toggle.addEventListener('click', function () {
        const next = !sidebar.classList.contains('is-collapsed');
        applyState(next, true);
        try { localStorage.setItem(STORAGE_KEY, next ? '1' : '0'); } catch (e) {}
    });

    // Close the mobile drawer if the window is resized up to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 768 && window.AdminDrawer) {
            AdminDrawer.close();
        }
    });
})();
</script>
