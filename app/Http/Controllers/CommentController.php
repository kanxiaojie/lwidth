<?php

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $commentRepository;
    protected $baseRepository;
    protected $userRepository;

    public function __construct(
        CommentRepository $commentRepository,
        BaseRepository $baseRepository,
        UserRepository $userRepository
    )
    {
        $this->commentRepository = $commentRepository;
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
    }

    public function publishComments(Request $request,$id)
    {
        $wesecret = $request->get('wesecret');
        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        $inputs['content'] = $request->get('content');


        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }else {
            $inputs['user_id'] = $user->id;
            $res = $this->commentRepository->publishForPost($inputs,$id);

            if (($res['status']) && ($res['status'] == 200)) {
                return response()->json(['status' => 200]);
            } else {
                return response()->json(['status' => 201, 'message' => 'Publish failed,please check the argument']);
            }
        }
    }

    public function commentToComment(Request $request, $id)
    {
        $inputs = [];
        $wesecret = $request->get('wesecret');
        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        $inputs['content'] = $request->get('content');
        $inputs['objectUser_id'] = $request->get('objectUser_id');

        if($user)
        {
            $comment = $this->commentRepository->getCommentToCommentById($id);
            if(!$comment)
            {
                return response()->json(['status' => 201,'message' => 'comment does not exist,check the argument!']);
            }else
            {
                $inputs['user_id'] = $user->id;
                $res = $this->commentRepository->saveCommentToComment($inputs,$comment);
                if ($res['status'] == 200)
                {
                    return response()->json(['status' => 200]);
                }else
                {
                    return response()->json(['status' => 201,'message' => 'Save failed,please check the argument!']);
                }
            }
        }else
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }


    }
}
