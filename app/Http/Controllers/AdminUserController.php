<?php

namespace App\Http\Controllers;

use App\AdminUser;
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
        $username = $request->get('name');
        $password = md5($request->get('password'));

        $adminUser = AdminUser::where('name',$username)->first();
        if($adminUser)
        {
            if($password == $adminUser->password)
            {
                return response()->json(['code'=>200,'message'=>'登录成功！']);
            }else
            {
                return response()->json(['code'=>201,'message'=>'密码错误！']);
            }
        }else
        {
            return response()->json(['code'=>201,'message'=>'用户名不存在！']);
        }
    }

}
