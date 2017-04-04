<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class UserController extends Controller
{
    protected $userRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
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
