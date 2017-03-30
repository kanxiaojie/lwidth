<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Praise extends Model
{
    protected $table = "praises";
    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function post()
    {
        return $this->belongsTo('App\Post','post_id');
    }
}
