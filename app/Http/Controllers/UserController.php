<?php

namespace App\Http\Controllers;

use App\Country;
use App\Gender;
use App\Profile;
use App\Repositories\BaseRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class UserController extends Controller
{
    protected $userRepository;
    protected $baseRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        BaseRepository $baseRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->baseRepository = $baseRepository;
    }

    public function encryptCode(Request $request)
    {
        $code = $request->get('code');

        $secret = Crypt::encrypt($code);

        return response()->json(['key' => $secret]);
    }

    public function decryptCode(Request $request)
    {
        $secret = $request->get('secret');

        $code = Crypt::decrypt($secret);

        return response()->json(['key' => $code]);

    }

    public function updateUser(Request $request)
    {
        $inputs = $request->all();

        $openid = Crypt::decrypt($inputs['wesecret']);
        $userInfo = [];
        $data = [];
        $user = $this->userRepository->getUserByOpenId($openid);
        if($user)
        {
            if(array_key_exists('file',$inputs))
            {
                $res = $this->baseRepository->uploadToQiniu($inputs);

                if($res['status'] == 201)
                {
                    return response()->json(['status' => 201,'message' => 'pictures upload failed']);
                }else
                {
                    $updateUser = $this->userRepository->updateUser($inputs,$user,$res['picturePath']);

                    $userInfo['id'] = $updateUser->id ;
                    $userInfo['nickName'] = $updateUser->nickname ;
                    $userInfo['avatarUrl'] = $updateUser->avatarUrl ;
                    $userInfo['gender'] = Gender::where('id',$updateUser->gender)->first()->name ;
                    $userInfo['province'] = "福建";
                    $userInfo['city'] = "厦门";
                    if($updateUser->country_id)
                    {
                        $userInfo['country'] = Country::where('id',$updateUser->country_id)->first()->name;
                    }
                    else
                    {
                        $userInfo['country'] = "中国";
                    }

                    if($updateUser->realname)
                    {
                        $userInfo['realname'] = $updateUser->realname;
                    }
                    else
                    {
                        $userInfo['realname'] = '';
                    }

                    $userInfo['college'] = '';
                    if($updateUser->major)
                    {
                        $userInfo['major'] = $updateUser->major;
                    }
                    else
                    {
                        $userInfo['major'] = '';
                    }

                    if($updateUser->grade)
                    {
                        $userInfo['grade'] = $updateUser->grade;
                    }else
                    {
                        $userInfo['grade'] = '';
                    }

                    if($updateUser->wechat)
                    {
                        $userInfo['wechat'] = $updateUser->wechat;
                    }else
                    {
                        $userInfo['wechat'] = '';
                    }

                    if($updateUser->QQ)
                    {
                        $userInfo['qq'] = $updateUser->QQ;
                    }else
                    {
                        $userInfo['qq'] = '';
                    }

                    if($updateUser->weibo)
                    {
                        $userInfo['weibo'] = $updateUser->weibo;
                    }else
                    {
                        $userInfo['weibo'] = '';
                    }

                    if($updateUser->phone)
                    {
                        $userInfo['mobilePhone'] = $updateUser->phone;
                    }else
                    {
                        $userInfo['mobilePhone'] = '';
                    }

                    $profile = Profile::where('user_id',$updateUser->id)->first();

                    if($profile->birthday)
                    {
                        $userInfo['birthday'] = $profile->birthday;
                    }
                    else
                    {
                        $userInfo['birthday'] = '';
                    }

                    if($profile->height)
                    {
                        $userInfo['height'] = $profile->height;
                    }
                    else
                    {
                        $userInfo['height'] = '';
                    }

                    if($profile->weight)
                    {
                        $userInfo['weight'] = $profile->weight;
                    }
                    else
                    {
                        $userInfo['weight'] = '';
                    }

                    if($profile->hometown)
                    {
                        $userInfo['hometown'] = $profile->hometown;
                    }
                    else
                    {
                        $userInfo['hometown'] = '';
                    }

                    if($profile->signature)
                    {
                        $userInfo['signature'] = $profile->signature;
                    }
                    else
                    {
                        $userInfo['signature'] = '';
                    }

                    if($profile->character)
                    {
                        $userInfo['character'] = $profile->character;
                    }
                    else
                    {
                        $userInfo['character'] = '';
                    }

                    if($profile->hobby)
                    {
                        $userInfo['hobby'] = $profile->hobby;
                    }
                    else
                    {
                        $userInfo['hobby'] = '';
                    }

                    if($profile->love_history)
                    {
                        $userInfo['love_history'] = $profile->love_history;
                    }
                    else
                    {
                        $userInfo['love_history'] = '';
                    }

                    if($profile->love_selecting)
                    {
                        $userInfo['love_selecting'] = $profile->love_selecting;
                    }
                    else
                    {
                        $userInfo['love_selecting'] = '';
                    }

                    return response()->json(['status' => 200,'data' => $userInfo]);

                }
            }
        }
        else
        {
            return response()->json(['status'=>201,'message'=>'user not exist']);
        }
    }

    public function getPictures()
    {
        $users = $this->userRepository->getPictures();

        $data = [];

        if($users)
        {
            foreach ($users as $user)
            {
                $data['id'] = $user->id;

                if(!empty($user->pictures))
                {
                    if(substr(trim($user->pictures),-1) == ',')
                    {
                        $data['pictures'] = explode(',',$user->pictures);
                    }else
                    {
                        $data['pictures'] = $user->pictures;
                    }
                }
                else
                {
                    $data['pictures'] = [];
                }
            }

            return response()->json(['status' => 200,'data' => $data]);
        }else
        {
            return response()->json(['status' => 200,'data' => $data]);
        }

    }
}
