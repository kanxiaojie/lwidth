<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-3-24
 * Time: 下午12:11
 */

namespace App\Repositories;


use App\Comment;
use App\Post;
use App\User;

class CommentRepository
{
    protected $comment;
    protected $postRepository;

    public function __construct(
        Comment $comment,
        PostRepository $postRepository
    )
    {
        $this->comment = $comment;
        $this->postRepository = $postRepository;
    }

    public function getPostComments($post_id)
    {
        $comments = $this->comment->where('post_id',$post_id)->get();

        return $comments;
    }

    public function savePublishForPost($id, $inputs)
    {
        $comment = new Comment();
        $comment->user_id = $inputs['user_id'];
        $comment->post_id = $id;
        $comment->content = $inputs['content'];

        $comment->save();
    }

    public function publishForPost($inputs,$id)
    {
        $res = array('status' => '');

        $post = $this->postRepository->getPost($id);
        if ($post)
        {
            $res['status'] = 200;

            $this->savePublishForPost($id,$inputs);
        }else
        {
            $res['status'] = 201;

        }

        return $res;

    }
}