<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'resource_type',
        'quantity',
        'unit',
        'purity',
        'yield_rate',
        'barangay_id',
        'latitude',
        'longitude',
        'status', // 'available', 'depleted', 'restricted'
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'quantity' => 'float',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }
}
