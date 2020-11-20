<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Item;

class Orders extends Model
{
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','address_id', 'item_id','paid', 'state',
    ];
    //relation with items
    public static function items(){
        return $this->hasMany(Item::class);
    }
 
    // Set Deleting 
    public static function boot() {
        parent::boot();

        static::deleting(function($orders) { 
        });
    }
}
