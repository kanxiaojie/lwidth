<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = "postings";

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function praises()
    {
        return $this->hasMany('App\Praise');
    }
}