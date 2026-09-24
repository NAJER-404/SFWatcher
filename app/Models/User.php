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

    public function isPromotionEligible(): bool
    {
        $next = match ($this->responder_class) { 'D' => 'C', 'C' => 'B', 'B' => 'A', default => null };
        return $this->isResponder() && $next !== null && $this->xp >= config('spectral_response.classes.'.$this->responder_class.'.promotion_xp');
    }
}
