<style>
    /* ===== Collapsible investigator sidebar ===== */
    #investigator-sidebar .sb-item .sb-label,
    #investigator-sidebar .sb-footer .sb-label { white-space: nowrap; }

    /* Tablet / desktop: collapse into a slim icon-only rail */
    @media (min-width: 768px) {
        #investigator-sidebar.is-collapsed { width: 4rem; }
        #investigator-sidebar.is-collapsed .sb-label,
        #investigator-sidebar.is-collapsed .sb-section-title { display: none; }
        #investigator-sidebar.is-collapsed .sb-center { justify-content: center; }
        #investigator-sidebar.is-collapsed .sb-item { justify-content: center; padding-left: 0; padding-right: 0; }
        #investigator-sidebar.is-collapsed .sb-section + .sb-section { border-top: 1px solid #2A3440; padding-top: 0.75rem; }
    }

    /* Mobile overlay drawer */
    @media (max-width: 767px) {
        #investigator-sidebar {
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
        #investigator-sidebar.mobile-open {
            transform: translateX(0) !important;
        }
        #investigator-sidebar-mobile-overlay {
            display: none;
            position: fixed !important;
            inset: 0 !important;
            top: 3.5rem !important;
            background: rgba(0,0,0,0.6) !important;
            z-index: 9998 !important;
            backdrop-filter: blur(2px);
        }
        #investigator-sidebar-mobile-overlay.active { display: block !important; }
    }
</style>

<!-- Mobile overlay backdrop -->
<div id="investigator-sidebar-mobile-overlay" onclick="InvestigatorSidebar.closeMobile()"></div>

