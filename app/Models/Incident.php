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
        'anomaly_hp', 'anomaly_max_hp', 'investigator_hp', 'investigator_max_hp',
        'response_progress', 'response_investigator_id', 'response_started_at',
        'response_deadline', 'response_status', 'support_requested_at',
        'investigation_completed_at', 'investigation_result',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'incident_date' => 'datetime',
        'response_started_at' => 'datetime',
        'response_deadline' => 'datetime',
        'support_requested_at' => 'datetime',
        'investigation_completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($incident) {
            if (empty($incident->incident_code)) {
                $count = static::count() + 1;
                $incident->incident_code = sprintf('SF-INC-%03d', $count);
            }
            if (empty($incident->anomaly_max_hp)) {
                $maxHp = config('spectral_response.anomaly_hp.' . ($incident->severity ?? 'HIGH'), 100);
                $incident->anomaly_max_hp = $maxHp;
                if ($incident->anomaly_hp === null) {
                    $incident->anomaly_hp = $maxHp;
                }
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

    public function responseInvestigator()
    {
        return $this->belongsTo(User::class, 'response_investigator_id');
    }

    public function responderAssignments() { return $this->hasMany(ResponderAssignment::class); }

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
