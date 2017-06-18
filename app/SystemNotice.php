<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemNotice extends Model
{
    protected $table = "systemNotices";

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }
}