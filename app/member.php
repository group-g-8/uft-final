<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class member extends Model
{
    //
    public function becomes(){
        return $this->hasOne('App\agent','agentid');
    }
}
