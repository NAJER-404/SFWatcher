<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardStation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'barangay_id',
        'latitude',
        'longitude',
        'status',
        'is_safe_zone',
        'safe_zone_radius_meters',
        'ward_protection_expires_at',
        'relief_food',
        'relief_water',
        'relief_first_aid',
        'assistance_services',
        'shield_level',
        'energy_level',
        'frequency',
        'radius_meters',
        'last_recalibrated',
    ];

    protected $casts = [
        'latitude'                   => 'float',
        'longitude'                  => 'float',
        'shield_level'               => 'integer',
        'energy_level'               => 'integer',
        'is_safe_zone'               => 'boolean',
        'safe_zone_radius_meters'    => 'integer',
        'ward_protection_expires_at' => 'datetime',
        'assistance_services'        => 'array',
        'radius_meters'              => 'integer',
        'last_recalibrated'          => 'date',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    /**
     * Is this safe zone's ward protection still active?
     */
    public function isProtectionActive(): bool
    {
        if (!$this->ward_protection_expires_at) return false;
        return $this->ward_protection_expires_at->isFuture();
    }

    /**
     * Human-readable time remaining, e.g. "02h 35m"
     */
    public function protectionTimeRemaining(): ?string
    {
        if (!$this->ward_protection_expires_at) return null;
        if ($this->ward_protection_expires_at->isPast()) return null;

        $diff = now()->diff($this->ward_protection_expires_at);
        $totalHours = $diff->h + ($diff->days * 24);

        return sprintf('%02dh %02dm', $totalHours, $diff->i);
    }
}
