<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class agent extends Model
{
    
    protected $fillable = [
        'userName','Member_Id','status','signature','district_Id','LastName','lastName'
    ];
     public function Agent(){

        return $this->belongsTo('App\district','district_Id');
  }
}

