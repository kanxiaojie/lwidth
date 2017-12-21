<?php

namespace App\Http\Controllers;

use App\College;
use App\Comment;
use App\CommentToComment;
use App\Post;
use App\Praise;
use App\PraiseToComment;
use App\PraiseToReply;
use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\User;
use App\Notice;
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

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 回复
     */
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
            $data['id'] = $comment->id;

            $userInfo1['id'] = $comment->user_id;
            $userInfo1['nickname'] = $commentUser->nickname;
            $userInfo1['avatarUrl'] = $commentUser->avatarUrl;
            $data['userInfo'] = $userInfo1;

            $diff_time = $this->postRepository->getTime($comment->created_at);
            $data['created_at'] = $diff_time;

            $data['praise_nums'] = $comment->r_likenum;
            $data['reply_nums'] = $comment->r_commentnum;

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

            if(count($commentToComments) == 0)
            {
                $data['replies'] = [];
            }
            else
            {
                $userInfo2 = [];
                $data2 = [];
                $objectUserInfo = [];

                foreach ($commentToComments as $commentToComment)
                {
                    if($commentToComment->available)
                    {
                        $data2['content'] = $commentToComment->content;
                        $data2['id'] = $commentToComment->id;

                        $user1 = $this->userRepository->getUserById($commentToComment->user_id);
                        $userInfo2['id'] = $user1->id;
                        $userInfo2['nickname'] = $user1->nickname;
                        $userInfo2['avatarUrl'] = $user1->avatarUrl;
                        $data2['userInfo'] = $userInfo2;

                        $objectUserInfo['id'] = $commentToComment->parent_id;
                        $objectUser = $this->userRepository->getUserById($commentToComment->parent_id);
                        $objectUserInfo['nickname'] = $objectUser->nickname;
                        $data2['objectUserInfo'] = $objectUserInfo;
                        $data2['praise_nums'] = $commentToComment->praise_nums;

                        $if_my_praise = PraiseToReply::where('reply_id',$commentToComment->id)->where('user_id',$comment->user_id)->first();

                        if(count($if_my_praise))
                        {
                            $data2['if_my_praise'] = 1;
                        }else
                        {
                            $data2['if_my_praise'] = 0;
                        }

                        $diff_time = $this->postRepository->getTime($commentToComment->created_at);
                        $data2['created_at'] = $diff_time;

                        $data['replies'][] = $data2;
                    }

                }
            }

            return response()->json(['status' => 200,'data' => $data]);
        }
    }

    public function deleteComment(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $comment_id = $request->get('comment_id');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 200,'message' => 'User Does Not Exist!']);
        }else{
            $comment = $this->commentRepository->getCommentById($comment_id);

            if(count($comment))
            {
                if($comment->user_id == $user->id)
                {
                    if($comment->post->commentnum >= 1)
                    {
                        $comment->post->commentnum -= 1;
                        $comment->post->save();
                    }

                    $comment_notices = Notice::where(['source_type' => 1, 'source_id' => $comment_id])->get();
                    if(count($comment_notices)) {
                       foreach ($comment_notices as $comment_notice) {
                           $comment_notice->delete();
                       }
                   }

                    $replies = CommentToComment::where('comment_id',$comment_id)->get();
                    $replyIds = CommentToComment::where('comment_id',$comment_id)->pluck('id')->toArray();
                    $reply_notices = Notice::whereIn('source_type', [2, 3])->whereIn('source_id', $replyIds)->get();
                    if(count($replies))
                    {
                        foreach ($replies as $reply)
                        {
                            $reply->delete();
                        }
                    }
                    if(count($reply_notices)) {
                       foreach ($reply_notices as $reply_notice) {
                           $reply_notice->delete();
                       }
                   }

                    $comment->delete();
                    return response()->json(['status' => 200]);
                }else
                {
                    return response()->json(['status' => 201,'message' => 'You have no authorize to delete this love!']);

                }
            }
            else
            {
                return response()->json(['status' => 201,'message' => 'Comment Does Not Exist!']);

            }
        }
    }

    public function deleteReply(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $reply_id = $request->get('reply_id');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 200,'message' => 'User Does Not Exist!']);
        }else{
            $reply = $this->commentRepository->getCommentToCommentById($reply_id);

            if(count($reply))
            {
                if($reply->user_id == $user->id)
                {
                    $comment = Comment::where('id',$reply->comment_id)->first();
                    if($comment->r_commentnum > 0)
                    {
                        $comment->r_commentnum -= 1;
                        $comment->save();
                    }

                    $reply_notices = Notice::where('source_id', $reply_id)->whereIn('source_type', [2, 3])->get();
                    if(count($reply_notices)) {
                        foreach ($reply_notices as $reply_notice) {
                            $reply_notice->delete();
                        }
                    }

                    $reply->delete();

                    return response()->json(['status' => 200]);
                }else
                {
                    return response()->json(['status' => 201,'message' => 'You have no authorize to delete this love!']);
                }
            }
            else
            {
                return response()->json(['status' => 201,'message' => 'Reply Does Not Exist!']);

            }
        }
    }


    public function getMyCommentPosts(Request $request)
    {
        $search = $request->get('search');
        $wesecret = $request->get('wesecret');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 200,'message' => 'User Does Not Exist!']);
        }else
        {
            $postIds = Comment::where('user_id',$user->id)->pluck('post_id');

            $posts = Post::where(function ($query) use($search){
                if(!empty($search))
                {
                    $query->whereHas('user',function ($queryUser) use ($search){
                        $queryUser->where('realname','LIKE','%'.$search.'%')
                            ->orWhere('nickname','LIKE','%'.$search.'%');
                    })
                        ->orWhereHas('user.college',function ($queryCollege) use ($search){
                            $queryCollege->where('name','LIKE','%'.$search.'%');
                        })
                        ->orWhere('content','LIKE','%'.$search.'%');
                }
            })
            ->where('visiable',0)
            ->whereIn('id',$postIds)
            ->orderBy('created_at','desc')->paginate(15);

            $data = array();
            $datas = array();

            if(count($posts))
            {
                foreach ($posts as $post)
                {
                    $userInfo = array();
                    if($post->user->available)
                    {
                        $data['id'] = $post->id;
                        $data['content'] = $post->content;

                        if(!empty($post->pictures))
                        {
                            if(substr(trim($post->pictures),-1) == ',')
                            {
                                $data['images'] = explode(',',$post->pictures);
                            }else {
                                $data['images'] = explode(',',$post->pictures);
                            }
                        }
                        else
                        {
                            $data['images'] = [];
                        }

                        $user =User::where('id',$post->user_id)->first();
                        if($post->anonymous == 1)
                        {
                            // $anonymousUser = User::where('college_id',$post->user->college_id)->first();
                            $anonymousUser = User::where(['college_id' => $post->user->college_id, 'role' => 0])->first();
                            $userInfo['id'] = $anonymousUser->id;
                            $userInfo['nickname'] = $anonymousUser->nickname;
                            $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

                        }else
                        {
                            $userInfo['id'] = $post->user_id;
                            $userInfo['nickname'] = $user->nickname;
                            $userInfo['avatarUrl'] =  $user->avatarUrl;
                        }


                        if(!empty($user->college_id))
                        {
                            $userInfo['college'] = College::where('id',(int)($user->college_id))->first()->name;
                        }
                        else
                        {
                            $userInfo['college'] = '';
                        }
                        $data['userInfo'] = $userInfo;

                        $diff_time = $this->postRepository->getTime($post->created_at);

                        $data['created_at'] = $diff_time;

                        if($post->likenum)
                        {
                            $data['praise_nums'] = $post->likenum;
                        }
                        else
                        {
                            $data['praise_nums'] = 0;
                        }

                        if($post->commentnum)
                        {
                            $data['comment_nums'] = $post->commentnum;
                        }
                        else
                        {
                            $data['comment_nums'] = 0;
                        }

                        $if_my_comment = Comment::where('post_id',$post->id)->where('user_id',$user->id)->first();
                        if($if_my_comment)
                        {
                            $data['if_my_comment'] = 1;
                        }
                        else
                        {
                            $data['if_my_comment'] = 0;
                        }

                        $if_my_praise = Praise::where('post_id',$post->id)->where('user_id',$user->id)->first();
                        if($if_my_praise)
                        {
                            $data['if_my_praise'] = 1;
                        }
                        else
                        {
                            $data['if_my_praise'] = 0;
                        }

                        if($post->location)
                        {
                            $location = explode(',',$post->location);

                            $data['location']['name'] = $location[2];
                            $data['location']['address'] = $location[3];
                            $data['location']['longitude'] = $location[1];
                            $data['location']['latitude'] = $location[0];

                        }
                        else
                        {
                            $data['location'] = '';
                        }

                        $datas[] = $data;

                    }
                }
            }else
            {
                $datas = [];
            }

            return response()->json(['status' => 200,'data' => $datas]);
        }
    }

    public function getMyPraisePosts(Request $request)
    {
        $search = $request->get('search');
        $wesecret = $request->get('wesecret');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 200,'message' => 'User Does Not Exist!']);
        }else
        {
            $postIds = Praise::where('user_id',$user->id)->pluck('post_id');

            $posts = Post::where(function ($query) use($search){
                if(!empty($search))
                {
                    $query->whereHas('user',function ($queryUser) use ($search){
                        $queryUser->where('realname','LIKE','%'.$search.'%')
                            ->orWhere('nickname','LIKE','%'.$search.'%');
                    })
                        ->orWhereHas('user.college',function ($queryCollege) use ($search){
                            $queryCollege->where('name','LIKE','%'.$search.'%');
                        })
                        ->orWhere('content','LIKE','%'.$search.'%');
                }
            })
                ->where('visiable',0)
                ->whereIn('id',$postIds)
                ->orderBy('created_at','desc')->paginate(15);

            $data = array();
            $datas = array();

            if(count($posts))
            {
                foreach ($posts as $post)
                {
                    $userInfo = array();
                    if($post->user->available)
                    {
                        $data['id'] = $post->id;
                        $data['content'] = $post->content;

                        if(!empty($post->pictures))
                        {
                            if(substr(trim($post->pictures),-1) == ',')
                            {
                                $data['images'] = explode(',',$post->pictures);
                            }else {
                                $data['images'] = explode(',',$post->pictures);
                            }
                        }
                        else
                        {
                            $data['images'] = [];
                        }

                        $User =User::where('id',$post->user_id)->first();
                        if($post->anonymous == 1)
                        {
                            // $anonymousUser = User::where('college_id',$post->user->college_id)->first();
                            $anonymousUser = User::where(['college_id' => $post->user->college_id, 'role' => 0])->first();
                            $userInfo['id'] = $anonymousUser->id;
                            $userInfo['nickname'] = $anonymousUser->nickname;
                            $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

                        }else
                        {
                            $userInfo['id'] = $post->user_id;
                            $userInfo['nickname'] = $User->nickname;
                            $userInfo['avatarUrl'] =  $User->avatarUrl;
                        }


                        if(!empty($user->college_id))
                        {
                            $userInfo['college'] = College::where('id',(int)($user->college_id))->first()->name;
                        }
                        else
                        {
                            $userInfo['college'] = '';
                        }
                        $data['userInfo'] = $userInfo;

                        $diff_time = $this->postRepository->getTime($post->created_at);

                        $data['created_at'] = $diff_time;

                        if($post->likenum)
                        {
                            $data['praise_nums'] = $post->likenum;
                        }
                        else
                        {
                            $data['praise_nums'] = 0;
                        }

                        if($post->commentnum)
                        {
                            $data['comment_nums'] = $post->commentnum;
                        }
                        else
                        {
                            $data['comment_nums'] = 0;
                        }

                        $if_my_comment = Comment::where('post_id',$post->id)->where('user_id',$user->id)->first();
                        if($if_my_comment)
                        {
                            $data['if_my_comment'] = 1;
                        }
                        else
                        {
                            $data['if_my_comment'] = 0;
                        }

                        $if_my_praise = Praise::where('post_id',$post->id)->where('user_id',$user->id)->first();
                        if($if_my_praise)
                        {
                            $data['if_my_praise'] = 1;
                        }
                        else
                        {
                            $data['if_my_praise'] = 0;
                        }

                        if($post->location)
                        {
                            $location = explode(',',$post->location);

                            $data['location']['name'] = $location[2];
                            $data['location']['address'] = $location[3];
                            $data['location']['longitude'] = $location[1];
                            $data['location']['latitude'] = $location[0];

                        }
                        else
                        {
                            $data['location'] = '';
                        }

                        $datas[] = $data;

                    }
                }
            }else
            {
                $datas = [];
            }

            return response()->json(['status' => 200,'data' => $datas]);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 获取单条评论
     */
    public function getCommentInfo(Request $request, $id)
    {
        $wesecret = $request->get('wesecret');

        $data = array();
        $userInfo = array();
        $replys = [];
        $objectUserInfo = [];

        $comment = $this->commentRepository->getCommentById($id);
        if(count($comment))
        {
            $data['id'] = $comment->id;
            $data['content'] = $comment->content;

            $user = User::where('id',$comment->user_id)->first();
            $userInfo['id'] = $user->id;
            $userInfo['openid'] = $user->openid;
            $userInfo['nickname'] = $user->nickname;
            $userInfo['avatarUrl'] =  $user->avatarUrl;
            $userInfo['openid'] =  (isset($user->openid)&&(!empty($user->openid)))?$user->openid:"";
            $data['userInfo'] = $userInfo;

            $diff_time = $this->postRepository->getTime($comment->created_at);
            $data['created_at'] = $diff_time;

            // $data['reply_nums'] = $comment->r_commentnum;
            $data['reply_nums'] = CommentToComment::where(['comment_id' => $comment->id, 'available' => 1])->get()->count();
            $data['praise_nums'] = $comment->r_likenum;
            if(empty($wesecret))
            {
                $data['if_my_comment'] = 0;
                $data['if_my_praise'] = 0;
            }else
            {
                $openid = $this->baseRepository->decryptCode($wesecret);
                $wesecretUser = $this->userRepository->getUserByOpenId($openid);

                if($wesecretUser)
                {
                    $if_my_comment = CommentToComment::where('comment_id',$comment->id)
                        ->where('user_id',$wesecretUser->id)->first();
                    if($if_my_comment)
                    {
                        $data['if_my_comment'] = 1;
                    }
                    else
                    {
                        $data['if_my_comment'] = 0;
                    }

                    $if_my_praise = PraiseToComment::where('comment_id',$comment->id)
                        ->where('user_id',$wesecretUser->id)->first();
                    if($if_my_praise)
                    {
                        $data['if_my_praise'] = 1;
                    }
                    else
                    {
                        $data['if_my_praise'] = 0;
                    }
                }
                else
                {
                    $data['if_my_comment'] = 0;
                    $data['if_my_praise'] = 0;
                }
            }

            $replies = CommentToComment::where('comment_id',$comment->id)
                ->orderBy('created_at','desc')->limit(3)->get();
            if(count($replies))
            {
                foreach ($replies as $reply)
                {
                    $replys['id'] = $reply->id;
                    $replys['content'] = $reply->content;

                    $user1 = $this->userRepository->getUserById($reply->user_id);
                    $replyUserInfo['id'] = $user1->id;
                    $replyUserInfo['nickname'] = $user1->nickname;
                    $replyUserInfo['avatarUrl'] = $user1->avatarUrl;
                    $userInfo['openid'] =  (isset($user1->openid)&&(!empty($user1->openid)))?$user->openid:"";
                    $replys['userInfo'] = $replyUserInfo;

                    $objectUserInfo['id'] = $reply->parent_id;
                    $objectUser = $this->userRepository->getUserById($reply->parent_id);
                    $objectUserInfo['nickname'] = $objectUser->nickname;
                    $replys['objectUserInfo'] = $objectUserInfo;
                    $replys['praise_nums'] = $reply->praise_nums;
                    $replys['if_my_praise'] = 0;

                    $diff_time = $this->postRepository->getTime($reply->created_at);
                    $replys['created_at'] = $diff_time;
                    $data['replies'][] = $replys;
                }
            }else
            {
                $data['replies'] = [];
            }

            return response()->json(['status' => 200,'message' => 'success.','data' => $data]);
        }
        else
        {
            return response()->json(['status' => 201,'message' => 'The Comment Doest Not Exist.']);
        }

    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 获取某条评论的所有回复
     */
    public function getCommentReplyInfos(Request $request, $id)
    {
        $wesecret = $request->get('wesecret');

        $data = array();
        $datas = array();
        $userInfo = array();
        $objectUserInfo = array();

        $replies = CommentToComment::where(['comment_id' => $id, 'available' => 1])->orderBy('created_at','desc')->paginate(15);
        if($replies)
        {
            foreach ($replies as $reply)
            {
                $data['id'] = $reply->id;
                $data['content'] = $reply->content;

                $replyUser = User::where('id',$reply->user_id)->first();
                $userInfo['id'] = $replyUser->id;
                $userInfo['openid'] = $replyUser->openid;
                $userInfo['nickname'] = $replyUser->nickname;
                $userInfo['avatarUrl'] = $replyUser->avatarUrl;
                $userInfo['openid'] =  (isset($replyUser->openid)&&(!empty($replyUser->openid)))?$replyUser->openid:"";
                $data['userInfo'] = $userInfo;

                $objectUser = User::where('id',$reply->parent_id)->first();
                $objectUserInfo['id'] = $objectUser->id;
                $objectUserInfo['nickname'] = $objectUser->nickname;
                $data['objectUserInfo'] = $objectUserInfo;

                $data['praise_nums'] = $reply->praise_nums;
                if(empty($wesecret))
                {
                    $data['if_my_praise'] = 0;
                }elseif(!empty($wesecret))
                {
                    $openid = $this->baseRepository->decryptCode($wesecret);
                    $wesecretUser = $this->userRepository->getUserByOpenId($openid);
                    if($wesecretUser)
                    {
                        $praiseToReply = PraiseToReply::where('user_id',$wesecretUser->id)
                            ->where('reply_id',$reply->id)->first();
                        if($praiseToReply)
                        {
                            $data['if_my_praise'] = 1;
                        }else{
                            $data['if_my_praise'] = 0;
                        }
                    }
                    else
                    {
                        $data['if_my_praise'] = 0;
                    }
                }
                $diff_time = $this->postRepository->getTime($reply->created_at);
                $data['created_at'] = $diff_time;

                $datas[] = $data;

            }
        }

        return response()->json(['status' => 200,'message' => 'success','data' =>$datas]);
    }
}
