<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\WardStation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSafeZoneController extends Controller
{
    public function index()
    {
        $zones = collect();
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('ward_stations', 'is_safe_zone')) {
                $zones = WardStation::with('barangay')->where('is_safe_zone', true)->orderBy('name')->get();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AdminSafeZoneController::index safe zones query failed: ' . $e->getMessage());
        }
        return view('admin.safe-zones.index', compact('zones'));
    }

    public function create()
    {
        $barangays = Barangay::orderBy('name')->get();
        return view('admin.safe-zones.create', compact('barangays'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                       => 'required|string|max:255',
            'code'                       => 'nullable|string|max:50|unique:ward_stations,code',
            'description'                => 'nullable|string|max:255',
            'barangay_id'                => 'nullable|exists:barangays,id',
            'latitude'                   => 'required|numeric|between:-90,90',
            'longitude'                  => 'required|numeric|between:-180,180',
            'safe_zone_radius_meters'    => 'required|integer|min:50|max:5000',
            'status'                     => 'required|in:active,degraded,breached',
            'protection_days'            => 'nullable|integer|min:0|max:30',
            'protection_hours'           => 'nullable|integer|min:0|max:24',
            'protection_minutes'         => 'nullable|integer|min:0|max:59',
            'protection_seconds'         => 'nullable|integer|min:0|max:59',
            'relief_food'                => 'nullable|in:available,limited,out_of_stock',
            'relief_water'               => 'nullable|in:available,limited,out_of_stock',
            'relief_first_aid'           => 'nullable|in:available,limited,out_of_stock',
            'assistance_services'        => 'nullable|array',
            'assistance_services.*'      => 'string|max:100',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = 'WS-' . strtoupper(Str::random(6));
        }

        $validated['is_safe_zone']        = $request->boolean('is_safe_zone', true);
        $validated['radius_meters']       = $validated['safe_zone_radius_meters'];
        $validated['assistance_services'] = $request->input('assistance_services', []);

        // 🕐 Compute protection expiry from duration
        $days    = (int) ($validated['protection_days'] ?? 0);
        $hours   = (int) ($validated['protection_hours'] ?? 0);
        $minutes = (int) ($validated['protection_minutes'] ?? 0);
        $seconds = (int) ($validated['protection_seconds'] ?? 0);

        if ($days || $hours || $minutes || $seconds) {
            $validated['ward_protection_expires_at'] = now()
                ->addDays($days)
                ->addHours($hours)
                ->addMinutes($minutes)
                ->addSeconds($seconds);
        } else {
            $validated['ward_protection_expires_at'] = null;
        }

        unset($validated['protection_days'], $validated['protection_hours'], $validated['protection_minutes'], $validated['protection_seconds']);

        WardStation::create($validated);

        return redirect()->route('admin.map')->with('success', "Safe Zone '{$validated['name']}' created successfully.");
    }

    public function edit($id)
    {
        $zone = WardStation::findOrFail($id);
        $barangays = Barangay::orderBy('name')->get();
        return view('admin.safe-zones.edit', compact('zone', 'barangays'));
    }

    public function update(Request $request, $id)
    {
        $zone = WardStation::findOrFail($id);

        $validated = $request->validate([
            'name'                       => 'required|string|max:255',
            'code'                       => 'nullable|string|max:50|unique:ward_stations,code,' . $zone->id,
            'description'                => 'nullable|string|max:255',
            'barangay_id'                => 'nullable|exists:barangays,id',
            'latitude'                   => 'required|numeric|between:-90,90',
            'longitude'                  => 'required|numeric|between:-180,180',
            'safe_zone_radius_meters'    => 'required|integer|min:50|max:5000',
            'status'                     => 'required|in:active,degraded,breached',
            'protection_days'            => 'nullable|integer|min:0|max:30',
            'protection_hours'           => 'nullable|integer|min:0|max:24',
            'protection_minutes'         => 'nullable|integer|min:0|max:59',
            'protection_seconds'         => 'nullable|integer|min:0|max:59',
            'relief_food'                => 'nullable|in:available,limited,out_of_stock',
            'relief_water'               => 'nullable|in:available,limited,out_of_stock',
            'relief_first_aid'           => 'nullable|in:available,limited,out_of_stock',
            'assistance_services'        => 'nullable|array',
            'assistance_services.*'      => 'string|max:100',
        ]);

        $validated['is_safe_zone']        = $request->boolean('is_safe_zone');
        $validated['radius_meters']       = $validated['safe_zone_radius_meters'];
        $validated['assistance_services'] = $request->input('assistance_services', []);

        $days    = (int) ($validated['protection_days'] ?? 0);
        $hours   = (int) ($validated['protection_hours'] ?? 0);
        $minutes = (int) ($validated['protection_minutes'] ?? 0);
        $seconds = (int) ($validated['protection_seconds'] ?? 0);

        if ($days || $hours || $minutes || $seconds) {
            $validated['ward_protection_expires_at'] = now()->addDays($days)->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);
        } else {
            $validated['ward_protection_expires_at'] = null;
        }

        unset($validated['protection_days'], $validated['protection_hours'], $validated['protection_minutes'], $validated['protection_seconds']);

        $zone->update($validated);

        return redirect()->route('admin.map')->with('success', "Safe Zone '{$zone->name}' updated.");
    }

    public function destroy($id)
    {
        $zone = WardStation::findOrFail($id);
        $name = $zone->name;
        $zone->delete();
        return redirect()->route('admin.map')->with('success', "Safe Zone '{$name}' deleted.");
    }

    public function toggle($id)
    {
        $zone = WardStation::findOrFail($id);
        $zone->is_safe_zone = !$zone->is_safe_zone;
        $zone->save();
        return back()->with('success', "Safe Zone '{$zone->name}' has been " . ($zone->is_safe_zone ? 'activated' : 'deactivated') . ".");
    }
}
