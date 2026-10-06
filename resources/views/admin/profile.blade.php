@extends('layouts.admin')

@section('title', 'Admin Profile — SpectraWatch Defense Command')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-6 max-w-4xl mx-auto w-full font-mono">

    <!-- Header + Breadcrumb -->
    <div>
        <div class="flex items-center gap-2 text-xs text-[#8B5CF6]">
            <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
            <span class="text-slate-600">/</span>
            <span class="text-slate-300 font-bold">Admin Profile</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">Administrator Profile</h1>
        <p class="text-xs text-slate-400 mt-0.5">Manage executive credentials and inspection privileges.</p>
    </div>

    <!-- Profile Overview Card -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                    SYSTEM ADMINISTRATOR
                </span>
                <span class="text-xs text-slate-500">ID #{{ $admin->id }}</span>
            </div>
            <h2 class="text-xl font-bold text-white mt-1">{{ $admin->name }}</h2>
            <p class="text-xs text-slate-400">{{ $admin->email }}</p>
        </div>

        <div class="text-xs text-slate-400 p-3 rounded-xl bg-[#11161D] border border-[#2A3440] text-center min-w-[140px]">
            <span class="text-[10px] text-slate-500 uppercase block">Commission Date</span>
            <span class="font-bold text-white block mt-0.5">{{ $admin->created_at ? $admin->created_at->format('Y-m-d') : '—' }}</span>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-5 text-xs">
        <div class="border-b border-[#2A3440] pb-3">
            <h2 class="text-sm font-bold uppercase tracking-wider text-white">Update Administrator Credentials</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Ensure your defense network contact email and access key remain current.</p>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Administrator Full Name</label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $admin->name) }}"
                       required
                       class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Defense Network Email Address</label>
                <input type="email"
                       name="email"
                       value="{{ old('email', $admin->email) }}"
                       required
                       class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none">
            </div>

            <div class="pt-2 border-t border-[#2A3440] space-y-3">
                <p class="text-[11px] text-slate-400">Change Password (leave blank to keep current):</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">New Password</label>
                        <input type="password"
                               name="password"
                               autocomplete="new-password"
                               class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Confirm New Password</label>
                        <input type="password"
                               name="password_confirmation"
                               autocomplete="new-password"
                               class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition">
                    [ Save Profile Credentials ]
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Actions by this Admin -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-3 text-xs">
        <h2 class="text-sm font-bold uppercase tracking-wider text-white border-b border-[#2A3440] pb-2">
            Recent Administrative Actions by {{ $admin->name }}
        </h2>

        @forelse($recentLogs as $log)
            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1 text-[11px]">
                <div class="flex justify-between items-center">
                    <span class="font-bold text-[#A78BFA]">[ {{ $log->action }} ]</span>
                    <span class="text-[10px] text-slate-500">{{ $log->created_at?->format('Y-m-d H:i') }}</span>
                </div>
                <p class="text-slate-300">{{ $log->description }}</p>
            </div>
        @empty
            <p class="text-slate-500 text-center py-4">No recent administrative action logs recorded.</p>
        @endforelse
    </div>

</main>
@endsection
