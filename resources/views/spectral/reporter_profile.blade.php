@extends('layouts.app')
@section('title', 'My Profile — SFWatch')

@php
    $user = $user ?? \Illuminate\Support\Facades\Auth::user();

    $myIncidents = collect($incidents ?? \App\Models\Incident::with('barangay')
        ->where('reported_by', $user->id)
        ->latest()
        ->get());

    $total    = $myIncidents->count();
    $pending  = $myIncidents->where('status', 'PENDING')->count();
    $active   = $myIncidents->whereIn('status', ['UNDER INVESTIGATION', 'VERIFIED'])->count();
    $resolved = $myIncidents->where('status', 'RESOLVED')->count();

    $nameParts = preg_split('/\s+/', trim($user->name));
    $initials  = strtoupper(mb_substr($nameParts[0] ?? 'U', 0, 1) . mb_substr($nameParts[1] ?? '', 0, 1));

    $inputClass = 'w-full rounded-lg bg-[#11161D] border border-[#2A3440] px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-[#8B5CF6] focus:ring-1 focus:ring-[#8B5CF6]/40 transition';
@endphp

@section('content')
@include('spectral.partials.sidebar')

<main class="flex-1 h-full overflow-y-auto bg-[#06090D] p-4 sm:p-6 lg:p-8">
    <div class="max-w-5xl mx-auto space-y-5">

        {{-- ── Flash messages ── --}}
        @if(session('success'))
            <div role="status" class="flex items-start gap-2.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-xs text-emerald-300">
                <svg class="w-4 h-4 mt-px shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- ═════ SINGLE VERTICAL COLUMN STACK ═════ --}}
        <div class="space-y-5">

            {{-- Identity card with aligned Security section --}}
            <section class="rounded-2xl bg-[#151B23] border border-[#2A3440] overflow-hidden" aria-labelledby="identity-heading">
                <div class="h-16 bg-gradient-to-r from-[#8B5CF6]/30 via-[#2DD4BF]/10 to-transparent border-b border-[#2A3440]" aria-hidden="true"></div>

                <div class="px-5 pb-5 -mt-8">
                    <div class="grid h-16 w-16 place-items-center rounded-2xl border-2 border-[#151B23] bg-[#1B222C] ring-1 ring-[#8B5CF6]/50 text-lg font-bold font-mono text-[#C4B5FD]" aria-hidden="true">
                        {{ $initials }}
                    </div>

                    <h2 id="identity-heading" class="mt-3 text-base font-bold text-white leading-tight">{{ $user->name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5 break-all">{{ $user->email }}</p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-md border border-[#8B5CF6]/40 bg-[#8B5CF6]/10 px-2 py-0.5 text-[10px] font-mono font-bold capitalize text-[#A78BFA]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#8B5CF6]"></span>{{ $user->role ?? 'reporter' }}
                        </span>
                    </div>

                    <dl class="mt-4 space-y-3 border-t border-[#2A3440] pt-4 text-xs">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[#64748B]">Member since</dt>
                            <dd class="text-slate-200 font-medium">{{ $user->created_at?->format('F j, Y') ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[#64748B]">Location</dt>
                            <dd class="text-slate-200 font-medium text-right">San Francisco, Agusan del Sur</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[#64748B]">Total Reports Filed</dt>
                            <dd class="text-slate-200 font-mono font-bold">{{ $total }}</dd>
                        </div>
                        
                        {{-- Security Settings Row --}}
                        <div class="flex items-center justify-between gap-3 pt-2 border-t border-[#2A3440]/60">
                            <dt class="text-[#64748B]">Security</dt>
                            <dd>
                                <button type="button" onclick="openPasswordModal()" class="inline-flex items-center gap-1.5 rounded-lg border border-[#8B5CF6]/40 bg-[#8B5CF6]/10 hover:bg-[#8B5CF6]/20 hover:border-[#8B5CF6]/60 px-3 py-1.5 text-xs font-semibold text-[#A78BFA] hover:text-white transition">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Change password
                                </button>
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- Stats Grid (2x2) --}}
            <section aria-label="Report statistics" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach([
                    ['Total reports', $total,    'text-white',        'bg-slate-400'],
                    ['Pending',       $pending,  'text-yellow-400',   'bg-yellow-400'],
                    ['In progress',   $active,   'text-[#A78BFA]',    'bg-[#8B5CF6]'],
                    ['Resolved',      $resolved, 'text-emerald-400',  'bg-emerald-400'],
                ] as [$label, $value, $text, $dot])
                    <div class="rounded-xl bg-[#151B23] border border-[#2A3440] p-4">
                        <p class="flex items-center gap-2 text-[11px] text-[#64748B]">
                            <span class="h-1.5 w-1.5 rounded-full {{ $dot }}" aria-hidden="true"></span>{{ $label }}
                        </p>
                        <p class="mt-2 text-2xl font-bold font-mono tabular-nums {{ $text }}">{{ $value }}</p>
                    </div>
                @endforeach
            </section>

            {{-- Status distribution progress bar --}}
            @if($total > 0)
            <section class="rounded-2xl bg-[#151B23] border border-[#2A3440] p-5" aria-labelledby="dist-heading">
                <h2 id="dist-heading" class="text-xs font-semibold text-slate-300">Where your reports stand</h2>
                <div class="mt-3 flex h-2 w-full overflow-hidden rounded-full bg-[#11161D]" role="img"
                     aria-label="{{ $pending }} pending, {{ $active }} in progress, {{ $resolved }} resolved">
                    @if($pending > 0)  <div class="bg-yellow-400"  style="width: {{ round($pending  / $total * 100, 2) }}%"></div>@endif
                    @if($active > 0)   <div class="bg-[#8B5CF6]"   style="width: {{ round($active   / $total * 100, 2) }}%"></div>@endif
                    @if($resolved > 0) <div class="bg-emerald-400" style="width: {{ round($resolved / $total * 100, 2) }}%"></div>@endif
                </div>
                <p class="mt-2 text-[11px] text-[#64748B]">
                    {{ $resolved }} of {{ $total }} {{ \Illuminate\Support\Str::plural('report', $total) }} resolved successfully.
                </p>
            </section>
            @endif

        </div>

    </div>
</main>

{{-- ═════ AJAX CHANGE PASSWORD MODAL ═════ --}}
<div id="passwordModal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 select-none">
    <div class="bg-[#151B23] border border-[#2A3440] rounded-2xl p-6 w-full max-w-md space-y-4 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-[#2A3440]">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-[#8B5CF6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Change Password
            </h3>
            <button type="button" onclick="closePasswordModal()" class="text-slate-400 hover:text-white transition text-lg">&times;</button>
        </div>

        <!-- Form State -->
        <form id="passwordChangeForm" onsubmit="submitPasswordAjax(event)" class="space-y-3">
            @csrf
            @method('PUT')
            
            <div id="passwordErrorBox" class="hidden rounded-xl border border-rose-500/30 bg-rose-500/10 p-3 text-xs text-rose-300"></div>

            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">Current password</label>
                <input name="current_password" type="password" required class="{{ $inputClass }}">
            </div>
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">New password</label>
                <input name="password" type="password" required class="{{ $inputClass }}">
            </div>
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">Confirm new password</label>
                <input name="password_confirmation" type="password" required class="{{ $inputClass }}">
            </div>

            <div class="pt-3 flex justify-end gap-2 text-xs">
                <button type="button" onclick="closePasswordModal()" class="px-4 py-2 rounded-lg bg-[#1E2631] text-slate-300 hover:text-white transition">Cancel</button>
                <button type="submit" id="submitPasswordBtn" class="px-4 py-2 rounded-lg bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold transition">Update password</button>
            </div>
        </form>

        <!-- Success State Options (Stay or Logout) -->
        <div id="passwordSuccessState" class="hidden space-y-4 text-center py-4">
            <div class="w-12 h-12 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 grid place-items-center mx-auto">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h4 class="text-sm font-bold text-white">Password Updated Successfully!</h4>
            <p class="text-xs text-slate-400">Would you like to stay logged in or log out of your session?</p>
            
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="closePasswordModal()" class="flex-1 py-2 px-3 rounded-lg bg-[#1E2631] hover:bg-[#2A3440] text-slate-200 text-xs font-semibold transition">Stay Logged In</button>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 text-xs font-semibold transition">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openPasswordModal() {
    document.getElementById('passwordErrorBox').classList.add('hidden');
    document.getElementById('passwordChangeForm').reset();
    document.getElementById('passwordChangeForm').classList.remove('hidden');
    document.getElementById('passwordSuccessState').classList.add('hidden');
    document.getElementById('passwordModal').classList.remove('hidden');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

async function submitPasswordAjax(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const errorBox = document.getElementById('passwordErrorBox');
    const submitBtn = document.getElementById('submitPasswordBtn');

    errorBox.classList.add('hidden');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';

    try {
        const response = await fetch("{{ route('spectral.profile.password') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });

        const data = await response.json();

        if (!response.ok) {
            let errorMsg = 'Failed to update password.';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('<br>');
            } else if (data.message) {
                errorMsg = data.message;
            }
            errorBox.innerHTML = errorMsg;
            errorBox.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update password';
        } else {
            form.reset();
            form.classList.add('hidden');
            document.getElementById('passwordSuccessState').classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update password';
        }
    } catch (err) {
        errorBox.textContent = 'An unexpected error occurred.';
        errorBox.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update password';
    }
}
</script>
@endsection