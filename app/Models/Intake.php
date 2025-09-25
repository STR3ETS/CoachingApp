<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intake extends Model
{
    protected $fillable = ['client_id','payload','is_complete'];
    protected $casts = ['payload' => 'array','is_complete' => 'boolean'];

    public function client() { return $this->belongsTo(Client::class); }
}
