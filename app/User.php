<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Address;
use App\Orders;


class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email','name', 'password', 'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //encrypt user pwd
    public function setPasswordAttribute($pwd){
        $this->attributes['password'] = bcrypt($pwd);
    }
    // check user
    public function isAdmin(){
        if(!$this->attributes['role'])return false;
       return  ($this->attributes['role']=="admin" ? true : false);
    }
    //get user addressess
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Orders::class);
    }
    // Set Deleting 
    public static function boot() {
        parent::boot();

        static::deleting(function($user) { 
             $user->orders()->delete();
             $user->addresses()->delete();
        });
    }

}
