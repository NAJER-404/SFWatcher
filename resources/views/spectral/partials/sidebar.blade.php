<style>
    /* ===== Collapsible sidebar ===== */
    #spectral-sidebar .sb-item .sb-label,
    #spectral-sidebar .sb-footer .sb-label { white-space: nowrap; }

    /* Tablet / desktop: collapse into a slim icon-only rail */
    @media (min-width: 768px) {
        #spectral-sidebar.is-collapsed { width: 4rem; }
        #spectral-sidebar.is-collapsed .sb-label,
        #spectral-sidebar.is-collapsed .sb-section-title { display: none; }
        #spectral-sidebar.is-collapsed .sb-center { justify-content: center; }
        #spectral-sidebar.is-collapsed .sb-item { justify-content: center; padding-left: 0; padding-right: 0; }
        #spectral-sidebar.is-collapsed .sb-section + .sb-section { border-top: 1px solid #2A3440; padding-top: 0.75rem; }
    }

    /* Mobile overlay drawer */
    @media (max-width: 767px) {
        #spectral-sidebar {
            position: fixed !important;
            top: 3.5rem !important; /* header height */
            left: 0 !important;
            bottom: 0 !important;
            width: 280px !important;
            max-width: 85vw !important;
            height: calc(100vh - 3.5rem) !important;
            height: calc(100dvh - 3.5rem) !important;
            z-index: 9999 !important;
            background-color: #151B23 !important;
            transform: translateX(-100%) !important;
            transition: transform 0.25s ease !important;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.7);
        }
        #spectral-sidebar.mobile-open {
            transform: translateX(0) !important;
        }
        #sidebar-mobile-overlay {
            display: none;
            position: fixed !important;
            inset: 0 !important;
            top: 3.5rem !important;
            background: rgba(0,0,0,0.6) !important;
            z-index: 9998 !important;
            backdrop-filter: blur(2px);
        }
        #sidebar-mobile-overlay.active { display: block !important; }
    }
</style>

<!-- Mobile overlay backdrop -->
<div id="sidebar-mobile-overlay" onclick="SpectralSidebar.closeMobile()"></div>

