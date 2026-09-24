<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponderAssignment extends Model
{
    protected $fillable = ['incident_id','investigator_id','responder_id','assigned_at','accepted_at','response_started_at','response_completed_at','status','anomaly_hp','anomaly_max_hp','responder_hp','responder_max_hp','response_progress','response_deadline','result','notes'];
    protected $casts = ['assigned_at'=>'datetime','accepted_at'=>'datetime','response_started_at'=>'datetime','response_completed_at'=>'datetime','response_deadline'=>'datetime'];
    public function incident() { return $this->belongsTo(Incident::class); }
    public function investigator() { return $this->belongsTo(User::class, 'investigator_id'); }
    public function responder() { return $this->belongsTo(User::class, 'responder_id'); }
}
