<?php

namespace App\Http\Controllers;

use App\AdminUser;
use App\User;
use App\College;

use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function login(Request $request)
    {
        // $params = $request->get('params');
        $params = $request->all();
        $username = $params['username'];
        $password = md5($params['password']);

        $adminUser = User::where('id',$username)->first();
        if($adminUser)
        {
            if($password == $adminUser->password)
            {
                if (empty($adminUser->manage_level_id) || $adminUser->manage_level_id > 4) {
                    return response()->json(['code'=>203,'msg'=>'您还不是管理员呢！']);
                }
                if ($adminUser->manage_level_id == 4 && !empty($adminUser->manage_college_id)) {
                    $user = [];
                    $user['id'] = $adminUser->id;
                    $user['nickname'] = $adminUser->nickname;
                    $user['realname'] = $adminUser->realname;
                    $user['avatarUrl'] = $adminUser->avatarUrl;
                    $user['gender_id'] = $adminUser->gender_id;
                    // $user['gender_name'] = $adminUser->gender->name;
                    $user['college_id'] = $adminUser->college_id;
                    // $user['college_name'] = $adminUser->college->name;
                    $user['manage_level_id'] = $adminUser->manage_level_id;
                    $user['manage_college_id'] = $adminUser->manage_college_id;
                    $user['manage_college_name'] = College::where('id', $adminUser->manage_college_id)->first()->name;
                
                    return response()->json(['code'=>200,'msg'=>'登录成功！', 'user' => $user]);
                }
                if ($adminUser->manage_level_id == 1) {
                    $user = [];
                    $user['id'] = $adminUser->id;
                    $user['nickname'] = $adminUser->nickname;
                    $user['realname'] = $adminUser->realname;
                    $user['avatarUrl'] = $adminUser->avatarUrl;                    
                    $user['gender_id'] = $adminUser->gender_id;
                    // $user['gender_name'] = $adminUser->gender->name;
                    $user['college_id'] = $adminUser->college_id;
                    // $user['college_name'] = $adminUser->college->name;
                    $user['manage_level_id'] = $adminUser->manage_level_id;
                    $user['manage_college_id'] = $adminUser->manage_college_id;
                
                    return response()->json(['code'=>200,'msg'=>'登录成功！', 'user' => $user]);
                } else {
                    return response()->json(['code'=>204,'msg'=>'您的管理权限不明确，请联系管理员修改，管理员qq/微信：1363168456！']);
                }
                
            }else
            {
                return response()->json(['code'=>201,'msg'=>'密码错误！']);
            }
        }else
        {
            return response()->json(['code'=>202,'msg'=>'用户名不存在！']);
        }
    }

}
