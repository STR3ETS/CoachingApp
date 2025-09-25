<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeighIn extends Model
{
    protected $fillable = ['client_id','date','weight_kg','notes'];
    protected $casts = ['date' => 'date', 'weight_kg' => 'decimal:1'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
