<?php

namespace App\Http\Controllers;

use App\CommentToComment;
use App\PraiseToComment;
use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $commentRepository;
    protected $baseRepository;
    protected $userRepository;
    protected $postRepository;

    public function __construct(
        CommentRepository $commentRepository,
        BaseRepository $baseRepository,
        UserRepository $userRepository,
        PostRepository $postRepository
    )
    {
        $this->commentRepository = $commentRepository;
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
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
            $comment = $this->commentRepository->getCommentById($id);
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

    public function getCommentToComments($id, Request $request)
    {
        $data = [];
        $userInfo1 = [];


        $comment = $this->commentRepository->getCommentById($id);
        if(!$comment)
        {
            return response()->json(['status' => 201,'message' => 'comment does not exist,check the argument!']);
        }
        else
        {
            $commentUser = $this->userRepository->getUserById($comment->user_id);
            $data['content'] = $comment->content;
            $data['comment_id'] = $comment->id;

            $userInfo1['id'] = $comment->user_id;
            $userInfo1['nickName'] = $commentUser->nickname;
            $userInfo1['avatarUrl'] = $commentUser->avatarUrl;
            $data['userInfo'] = $userInfo1;

            $diff_time = $this->postRepository->getTime($comment->created_at);
            $data['created_at'] = $diff_time;

            $data['praise_nums'] = $comment->r_likenum;
            $data['comment_nums'] = $comment->r_commentnum;

            $if_my_comment = CommentToComment::where('comment_id',$comment->id)->where('user_id',$comment->user_id)->first();
            if($if_my_comment)
            {
                $data['if_my_comment'] = 1;
            }
            else
            {
                $data['if_my_comment'] = 0;
            }

            $if_my_praise = PraiseToComment::where('comment_id',$comment->id)->where('user_id',$comment->user_id)->first();
            if($if_my_praise)
            {
                $data['if_my_praise'] = 1;
            }
            else
            {
                $data['if_my_praise'] = 0;
            }


            $commentToComments = $this->commentRepository->getCommentToComments($comment->id);
            if(empty($commentToComments))
            {
                $data['comment_comments'][] = [];
            }
            else
            {
                $userInfo2 = [];
                $data2 = [];
                $objectUserInfo = [];

                foreach ($commentToComments as $commentToComment)
                {
                    $data2['content'] = $commentToComment->content;
                    $data2['comment_id'] = $commentToComment->id;

                    $user1 = $this->userRepository->getUserById($commentToComment->user_id);
                    $userInfo2['id'] = $user1->id;
                    $userInfo2['nickName'] = $user1->nickname;
                    $userInfo2['avatarUrl'] = $user1->avatarUrl;
                    $data2['userInfo'] = $userInfo2;

                    $objectUserInfo['id'] = $commentToComment->parent_id;
                    $objectUser = $this->userRepository->getUserById($commentToComment->parent_id);
                    $objectUserInfo['nickName'] = $objectUser->nickname;
                    $data2['objectUserInfo'] = $objectUserInfo;

                    $diff_time = $this->postRepository->getTime($commentToComment->created_at);
                    $data2['created_at'] = $diff_time;

                    $data['comment_comments'][] = $data2;
                }
            }

            return response()->json(['status' => 200,'data' => $data]);
        }
    }
}
