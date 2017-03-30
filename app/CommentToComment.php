<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class CommentToComment extends Model
{
    protected $table = "commentToComments";

    public function comment()
    {
        return $this->belongsTo('App\Comment','comment_id');
    }
}