<aside id="investigator-sidebar" class="w-full md:w-64 lg:w-64 bg-[#151B23] border-r border-[#2A3440] flex flex-col md:h-full flex-shrink-0 z-20 select-none overflow-hidden transition-all duration-300">

    <!-- Sector Location Header + Toggle -->
    <div class="sb-header px-4 pt-3.5 pb-3 border-b border-[#2A3440]">
        <div class="sb-center flex items-center justify-between gap-2">
            <div class="sb-label flex items-center gap-2 text-xs min-w-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#8B5CF6] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                <div class="leading-tight text-slate-200 text-[12px] font-medium">
                    <div>San Francisco,</div>
                    <div>Agusan del Sur</div>
                </div>
            </div>

            <!-- Open / Close toggle (desktop) -->
            <button type="button" id="investigator-sidebar-toggle"
                    aria-controls="investigator-sidebar" aria-expanded="true" aria-label="Collapse sidebar" title="Collapse sidebar"
                    class="hidden md:flex flex-shrink-0 w-8 h-8 items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-[#1B222C] transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/>
                </svg>
            </button>

            <!-- Close button (mobile drawer) -->
            <button type="button" onclick="InvestigatorSidebar.closeMobile()"
                    class="flex md:hidden flex-shrink-0 w-7 h-7 items-center justify-center rounded-md text-slate-400 hover:text-white hover:bg-[#1B222C] transition"
                    aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Scrollable Navigation -->
    <div class="sb-scroll flex-1 min-h-0 overflow-y-auto px-3 py-4 space-y-5 text-xs bg-[#151B23]">

        <!-- OVERVIEW -->
        <div class="sb-section space-y-1">
            <p class="sb-section-title text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Overview</p>
            <a href="{{ route('investigator.dashboard') }}" data-label="Dashboard"
               onclick="InvestigatorSidebar.closeMobile()"
               class="sb-item w-full flex items-center gap-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('investigator.dashboard') ? 'bg-[#8B5CF6]/20 text-white border border-[#8B5CF6]/40' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#8B5CF6] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                <span class="sb-label">Dashboard</span>
            </a>
        </div>

        <!-- INCIDENT MANAGEMENT -->
        <div class="sb-section space-y-1">
            <p class="sb-section-title text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Incident Management</p>

            <!-- Incident Reports -->
            <a href="{{ route('investigator.incidents.index') }}" data-label="Incident Reports"
               onclick="InvestigatorSidebar.closeMobile()"
               class="sb-item w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.incidents.index') && !request()->has('status') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#38BDF8] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span class="sb-label">Incident Reports</span>
                </span>
            </a>

            <!-- Investigation Queue -->
            <a href="{{ route('investigator.queue') }}" data-label="Investigation Queue"
               onclick="InvestigatorSidebar.closeMobile()"
               class="sb-item w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.queue') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#EAB308] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span class="sb-label">Investigation Queue</span>
                </span>
            </a>
        </div>

        <!-- RESPONSE -->
        <div class="sb-section space-y-1">
            <p class="sb-section-title text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Response</p>

            <!-- Responders -->
            <a href="{{ route('investigator.responders') }}" data-label="Responders"
               onclick="InvestigatorSidebar.closeMobile()"
               class="sb-item w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.responders') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#38BDF8] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span class="sb-label">Responders</span>
                </span>
            </a>

            <!-- Safe Ward Stations -->
            <a href="{{ route('investigator.safe-zones') }}" data-label="Safe Ward Stations"
               onclick="InvestigatorSidebar.closeMobile()"
               class="sb-item w-full flex items-center justify-between px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.safe-zones') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <span class="flex items-center gap-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#22C55E] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span class="sb-label">Safe Ward Stations</span>
                </span>
            </a>
        </div>

        <!-- ACCOUNT -->
        <div class="sb-section space-y-1">
            <p class="sb-section-title text-[10px] font-mono font-bold uppercase tracking-widest text-[#64748B] px-2 mb-1">Account</p>

            <a href="{{ route('investigator.profile') }}" data-label="Profile"
               onclick="InvestigatorSidebar.closeMobile()"
               class="sb-item w-full flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('investigator.profile') ? 'bg-[#1B222C] text-white border border-[#2A3440]' : 'text-slate-300 hover:bg-[#1B222C] hover:text-white' }} transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#64748B] flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <span class="sb-label">Profile</span>
            </a>

            <form method="POST" action="{{ route('investigator.logout') }}">
                @csrf
                <button type="submit" data-label="Logout"
                        class="sb-item w-full flex items-center gap-2.5 px-3 py-1.5 rounded-lg text-slate-300 hover:bg-[#1B222C] hover:text-red-400 transition text-left">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 hover:text-red-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span class="sb-label">Logout</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Sidebar Footer -->
    <div class="sb-footer px-4 py-3 border-t border-[#2A3440] bg-[#11161D]">
        <div class="sb-center flex items-center gap-2">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="SpectraWatch" class="w-5 h-5 object-contain opacity-70 flex-shrink-0">
            <div class="sb-label leading-tight">
                <p class="text-[10px] font-semibold text-[#8B5CF6]">SpectraWatch</p>
                <p class="text-[9px] text-[#64748B]">Investigation &amp; Response</p>
            </div>
        </div>
    </div>

</aside>

<script>
window.InvestigatorSidebar = {
    isMobile() { return window.innerWidth < 768; },

    openMobile() {
        document.getElementById('investigator-sidebar')?.classList.add('mobile-open');
        document.getElementById('investigator-sidebar-mobile-overlay')?.classList.add('active');
        document.body.style.overflow = 'hidden';
    },

    closeMobile() {
        if (!this.isMobile()) return;
        document.getElementById('investigator-sidebar')?.classList.remove('mobile-open');
        document.getElementById('investigator-sidebar-mobile-overlay')?.classList.remove('active');
        document.body.style.overflow = '';
    },
};
const InvestigatorSidebar = window.InvestigatorSidebar;

/* ===== Desktop collapse logic ===== */
(function () {
    const sidebar = document.getElementById('investigator-sidebar');
    const toggle  = document.getElementById('investigator-sidebar-toggle');
    if (!sidebar || !toggle) return;

    const STORAGE_KEY = 'investigator_sidebar_collapsed';

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
            InvestigatorSidebar.closeMobile();
        }
    });
})();
</script>
