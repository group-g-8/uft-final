<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class payment extends Model
{
    protected $fillable = [
        'amountpaid'
    ];
    public function agentpays(){

        return $this->hasMany('App\agent','Payment_Id');
    }
    public function adminpays(){

        return $this->hasMany('App\User','Payment_Id');
    }

}
  
