<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingPlan extends Model
{
    protected $fillable = ['client_id','coach_id','title','weeks','plan_json','is_final'];
    protected $casts = [
        'plan_json' => 'array',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_final'  => 'boolean',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function coach()  { return $this->belongsTo(Coach::class); }
    public function sessionLogs()
    {
        return $this->hasMany(\App\Models\TrainingSessionLog::class, 'plan_id');
    }
}
