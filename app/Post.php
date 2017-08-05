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

    public function college()
    {
        return $this->belongsTo('App\College','college_id');
    }

    public function city()
    {
        return $this->belongsTo('App\City','city_id');
    }

    public function province()
    {
        return $this->belongsTo('App\Province','province_id');
    }

    public function postingType()
    {
        return $this->belongsTo('App\PostingType','postingType_id');
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
