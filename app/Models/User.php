<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name','email','password','role'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_set_at'   => 'datetime',
    ];
    protected $hidden = ['password','remember_token'];

    public function coach()  { return $this->hasOne(Coach::class); }
    public function client() { return $this->hasOne(Client::class); }
}
