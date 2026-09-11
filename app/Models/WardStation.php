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
        'barangay_id',
        'latitude',
        'longitude',
        'status', // 'active', 'degraded', 'breached'
        'shield_level',
        'energy_level',
        'frequency',
        'radius_meters',
        'last_recalibrated',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'shield_level' => 'integer',
        'energy_level' => 'integer',
        'radius_meters' => 'integer',
        'last_recalibrated' => 'date',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }
}
