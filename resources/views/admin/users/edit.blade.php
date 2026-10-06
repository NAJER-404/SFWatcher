@extends('layouts.admin')

@section('title', 'Update User: ' . $user->name . ' — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 space-y-6 max-w-4xl mx-auto w-full font-mono">

    <!-- Breadcrumb: Dashboard / All Users / Update -->
    <nav class="flex items-center gap-2 text-xs text-[#8B5CF6]" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
        <span class="text-slate-600">/</span>
        <a href="{{ route('admin.users.index') }}" class="hover:underline">All Users</a>
        <span class="text-slate-600">/</span>
        <span class="text-slate-300 font-bold">Update</span>
    </nav>

    <!-- Header Card -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border
                    @if($user->role === 'admin') bg-amber-500/15 text-amber-300 border-amber-500/40
                    @elseif($user->role === 'investigator') bg-purple-500/15 text-purple-300 border-purple-500/40
                    @elseif($user->role === 'responder') bg-emerald-500/15 text-emerald-300 border-emerald-500/40
                    @else bg-cyan-500/15 text-cyan-300 border-cyan-500/40
                    @endif">
                    {{ strtoupper($user->role) }}
                </span>
                <span class="text-xs text-slate-500">ID #{{ $user->id }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">Update Account: {{ $user->name }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Modify account credentials, role authorization, or tactical classification.</p>
        </div>

        <a href="{{ route('admin.users.index') }}" class="px-3 py-1.5 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white transition text-xs self-start">
            &larr; Back to Users
        </a>
    </div>

    <!-- Update Form -->
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Primary Account Details -->
        <div class="p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#2A3440] space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-[#2A3440] pb-2.5">
                Profile Credentials
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label for="name" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Full Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition">
                    @error('name')
                        <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition">
                    @error('email')
                        <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Role Assignment -->
            <div class="pt-2">
                <label for="role" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">System Role</label>
                <select id="role"
                        name="role"
                        onchange="toggleResponderFields(this.value)"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition text-xs">
                    <option value="reporter" @selected(old('role', $user->role) === 'reporter')>Reporter (Civilian Incident Reporting)</option>
                    <option value="investigator" @selected(old('role', $user->role) === 'investigator')>Investigator (Field Investigation & Review)</option>
                    <option value="responder" @selected(old('role', $user->role) === 'responder')>Responder (Tactical Anomaly Containment)</option>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator (Full System Administration)</option>
                </select>
                @error('role')
                    <p class="text-rose-400 text-[10px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Responder Tactical Fields (Conditional) -->
        <div id="responder-fields" class="{{ old('role', $user->role) === 'responder' ? '' : 'hidden' }} p-5 sm:p-6 rounded-2xl bg-[#151B23] border border-[#8B5CF6]/40 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider border-b border-[#2A3440] pb-2.5 flex items-center justify-between">
                <span>Responder Tactical Specifications</span>
                <span class="text-[10px] text-[#A78BFA] font-normal">Active for Responder Role Only</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label for="responder_class" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Class Tier</label>
                    <select id="responder_class"
                            name="responder_class"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition">
                        <option value="D" @selected(old('responder_class', $user->responder_class ?? 'D') === 'D')>Class D (100 Max HP)</option>
                        <option value="C" @selected(old('responder_class', $user->responder_class) === 'C')>Class C (120 Max HP)</option>
                        <option value="B" @selected(old('responder_class', $user->responder_class) === 'B')>Class B (145 Max HP)</option>
                        <option value="A" @selected(old('responder_class', $user->responder_class) === 'A')>Class A (170 Max HP)</option>
                    </select>
                </div>

                <div>
                    <label for="xp" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Current XP</label>
                    <input type="number"
                           id="xp"
                           name="xp"
                           min="0"
                           value="{{ old('xp', $user->xp ?? 0) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-amber-400 font-bold focus:border-[#8B5CF6] focus:outline-none transition">
                </div>

                <div>
                    <label for="responder_status" class="block text-[11px] text-slate-400 uppercase font-bold mb-1">Availability Status</label>
                    <select id="responder_status"
                            name="responder_status"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-white focus:border-[#8B5CF6] focus:outline-none transition">
                        <option value="AVAILABLE" @selected(old('responder_status', $user->responder_status ?? 'AVAILABLE') === 'AVAILABLE')>AVAILABLE (Ready for Callout)</option>
                        <option value="RESPONDING" @selected(old('responder_status', $user->responder_status) === 'RESPONDING')>RESPONDING (Active Mission)</option>
                        <option value="INACTIVE" @selected(old('responder_status', $user->responder_status) === 'INACTIVE')>INACTIVE (Off Duty)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Form Submit & Cancel Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-white transition text-xs font-bold">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition text-xs shadow-lg shadow-purple-900/30">
                Update User
            </button>
        </div>
    </form>

</main>

<script>
    function toggleResponderFields(role) {
        const el = document.getElementById('responder-fields');
        if (role === 'responder') {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }
</script>
@endsection
