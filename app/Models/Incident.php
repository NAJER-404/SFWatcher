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
        'required_responder_class',
        'status',
        'notes',
        'anomaly_hp', 'anomaly_max_hp', 'investigator_hp', 'investigator_max_hp',
        'response_progress', 'response_investigator_id', 'response_started_at',
        'response_deadline', 'response_status', 'support_requested_at',
        'investigation_completed_at', 'investigation_result',
        'archived_from_map_at', 'archived_by', 'archive_notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'incident_date' => 'datetime',
        'response_started_at' => 'datetime',
        'response_deadline' => 'datetime',
        'support_requested_at' => 'datetime',
        'investigation_completed_at' => 'datetime',
        'archived_from_map_at' => 'datetime',
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
                $sev = strtoupper($incident->severity ?? 'MEDIUM');
                $maxHp = config("spectral_response.anomaly_hp.{$sev}", 80);
                $incident->anomaly_max_hp = $maxHp;
                if ($incident->anomaly_hp === null) {
                    $incident->anomaly_hp = $maxHp;
                }
            }
            if (empty($incident->required_responder_class)) {
                $sev = strtoupper($incident->severity ?? 'MEDIUM');
                $incident->required_responder_class = config("spectral_response.required_minimum_class.{$sev}", 'D');
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

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function isArchivedFromMap(): bool
    {
        return !is_null($this->archived_from_map_at);
    }

    public function scopeActiveOnMap($query)
    {
        return $query->whereNull('archived_from_map_at');
    }

    public function scopeArchivedFromMap($query)
    {
        return $query->whereNotNull('archived_from_map_at');
    }

    public function getSeverityColorAttribute(): string
    {
        return match (strtoupper($this->severity ?? '')) {
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
            default => 'badge-pending',
        };
    }
}
