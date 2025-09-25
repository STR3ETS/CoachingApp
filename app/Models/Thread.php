<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = ['client_id','coach_id','subject'];
    public function messages() { return $this->hasMany(Message::class); }
    public function client()   { return $this->belongsTo(Client::class); }
    public function coach()    { return $this->belongsTo(Coach::class); }
}
