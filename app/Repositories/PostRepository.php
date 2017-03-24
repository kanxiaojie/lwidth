<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-3-24
 * Time: 下午12:11
 */

namespace App\Repositories;


use App\Post;
use App\User;

class PostRepository
{
    protected $post;

    public function __construct(
        Post $post
    )
    {
        $this->post = $post;
    }

    public function getPost($id)
    {
        $post = $this->post->where('id',$id)->first();
    }
}