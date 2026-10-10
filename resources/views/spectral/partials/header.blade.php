@php
    // Status → color (rgb triplet), short label, friendly message, icon (Feather paths)
    $notifStatus = [
        'RESOLVED' => ['rgb' => '52 211 153',  'label' => 'Resolved',      'msg' => 'Your report was resolved',
            'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
        'VERIFIED' => ['rgb' => '96 165 250',  'label' => 'Verified',      'msg' => 'Your report was verified',
            'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>'],
        'UNDER INVESTIGATION' => ['rgb' => '251 191 36', 'label' => 'Investigating', 'msg' => 'An investigator is reviewing your report',
            'icon' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>'],
        'REJECTED' => ['rgb' => '244 63 94',   'label' => 'Rejected',      'msg' => 'Your report was not accepted',
            'icon' => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'],
        'ASSIGNED' => ['rgb' => '34 211 238',  'label' => 'Responder assigned', 'msg' => 'Verified → Responder assigned',
            'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/>'],
    ];
    $notifDefault = ['rgb' => '148 163 184', 'label' => 'Update', 'msg' => 'Your report was updated',
        'icon' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>'];

    $notifList   = collect($notifications ?? []);
    $unreadCount = $unreadNotifCount ?? $notifList->filter(fn ($n) => empty($n['is_read']))->count();

    // Group by day
    $notifGroups = $notifList->groupBy(function ($n) {
        $d = \Carbon\Carbon::parse($n['updated_at']);
        return $d->isToday() ? 'Today' : ($d->isYesterday() ? 'Yesterday' : 'Earlier');
    });
@endphp

<style>
    /* ===== Notifications ===== */
    #notif-dropdown .notif-item { --c: 148 163 184; }
    #notif-dropdown .notif-icon   { background: rgb(var(--c) / .12); color: rgb(var(--c)); border: 1px solid rgb(var(--c) / .28); }
    #notif-dropdown .notif-accent { background: rgb(var(--c)); }
    #notif-dropdown .notif-pill   { background: rgb(var(--c) / .12); color: rgb(var(--c)); border: 1px solid rgb(var(--c) / .25); }
    #notif-dropdown .notif-dot    { background: rgb(var(--c)); box-shadow: 0 0 0 3px rgb(var(--c) / .18); }

    #notif-dropdown .notif-item[data-read="0"] { background: rgba(139, 92, 246, .06); }
    #notif-dropdown .notif-item[data-read="1"] .notif-accent { opacity: 0; }
    #notif-dropdown .notif-item[data-read="1"] .notif-dot    { background: #475569; box-shadow: none; }
    #notif-dropdown .notif-item[data-read="1"] .notif-msg    { color: #CBD5E1; font-weight: 500; }
    #notif-dropdown .notif-item[data-read="1"] .notif-icon   { opacity: .75; }

    #notif-dropdown[data-filter="unread"] .notif-item[data-read="1"] { display: none; }
    #notif-dropdown .notif-tab[aria-selected="true"] { color: #fff; background: #1B222C; border-color: #3A4654; }

    #notif-list::-webkit-scrollbar { width: 6px; }
    #notif-list::-webkit-scrollbar-thumb { background: #2A3440; border-radius: 9999px; }

    @media (prefers-reduced-motion: no-preference) {
        #notif-dropdown:not(.hidden) { animation: notif-in .16s ease-out; }
        @keyframes notif-in { from { opacity: 0; transform: translateY(-6px) scale(.98); } to { opacity: 1; transform: none; } }
    }
</style>

<header class="h-14 bg-[#11161D] border-b border-[#2A3440] px-3 sm:px-4 flex items-center justify-between flex-shrink-0 z-30 select-none">
    <!-- Left: Hamburger (mobile) + Brand & Logo -->
    <div class="flex items-center gap-2">
        <!-- Mobile hamburger -->
        <button type="button"
                id="mobile-menu-toggle"
                onclick="SpectralSidebar.openMobile()"
                class="flex md:hidden w-8 h-8 items-center justify-center rounded-lg bg-[#1B222C] border border-[#2A3440] text-[#9CA3AF] hover:text-white hover:border-[#8B5CF6]/50 transition-all"
                aria-label="Open menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>
        <a href="{{ route('spectral.dashboard') }}" class="flex items-center gap-[5px] hover:opacity-90 transition">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra" class="h-9 w-9 object-contain">
            <span class="text-xl font-extrabold tracking-wide font-sans bg-gradient-to-r from-[#3B82F6] to-[#06B6D4] bg-clip-text text-transparent">SFWatch</span>
        </a>
    </div>

    <!-- Right: Role Badge, Notification Bell, Quick Report, User Profile, Logout -->
    <div class="flex items-center gap-2 sm:gap-3">

        <!-- Role Badge -->
        @auth
            @if(Auth::user()->isAdmin())
                <div class="hidden sm:flex items-center px-2.5 py-1 rounded-md bg-[#1B222C] border border-[#EF4444]/30 text-[11px] font-semibold font-mono text-[#F87171]">
                    Administrator
                </div>
            @elseif(Auth::user()->isInvestigator())
                <a href="{{ route('investigator.dashboard') }}" title="Go to Investigator Portal" class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#1B222C] hover:bg-[#222B38] border border-[#8B5CF6]/40 text-[11px] font-semibold font-mono text-[#A78BFA] transition">
                    <span>Investigator</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @endif
        @endauth

        <!-- Notification Bell -->
        @auth
        <div class="relative" id="notif-wrapper">
            <button type="button"
                id="notif-bell-btn"
                onclick="SpectralNotif.toggle()"
                aria-haspopup="true" aria-expanded="false" aria-controls="notif-dropdown"
                class="relative w-8 h-8 rounded-lg bg-[#1B222C] border border-[#2A3440] flex items-center justify-center text-[#9CA3AF] hover:text-white hover:border-[#8B5CF6]/50 transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#8B5CF6]"
                title="Notifications" aria-label="Notifications"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span id="notif-badge" class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 min-w-[16px] h-[16px] rounded-full bg-[#8B5CF6] text-white text-[9px] font-bold font-mono flex items-center justify-center px-0.5 leading-none ring-2 ring-[#11161D]">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </button>

            <!-- Notification Panel -->
            <div id="notif-dropdown" data-filter="all" role="region" aria-label="Notifications"
                class="hidden fixed inset-x-3 top-16 sm:absolute sm:inset-auto sm:right-0 sm:top-11 sm:w-80 rounded-2xl bg-[#151B23] border border-[#2A3440] shadow-2xl shadow-black/60 z-[200] overflow-hidden"
            >
                <!-- Header -->
                <div class="px-3.5 pt-3 pb-2.5 border-b border-[#2A3440] bg-[#11161D]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-[13px] font-bold text-white">Notifications</h2>
                            <p id="notif-unread-header" class="text-[10px] text-slate-500">
                                {{ $unreadCount > 0 ? $unreadCount . ' unread' : 'All caught up' }}
                            </p>
                        </div>
                        <button type="button"
                            id="notif-mark-all-btn"
                            onclick="SpectralNotif.markAllAsRead(event)"
                            class="{{ $unreadCount > 0 ? '' : 'hidden' }} inline-flex items-center gap-1 text-[10px] font-medium text-slate-300 hover:text-white transition px-2 py-1 rounded-md bg-[#1B222C] hover:bg-[#222B38] border border-[#2A3440] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#8B5CF6]">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                            Mark all read
                        </button>
                    </div>

                    <!-- Filter tabs -->
                    <div class="mt-2 flex items-center gap-1" role="tablist" aria-label="Filter notifications">
                        <button type="button" role="tab" aria-selected="true" data-tab="all" onclick="SpectralNotif.setFilter('all')"
                                class="notif-tab px-2.5 py-0.5 rounded-md border border-transparent text-[10px] font-medium text-slate-400 hover:text-white transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#8B5CF6]">
                            All
                        </button>
                        <button type="button" role="tab" aria-selected="false" data-tab="unread" onclick="SpectralNotif.setFilter('unread')"
                                class="notif-tab px-2.5 py-0.5 rounded-md border border-transparent text-[10px] font-medium text-slate-400 hover:text-white transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#8B5CF6]">
                            Unread
                        </button>
                    </div>
                </div>

                <!-- List -->
                <div id="notif-list" class="max-h-[min(20rem,60vh)] overflow-y-auto overscroll-contain">

                    @forelse($notifGroups as $groupName => $items)
                    <section class="notif-group" data-group>
                        <h3 class="sticky top-0 z-10 px-3.5 py-1 bg-[#151B23]/95 backdrop-blur text-[9px] font-semibold uppercase tracking-wider text-slate-500 border-b border-[#1E2631]">
                            {{ $groupName }}
                        </h3>

                        <div class="divide-y divide-[#1E2631]">
                        @foreach($items as $notif)
                            @php
                                $isRead    = !empty($notif['is_read']);
                                $cfg       = $notifStatus[$notif['status'] ?? ''] ?? $notifDefault;
                                $isRejId   = str_starts_with((string) $notif['incident_id'], 'rej_');
                                $notifUrl  = $isRejId ? '#' : route('spectral.incidents.show', $notif['incident_id']);
                                $inv       = $notif['investigator'] ?? null;
                                $updated   = \Carbon\Carbon::parse($notif['updated_at']);
                            @endphp
                            <a href="{{ $notifUrl }}"
                               data-incident-id="{{ $notif['incident_id'] }}"
                               data-read="{{ $isRead ? '1' : '0' }}"
                               onclick="SpectralNotif.markAsRead(this, event)"
                               style="--c: {{ $cfg['rgb'] }}"
                               class="notif-item group relative flex items-start gap-2.5 py-2.5 pl-3.5 pr-3 transition hover:bg-[#1B222C] focus-visible:outline focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-[#8B5CF6]">

                                <span class="notif-accent absolute left-0 top-2.5 bottom-2.5 w-[3px] rounded-r-full" aria-hidden="true"></span>

                                <span class="notif-icon grid h-7 w-7 shrink-0 place-items-center rounded-full" aria-hidden="true">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $cfg['icon'] !!}</svg>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="notif-msg text-xs font-semibold leading-snug text-white">{{ $cfg['msg'] }}</p>
                                        <span class="flex shrink-0 items-center gap-2 pt-0.5 text-[10px] text-slate-500">
                                            <time datetime="{{ $updated->toIso8601String() }}" title="{{ $updated->format('M j, Y g:i A') }}">{{ $updated->diffForHumans(['short' => true]) }}</time>
                                            <span class="notif-dot h-2 w-2 rounded-full" aria-label="{{ $isRead ? 'Read' : 'Unread' }}"></span>
                                        </span>
                                    </div>

                                    <p class="mt-0.5 truncate text-[11px] text-slate-300">
                                        <span class="font-mono text-[#A78BFA]">{{ $notif['incident_code'] }}</span>
                                        <span class="text-slate-600">&middot;</span>
                                        {{ $notif['title'] }}
                                    </p>

                                    @if(!empty($notif['notes']))
                                    <p class="mt-1 line-clamp-1 rounded border-l-2 border-[#2A3440] bg-[#11161D] px-2 py-1 text-[10px] leading-snug text-slate-400">{{ $notif['notes'] }}</p>
                                    @endif

                                    <div class="mt-1.5 flex items-center gap-2 text-[10px] text-slate-500">
                                        <span class="notif-pill rounded-full px-1.5 py-px text-[9px] font-medium">{{ $cfg['label'] }}</span>
                                        @if($inv)
                                            <span class="truncate">by {{ $inv }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                        </div>
                    </section>
                    @empty
                    <div class="px-5 py-8 text-center">
                        <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full border border-[#2A3440] bg-[#11161D] text-slate-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-white">No updates yet</p>
                        <p class="mt-1 text-xs text-slate-500">You'll be notified here when an investigator reviews one of your reports.</p>
                    </div>
                    @endforelse

                    <!-- Shown by JS when the Unread tab has nothing -->
                    <div id="notif-empty-unread" class="hidden px-5 py-8 text-center">
                        <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 text-emerald-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-white">You're all caught up</p>
                        <p class="mt-1 text-xs text-slate-500">No unread notifications.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-3.5 py-2 border-t border-[#2A3440] bg-[#11161D]">
                    <a href="{{ route('spectral.my-reports') }}" class="flex items-center justify-center gap-1.5 text-[11px] font-semibold text-[#A78BFA] hover:text-white transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#8B5CF6] rounded">
                        View all my reports
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @endauth

        <!-- Quick Report Incident Button — shown on Interactive Map ONLY (not on dashboard) -->
        @if(request()->routeIs('spectral.map'))
        <button type="button" onclick="SpectralUI.openReportModal()" class="px-3 py-1.5 bg-[#38BDF8] hover:bg-[#0284C7] active:scale-95 text-[#070B12] hover:text-white font-bold text-xs rounded-lg transition-all flex items-center gap-1.5 shadow-sm shadow-[#38BDF8]/20 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Report Incident</span>
        </button>
        @endif

        <!-- User Profile Dropdown Pill matching mockup -->
        @auth
        <div class="relative" id="user-menu-wrapper">
            <button type="button"
                    id="user-menu-btn"
                    onclick="SpectralHeader.toggleUserMenu()"
                    class="flex items-center gap-2.5 py-1 px-2 rounded-lg hover:bg-[#1B222C] transition group cursor-pointer focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-[#1e293b] border border-[#334155] flex items-center justify-center text-xs font-bold text-slate-200">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="text-left hidden sm:block">
                    <p class="text-xs font-bold text-slate-200 leading-tight group-hover:text-white transition">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 capitalize leading-tight">{{ Auth::user()->role ?? 'Reporter' }}</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-200 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div id="user-dropdown-menu"
                 class="hidden absolute right-0 top-12 w-56 rounded-xl bg-[#151B23] border border-[#2A3440] shadow-2xl shadow-black/80 py-1.5 z-50 overflow-hidden">
                <div class="px-3.5 py-2.5 border-b border-[#2A3440] bg-[#11161D]">
                    <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    <span class="inline-block mt-1 text-[9px] font-mono font-bold uppercase px-1.5 py-0.5 rounded bg-[#1e293b] text-[#38BDF8] border border-[#38BDF8]/30">
                        {{ Auth::user()->role }}
                    </span>
                </div>

                <div class="py-1">
                    <a href="{{ route('spectral.profile') }}"
                       class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-slate-300 hover:text-white hover:bg-[#1B222C] transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>Profile & Settings</span>
                    </a>

                    <a href="{{ route('spectral.my-reports') }}"
                       class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-slate-300 hover:text-white hover:bg-[#1B222C] transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <span>My Reports</span>
                    </a>

                    @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-red-400 hover:text-red-300 hover:bg-[#1B222C] transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                            <span>Admin Portal</span>
                        </a>
                    @elseif(Auth::user()->isInvestigator())
                        <a href="{{ route('investigator.dashboard') }}"
                           class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-purple-400 hover:text-purple-300 hover:bg-[#1B222C] transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <span>Investigator Portal</span>
                        </a>
                    @endif
                </div>

                <div class="border-t border-[#2A3440] py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs text-slate-400 hover:text-red-400 hover:bg-[#1B222C] transition text-left">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                            </svg>
                            <span>Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endauth

    </div>
</header>

<script>
const SpectralNotif = {
    STORAGE_KEY: 'spectral_read_notifs',
    URL: '/notifications/mark-as-read',

    el(id) { return document.getElementById(id); },
    csrf() { return document.querySelector('meta[name="csrf-token"]')?.content; },

    /* ── open / close ── */
    isOpen() { return !this.el('notif-dropdown')?.classList.contains('hidden'); },
    open() {
        this.el('notif-dropdown')?.classList.remove('hidden');
        this.el('notif-bell-btn')?.setAttribute('aria-expanded', 'true');
        this.refresh();
    },
    close(returnFocus) {
        this.el('notif-dropdown')?.classList.add('hidden');
        const btn = this.el('notif-bell-btn');
        if (btn) {
            btn.setAttribute('aria-expanded', 'false');
            if (returnFocus) btn.focus();
        }
    },
    toggle() { this.isOpen() ? this.close() : this.open(); },

    /* ── read-state storage (ids always stored as strings) ── */
    getReadIds() {
        try { return JSON.parse(localStorage.getItem(this.STORAGE_KEY) || '[]').map(String); }
        catch (_) { return []; }
    },
    saveReadIds(ids) {
        try { localStorage.setItem(this.STORAGE_KEY, JSON.stringify(ids)); } catch (_) {}
    },

    /* ── filter tabs ── */
    setFilter(filter) {
        const dd = this.el('notif-dropdown');
        if (!dd) return;
        dd.setAttribute('data-filter', filter);
        dd.querySelectorAll('.notif-tab').forEach(t =>
            t.setAttribute('aria-selected', String(t.getAttribute('data-tab') === filter)));
        this.refresh();
    },

    /* ── recompute counters, groups and empty states ── */
    refresh() {
        const dd     = this.el('notif-dropdown');
        const items  = document.querySelectorAll('.notif-item');
        const unread = Array.from(items).filter(el => el.getAttribute('data-read') !== '1').length;
        const filter = dd ? dd.getAttribute('data-filter') : 'all';

        const badge = this.el('notif-badge');
        if (badge) {
            badge.textContent = unread > 9 ? '9+' : unread;
            badge.classList.toggle('hidden', unread === 0);
        }

        const header = this.el('notif-unread-header');
        if (header) header.textContent = unread > 0 ? `${unread} unread` : 'All caught up';

        this.el('notif-mark-all-btn')?.classList.toggle('hidden', unread === 0);

        // Hide day headings that have no visible items under the Unread tab
        document.querySelectorAll('.notif-group').forEach(group => {
            const hasVisible = filter === 'all' ||
                group.querySelector('.notif-item[data-read="0"]') !== null;
            group.classList.toggle('hidden', !hasVisible);
        });

        this.el('notif-empty-unread')?.classList.toggle('hidden',
            !(filter === 'unread' && unread === 0 && items.length > 0));

        const sidebarCount = this.el('sidebar-notif-count');
        if (sidebarCount) {
            sidebarCount.textContent = unread;
            sidebarCount.classList.toggle('hidden', unread === 0);
        }
    },

    persist(payload) {
        try {
            fetch(this.URL, {
                method: 'POST',
                keepalive: true, // survives the page navigation that follows a click
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrf(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
        } catch (_) {}
    },

    /* ── mark one as read (called from the item's onclick) ── */
    markAsRead(itemEl, event) {
        if (!itemEl) return;
        const id = String(itemEl.getAttribute('data-incident-id'));

        // Rejected notices have no detail page (href="#"): don't jump to top of page
        if (itemEl.getAttribute('href') === '#' && event) event.preventDefault();

        itemEl.setAttribute('data-read', '1');

        const ids = this.getReadIds();
        if (!ids.includes(id)) { ids.push(id); this.saveReadIds(ids); }

        this.refresh();
        this.persist({ incident_id: id });
    },

    /* ── mark everything as read ── */
    markAllAsRead(event) {
        if (event) event.stopPropagation();

        const ids = this.getReadIds();
        document.querySelectorAll('.notif-item').forEach(el => {
            el.setAttribute('data-read', '1');
            const id = String(el.getAttribute('data-incident-id'));
            if (!ids.includes(id)) ids.push(id);
        });

        this.saveReadIds(ids);
        this.refresh();
        this.persist({ all: true });
    }
};

const SpectralHeader = {
    toggleUserMenu() {
        const menu = document.getElementById('user-dropdown-menu');
        if (menu) menu.classList.toggle('hidden');
    },
    closeUserMenu() {
        const menu = document.getElementById('user-dropdown-menu');
        if (menu) menu.classList.add('hidden');
    }
};

// Re-apply locally stored read state on page load
document.addEventListener('DOMContentLoaded', function () {
    const readIds = SpectralNotif.getReadIds();
    if (readIds.length > 0) {
        document.querySelectorAll('.notif-item').forEach(el => {
            if (readIds.includes(String(el.getAttribute('data-incident-id')))) {
                el.setAttribute('data-read', '1');
            }
        });
    }
    SpectralNotif.refresh();
});

// Close when clicking outside
document.addEventListener('click', function (e) {
    const wrapper = document.getElementById('notif-wrapper');
    if (wrapper && !wrapper.contains(e.target)) SpectralNotif.close();

    const userWrapper = document.getElementById('user-menu-wrapper');
    if (userWrapper && !userWrapper.contains(e.target)) SpectralHeader.closeUserMenu();
});

// Close with Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        if (SpectralNotif.isOpen()) SpectralNotif.close(true);
        SpectralHeader.closeUserMenu();
    }
});
</script>
