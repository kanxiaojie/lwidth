<?php

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PraiseRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class PraiseController extends Controller
{
    protected $praiseRepository;
    protected $baseRepository;
    protected $userRepository;

    public function __construct(
        PraiseRepository $praiseRepository,
        BaseRepository $baseRepository,
        UserRepository $userRepository
    )
    {
        $this->praiseRepository = $praiseRepository;
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
    }

    public function praiseToPost(Request $request, $id)
    {
        $inputs = [];
        $inputs['wesecret'] = $request->get('wesecret');
        $inputs['praise'] = $request->get('praise');

        $openid = $this->baseRepository->decryptCode($inputs['wesecret']);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }
        else
        {
            $inputs['user_id'] = $user->id;
            $res = $this->praiseRepository->praiseToPost($inputs,$id);

            if ($res['status'] == 200)
            {
                return response()->json(['status' => 200]);
            }else
            {
                return response()->json(['status' => 201,'message' => 'Praise failed,please check the argument!']);
            }

        }

    }


}
