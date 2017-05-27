<?php

namespace App\Http\Controllers;

use App\College;
use App\Comment;
use App\CommentToComment;
use App\Notice;
use App\Post;
use App\Praise;
use App\PraiseToComment;
use App\PraiseToReply;
use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\User;
use Illuminate\Http\Request;

class NoticeController extends Controller
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

    public function getUnreadNoticeNums(Request $request)
    {
        $wesecret = $request->get('wesecret');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if($user)
        {
            $postIds = Post::where('user_id',$user->id)->pluck('id')->toArray();
            $commentIds = Comment::whereIn('post_id',$postIds)->pluck('id')->toArray();
            $num1 = count(Notice::where('source_type',1)->whereIn('source_id',$commentIds)->get());

            $myCommentIds = Comment::where('user_id',$user->id)->pluck('id')->toArray();
            $replyIds = CommentToComment::whereIn('comment_id',$myCommentIds)->pluck('id')->toArray();
            $num2 = count(Notice::where('source_type',2)->whereIn('source_id',$replyIds)->get());

            $notices = $num1+$num2;

            return response()->json(['status'=>200,'message'=>'success','unreadNoticeNums'=>$notices]);
        }
        else
        {
            return response()->json(['status'=>201,'message' => 'User Does Not Exist.']);
        }
    }


    public function getNotices(Request $request)
    {
        $wesecret = $request->get('wesecret');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        $datas = [];
        $data = [];
        $commentOrReplyUserInfo = [];
        $objectUserInfo = [];
        $postOrCommentUserInfo = [];
        $source = [];

        if($user)
        {
            $postIds = Post::where('user_id',$user->id)->pluck('id')->toArray();
            $commentIds = Comment::whereIn('post_id',$postIds)->pluck('id')->toArray();

            $myCommentIds = Comment::where('user_id',$user->id)->pluck('id')->toArray();
            $replyIds = CommentToComment::whereIn('comment_id',$myCommentIds)->pluck('id')->toArray();

            $notices = Notice::where('source_type',1)->whereIn('source_id',$commentIds)
                ->orWhere('source_type',2)->whereIn('source_id',$replyIds)->paginate(10);

            if($notices)
            {
                foreach ($notices as $notice)
                {
                    $data['if_read'] = $notice->if_read;
                    $data['id'] = $notice->id;
                    $data['content'] = $notice->content;

                    $user = User::where('id',$notice->user_id)->first();
                    $commentOrReplyUserInfo['id'] =$notice->user_id;
                    $commentOrReplyUserInfo['nickName'] = $user->nickname;
                    $commentOrReplyUserInfo['avatar'] = $user->avatar;
                    $data['userInfo'] = $commentOrReplyUserInfo;

                    if($notice->source_type == 1)
                    {
                        $data['type'] = 'comment';
                        $comment = Comment::where('id',$notice->source_id)->first();
                        $post = Post::where('id',$comment->post_id)->first();
                        $source['love_id'] = $post->id;
                        $source['content'] = $post->content;
                        $postOrCommentUserInfo['id'] = $post->user_id;
                        $postOrCommentUserInfo['nickName'] = $post->user->nickname;
                        $postOrCommentUserInfo['avatarUrl'] = $post->user->avatar;

                    }
                    elseif ($notice->source_type == 2)
                    {
                        $data['type'] = 'reply';
                        $reply = CommentToComment::where('id',$notice->source_id)->first();
                        $objectUser = User::where('id',$reply->parent_id)->first();
                        $objectUserInfo['id'] = $objectUser->id;
                        $objectUserInfo['nickName'] = $objectUser->nickname;
                        $data['objectUserInfo'] = $objectUserInfo;

                        $source['comment_id'] = $reply->comment_id;
                        $comment = Comment::where('id',$reply->comment_id)->first();
                        $source['comment_id'] = $comment->id;
                        $source['content'] = $comment->content;
                        $postOrCommentUser = User::where('id',$comment->user_id)->first();
                        $postOrCommentUserInfo['id'] = $postOrCommentUser->id;
                        $postOrCommentUserInfo['nickName'] = $postOrCommentUser->nickname;
                        $postOrCommentUserInfo['avatarUrl'] = $postOrCommentUser->avatarUrl;
                        $source['userInfo'] = $postOrCommentUserInfo;
                        $data['source'] = $source;
                    }

                    $datas[] = $data;
                }
            }

            return response()->json(['status'=>200,'message'=>'success','data'=>$datas]);
        }
        else
        {
            return response()->json(['status'=>201,'message' => 'User Does Not Exist.']);
        }
    }

}
