<header class="h-14 bg-[#11161D] border-b border-[#2A3440] px-4 flex items-center justify-between flex-shrink-0 z-30 select-none">
    <!-- Left: Brand & Logo -->
    <div class="flex items-center">
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
        @php
            $unreadCount = $unreadNotifCount ?? (isset($notifications) ? $notifications->where('is_read', false)->count() : 0);
        @endphp
        <div class="relative" id="notif-wrapper">
            <button type="button"
                id="notif-bell-btn"
                onclick="SpectralNotif.toggle()"
                class="relative w-8 h-8 rounded-lg bg-[#1B222C] border border-[#2A3440] flex items-center justify-center text-[#9CA3AF] hover:text-white hover:border-[#8B5CF6]/50 transition-all"
                title="Notifications"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span id="notif-badge" class="{{ $unreadCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-1 min-w-[16px] h-[16px] rounded-full bg-[#8B5CF6] text-white text-[9px] font-bold font-mono flex items-center justify-center px-0.5 leading-none">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </button>

            <!-- Notification Dropdown -->
            <div id="notif-dropdown"
                class="hidden absolute right-0 top-10 w-80 sm:w-96 rounded-xl bg-[#151B23] border border-[#2A3440] shadow-2xl shadow-black/60 z-[200] overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-[#2A3440] bg-[#11161D]">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="text-xs font-bold text-white">Incident Updates</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="notif-unread-header" class="text-[10px] font-mono text-[#8B5CF6] font-bold">
                            {{ $unreadCount > 0 ? $unreadCount . ' new' : 'All caught up' }}
                        </span>
                        <button type="button"
                            id="notif-mark-all-btn"
                            onclick="SpectralNotif.markAllAsRead(event)"
                            class="{{ $unreadCount > 0 ? '' : 'hidden' }} text-[10px] font-mono text-[#9CA3AF] hover:text-white transition px-2 py-0.5 rounded bg-[#1B222C] hover:bg-[#2A3440] border border-[#2A3440]">
                            Mark all as read
                        </button>
                    </div>
                </div>

                <!-- Notification Items -->
                <div class="max-h-80 overflow-y-auto divide-y divide-[#1E2631]" id="notif-items-list">
                    @forelse($notifications as $notif)
                    @php
                        $isRead = !empty($notif['is_read']);
                        $dotColor = match($notif['status']) {
                            'RESOLVED'           => 'bg-emerald-400',
                            'VERIFIED'           => 'bg-blue-400',
                            'UNDER INVESTIGATION'=> 'bg-amber-400',
                            'ESCALATED'          => 'bg-red-500',
                            default              => 'bg-slate-400',
                        };
                    @endphp
                    <a href="{{ route('spectral.incidents.show', $notif['incident_id']) }}"
                       data-incident-id="{{ $notif['incident_id'] }}"
                       data-read="{{ $isRead ? '1' : '0' }}"
                       onclick="SpectralNotif.markAsRead({{ $notif['incident_id'] }}, event)"
                       class="notif-item flex items-start gap-3 px-4 py-3 hover:bg-[#1B222C] transition group {{ $isRead ? 'opacity-55 bg-[#0e1318]/50' : '' }}">

                        <!-- Status dot / read indicator -->
                        <div class="mt-0.5 flex-shrink-0 notif-dot-wrap">
                            @if(!$isRead)
                            <span class="notif-dot w-2 h-2 rounded-full {{ $dotColor }} block mt-1 ring-2 ring-emerald-500/20"></span>
                            @else
                            <span class="notif-dot w-2 h-2 rounded-full bg-slate-600 block mt-1"></span>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1 mb-0.5">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-[10px] font-mono font-bold text-[#8B5CF6]">{{ $notif['incident_code'] }}</span>
                                    <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded
                                        {{ $notif['status'] === 'RESOLVED' ? 'bg-emerald-500/15 text-emerald-400' :
                                           ($notif['status'] === 'ESCALATED' ? 'bg-red-500/15 text-red-400' :
                                           ($notif['status'] === 'VERIFIED' ? 'bg-blue-500/15 text-blue-400' : 'bg-amber-500/15 text-amber-400')) }}">
                                        {{ $notif['status'] }}
                                    </span>
                                </div>
                                <span class="notif-badge-pill text-[9px] font-mono px-1.5 py-0.2 rounded {{ $isRead ? 'text-[#64748B]' : 'text-emerald-400 font-bold bg-emerald-500/10' }}">
                                    {{ $isRead ? 'Read' : 'New' }}
                                </span>
                            </div>
                            <p class="text-xs text-white font-semibold truncate">{{ $notif['title'] }}</p>
                            @if($notif['notes'])
                            <p class="text-[11px] text-[#9CA3AF] mt-0.5 line-clamp-2">{{ $notif['notes'] }}</p>
                            @endif
                            <div class="flex items-center gap-1 mt-1 text-[10px] text-[#64748B]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>{{ \Carbon\Carbon::parse($notif['updated_at'])->diffForHumans() }}</span>
                                <span class="text-[#3B4A5A]">&bull;</span>
                                <span class="truncate">{{ $notif['investigator'] }}</span>
                            </div>
                        </div>

                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-[#3B4A5A] group-hover:text-[#8B5CF6] transition flex-shrink-0 mt-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                    @empty
                    <div class="px-4 py-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-[#2A3440] mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <p class="text-xs text-[#64748B]">No updates on your reports yet.</p>
                    </div>
                    @endforelse
                </div>

                <!-- Footer -->
                <div class="px-4 py-2.5 border-t border-[#2A3440] bg-[#11161D]">
                    <a href="{{ route('spectral.my-reports') }}" class="flex items-center justify-center gap-1.5 text-[11px] font-semibold text-[#8B5CF6] hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        View All My Reports
                    </a>
                </div>
            </div>
        </div>
        @endauth

        <!-- Quick Report Incident Button -->
        <button type="button" onclick="SpectralUI.openReportModal()" class="px-3 py-1.5 bg-[#8B5CF6] hover:bg-[#7C3AED] active:scale-95 text-white font-semibold text-xs rounded-lg transition-all flex items-center gap-1.5 shadow-sm shadow-[#8B5CF6]/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span class="hidden sm:inline">Report Incident</span>
        </button>

        <!-- User Profile -->
        @auth
        <div class="hidden lg:flex items-center gap-2 pl-2 border-l border-[#2A3440]">
            <div class="w-7 h-7 rounded-full bg-[#1B222C] border border-[#8B5CF6]/40 flex items-center justify-center text-xs font-bold text-[#8B5CF6] font-mono">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(strrchr(Auth::user()->name, ' '), 1, 1) ?: substr(Auth::user()->name, 1, 1)) }}
            </div>
            <div class="text-left">
                <p class="text-xs font-semibold text-white leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-[10px] text-[#9CA3AF] font-mono leading-tight capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}" class="flex items-center">
            @csrf
            <button type="submit"
                title="Sign out"
                class="w-7 h-7 rounded-lg bg-[#1B222C] border border-[#2A3440] flex items-center justify-center text-[#9CA3AF] hover:text-red-400 hover:border-red-400/40 transition-all"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                </svg>
            </button>
        </form>
        @endauth

    </div>
