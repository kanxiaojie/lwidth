<?php

namespace App\Http\Controllers;

use App\PraiseUser;
use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PraiseRepository;
use App\Repositories\UserRepository;
use App\User;
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

    public function praiseToComment(Request $request, $id)
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
            $res = $this->praiseRepository->praiseToComment($inputs,$id);

            if ($res['status'] == 200)
            {
                return response()->json(['status' => 200]);
            }else
            {
                return response()->json(['status' => 201,'message' => 'Praise failed,please check the argument!']);
            }

        }
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

    public function praiseToReplies(Request $request, $id)
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
            $res = $this->praiseRepository->praiseToReply($inputs,$id);

            if ($res['status'] == 200)
            {
                return response()->json(['status' => 200]);
            }else
            {
                return response()->json(['status' => 201,'message' => 'Praise failed,please check the argument!']);
            }

        }
    }

    public function praiseToUser(Request $request,$id)
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
            $praisedUser = User::where('id',$id)->first();
            if($praisedUser)
            {
                if($inputs['praise'] == 1)
                {
                    $praisedToUser = PraiseUser::where('praise_user_id',$user->id)
                        ->where('praised_user_id',$praisedUser->id)->first();
                    if(!$praisedToUser)
                    {
                        $praiseToUser = new PraiseUser();
                        $praiseToUser->praise_user_id = $user->id;
                        $praiseToUser->praised_user_id = $praisedUser->id;
                        $praiseToUser->save();
                    }
                }else
                {
                    $praisedToUsers = PraiseUser::where('praise_user_id',$user->id)
                        ->where('praised_user_id',$praisedUser->id)->get();
                    if($praisedToUsers)
                    {
                        foreach ($praisedToUsers as $praisedToUser)
                        {
                            $praisedToUser->delete();
                        }
                    }
                }

                return response()->json(['status' =>200,'message' => 'success']);
            }
            else
            {
                return response()->json(['status' => 201,'message' => 'The user you praise does not exist.']);
            }

        }
    }


}
