{{-- Report Incident Modal - included in dashboard and my-reports pages --}}
<div id="report-modal" class="hidden fixed inset-0 z-[1000] flex items-center justify-center p-3 sm:p-4">
    <!-- Backdrop -->
    <div class="absolute inset-0 ecto-modal-backdrop" onclick="SpectralUI.closeReportModal()"></div>

    <!-- Modal Content -->
    <div class="relative ecto-modal-content w-full max-w-lg max-h-[94vh] flex flex-col select-none rounded-xl bg-[#151B23] border border-[#2A3440] shadow-2xl overflow-hidden">
        
        <!-- Modal Header -->
        <div class="px-4 sm:px-5 py-3 border-b border-[#2A3440] flex items-center justify-between flex-shrink-0 bg-[#11161D]">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/spectra-logo.png') }}" alt="Spectra" class="w-6 h-6 object-contain">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-wide">Report Incident</h3>
                    <p class="text-[10px] text-[#9CA3AF]">San Francisco, Agusan del Sur</p>
                </div>
            </div>
            <button onclick="SpectralUI.closeReportModal()" class="w-7 h-7 rounded-md bg-[#1B222C] hover:bg-[#222B38] text-[#9CA3AF] hover:text-white flex items-center justify-center text-sm font-bold transition">
                &times;
            </button>
        </div>

        <!-- Form Body -->
        <form id="report-form" action="{{ route('spectral.incidents.store') }}" method="POST" enctype="multipart/form-data" onsubmit="SpectralUI.submitReport(event)" class="p-3.5 sm:p-5 overflow-y-auto space-y-3 sm:space-y-3.5 text-xs">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Incident Type -->
                <div class="space-y-1">
                    <label class="text-[11px] font-semibold text-slate-300">Incident Type</label>
                    <select id="report-type" name="incident_type" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" required>
                        <option class="bg-[#151B23] text-slate-200" value="Ectoplasmic Anomaly">Ectoplasmic Anomaly</option>
                        <option class="bg-[#151B23] text-slate-200" value="Spirit Activity">Spirit Activity</option>
                        <option class="bg-[#151B23] text-slate-200" value="Spectral Residue">Spectral Residue</option>
                        <option class="bg-[#151B23] text-slate-200" value="Ward Failure">Ward Failure</option>
                        <option class="bg-[#151B23] text-slate-200" value="Containment Breach">Containment Breach</option>
                        <option class="bg-[#151B23] text-slate-200" value="Unknown Phenomenon">Unknown Phenomenon</option>
                    </select>
                </div>

                <!-- Severity -->
                <div class="space-y-1">
                    <label class="text-[11px] font-semibold text-slate-300">Severity</label>
                    <select id="report-severity" name="severity" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" required>
                        <option class="bg-[#151B23] text-slate-200" value="LOW">Low</option>
                        <option class="bg-[#151B23] text-slate-200" value="MEDIUM" selected>Medium</option>
                        <option class="bg-[#151B23] text-slate-200" value="HIGH">High</option>
                        <option class="bg-[#151B23] text-slate-200" value="CRITICAL">Critical</option>
                    </select>
                </div>
            </div>

            <!-- Incident Title -->
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">Title</label>
                <input type="text" id="report-title" name="title" class="ecto-input bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none placeholder-slate-500" placeholder="e.g. Energy surge detected near Hubang..." required>
            </div>

            <!-- Location Picker, Readable Address & Barangay Selector -->
            <div class="p-3.5 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-semibold text-slate-200">Incident Location</span>
                        <span id="report-geocode-status" class="text-[10px] font-mono text-[#34D399]"></span>
                    </div>
                    <button type="button" onclick="SpectralMap.startLocationPicking()" class="px-2.5 py-1 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold text-[11px] rounded-md transition flex items-center gap-1.5 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        <span>Select on Map</span>
                    </button>
                </div>

                <!-- Address Lookup Loading Indicator -->
                <div id="report-geocode-loading" class="hidden flex items-center gap-2 px-3 py-2 rounded-lg bg-[#8B5CF6]/10 border border-[#8B5CF6]/30 text-[#A78BFA] text-[11px]">
                    <svg class="animate-spin h-3.5 w-3.5 text-[#8B5CF6] flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Retrieving address via OpenStreetMap Nominatim...</span>
                </div>

                <!-- Address Lookup Fallback/Error Alert -->
                <div id="report-geocode-error" class="hidden flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-[11px]">
                    <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span id="report-geocode-error-text">Address lookup unavailable. Exact coordinates retained.</span>
                </div>

                <!-- Readable Address & Location Review Card -->
                <div id="report-location-review-card" class="rounded-lg bg-[#151B23] border border-[#2A3440] p-2.5 space-y-2">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="text-[10px] text-[#9CA3AF] font-mono">Readable Address</label>
                            <span id="report-geocode-badge" class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Verified</span>
                        </div>
                        <input type="text" id="report-address" name="resolved_address" readonly
                            class="ecto-input bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-2.5 py-1.5 text-xs w-full focus:outline-none placeholder-slate-500 font-sans cursor-default"
                            placeholder="Address will be resolved automatically after picking location...">
                    </div>

                    <!-- Location Hierarchy Review -->
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="bg-[#11161D] p-2 rounded border border-[#2A3440]">
                            <span class="text-[9px] text-[#9CA3AF] block font-mono">Barangay</span>
                            <span id="report-review-barangay" class="text-slate-200 font-medium truncate block">Brgy. Hubang</span>
                        </div>
                        <div class="bg-[#11161D] p-2 rounded border border-[#2A3440]">
                            <span class="text-[9px] text-[#9CA3AF] block font-mono">Municipality & Province</span>
                            <span id="report-review-municipality" class="text-slate-200 font-medium truncate block">San Francisco, Agusan del Sur</span>
                        </div>
                    </div>

                    <!-- Confirmation Checkbox & Coordinates Display -->
                    <div class="pt-1.5 border-t border-[#2A3440] flex items-center justify-between gap-2">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" id="report-location-confirmed" checked class="w-3.5 h-3.5 rounded bg-[#11161D] border-[#2A3440] text-[#8B5CF6] focus:ring-[#8B5CF6] focus:ring-offset-0">
                            <span class="text-[11px] text-slate-300 font-medium">I have reviewed and confirmed this location</span>
                        </label>
                        <span id="report-review-coords" class="text-[10px] font-mono text-[#9CA3AF] flex-shrink-0">8.531000, 125.973000</span>
                    </div>
                </div>

                <!-- Manual Fine-Tuning Coordinates & Barangay Selector -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Barangay</label>
                        <select id="report-barangay" name="barangay_id" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-2.5 py-1.5 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" required>
                            @foreach(isset($barangays) ? $barangays : \App\Models\Barangay::orderBy('name')->get() as $b)
                                <option class="bg-[#151B23] text-slate-200" value="{{ $b->id }}" data-id="{{ $b->id }}" data-name="{{ $b->name }}" data-lat="{{ $b->latitude }}" data-lng="{{ $b->longitude }}" {{ $b->name === 'Hubang' ? 'selected' : '' }}>
                                    Brgy. {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Latitude</label>
                        <input type="number" step="0.000001" id="report-lat" name="latitude" class="ecto-input font-mono bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-2.5 py-1.5 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" placeholder="8.5310" value="8.5310" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Longitude</label>
                        <input type="number" step="0.000001" id="report-lng" name="longitude" class="ecto-input font-mono bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-2.5 py-1.5 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" placeholder="125.9730" value="125.9730" required>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">Description</label>
                <textarea id="report-desc" name="description" rows="3" class="ecto-input bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg p-2.5 text-xs w-full focus:border-[#8B5CF6] focus:outline-none placeholder-slate-500" placeholder="Provide incident details, observations, or warnings..." required></textarea>
            </div>

            <!-- Evidence Photo Upload -->
            <div class="space-y-2">
                <label class="text-[11px] font-semibold text-slate-300">Evidence Photo (Optional)</label>
                <input type="file" id="report-evidence-file" name="evidence" accept="image/*" onchange="SpectralUI.handleEvidenceUpload(this)" class="ecto-input file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#8B5CF6] file:text-white hover:file:bg-[#7C3AED]">
                
                <!-- Image Preview Card -->
                <div id="report-evidence-preview-container" class="hidden relative rounded-lg border border-[#2A3440] bg-[#11161D] p-2 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img id="report-evidence-preview" src="" alt="Preview" class="w-16 h-12 object-cover rounded-md border border-[#2A3440]">
                        <span class="text-[11px] text-slate-300 font-mono">File Attached</span>
                    </div>
                    <button type="button" onclick="SpectralUI.removeEvidencePreview()" class="px-2 py-1 bg-[#1B222C] hover:bg-[#EF4444] text-slate-300 hover:text-white rounded text-[10px] font-semibold transition">
                        Remove
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-[#2A3440] flex items-center justify-end gap-2">
                <button type="button" onclick="SpectralUI.closeReportModal()" class="px-4 py-2 bg-[#1B222C] hover:bg-[#222B38] text-slate-300 font-semibold rounded-lg text-xs transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold rounded-lg text-xs shadow-md shadow-[#8B5CF6]/20 transition">
                    Submit Report
                </button>
            </div>

        </form>

    </div>
</div>
