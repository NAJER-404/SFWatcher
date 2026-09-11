<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_code',
        'reported_by',
        'barangay_id',
        'incident_type',
        'title',
        'description',
        'latitude',
        'longitude',
        'incident_date',
        'severity',
        'status',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'incident_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($incident) {
            if (empty($incident->incident_code)) {
                $count = static::count() + 1;
                $incident->incident_code = sprintf('SF-INC-%03d', $count);
            }
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function evidence()
    {
        return $this->hasMany(IncidentEvidence::class);
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class);
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'LOW' => '#22C55E',
            'MEDIUM' => '#EAB308',
            'HIGH' => '#F97316',
            'CRITICAL' => '#EF4444',
            default => '#9CA3AF',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'PENDING' => 'badge-pending',
            'UNDER INVESTIGATION' => 'badge-investigating',
            'VERIFIED' => 'badge-verified',
            'RESOLVED' => 'badge-resolved',
            'ESCALATED' => 'badge-escalated',
            default => 'badge-pending',
        };
    }
}
