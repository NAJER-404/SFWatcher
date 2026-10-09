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
        <form id="report-form" onsubmit="SpectralUI.submitReport(event)" class="p-3.5 sm:p-5 overflow-y-auto space-y-3 sm:space-y-3.5 text-xs">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <!-- Incident Type -->
                <div class="space-y-1">
                    <label class="text-[11px] font-semibold text-slate-300">Incident Type</label>
                    <select id="report-type" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" required>
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
                    <select id="report-severity" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" required>
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
                <input type="text" id="report-title" class="ecto-input bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-3 py-2 text-xs w-full focus:border-[#8B5CF6] focus:outline-none placeholder-slate-500" placeholder="e.g. Energy surge detected near Hubang..." required>
            </div>

            <!-- Location Picker & Barangay Selector -->
            <div class="p-3 rounded-lg bg-[#11161D] border border-[#2A3440] space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-semibold text-slate-300">Coordinates</span>
                        <span id="report-geocode-status" class="text-[10px] font-mono text-[#9CA3AF]"></span>
                    </div>
                    <button type="button" onclick="SpectralMap.startLocationPicking()" class="px-2.5 py-1 bg-[#8B5CF6] hover:bg-[#7C3AED] text-white font-semibold text-[11px] rounded-md transition flex items-center gap-1.5 shadow-sm">
                        <span>Select on Map</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Barangay</label>
                        <select id="report-barangay" name="barangay_id" class="ecto-select bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-2.5 py-1.5 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" required>
                            @foreach(isset($barangays) ? $barangays : \App\Models\Barangay::orderBy('name')->get() as $b)
                                <option class="bg-[#151B23] text-slate-200" value="{{ $b->id }}" data-id="{{ $b->id }}" data-name="{{ $b->name }}" {{ $b->name === 'Hubang' ? 'selected' : '' }}>
                                    Brgy. {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Latitude</label>
                        <input type="number" step="0.000001" id="report-lat" class="ecto-input font-mono bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-2.5 py-1.5 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" placeholder="8.5310" value="8.5310" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] text-[#9CA3AF] font-mono">Longitude</label>
                        <input type="number" step="0.000001" id="report-lng" class="ecto-input font-mono bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg px-2.5 py-1.5 text-xs w-full focus:border-[#8B5CF6] focus:outline-none" placeholder="125.9730" value="125.9730" required>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-1">
                <label class="text-[11px] font-semibold text-slate-300">Description</label>
                <textarea id="report-desc" rows="3" class="ecto-input bg-[#11161D] text-slate-200 border border-[#2A3440] rounded-lg p-2.5 text-xs w-full focus:border-[#8B5CF6] focus:outline-none placeholder-slate-500" placeholder="Provide incident details, observations, or warnings..." required></textarea>
            </div>

            <!-- Evidence Photo Upload -->
            <div class="space-y-2">
                <label class="text-[11px] font-semibold text-slate-300">Evidence Photo (Optional)</label>
                <input type="file" id="report-evidence-file" accept="image/*" onchange="SpectralUI.handleEvidenceUpload(this)" class="ecto-input file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#8B5CF6] file:text-white hover:file:bg-[#7C3AED]">
                
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