</header>

<script>
const SpectralNotif = {
    toggle() {
        const dd = document.getElementById('notif-dropdown');
        if (dd) dd.classList.toggle('hidden');
    },
    close() {
        const dd = document.getElementById('notif-dropdown');
        if (dd) dd.classList.add('hidden');
    },
    getReadIds() {
        try {
            return JSON.parse(localStorage.getItem('spectral_read_notifs') || '[]');
        } catch (_) {
            return [];
        }
    },
    saveReadIds(ids) {
        try {
            localStorage.setItem('spectral_read_notifs', JSON.stringify(ids));
        } catch (_) {}
    },
    updateBadges() {
        const items = document.querySelectorAll('.notif-item');
        let unread = 0;
        items.forEach(el => {
            if (el.getAttribute('data-read') !== '1') {
                unread++;
            }
        });

        // Update header bell badge
        const badge = document.getElementById('notif-badge');
        if (badge) {
            badge.textContent = unread > 9 ? '9+' : unread;
            if (unread > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Update header counter text
        const headerText = document.getElementById('notif-unread-header');
        if (headerText) {
            headerText.textContent = unread > 0 ? `${unread} new` : 'All caught up';
        }

        // Update Mark all button
        const markAllBtn = document.getElementById('notif-mark-all-btn');
        if (markAllBtn) {
            if (unread > 0) {
                markAllBtn.classList.remove('hidden');
            } else {
                markAllBtn.classList.add('hidden');
            }
        }

        // Update sidebar notifications counter
        const sidebarCount = document.getElementById('sidebar-notif-count');
        if (sidebarCount) {
            sidebarCount.textContent = unread;
            if (unread > 0) {
                sidebarCount.classList.remove('hidden');
            } else {
                sidebarCount.classList.add('hidden');
            }
        }
    },
    markItemAsReadUi(itemEl) {
        if (!itemEl) return;
        itemEl.setAttribute('data-read', '1');
        itemEl.classList.add('opacity-55', 'bg-[#0e1318]/50');

        const dot = itemEl.querySelector('.notif-dot');
        if (dot) {
            dot.className = 'notif-dot w-2 h-2 rounded-full bg-slate-600 block mt-1';
        }

        const pill = itemEl.querySelector('.notif-badge-pill');
        if (pill) {
            pill.textContent = 'Read';
            pill.className = 'notif-badge-pill text-[9px] font-mono px-1.5 py-0.2 rounded text-[#64748B]';
        }
    },
    async markAsRead(incidentId, event) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const itemEl = document.querySelector(`.notif-item[data-incident-id="${incidentId}"]`);

        // Mark in UI immediately
        this.markItemAsReadUi(itemEl);

        // Update localStorage
        const readIds = this.getReadIds();
        if (!readIds.includes(incidentId)) {
            readIds.push(incidentId);
            this.saveReadIds(readIds);
        }

        this.updateBadges();

        // Send beacon/fetch to server
        try {
            fetch('/notifications/mark-as-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ incident_id: incidentId })
            });
        } catch (_) {}
    },
    async markAllAsRead(event) {
        if (event) event.stopPropagation();

        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const items = document.querySelectorAll('.notif-item');
        const readIds = this.getReadIds();

        items.forEach(el => {
            this.markItemAsReadUi(el);
            const id = parseInt(el.getAttribute('data-incident-id'));
            if (id && !readIds.includes(id)) {
                readIds.push(id);
            }
        });

        this.saveReadIds(readIds);
        this.updateBadges();

        try {
            await fetch('/notifications/mark-as-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ all: true })
            });
        } catch (_) {}
    }
};

// Sync localStorage on page load
document.addEventListener('DOMContentLoaded', function() {
    const readIds = SpectralNotif.getReadIds();
    if (readIds.length > 0) {
        readIds.forEach(id => {
            const el = document.querySelector(`.notif-item[data-incident-id="${id}"]`);
            if (el) SpectralNotif.markItemAsReadUi(el);
        });
        SpectralNotif.updateBadges();
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('notif-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        SpectralNotif.close();
    }
});
</script>
