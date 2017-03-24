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

    public function getPostLists($inputs,$orderby = 'created_at', $direction = 'desc')
    {
        $posts = $this->post->where(function ($query) use ($inputs){
            if(isset($inputs['search']))
            {
                $query->where('title','LIKE','%'.$inputs['search'].'%')
                    ->orWhereHas('user',function ($query1) use ($inputs){
                        $query1->where('nickname','LIKE','%'.$inputs['search'].'%')
                            ->orWhere('realname','LIKE','%'.$inputs['search'].'%');
                    })
                ;
            }
        })->orderBy($orderby,$direction)->get();

        return $posts;
    }

    public function getPost($id)
    {
        $post = $this->post->where('id',$id)->first();
    }
}