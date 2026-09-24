@extends('layouts.investigator')

@section('title', 'Incident Reports Registry — Investigator Portal')

@section('content')
<!-- INVESTIGATOR SIDEBAR -->
@include('investigator.partials.sidebar')

<!-- REGISTRY WORKSPACE -->
<main class="flex-1 overflow-y-auto p-5 md:p-7 bg-[#0B0F14] space-y-6 max-w-7xl mx-auto w-full">

    <!-- Top Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-[#2A3440]">
        <div>
            <h1 class="text-lg font-bold text-white font-mono uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#38BDF8]"></span>
                <span>Incident Reports Registry</span>
            </h1>
            <p class="text-xs text-[#9CA3AF]">All reported spectral disturbances across San Francisco, Agusan del Sur</p>
        </div>

        <div class="flex items-center gap-2 text-xs font-mono">
            <span class="px-2.5 py-1 rounded-md bg-[#1B222C] border border-[#2A3440] text-slate-300">
                Total Records: <strong class="text-white">{{ $incidents->total() }}</strong>
            </span>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('investigator.incidents.index') }}" class="p-4 rounded-xl bg-[#151B23] border border-[#2A3440] grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
        <div>
            <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Code, title, description..." class="ecto-input">
        </div>

        <div>
            <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Status</label>
            <select name="status" class="ecto-select">
                <option value="ALL">All Statuses</option>
                <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE (Unresolved)</option>
                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                <option value="UNDER INVESTIGATION" {{ request('status') === 'UNDER INVESTIGATION' ? 'selected' : '' }}>UNDER INVESTIGATION</option>
                <option value="VERIFIED" {{ request('status') === 'VERIFIED' ? 'selected' : '' }}>VERIFIED</option>
                <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
                <option value="ESCALATED" {{ request('status') === 'ESCALATED' ? 'selected' : '' }}>ESCALATED</option>
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-mono text-[#64748B] uppercase mb-1">Barangay</label>
            <select name="barangay_id" class="ecto-select">
                <option value="">All Barangays</option>
                @foreach($barangays as $b)
                    <option value="{{ $b->id }}" {{ request('barangay_id') == $b->id ? 'selected' : '' }}>Brgy. {{ $b->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 py-2 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold rounded-lg text-xs transition">
                Apply Filters
            </button>
            <a href="{{ route('investigator.incidents.index') }}" class="px-3 py-2 bg-[#1B222C] hover:bg-[#222B38] text-slate-400 hover:text-white rounded-lg text-xs transition">
                Reset
            </a>
        </div>
    </form>

    <!-- Table -->
    <div class="rounded-xl bg-[#151B23] border border-[#2A3440] shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-[#0F151C] text-[10px] font-mono uppercase tracking-wider text-[#64748B] border-b border-[#2A3440]">
                    <tr>
                        <th class="px-4 py-3">Incident Code</th>
                        <th class="px-4 py-3">Title &amp; Type</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Severity</th>
                        <th class="px-4 py-3">Reported Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1E2631]">
                    @forelse($incidents as $inc)
                    <tr class="hover:bg-[#1B222C]/70 transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-[#8B5CF6]">
                            {{ $inc->incident_code }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-200 truncate max-w-[240px]">{{ $inc->title }}</div>
                            <div class="text-[10px] text-[#64748B] font-mono">{{ $inc->incident_type }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-medium text-slate-300">Brgy. {{ $inc->barangay->name ?? 'San Francisco' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge-{{ strtolower($inc->severity) }} px-2 py-0.5 rounded text-[10px] font-bold font-mono">
                                {{ $inc->severity }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] text-slate-400">
                            {{ $inc->incident_date ? $inc->incident_date->format('Y-m-d h:i A') : ($inc->created_at ? $inc->created_at->format('Y-m-d h:i A') : '—') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="{{ $inc->status_badge_class }} px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono">
                                {{ $inc->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('investigator.incidents.review', $inc->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#1B222C] hover:bg-[#8B5CF6] border border-[#2A3440] hover:border-[#8B5CF6] text-slate-300 hover:text-white font-semibold rounded-lg text-xs transition shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span>Inspect</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                            No incident records match the filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($incidents->hasPages())
        <div class="p-4 border-t border-[#2A3440] bg-[#11161D]">
            {{ $incidents->withQueryString()->links() }}
        </div>
        @endif
    </div>

</main>
@endsection
