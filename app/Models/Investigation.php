<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investigation extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'investigator_id',
        'notes',
        'investigation_date',
        'result',
    ];

    protected $casts = [
        'investigation_date' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'investigator_id');
    }
}
