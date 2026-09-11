<header class="h-14 bg-[#11161D] border-b border-[#2A3440] px-4 flex items-center justify-between flex-shrink-0 z-30 select-none">
    <!-- Left: Brand & Logo -->
    <div class="flex items-center">
        <a href="{{ route('spectral.dashboard') }}" class="flex items-center gap-[5px] hover:opacity-90 transition">
            <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra" class="h-9 w-9 object-contain">
            <span class="text-xl font-extrabold tracking-wide font-sans bg-gradient-to-r from-[#3B82F6] to-[#06B6D4] bg-clip-text text-transparent">Spectra</span>
        </a>
    </div>

    <!-- Right: Role Badge, Quick Report, User Profile, Logout -->
    <div class="flex items-center gap-2 sm:gap-3">

        <!-- Role Badge -->
        @auth
            @if(Auth::user()->isInvestigator())
                <div class="hidden sm:flex items-center px-2.5 py-1 rounded-md bg-[#1B222C] border border-[#8B5CF6]/30 text-[11px] font-semibold font-mono text-[#A78BFA]">
                    Administrator
                </div>
            @else
                <div class="hidden sm:flex items-center px-2.5 py-1 rounded-md bg-[#1B222C] border border-emerald-500/30 text-[11px] font-semibold font-mono text-emerald-400">
                    Reporter
                </div>
            @endif
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
                <p class="text-[10px] text-[#9CA3AF] font-mono leading-tight capitalize">{{ Auth::user()->role === 'investigator' ? 'Administrator' : 'Reporter' }}</p>
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

