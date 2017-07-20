<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function profile()
    {
        return $this->hasOne('App\Profile');

    }

    public function language()
    {
        return $this->belongsTo('App\Language');
    }

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function interest()
    {
        return $this->belongsTo('App\Interest','interest_id');
    }

    public function systemNotices()
    {
        return $this->hasMany('App\SystemNotice');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function praises()
    {
        return $this->hasMany('App\Praise');
    }

    public function gender()
    {
        return $this->belongsTo('App\Gender','gender');
    }

    public function college()
    {
        return $this->belongsTo('App\College','college_id');
    }

    public function grade()
    {
        return $this->belongsTo('App\Grade','grade');
    }
}
