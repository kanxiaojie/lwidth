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

    public function __construct(
        Comment $comment
    )
    {
        $this->comment = $comment;
    }

    public function getPostComments($post_id)
    {
        $comments = $this->comment->where('post_id',$post_id)->get();

        return $comments;
    }
}