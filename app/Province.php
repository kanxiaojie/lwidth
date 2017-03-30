<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    public function country()
    {
        return $this->belongsTo('App\Country','country_id');
    }

    public function cities()
    {
        return $this->hasMany('App\City');
    }

}
