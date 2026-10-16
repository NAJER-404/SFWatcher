<?php

namespace App\Services;

use App\Models\WardStation;

class SafeZoneService
{
    public const BLOCKED_TITLE = 'Safe Zone Protected';
    public const BLOCKED_MESSAGE = 'You cannot report an incident within this protected radius. This Safe Zone is protected by Investigators and Responders. Please select a location outside the Safe Zone.';

    /**
     * Calculate Haversine distance in meters between two coordinates.
     */
    public static function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get all active Safe Zones from the ward_stations table.
     * Falls back to legacy hardcoded values if the new columns don't exist yet.
     *
     * @return array<int, array{id?: int, code?: string, name: string, lat: float, lng: float, radius: float, status: string, barangay?: string}>
     */
    public static function getActiveSafeZones(): array
    {
        $safeZones = [];

        try {
            $stations = WardStation::with('barangay')
                ->where('status', 'active')
                ->where('is_safe_zone', true)
                ->get();

            foreach ($stations as $ws) {
                $radius = $ws->safe_zone_radius_meters
                    ?? $ws->radius_meters
                    ?? 450;

                $safeZones[] = [
                    'id'       => $ws->id,
                    'code'     => $ws->code,
                    'name'     => $ws->name,
                    'lat'      => (float) $ws->latitude,
                    'lng'      => (float) $ws->longitude,
                    'radius'   => (float) $radius,
                    'status'   => 'active',
                    'barangay' => $ws->barangay?->name ?? 'San Francisco',
                ];
            }
        } catch (\Throwable $e) {
            // Column or table missing — fall back below
        }

        // Legacy fallback (only if DB lookup failed or returned nothing)
        if (empty($safeZones)) {
            $safeZones = [
                [
                    'name'     => 'San Francisco Municipal Gymnasium Sanctuary',
                    'lat'      => 8.5098,
                    'lng'      => 125.9780,
                    'radius'   => 450.0,
                    'status'   => 'active',
                    'barangay' => 'Barangay 1',
                ],
                [
                    'name'     => 'Hubang Transport Safe Haven',
                    'lat'      => 8.5310,
                    'lng'      => 125.9730,
                    'radius'   => 550.0,
                    'status'   => 'active',
                    'barangay' => 'Hubang',
                ],
            ];
        }

        return $safeZones;
    }

    /**
     * Check if a given latitude and longitude falls within any active Safe Zone.
     */
    public static function checkProtection(float $lat, float $lng): ?array
    {
        $safeZones = self::getActiveSafeZones();

        foreach ($safeZones as $sz) {
            if (($sz['status'] ?? 'active') !== 'active') {
                continue;
            }

            $szLat  = (float) $sz['lat'];
            $szLng  = (float) $sz['lng'];
            $radius = (float) $sz['radius'];

            $distance = self::calculateDistanceMeters($lat, $lng, $szLat, $szLng);

            if ($distance <= $radius) {
                return [
                    'protected' => true,
                    'safe_zone' => $sz,
                    'distance'  => $distance,
                    'radius'    => $radius,
                ];
            }
        }

        return null;
    }
}
