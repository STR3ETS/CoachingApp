<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    protected $fillable = ['user_id','bio','specialties','is_active'];
    protected $casts = ['specialties' => 'array', 'is_active' => 'boolean'];

    public function user()    { return $this->belongsTo(User::class); }
    public function clients() { return $this->hasMany(Client::class); }
    public function plans()   { return $this->hasMany(TrainingPlan::class); }
    public function threads() { return $this->hasMany(Thread::class); }
}
