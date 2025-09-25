<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    protected $table = 'client_profiles';

    protected $fillable = [
        'client_id','birthdate','address','gender','height_cm','weight_kg','injuries','goals',
        'period_weeks','frequency','background','facilities','materials','work_hours',
        'heartrate','test_12min','test_5k','coach_preference'
    ];

    protected $casts = [
        'birthdate' => 'date',
        'injuries' => 'array',
        'goals' => 'array',
        'frequency' => 'array',
        'heartrate' => 'array',
        'test_12min' => 'array',
        'test_5k' => 'array',
    ];

    public function client() { return $this->belongsTo(Client::class); }
}
