<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-3-24
 * Time: 下午12:11
 */

namespace App\Repositories;



use App\Comment;
use App\CommentToComment;
use App\Praise;
use App\PraiseToComment;
use App\PraiseToReply;

class PraiseRepository
{

    protected $praise;
    protected $postRepository;

    public function __construct(
        Praise $praise,
        PostRepository $postRepository
    )
    {
        $this->praise = $praise;
        $this->postRepository = $postRepository;
    }

    public function getPraiseByCommentId($inputs, $comment_id)
    {
        $code = array('code'=>'');

        $praise = PraiseToComment::where('comment_id',$comment_id)->where('user_id',$inputs['user_id'])->first();

        if((!$praise))
        {
            $praise = new PraiseToComment();
            $praise->user_id = $inputs['user_id'];
            $praise->comment_id = $comment_id;
            $praise->save();

            $code['code'] = 200;

            return $code;
        }else
        {
            $praise->delete();
            $code['code'] = 202;

            return $code;
        }
    }

    public function getPraiseByPostId($inputs, $post_id)
    {
        $code = array('code'=>'');

        $praise = $this->praise->where('post_id',$post_id)->where('user_id',$inputs['user_id'])->first();

        if((!$praise))
        {
            $praise = new Praise();
            $praise->user_id = $inputs['user_id'];
            $praise->post_id = $post_id;
            $praise->like = 1;
            $praise->save();

            $code['code'] = 200;

            return $code;
        }else
        {
            $praise->delete();
            $code['code'] = 202;

            return $code;
        }
    }

    public function praiseToComment($inputs, $comment_id)
    {
        $res = array('status' => '');

        $comment = Comment::where('id',$comment_id)->first();
        if ($comment)
        {
            $code = $this->getPraiseByCommentId($inputs,$comment_id);

            if ($code['code'] == 200)
            {
                $comment->r_likenum += 1;
            }elseif($code['code'] == 202)
            {
                $comment->r_likenum -= 1;
            }

            $comment->save();

            $res['status'] = 200;
        }
        else
        {
            $res['status'] = 201;
        }

        return $res;
    }

    public function praiseToPost($inputs, $post_id)
    {
        $res = array('status' => '');

        $post = $this->postRepository->getPost($post_id);
        if ($post)
        {
            $code = $this->getPraiseByPostId($inputs,$post_id);

            if ($code['code'] == 200)
            {
                $post->likenum += 1;
            }elseif($code['code'] == 202)
            {
                $post->likenum -= 1;
            }

            $post->save();

            $res['status'] = 200;
        }
        else
        {
            $res['status'] = 201;
        }

        return $res;
    }

    public function getReplyById($inputs, $reply_id)
    {

        $code = array('code'=>'');

        $praise = PraiseToReply::where('reply_id',$reply_id)->where('user_id',$inputs['user_id'])->first();

        if((!$praise))
        {
            $praise = new PraiseToReply();
            $praise->user_id = $inputs['user_id'];
            $praise->reply_id = $reply_id;
            $praise->save();

            $code['code'] = 200;

            return $code;
        }else
        {
            $praise->delete();
            $code['code'] = 202;

            return $code;
        }

    }

    public function praiseToReply($inputs, $reply_id)
    {
        $res = array('status' => '');

        $reply = CommentToComment::where('id',$reply_id)->first();
        if ($reply)
        {
            $code = $this->getReplyById($inputs,$reply_id);

            if ($code['code'] == 200)
            {
                $reply->praise_nums += 1;
            }elseif($code['code'] == 202)
            {
                $reply->praise_nums -= 1;
            }

            $reply->save();

            $res['status'] = 200;
        }
        else
        {
            $res['status'] = 201;
        }

        return $res;
    }

}