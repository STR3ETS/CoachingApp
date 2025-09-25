<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['user_id','coach_id','status'];

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }
    public function latestSubscription()
    {
        return $this->hasOne(\App\Models\Subscription::class)->latestOfMany();
    }
    public function activeSubscription()
    {
        return $this->hasOne(\App\Models\Subscription::class)
            ->ofMany('created_at', 'max', function ($q) {
                $q->where('status', 'active');
            });
    }
    public function trainingPlans()
    {
        return $this->hasMany(\App\Models\TrainingPlan::class);
    }
    public function user()     { return $this->belongsTo(User::class); }
    public function coach()    { return $this->belongsTo(Coach::class); }
    public function profile()  { return $this->hasOne(ClientProfile::class); }
    public function intakes()  { return $this->hasMany(Intake::class); }
    public function plans()    { return $this->hasMany(TrainingPlan::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function subs()     { return $this->hasMany(Subscription::class); }
    public function threads()  { return $this->hasMany(Thread::class); }
    public function weighIns() { return $this->hasMany(\App\Models\WeighIn::class); }
    public function sessionLogs()
    {
        return $this->hasMany(\App\Models\TrainingSessionLog::class);
    }
}
