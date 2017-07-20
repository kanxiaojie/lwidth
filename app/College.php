<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $table = "colleges";

    public function users()
    {
        return $this->hasMany('App\User');
    }

    public function cities()
    {
        return $this->belongsTo('App\City','city_id');
    }
}
