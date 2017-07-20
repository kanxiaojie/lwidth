<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $table = "interests";

    public function users()
    {
        return $this->hasMany('App\User');
    }
}