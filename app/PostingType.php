<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostingType extends Model
{
    protected $table = "postingTypes";

    public function postings()
    {
        return $this->hasMany('App\Post');
    }
}