<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class treasury extends Model
{
    public function amountAvailable(){
        //    return $this->hasMany('App\payment','payment_id');

    }
    protected $fillable = [
        'source','amount','district','date'
    ];
}
