<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    protected $table = "genders";

    public function user()
    {
        return $this->hasMany('App\User');
    }

}
