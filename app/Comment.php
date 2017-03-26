<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function post()
    {
        return $this->belongsTo('App\Post','post_id');
    }

    public function commentToComments()
    {
        return $this->hasMany('App\CommentToComment');
    }

    public function praiseToComments()
    {
        return $this->hasMany('App\PraiseToComment');
    }
}
