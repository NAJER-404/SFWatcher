{{-- Delete Confirmation Modal --}}
<div id="delete-confirm-modal" class="fixed inset-0 z-[1100] hidden items-center justify-center p-3.5 sm:p-4">
    <!-- Backdrop with blur -->
    <div id="delete-modal-backdrop" onclick="closeDeleteModal()" class="absolute inset-0 bg-black/85 backdrop-blur-md opacity-0 transition-opacity duration-200"></div>

    <!-- Modal Card with scale transition -->
    <div id="delete-modal-card" class="relative w-full max-w-md rounded-2xl bg-[#151B23] border border-rose-500/40 shadow-2xl shadow-rose-950/50 p-4 sm:p-6 space-y-4 sm:space-y-5 transform scale-95 opacity-0 transition-all duration-200 select-none overflow-hidden">
        
        <!-- Subtle Danger Ambient Glow -->
        <div class="absolute -top-16 -right-16 w-36 h-36 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Header -->
        <div class="flex items-start gap-3 sm:gap-4 relative">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-rose-500/15 border border-rose-500/40 flex items-center justify-center flex-shrink-0 text-rose-400 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm sm:text-base font-bold text-white tracking-wide">Delete Incident Report?</h3>
                <p class="text-xs text-[#9CA3AF] mt-1 leading-relaxed">
                    Are you sure you want to permanently delete <span id="delete-modal-code" class="text-rose-400 font-mono font-bold"></span><span id="delete-modal-title" class="text-slate-200 font-medium"></span>?
                </p>
            </div>
        </div>

        <!-- Warning Callout -->
        <div class="p-3 rounded-xl bg-rose-950/30 border border-rose-500/30 text-[11px] text-rose-300 font-mono flex items-center gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>This action is permanent and will erase all evidence and map markers from the GIS network.</span>
        </div>

        <!-- Action Buttons -->
        <form id="delete-confirm-form" method="POST" action="" onsubmit="handleDeleteSubmit(event)" class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3 pt-3 border-t border-[#1E2631]">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDeleteModal()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#11161D] border border-[#2A3440] hover:border-slate-500 hover:bg-[#1B222C] text-[#9CA3AF] hover:text-white text-xs font-mono font-semibold transition active:scale-95 text-center">
                Cancel
            </button>
            <button type="submit" id="delete-modal-submit-btn" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 via-rose-600 to-rose-700 hover:from-red-500 hover:to-rose-600 text-white text-xs font-mono font-bold transition-all shadow-lg shadow-rose-950/60 hover:shadow-rose-600/40 flex items-center justify-center gap-2 active:scale-95 border border-rose-400/40 group">
                <svg id="delete-btn-icon" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white group-hover:rotate-12 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span id="delete-btn-label">Yes, Delete Incident</span>
            </button>
        </form>
    </div>
</div>

<script>
function openDeleteModal(deleteUrl, incidentCode, incidentTitle) {
    const modal = document.getElementById('delete-confirm-modal');
    const backdrop = document.getElementById('delete-modal-backdrop');
    const card = document.getElementById('delete-modal-card');
    const form = document.getElementById('delete-confirm-form');
    const codeEl = document.getElementById('delete-modal-code');
    const titleEl = document.getElementById('delete-modal-title');
    const btn = document.getElementById('delete-modal-submit-btn');
    const btnLabel = document.getElementById('delete-btn-label');

    if (!modal) return;

    form.action = deleteUrl;
    if (codeEl) codeEl.textContent = incidentCode || '';
    if (titleEl) titleEl.textContent = incidentTitle ? ` ("${incidentTitle}")` : '';

    // Reset button state
    if (btn) btn.disabled = false;
    if (btnLabel) btnLabel.textContent = 'Yes, Delete Incident';

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        card.classList.remove('scale-95', 'opacity-0');
        card.classList.add('scale-100', 'opacity-100');
    });
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-confirm-modal');
    const backdrop = document.getElementById('delete-modal-backdrop');
    const card = document.getElementById('delete-modal-card');

    if (!modal) return;

    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }, 200);
}

function handleDeleteSubmit(e) {
    const btn = document.getElementById('delete-modal-submit-btn');
    const btnLabel = document.getElementById('delete-btn-label');
    const icon = document.getElementById('delete-btn-icon');

    if (btn) {
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
    }
    if (btnLabel) btnLabel.textContent = 'Deleting...';
    if (icon) icon.classList.add('animate-spin');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDeleteModal();
});
</script>
