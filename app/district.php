<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class district extends Model
{   public function AgentAvailable(){

      return $this->hasMany('App\agent');
}
protected $fillable = [
      'name'
  ];
}
