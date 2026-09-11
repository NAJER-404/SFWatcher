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

    public function isInvestigator(): bool
    {
        return $this->role === 'investigator';
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
}
