<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PraiseUser extends Model
{
    protected $table = "praiseUsers";
    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function post()
    {
        return $this->belongsTo('App\Post','post_id');
    }
}
