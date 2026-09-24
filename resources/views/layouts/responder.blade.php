<!doctype html>
<html lang="en" class="h-full bg-[#0B0F14] text-[#F3F4F6]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Responder Operations — Spectra')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .ecto-input {
            width: 100%;
            background: #11161D;
            border: 1px solid #2A3440;
            border-radius: 8px;
            padding: 8px 12px;
            color: #FFFFFF;
            font-size: 13px;
        }
        .ecto-input:focus {
            outline: none;
            border-color: #8B5CF6;
        }
        .ecto-select {
            width: 100%;
            background: #11161D;
            border: 1px solid #2A3440;
            border-radius: 8px;
            padding: 8px 12px;
            color: #FFFFFF;
            font-size: 13px;
        }
    </style>
</head>
<body class="min-h-screen bg-[#0B0F14] text-slate-100 flex flex-col">

    <!-- Top Application Bar -->
    <header class="h-14 bg-[#11161D] border-b border-[#2A3440] px-5 flex items-center justify-between flex-shrink-0 z-30 select-none">
        <div class="flex items-center gap-3">
            <a href="{{ route('responder.dashboard') }}" class="flex items-center gap-2 hover:opacity-90 transition">
                <span class="text-lg font-extrabold tracking-wide font-sans bg-gradient-to-r from-[#3B82F6] to-[#06B6D4] bg-clip-text text-transparent">Spectra</span>
            </a>
            <span class="text-[#2A3440]">|</span>
            <span class="text-xs font-mono font-bold text-[#A78BFA] uppercase tracking-wider">Responder Operations</span>
        </div>

        <div class="flex items-center gap-3">
            @php
                $respUser = auth('responder')->user() ?? auth()->user();
            @endphp
            @if($respUser)
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded bg-[#1B222C] border border-[#8B5CF6]/40 text-[#A78BFA] font-mono text-xs font-bold">
                        CLASS {{ $respUser->responder_class ?? 'D' }}
                    </span>
                    <span class="hidden sm:inline-block px-2.5 py-1 rounded bg-[#1B222C] border border-[#2A3440] text-slate-300 font-mono text-xs">
                        {{ $respUser->xp ?? 0 }} XP
                    </span>
                </div>
                <div class="hidden md:block text-right">
                    <p class="text-xs font-bold text-white leading-tight">{{ $respUser->name }}</p>
                    <p class="text-[10px] font-mono text-slate-400 capitalize">{{ $respUser->responder_status ?? 'AVAILABLE' }}</p>
                </div>
                <form method="POST" action="{{ route('responder.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#1B222C] hover:bg-[#222B38] border border-[#2A3440] hover:border-red-500/50 text-slate-300 hover:text-red-400 font-mono text-xs transition">
                        Logout
                    </button>
                </form>
            @endif
        </div>
    </header>

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="bg-emerald-950/50 border-b border-emerald-500/40 text-emerald-300 px-5 py-2.5 text-xs font-mono flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-amber-950/50 border-b border-amber-500/40 text-amber-300 px-5 py-2.5 text-xs font-mono flex items-center justify-between">
            <span>{{ session('warning') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-rose-950/50 border-b border-rose-500/40 text-rose-300 px-5 py-2.5 text-xs font-mono">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Content -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
