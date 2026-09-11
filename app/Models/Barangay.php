<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'municipality',
        'province',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function incidents()
    {
        return $this->hasMany(Incident::class);
    }

    public function wardStations()
    {
        return $this->hasMany(WardStation::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }
}
