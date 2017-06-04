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
use App\Notice;
use App\Post;
use App\User;

class CommentRepository
{
    protected $comment;
    protected $postRepository;
    protected $commentToComment;

    public function __construct(
        Comment $comment,
        PostRepository $postRepository,
        CommentToComment $commentToComment
    )
    {
        $this->comment = $comment;
        $this->postRepository = $postRepository;
        $this->commentToComment = $commentToComment;
    }

    public function getPostComments($post_id)
    {
        $comments = $this->comment->where('post_id',$post_id)->orderBy('created_at','desc')->paginate(5);

        return $comments;
    }

    public function getCommentToComments($comment_id)
    {
        $commentToComments = $this->commentToComment->where('comment_id',$comment_id)->orderBy('created_at','desc')->get();

        return $commentToComments;
    }

    public function getCommentById($id)
    {
        $comment = $this->comment->where('id',$id)->first();
        return $comment;
    }

    public function getCommentToCommentById($id)
    {
        $commentToComment = $this->commentToComment->where('id',$id)->first();

        return $commentToComment;
    }

    public function saveNotice($source_type,$source_id,$user_id,$content)
    {
        $notice = new Notice();
        $notice->source_type=$source_type;
        $notice->source_id=$source_id;
        $notice->user_id=$user_id;
        $notice->content=$content;
        $notice->save();
    }

    public function savePublishForPost($id, $inputs)
    {
        $comment = new Comment();
        $comment->user_id = $inputs['user_id'];
        $comment->post_id = $id;
        $comment->content = $inputs['content'];

        $comment->save();

        $this->saveNotice(1,$comment->id,$inputs['user_id'],$inputs['content']);
    }

    public function saveCommentToComment($inputs, $comment)
    {
        $res = array('status' => '');

        $commentToComment = new CommentToComment();
        $commentToComment->user_id = $inputs['user_id'];
        $commentToComment->post_id = $comment->post_id;
        $commentToComment->comment_id = $comment->id;
        $commentToComment->parent_id = $inputs['objectUser_id'];
        $commentToComment->content = $inputs['content'];
        $commentToComment->save();

        $this->saveNotice(2,$commentToComment->id,$inputs['user_id'],$inputs['content']);

        $comment = Comment::where('id',$comment->id)->first();
        $comment->r_commentnum += 1;
        $comment->save();

        $res['status'] = 200;

        return $res;
    }

    public function publishForPost($inputs,$id)
    {
        $res = array('status' => '');

        $post = $this->postRepository->getPost($id);
        if ($post)
        {
            $res['status'] = 200;

            $post->commentnum += 1;
            $post->save();

            $this->savePublishForPost($id,$inputs);
        }else
        {
            $res['status'] = 201;

        }

        return $res;

    }
}