<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateChat extends Model
{
    public $timestamps = false;
    protected $table = "private_chat_log";

    /**
     * @name 保存
     */
    public static function saveRecord($params = [])
    {
        $private_chat = new PrivateChat();
        $private_chat->from_user_id = $params['from_user_id'];
        $private_chat->to_user_id = $params['to_user_id'];
        $private_chat->content = $params['content'];
        $private_chat->created_at = time();
        if ($private_chat->save()){
            return true;
        }else{
            return false;
        }
    }
}
