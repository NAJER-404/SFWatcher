@extends('layouts.admin')

@section('title', ($sectionTitle ?? 'User Management') . ' — SpectraWatch Admin')

@section('content')
<main class="flex-1 overflow-y-auto p-3.5 sm:p-6 md:p-8 space-y-5 max-w-7xl mx-auto w-full font-mono">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-[#8B5CF6]">
                <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-300 font-bold">{{ $sectionTitle ?? 'User Management' }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-white mt-1">{{ $sectionTitle ?? 'User Management' }}</h1>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <a href="{{ route('admin.users.index') }}"
               class="px-3 py-1.5 rounded-lg transition {{ (is_null($lockedRole) && !request('role')) ? 'bg-[#8B5CF6] text-white font-bold' : 'bg-[#151B23] border border-[#2A3440] text-slate-400 hover:text-white' }}">
                All ({{ $roleCounts['all'] }})
            </a>
            <a href="{{ route('admin.users.investigators') }}"
               class="px-3 py-1.5 rounded-lg transition {{ ($lockedRole === 'investigator') ? 'bg-[#8B5CF6] text-white font-bold' : 'bg-[#151B23] border border-[#2A3440] text-slate-400 hover:text-white' }}">
                Investigators ({{ $roleCounts['investigator'] }})
            </a>
            <a href="{{ route('admin.users.responders') }}"
               class="px-3 py-1.5 rounded-lg transition {{ ($lockedRole === 'responder') ? 'bg-[#8B5CF6] text-white font-bold' : 'bg-[#151B23] border border-[#2A3440] text-slate-400 hover:text-white' }}">
                Responders ({{ $roleCounts['responder'] }})
            </a>
            <a href="{{ route('admin.users.reporters') }}"
               class="px-3 py-1.5 rounded-lg transition {{ ($lockedRole === 'reporter') ? 'bg-[#8B5CF6] text-white font-bold' : 'bg-[#151B23] border border-[#2A3440] text-slate-400 hover:text-white' }}">
                Reporters ({{ $roleCounts['reporter'] }})
            </a>

            <button type="button" onclick="openAddUserModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition shadow-lg shadow-[#8B5CF6]/20">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add User
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div id="flash-success" class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 text-xs font-bold transition-opacity duration-500">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3 rounded-lg bg-rose-500/10 border border-rose-500/40 text-rose-300 text-xs">
            <strong class="font-bold">Please fix the following:</strong>
            <ul class="list-disc ml-5 mt-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Search & Filters Toolbar -->
    <form method="GET" action="{{ url()->current() }}" class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between text-xs">
        <div class="flex-1 flex flex-col sm:flex-row gap-2.5">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                   class="flex-1 px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-500 focus:border-[#8B5CF6] focus:outline-none">

            <select name="responder_class" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 focus:border-[#8B5CF6] focus:outline-none">
                <option value="">Class: All</option>
                <option value="D" @selected(request('responder_class') === 'D')>Class D (100 HP)</option>
                <option value="C" @selected(request('responder_class') === 'C')>Class C (120 HP)</option>
                <option value="B" @selected(request('responder_class') === 'B')>Class B (145 HP)</option>
                <option value="A" @selected(request('responder_class') === 'A')>Class A (170 HP)</option>
            </select>

            <select name="status" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 focus:border-[#8B5CF6] focus:outline-none">
                <option value="">Status: All</option>
                <option value="AVAILABLE" @selected(request('status') === 'AVAILABLE')>Available</option>
                <option value="RESPONDING" @selected(request('status') === 'RESPONDING')>Responding</option>
                <option value="INACTIVE" @selected(request('status') === 'INACTIVE')>Inactive</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'responder_class', 'status', 'role']))
                <a href="{{ url()->current() }}" class="px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-400 hover:text-white transition">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- Total Records -->
    <div class="text-xs text-slate-500">
        {{ $users->total() }} record(s) found
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-hidden rounded-xl border border-[#2A3440] bg-[#151B23]">
        <table class="w-full text-left text-xs">
            <thead class="bg-[#11161D] text-slate-400 border-b border-[#2A3440] uppercase">
                <tr>
                    <th class="p-3.5">ID</th>
                    <th class="p-3.5">User / Email</th>
                    <th class="p-3.5">Role</th>
                    <th class="p-3.5">Status</th>
                    <th class="p-3.5">Responder Info</th>
                    <th class="p-3.5">Registered</th>
                    <th class="p-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#2A3440]">
                @forelse($users as $u)
                    <tr class="hover:bg-[#1B222C]/70 transition">
                        <td class="p-3.5 text-slate-500 font-bold">#{{ $u->id }}</td>
                        <td class="p-3.5">
                            <div class="font-bold text-white text-sm">{{ $u->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                            @if($u->google_id)
                                <span class="inline-block mt-0.5 text-[9px] text-cyan-400">[ Google Auth ]</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase
                                @if($u->role === 'admin') bg-amber-500/15 text-amber-300 border-amber-500/40
                                @elseif($u->role === 'investigator') bg-purple-500/15 text-purple-300 border-purple-500/40
                                @elseif($u->role === 'responder') bg-emerald-500/15 text-emerald-300 border-emerald-500/40
                                @else bg-cyan-500/15 text-cyan-300 border-cyan-500/40 @endif">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="p-3.5">
                            @if($u->isResponder())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border
                                    @if($u->responder_status === 'AVAILABLE') bg-emerald-500/15 text-emerald-300 border-emerald-500/30
                                    @elseif($u->responder_status === 'RESPONDING') bg-purple-500/15 text-purple-300 border-purple-500/30
                                    @else bg-slate-700 text-slate-300 border-slate-600 @endif">
                                    {{ $u->responder_status ?? 'AVAILABLE' }}
                                </span>
                            @else
                                <span class="text-emerald-400 text-[11px] font-bold">ACTIVE</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            @if($u->isResponder())
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white">Class {{ $u->responder_class ?? 'D' }}</span>
                                        <span class="text-[10px] text-rose-400">({{ $u->max_hp }} HP)</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        XP: <span class="text-amber-400 font-bold">{{ $u->xp }}</span>
                                        &bull; Missions: <span class="text-slate-300">{{ $u->successful_responses }}</span>
                                    </div>
                                    @if($u->isPromotionEligible())
                                        <span class="text-[10px] text-emerald-400 font-bold">[ ELIGIBLE FOR PROMOTION ]</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-slate-400 text-[11px]">{{ $u->created_at ? $u->created_at->format('Y-m-d') : '—' }}</td>
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.users.show', $u) }}" class="px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] hover:border-[#8B5CF6] text-[#A78BFA] hover:text-white font-bold transition">View</a>
                                <button type="button"
                                        onclick="openEditUserModal(
                                            '{{ $u->id }}',
                                            '{{ addslashes($u->name) }}',
                                            '{{ addslashes($u->email) }}',
                                            '{{ $u->role }}',
                                            '{{ $u->responder_class ?? 'D' }}',
                                            '{{ $u->responder_status ?? 'AVAILABLE' }}',
                                            '{{ $u->xp ?? 0 }}',
                                            '{{ route('admin.users.update', $u) }}',
                                            '{{ route('admin.users.promote', $u) }}'
                                        )"
                                        class="px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] hover:border-amber-500/50 text-amber-400 hover:text-white font-bold transition">
                                    Update
                                </button>
                                @if($u->id !== auth()->id() && $u->email !== 'admin@ectonet.gov')
                                    <button type="button"
                                            onclick="openDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ route('admin.users.destroy', $u) }}')"
                                            class="px-2.5 py-1 rounded-lg bg-[#11161D] border border-rose-500/30 hover:border-rose-500 text-rose-400 hover:bg-rose-500/10 font-bold transition">
                                        Delete
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            No users found matching current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-3">
        @forelse($users as $u)
            <div class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] space-y-3 font-mono text-xs">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="text-[10px] text-slate-500 font-bold">#{{ $u->id }}</span>
                        <h3 class="font-bold text-white text-sm">{{ $u->name }}</h3>
                        <p class="text-[11px] text-slate-400">{{ $u->email }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase shrink-0
                        @if($u->role === 'admin') bg-amber-500/15 text-amber-300 border-amber-500/40
                        @elseif($u->role === 'investigator') bg-purple-500/15 text-purple-300 border-purple-500/40
                        @elseif($u->role === 'responder') bg-emerald-500/15 text-emerald-300 border-emerald-500/40
                        @else bg-cyan-500/15 text-cyan-300 border-cyan-500/40 @endif">
                        {{ $u->role }}
                    </span>
                </div>
                @if($u->isResponder())
                    <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-1 text-[11px]">
                        <div class="flex justify-between"><span class="text-slate-400">Class / HP:</span><span class="text-white font-bold">Class {{ $u->responder_class ?? 'D' }} ({{ $u->max_hp }} HP)</span></div>
                        <div class="flex justify-between"><span class="text-slate-400">XP / Missions:</span><span class="text-amber-400 font-bold">{{ $u->xp }} XP &bull; {{ $u->successful_responses }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-400">Status:</span><span class="text-emerald-400 font-bold">{{ $u->responder_status ?? 'AVAILABLE' }}</span></div>
                    </div>
                @endif
                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#2A3440] text-[11px] text-slate-400">
                    <span>Registered: {{ $u->created_at ? $u->created_at->format('Y-m-d') : '—' }}</span>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('admin.users.show', $u) }}" class="px-2.5 py-1 rounded bg-[#11161D] border border-[#8B5CF6]/50 text-[#C4B5FD] font-bold">View</a>
                        <button type="button"
                                onclick="openEditUserModal(
                                    '{{ $u->id }}',
                                    '{{ addslashes($u->name) }}',
                                    '{{ addslashes($u->email) }}',
                                    '{{ $u->role }}',
                                    '{{ $u->responder_class ?? 'D' }}',
                                    '{{ $u->responder_status ?? 'AVAILABLE' }}',
                                    '{{ $u->xp ?? 0 }}',
                                    '{{ route('admin.users.update', $u) }}',
                                    '{{ route('admin.users.promote', $u) }}'
                                )"
                                class="px-2.5 py-1 rounded bg-[#11161D] border border-amber-500/40 text-amber-400 font-bold">
                            Update
                        </button>
                        @if($u->id !== auth()->id() && $u->email !== 'admin@ectonet.gov')
                            <button type="button"
                                    onclick="openDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ route('admin.users.destroy', $u) }}')"
                                    class="px-2.5 py-1 rounded bg-[#11161D] border border-rose-500/40 text-rose-400 font-bold">
                                Delete
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-500 font-mono text-xs bg-[#151B23] border border-[#2A3440] rounded-xl">
                No users found matching current filters.
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-2 font-mono text-xs">
        {{ $users->links() }}
    </div>

    <!-- ═════════════════ ADD USER MODAL ═════════════════ -->
    <div id="add-user-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/75 backdrop-blur-xs font-mono overflow-y-auto">
        <div class="w-full max-w-md rounded-xl bg-[#151B23] border border-[#2A3440] shadow-2xl my-8">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#2A3440]">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Add New User</h3>
                <button type="button" onclick="closeAddUserModal()" class="text-slate-500 hover:text-white transition p-1">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="p-5 space-y-4 text-xs">
                @csrf
                <div>
                    <label for="add-name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Full Name</label>
                    <input type="text" id="add-name" name="name" value="{{ old('name') }}" required placeholder="Enter full name" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-600 focus:border-[#8B5CF6] focus:outline-none">
                </div>
                <div>
                    <label for="add-email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Email Address</label>
                    <input type="email" id="add-email" name="email" value="{{ old('email') }}" required placeholder="user@ectonet.gov" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-600 focus:border-[#8B5CF6] focus:outline-none">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="add-password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Password</label>
                        <input type="password" id="add-password" name="password" required placeholder="Min. 8 chars" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-600 focus:border-[#8B5CF6] focus:outline-none">
                    </div>
                    <div>
                        <label for="add-password-confirm" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Confirm Password</label>
                        <input type="password" id="add-password-confirm" name="password_confirmation" required placeholder="Repeat password" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-600 focus:border-[#8B5CF6] focus:outline-none">
                    </div>
                </div>
                <div>
                    <label for="add-role" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Role</label>
                    <select id="add-role" name="role" required class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 focus:border-[#8B5CF6] focus:outline-none">
                        <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select a role</option>
                        <option value="reporter" @selected(old('role') === 'reporter')>Reporter</option>
                        <option value="investigator" @selected(old('role') === 'investigator')>Investigator</option>
                        <option value="responder" @selected(old('role') === 'responder')>Responder</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-4 border-t border-[#2A3440] mt-6">
                    <button type="button" onclick="closeAddUserModal()" class="w-full sm:w-auto px-4 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white transition font-bold">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition shadow-lg shadow-[#8B5CF6]/20">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═════════════════ EDIT USER MODAL ═════════════════ -->
    <div id="edit-user-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/75 backdrop-blur-xs font-mono overflow-y-auto">
        <div class="w-full max-w-md rounded-xl bg-[#151B23] border border-[#2A3440] shadow-2xl my-8">
            <div class="flex items-center justify-between px-5 py-4 border-b border-[#2A3440]">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Edit User</h3>
                <button type="button" onclick="closeEditUserModal()" class="text-slate-500 hover:text-white transition p-1">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="edit-user-form" method="POST" action="" class="p-5 space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit-name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Full Name</label>
                    <input type="text" id="edit-name" name="name" required placeholder="Enter full name" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-600 focus:border-[#8B5CF6] focus:outline-none">
                </div>
                <div>
                    <label for="edit-email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Email Address</label>
                    <input type="email" id="edit-email" name="email" required placeholder="user@ectonet.gov" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 placeholder-slate-600 focus:border-[#8B5CF6] focus:outline-none">
                </div>
                <div>
                    <label for="edit-role" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Role</label>
                    <select id="edit-role" name="role" required onchange="toggleEditResponderFields()" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 focus:border-[#8B5CF6] focus:outline-none">
                        <option value="reporter">Reporter</option>
                        <option value="investigator">Investigator</option>
                        <option value="responder">Responder</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <!-- Responder Specific Fields -->
                <div id="edit-responder-fields" class="hidden space-y-4 pt-4 border-t border-[#2A3440]">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit-responder-class" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Class</label>
                            <select id="edit-responder-class" name="responder_class" onchange="updatePromotionProgress()" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-200 focus:border-[#8B5CF6] focus:outline-none">
                                <option value="D">Class D (100 HP)</option>
                                <option value="C">Class C (120 HP)</option>
                                <option value="B">Class B (145 HP)</option>
                                <option value="A">Class A (170 HP)</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit-xp" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">XP</label>
                            <input type="number" id="edit-xp" name="xp" min="0" oninput="updatePromotionProgress()" class="w-full px-3 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-amber-400 font-bold placeholder-slate-600 focus:border-[#8B5CF6] focus:outline-none">
                        </div>
                    </div>

                    {{-- PROMOTION PROGRESS --}}
                    <div id="edit-promotion-panel" class="pt-4 border-t border-[#2A3440] space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-[11px] font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="17 11 12 6 7 11"/>
                                    <polyline points="17 18 12 13 7 18"/>
                                </svg>
                                Promotion Progress
                            </h4>
                            <span id="edit-promo-max-badge" class="hidden inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                                MAX TIER
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440]">
                                <p class="text-[9px] text-slate-500 uppercase font-bold">Current Class</p>
                                <p id="edit-promo-cur-class" class="text-base font-extrabold text-white">D</p>
                            </div>
                            <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440]">
                                <p class="text-[9px] text-slate-500 uppercase font-bold">Required XP</p>
                                <p id="edit-promo-req-xp" class="text-base font-extrabold text-slate-300">100</p>
                            </div>
                        </div>

                        <div id="edit-promo-progress-wrap">
                            <div class="flex items-center justify-between text-[10px] mb-1.5">
                                <span id="edit-promo-progress-label" class="text-slate-400">To Class C</span>
                                <span id="edit-promo-progress-status" class="font-mono font-bold text-[#A78BFA]">0 XP remaining</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-[#11161D] border border-[#2A3440] overflow-hidden">
                                <div id="edit-promo-progress-bar" class="h-full bg-gradient-to-r from-[#8B5CF6] to-[#A78BFA] transition-all duration-500" style="width: 0%;"></div>
                            </div>
                            <div class="flex items-center justify-between text-[9px] text-slate-500 font-mono mt-1">
                                <span id="edit-promo-xp-cur">0 XP</span>
                                <span id="edit-promo-xp-pct">0%</span>
                                <span id="edit-promo-xp-req">100 XP</span>
                            </div>
                        </div>

                        <button type="button"
                                id="edit-promo-btn"
                                onclick="openPromoteConfirmModal()"
                                class="hidden w-full py-2.5 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-[11px] uppercase tracking-wider transition shadow-lg shadow-emerald-900/40 flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="17 11 12 6 7 11"/>
                                <polyline points="17 18 12 13 7 18"/>
                            </svg>
                            <span id="edit-promo-btn-label">Promote to Class C</span>
                        </button>

                        <p id="edit-promo-waiting" class="text-[10px] text-slate-500 text-center italic">
                            Needs more XP to become eligible for promotion.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-4 border-t border-[#2A3440] mt-6">
                    <button type="button" onclick="closeEditUserModal()" class="w-full sm:w-auto px-4 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white transition font-bold">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-bold transition shadow-lg shadow-[#8B5CF6]/20">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden promote form -->
    <form id="promote-user-form" method="POST" action="" class="hidden">
        @csrf
        <input type="hidden" name="xp" id="promote-hidden-xp" value="0">
        <input type="hidden" name="responder_class" id="promote-hidden-class" value="D">
    </form>

    <!-- ═════════════════ PROMOTE CONFIRMATION MODAL ═════════════════ -->
    <div id="promote-confirm-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-black/75 backdrop-blur-xs font-mono">
        <div class="w-full max-w-md rounded-xl bg-[#151B23] border border-emerald-500/40 p-6 space-y-5 shadow-2xl">

            <div class="flex items-start gap-3">
                <div class="shrink-0 w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="17 11 12 6 7 11"/>
                        <polyline points="17 18 12 13 7 18"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Confirm Promotion</h3>
                    <p class="text-xs text-slate-400 mt-1">
                        You are about to promote this responder to the next tactical class tier.
                    </p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-[#11161D] border border-[#2A3440]">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-center flex-1">
                        <p class="text-[9px] text-slate-500 uppercase font-bold mb-1">From</p>
                        <p id="promote-from-class" class="text-2xl font-extrabold text-white">D</p>
                        <p id="promote-from-hp" class="text-[10px] text-slate-500 font-mono mt-0.5">100 HP</p>
                    </div>
                    <div class="shrink-0 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </div>
                    <div class="text-center flex-1">
                        <p class="text-[9px] text-emerald-400 uppercase font-bold mb-1">To</p>
                        <p id="promote-to-class" class="text-2xl font-extrabold text-emerald-400">C</p>
                        <p id="promote-to-hp" class="text-[10px] text-emerald-400/70 font-mono mt-0.5">120 HP</p>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Responder</span>
                    <strong id="promote-user-name" class="text-white">—</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Current XP</span>
                    <strong id="promote-user-xp" class="text-amber-400 font-mono">0 XP</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Required XP</span>
                    <strong id="promote-user-req" class="text-slate-300 font-mono">100 XP</strong>
                </div>
            </div>

            <div class="flex items-start gap-2 p-3 rounded-lg bg-amber-500/10 border border-amber-500/30 text-[11px] text-amber-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <span>This action will update the responder's class tier and maximum HP. The promotion will be logged in the audit trail.</span>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-1">
                <button type="button"
                        onclick="closePromoteConfirmModal()"
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white transition text-xs font-bold">
                    Cancel
                </button>
                <button type="button"
                        onclick="confirmPromotion()"
                        class="w-full sm:w-auto px-5 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold transition text-xs shadow-lg shadow-emerald-900/40 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Confirm Promotion
                </button>
            </div>
        </div>
    </div>

    <!-- ═════════════════ DELETE USER MODAL ═════════════════ -->
    <div id="delete-user-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/75 backdrop-blur-xs font-mono">
        <div class="w-full max-w-md rounded-xl bg-[#151B23] border border-rose-500/40 p-6 space-y-4 shadow-2xl">
            <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 shrink-0">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Delete User Account</h3>
                    <p class="text-xs text-slate-400 mt-1">This will permanently remove the account and revoke all system access privileges.</p>
                </div>
            </div>
            <div class="p-3 rounded-xl bg-[#11161D] border border-[#2A3440] space-y-1 text-xs">
                <p class="text-slate-300"><span class="text-slate-500">Name:</span> <strong id="delete-user-name" class="text-white"></strong></p>
                <p class="text-slate-300"><span class="text-slate-500">Email:</span> <span id="delete-user-email" class="text-slate-400"></span></p>
            </div>
            <form id="delete-user-form" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="closeDeleteUserModal()" class="px-4 py-2 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white transition text-xs font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition text-xs shadow-lg shadow-rose-950/40">Delete Account</button>
                </div>
            </form>
        </div>
    </div>

</main>

@php
    $promoConfig = [
        'D' => [
            'next'   => 'C',
            'xp'     => (int) config('spectral_response.classes.D.promotion_xp', 100),
            'hp'     => (int) config('spectral_response.classes.D.responder_hp', 100),
            'nextHp' => (int) config('spectral_response.classes.C.responder_hp', 120),
        ],
        'C' => [
            'next'   => 'B',
            'xp'     => (int) config('spectral_response.classes.C.promotion_xp', 150),
            'hp'     => (int) config('spectral_response.classes.C.responder_hp', 120),
            'nextHp' => (int) config('spectral_response.classes.B.responder_hp', 145),
        ],
        'B' => [
            'next'   => 'A',
            'xp'     => (int) config('spectral_response.classes.B.promotion_xp', 250),
            'hp'     => (int) config('spectral_response.classes.B.responder_hp', 145),
            'nextHp' => (int) config('spectral_response.classes.A.responder_hp', 170),
        ],
        'A' => [
            'next'   => null,
            'xp'     => null,
            'hp'     => (int) config('spectral_response.classes.A.responder_hp', 170),
            'nextHp' => null,
        ],
    ];
@endphp

<script>
    // 🎯 Promotion config — sourced directly from Laravel config (single source of truth)
    window.PROMO_CONFIG = @json($promoConfig);

    if (!window.PROMO_CONFIG || !window.PROMO_CONFIG.D) {
        window.PROMO_CONFIG = {
            D: { next: 'C', xp: 100, hp: 100, nextHp: 120 },
            C: { next: 'B', xp: 150, hp: 120, nextHp: 145 },
            B: { next: 'A', xp: 250, hp: 145, nextHp: 170 },
            A: { next: null, xp: null, hp: 170, nextHp: null }
        };
    }

    const PROMO_CONFIG = window.PROMO_CONFIG;

    let currentPromoteUrl = '';
    let pendingPromotion = { fromClass: '', toClass: '', userName: '', currentXp: 0, requiredXp: 0 };

    // ── Add User Modal ──
    function openAddUserModal() {
        const modal = document.getElementById('add-user-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => document.getElementById('add-name').focus(), 50);
    }
    function closeAddUserModal() {
        const modal = document.getElementById('add-user-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // ── Edit User Modal ──
    function openEditUserModal(id, name, email, role, responderClass, responderStatus, xp, actionUrl, promoteUrl) {
        document.getElementById('edit-user-form').action = actionUrl;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-role').value = role;

        document.getElementById('edit-responder-class').value = responderClass || 'D';
        document.getElementById('edit-xp').value = xp || 0;

        currentPromoteUrl = promoteUrl || '';

        toggleEditResponderFields();

        const modal = document.getElementById('edit-user-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => document.getElementById('edit-name').focus(), 50);
    }

    function closeEditUserModal() {
        const modal = document.getElementById('edit-user-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleEditResponderFields() {
        const role = document.getElementById('edit-role').value;
        const responderFields = document.getElementById('edit-responder-fields');
        if (role === 'responder') {
            responderFields.classList.remove('hidden');
            updatePromotionProgress();
        } else {
            responderFields.classList.add('hidden');
        }
    }

    // ── Live Promotion Progress ──
    function updatePromotionProgress() {
        const classEl = document.getElementById('edit-responder-class');
        const xpEl    = document.getElementById('edit-xp');

        const currentClass = (classEl.value || 'D').toUpperCase();
        const currentXp    = Math.max(0, parseInt(xpEl.value || '0', 10) || 0);

        const cfg         = PROMO_CONFIG[currentClass] || PROMO_CONFIG.D;
        const nextClass   = cfg.next;
        const requiredXp  = cfg.xp;
        const isMaxClass  = nextClass === null;

        const maxBadge = document.getElementById('edit-promo-max-badge');
        const progressWrap = document.getElementById('edit-promo-progress-wrap');
        const promoBtn = document.getElementById('edit-promo-btn');
        const waitingMsg = document.getElementById('edit-promo-waiting');

        document.getElementById('edit-promo-cur-class').textContent = currentClass;
        document.getElementById('edit-promo-req-xp').textContent = isMaxClass ? '—' : requiredXp.toLocaleString();

        if (isMaxClass) {
            maxBadge.classList.remove('hidden');
            maxBadge.classList.add('inline-flex');
            progressWrap.classList.add('hidden');
            promoBtn.classList.add('hidden');
            waitingMsg.textContent = 'This responder has reached the maximum Class A tier.';
            waitingMsg.classList.remove('hidden');
            waitingMsg.className = 'text-[10px] text-amber-300 text-center italic';
            return;
        }

        maxBadge.classList.add('hidden');
        maxBadge.classList.remove('inline-flex');
        progressWrap.classList.remove('hidden');

        const isEligible = currentXp >= requiredXp;
        const xpRemaining = Math.max(0, requiredXp - currentXp);
        const progressPct = Math.min(100, Math.round((currentXp / requiredXp) * 100));

        const bar = document.getElementById('edit-promo-progress-bar');
        bar.style.width = progressPct + '%';
        bar.className = 'h-full transition-all duration-500 ' + (
            isEligible
                ? 'bg-gradient-to-r from-emerald-500 to-emerald-400'
                : 'bg-gradient-to-r from-[#8B5CF6] to-[#A78BFA]'
        );

        document.getElementById('edit-promo-progress-label').textContent = `To Class ${nextClass}`;
        document.getElementById('edit-promo-xp-cur').textContent = currentXp.toLocaleString() + ' XP';
        document.getElementById('edit-promo-xp-pct').textContent = progressPct + '%';
        document.getElementById('edit-promo-xp-req').textContent = requiredXp.toLocaleString() + ' XP';

        const statusEl = document.getElementById('edit-promo-progress-status');
        if (isEligible) {
            statusEl.textContent = '✓ READY TO PROMOTE';
            statusEl.className = 'font-mono font-bold text-emerald-400';
            promoBtn.classList.remove('hidden');
            waitingMsg.classList.add('hidden');
            document.getElementById('edit-promo-btn-label').textContent = `Promote to Class ${nextClass}`;
        } else {
            statusEl.textContent = xpRemaining.toLocaleString() + ' XP remaining';
            statusEl.className = 'font-mono font-bold text-[#A78BFA]';
            promoBtn.classList.add('hidden');
            waitingMsg.textContent = `Needs ${xpRemaining.toLocaleString()} more XP to become eligible for Class ${nextClass}.`;
            waitingMsg.className = 'text-[10px] text-slate-500 text-center italic';
            waitingMsg.classList.remove('hidden');
        }
    }

    // ── Open the confirmation modal ──
    function openPromoteConfirmModal() {
        const currentClass = document.getElementById('edit-responder-class').value.toUpperCase();
        const currentXp    = Math.max(0, parseInt(document.getElementById('edit-xp').value || '0', 10) || 0);
        const userName     = document.getElementById('edit-name').value;

        const cfg = PROMO_CONFIG[currentClass] || PROMO_CONFIG.D;
        if (!cfg.next) return;

        pendingPromotion = {
            fromClass: currentClass,
            toClass: cfg.next,
            userName: userName,
            currentXp: currentXp,
            requiredXp: cfg.xp,
        };

        document.getElementById('promote-from-class').textContent = currentClass;
        document.getElementById('promote-to-class').textContent = cfg.next;
        document.getElementById('promote-from-hp').textContent = cfg.hp + ' HP';
        document.getElementById('promote-to-hp').textContent = cfg.nextHp + ' HP';
        document.getElementById('promote-user-name').textContent = userName;
        document.getElementById('promote-user-xp').textContent = currentXp.toLocaleString() + ' XP';
        document.getElementById('promote-user-req').textContent = cfg.xp.toLocaleString() + ' XP';

        const modal = document.getElementById('promote-confirm-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePromoteConfirmModal() {
        const modal = document.getElementById('promote-confirm-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // ── Confirm and submit the promote form ──
    function confirmPromotion() {
        if (!currentPromoteUrl) return;

        const currentXp    = Math.max(0, parseInt(document.getElementById('edit-xp').value || '0', 10) || 0);
        const currentClass = (document.getElementById('edit-responder-class').value || 'D').toUpperCase();

        document.getElementById('promote-hidden-xp').value = currentXp;
        document.getElementById('promote-hidden-class').value = currentClass;

        closePromoteConfirmModal();
        closeEditUserModal();

        const form = document.getElementById('promote-user-form');
        form.action = currentPromoteUrl;
        form.submit();
    }

    // ── Delete User Modal ──
    function openDeleteUserModal(id, name, email, actionUrl) {
        document.getElementById('delete-user-name').textContent = name;
        document.getElementById('delete-user-email').textContent = email;
        document.getElementById('delete-user-form').action = actionUrl;
        const modal = document.getElementById('delete-user-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    function closeDeleteUserModal() {
        const modal = document.getElementById('delete-user-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // ── ESC closes all + backdrop click close ──
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddUserModal();
            closeEditUserModal();
            closePromoteConfirmModal();
            closeDeleteUserModal();
        }
    });

    ['add-user-modal', 'edit-user-modal', 'promote-confirm-modal', 'delete-user-modal'].forEach(function(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });

    @if(session('success'))
        setTimeout(function() {
            const flash = document.getElementById('flash-success');
            if (flash) {
                flash.style.opacity = '0';
                setTimeout(() => flash.remove(), 500);
            }
        }, 2000);
    @endif

    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            openAddUserModal();
        });
    @endif
</script>
@endsection