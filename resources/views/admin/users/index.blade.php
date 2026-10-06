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
        </div>
    </div>

    <!-- Search & Filters Toolbar -->
    <form method="GET" action="{{ url()->current() }}" class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between text-xs">
        <div class="flex-1 flex flex-col sm:flex-row gap-2.5">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search by name or email..."
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
                                @else bg-cyan-500/15 text-cyan-300 border-cyan-500/40
                                @endif">
                                {{ $u->role }}
                            </span>
                        </td>

                        <td class="p-3.5">
                            @if($u->isResponder())
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border
                                    @if($u->responder_status === 'AVAILABLE') bg-emerald-500/15 text-emerald-300 border-emerald-500/30
                                    @elseif($u->responder_status === 'RESPONDING') bg-purple-500/15 text-purple-300 border-purple-500/30
                                    @else bg-slate-700 text-slate-300 border-slate-600
                                    @endif">
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

                        <td class="p-3.5 text-slate-400 text-[11px]">
                            {{ $u->created_at ? $u->created_at->format('Y-m-d') : '—' }}
                        </td>

                        <!-- Actions: View, Update, Delete -->
                        <td class="p-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.users.show', $u) }}"
                                   title="View Profile"
                                   class="px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] hover:border-[#8B5CF6] text-[#A78BFA] hover:text-white font-bold transition">
                                    View
                                </a>
                                <a href="{{ route('admin.users.edit', $u) }}"
                                   title="Update User"
                                   class="px-2.5 py-1 rounded-lg bg-[#11161D] border border-[#2A3440] hover:border-amber-500/50 text-amber-400 hover:text-white font-bold transition">
                                    Update
                                </a>
                                @if($u->id !== auth()->id() && $u->email !== 'admin@ectonet.gov')
                                    <button type="button"
                                            onclick="openDeleteUserModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->email) }}', '{{ route('admin.users.destroy', $u) }}')"
                                            title="Delete User"
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
                        @else bg-cyan-500/15 text-cyan-300 border-cyan-500/40
                        @endif">
                        {{ $u->role }}
                    </span>
                </div>

                @if($u->isResponder())
                    <div class="p-2.5 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-1 text-[11px]">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Class / HP:</span>
                            <span class="text-white font-bold">Class {{ $u->responder_class ?? 'D' }} ({{ $u->max_hp }} HP)</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">XP / Missions:</span>
                            <span class="text-amber-400 font-bold">{{ $u->xp }} XP &bull; {{ $u->successful_responses }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status:</span>
                            <span class="text-emerald-400 font-bold">{{ $u->responder_status ?? 'AVAILABLE' }}</span>
                        </div>
                        @if($u->isPromotionEligible())
                            <p class="text-emerald-400 font-bold text-[10px] pt-1 border-t border-[#2A3440]">[ ELIGIBLE FOR PROMOTION ]</p>
                        @endif
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#2A3440] text-[11px] text-slate-400">
                    <span>Registered: {{ $u->created_at ? $u->created_at->format('Y-m-d') : '—' }}</span>
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('admin.users.show', $u) }}" class="px-2.5 py-1 rounded bg-[#11161D] border border-[#8B5CF6]/50 text-[#C4B5FD] font-bold">
                            View
                        </a>
                        <a href="{{ route('admin.users.edit', $u) }}" class="px-2.5 py-1 rounded bg-[#11161D] border border-amber-500/40 text-amber-400 font-bold">
                            Update
                        </a>
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

    <!-- Delete Confirmation Alert Modal -->
    <div id="delete-user-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/75 backdrop-blur-xs font-mono">
        <div class="w-full max-w-md rounded-2xl bg-[#151B23] border border-rose-500/40 p-6 space-y-4 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
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
                    <button type="button"
                            onclick="closeDeleteUserModal()"
                            class="px-4 py-2 rounded-xl bg-[#11161D] border border-[#2A3440] text-slate-300 hover:text-white transition text-xs font-bold">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold transition text-xs shadow-lg shadow-rose-950/40">
                        Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

<script>
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

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDeleteUserModal();
    });
</script>
@endsection
