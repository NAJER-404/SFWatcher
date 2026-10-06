<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'reporter' | 'investigator'
        'responder_class',
        'responder_status',
        'xp',
        'successful_responses',
        'investigations_completed',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isInvestigator(): bool
    {
        return $this->role === 'investigator';
    }

    public function isResponder(): bool { return $this->role === 'responder'; }

    public function responderAssignments()
    {
        return $this->hasMany(ResponderAssignment::class, 'responder_id');
    }

    public function isReporter(): bool
    {
        return $this->role === 'reporter';
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'reported_by');
    }

    public function investigations()
    {
        return $this->hasMany(Investigation::class, 'investigator_id');
    }

    public function evidence()
    {
        return $this->hasMany(IncidentEvidence::class, 'uploaded_by');
    }

    public function promotionHistory()
    {
        return $this->hasMany(PromotionHistory::class);
    }

    public function adminActivityLogs()
    {
        return $this->hasMany(AdminActivityLog::class);
    }

    public function getMaxHpAttribute(): int
    {
        $class = strtoupper($this->responder_class ?? 'D');
        return (int) config("spectral_response.classes.{$class}.responder_hp", 100);
    }

    public function getNextClassAttribute(): ?string
    {
        return match (strtoupper($this->responder_class ?? 'D')) {
            'D' => 'C',
            'C' => 'B',
            'B' => 'A',
            default => null,
        };
    }

    public function getRequiredPromotionXpAttribute(): ?int
    {
        $class = strtoupper($this->responder_class ?? 'D');
        return config("spectral_response.classes.{$class}.promotion_xp");
    }

    public function isPromotionEligible(): bool
    {
        $next = $this->next_class;
        $req = $this->required_promotion_xp;
        return $this->isResponder() && $next !== null && $req !== null && $this->xp >= $req;
    }
}
