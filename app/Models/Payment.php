<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['client_id','stripe_session_id','amount','currency','status','period_weeks'];
    protected $casts = ['amount' => 'integer','period_weeks' => 'integer',];
    public function client() { return $this->belongsTo(Client::class); }
}