<aside id="spectral-sidebar" class="w-full md:w-52 lg:w-52 bg-[#0d1117] border-r border-[#1a2130] flex flex-col md:h-full flex-shrink-0 z-20 select-none overflow-hidden transition-all duration-300">

    <!-- Location Header + Toggle -->
    <div class="sb-header px-4 pt-4 pb-3 border-b border-[#1a2130]">
        <div class="sb-center flex items-center justify-between gap-2">
            <div class="sb-label flex items-center gap-2 text-xs min-w-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#64748B] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                <div class="leading-tight text-slate-400 text-[11px]">
                    <div>San Francisco,</div>
                    <div>Agusan del Sur</div>
                </div>
            </div>

            <!-- Open / Close toggle (desktop) -->
            <button type="button" id="sidebar-toggle"
                    aria-controls="spectral-sidebar" aria-expanded="true" aria-label="Collapse sidebar" title="Collapse sidebar"
                    class="hidden md:flex flex-shrink-0 w-7 h-7 items-center justify-center rounded-lg text-slate-600 hover:text-slate-300 hover:bg-[#161d27] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>

            <!-- Close button (mobile drawer) -->
            <button type="button" onclick="SpectralSidebar.closeMobile()"
                    class="flex md:hidden flex-shrink-0 w-7 h-7 items-center justify-center rounded-md text-slate-400 hover:text-white hover:bg-[#1B222C] transition"
                    aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Scrollable Nav -->
    <div class="sb-scroll flex-1 min-h-0 overflow-y-auto px-2 py-4 space-y-5 text-xs bg-[#0d1117]">

        <!-- OVERVIEW -->
        <div class="sb-section space-y-0.5">
            <p class="sb-section-title text-[9px] font-mono font-bold uppercase tracking-widest text-[#3d4e5f] px-2 mb-1.5">Overview</p>
            <a href="{{ route('spectral.dashboard') }}" data-label="Dashboard"
               onclick="SpectralSidebar.closeMobile()"
               class="sb-item w-full flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('spectral.dashboard') || request()->routeIs('spectral.index') ? 'bg-[#1a2540] text-white border border-[#2a3a60]/60' : 'text-slate-400 hover:bg-[#161d27] hover:text-white' }} font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ request()->routeIs('spectral.dashboard') || request()->routeIs('spectral.index') ? 'text-[#3B82F6]' : 'text-slate-500' }} flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                <span class="sb-label">Dashboard</span>
            </a>
        </div>

        <!-- MONITORING -->
        <div class="sb-section space-y-0.5">
            <p class="sb-section-title text-[9px] font-mono font-bold uppercase tracking-widest text-[#3d4e5f] px-2 mb-1.5">Monitoring</p>

            <!-- Interactive Map -->
            <a href="{{ route('spectral.dashboard') }}#map" data-label="Interactive Map"
               onclick="SpectralSidebar.closeMobile()"
               class="sb-item w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-400 hover:bg-[#161d27] hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
                    <line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/>
                </svg>
                <span class="sb-label">Interactive Map</span>
            </a>

            <!-- Incidents -->
            <a href="{{ route('spectral.incidents.index') }}" data-label="Incidents"
               onclick="SpectralSidebar.closeMobile()"
               class="sb-item w-full flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('spectral.incidents.*') ? 'bg-[#1a2540] text-white border border-[#2a3a60]/60' : 'text-slate-400 hover:bg-[#161d27] hover:text-white' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ request()->routeIs('spectral.incidents.*') ? 'text-[#3B82F6]' : 'text-slate-500' }} flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span class="sb-label">Incidents</span>
            </a>

            <!-- Safe Zones -->
            <button onclick="SpectralSidebar.closeMobile();" data-label="Safe Zones"
                    class="sb-item w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-400 hover:bg-[#161d27] hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span class="sb-label">Safe Zones</span>
            </button>
        </div>

        <!-- MY ACTIVITY -->
        <div class="sb-section space-y-0.5">
            <p class="sb-section-title text-[9px] font-mono font-bold uppercase tracking-widest text-[#3d4e5f] px-2 mb-1.5">My Activity</p>

            <!-- My Reports -->
            <a href="{{ route('spectral.my-reports') }}" data-label="My Reports"
               onclick="SpectralSidebar.closeMobile()"
               class="sb-item w-full flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('spectral.my-reports') ? 'bg-[#1a2540] text-white border border-[#2a3a60]/60' : 'text-slate-400 hover:bg-[#161d27] hover:text-white' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ request()->routeIs('spectral.my-reports') ? 'text-[#3B82F6]' : 'text-slate-500' }} flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                </svg>
                <span class="sb-label">My Reports</span>
            </a>

            <!-- Notifications -->
            <button onclick="SpectralNotif.toggle(); SpectralSidebar.closeMobile();" data-label="Notifications"
                    class="sb-item w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-400 hover:bg-[#161d27] hover:text-white transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-500 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="sb-label">Notifications</span>
                </span>
                @if(($unreadNotifCount ?? 0) > 0)
                <span id="sidebar-notif-count" class="sb-label text-[9px] font-mono font-bold text-white bg-[#3B82F6] rounded-full min-w-[16px] h-4 flex items-center justify-center px-1">
                    {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
                </span>
                @else
                <span id="sidebar-notif-count" class="sb-label hidden text-[9px] font-mono font-bold text-white bg-[#3B82F6] rounded-full min-w-[16px] h-4 flex items-center justify-center px-1"></span>
                @endif
            </button>
        </div>

        <!-- ACCOUNT -->
        <div class="sb-section space-y-0.5">
            <p class="sb-section-title text-[9px] font-mono font-bold uppercase tracking-widest text-[#3d4e5f] px-2 mb-1.5">Account</p>

            <a href="{{ route('spectral.profile') }}" data-label="Profile"
               onclick="SpectralSidebar.closeMobile()"
               class="sb-item w-full flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('spectral.profile') ? 'bg-[#1a2540] text-white border border-[#2a3a60]/60' : 'text-slate-400 hover:bg-[#161d27] hover:text-white' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ request()->routeIs('spectral.profile') ? 'text-[#3B82F6]' : 'text-slate-500' }} flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <span class="sb-label">Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" data-label="Logout"
                        class="sb-item w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-slate-400 hover:bg-[#161d27] hover:text-red-400 transition text-left">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span class="sb-label">Logout</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Sidebar Footer -->
    <div class="sb-footer px-4 py-3 border-t border-[#1a2130] bg-[#0b0f17]">
        <div class="sb-center flex items-center gap-2">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra" class="w-5 h-5 object-contain opacity-40 flex-shrink-0">
            <div class="sb-label leading-tight">
                <p class="text-[9px] font-semibold text-[#3d4e5f]">Safer Communities</p>
                <p class="text-[9px] text-[#2d3a4a]">Stronger Tomorrow</p>
            </div>
        </div>
    </div>

</aside>

<script>
/* ===== Sidebar Controller ===== */
const SpectralSidebar = {
    isMobile() { return window.innerWidth < 768; },

    openMobile() {
        document.getElementById('spectral-sidebar')?.classList.add('mobile-open');
        document.getElementById('sidebar-mobile-overlay')?.classList.add('active');
        document.body.style.overflow = 'hidden';
    },

    closeMobile() {
        if (!this.isMobile()) return;
        document.getElementById('spectral-sidebar')?.classList.remove('mobile-open');
        document.getElementById('sidebar-mobile-overlay')?.classList.remove('active');
        document.body.style.overflow = '';
    },
};

/* ===== Desktop collapse logic ===== */
(function () {
    const sidebar = document.getElementById('spectral-sidebar');
    const toggle  = document.getElementById('sidebar-toggle');
    if (!sidebar || !toggle) return;

    const STORAGE_KEY = 'spectral_sidebar_collapsed';

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

    // Close mobile drawer on resize to desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 768) {
            SpectralSidebar.closeMobile();
        }
    });
})();
</script>
