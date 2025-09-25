<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['client_id','period_weeks','starts_at','ends_at','status'];
    protected $casts = ['starts_at'=>'datetime','ends_at'=>'datetime'];
    public function client() { return $this->belongsTo(Client::class); }
}
