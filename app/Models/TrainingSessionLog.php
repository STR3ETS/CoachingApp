<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingSessionLog extends Model
{
    protected $fillable = [
        'client_id','plan_id','week_number','session_index','session_day',
        'completed_at','went_well','went_poorly','rpe','duration_minutes','notes'
    ];
    public function client(){ return $this->belongsTo(Client::class); }
    public function plan(){ return $this->belongsTo(TrainingPlan::class,'plan_id'); }
}
