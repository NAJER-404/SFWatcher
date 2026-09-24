<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionHistory extends Model
{
    protected $fillable = ['user_id', 'promoted_by', 'from_class', 'to_class', 'xp_at_promotion'];

    public function user() { return $this->belongsTo(User::class); }
    public function promoter() { return $this->belongsTo(User::class, 'promoted_by'); }
}
