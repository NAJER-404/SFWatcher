<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentEvidence extends Model
{
    use HasFactory;

    protected $table = 'incident_evidence';

    protected $appends = ['url'];

    protected $fillable = [
        'incident_id',
        'file_path',
        'file_name',
        'file_type',
        'description',
        'uploaded_by',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }
        return asset('storage/' . $this->file_path);
    }
}
