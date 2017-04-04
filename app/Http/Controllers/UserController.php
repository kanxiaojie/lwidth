<?php

namespace App\Http\Controllers;

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
