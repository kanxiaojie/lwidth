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

    public function getLovesOfOneUser($user_id,$orderby = 'created_at', $direction = 'desc')
    {
        $posts = $this->post->where('user_id',$user_id)->orderBy($orderby,$direction)->get();

        return $posts;
    }

    public function getPost($id)
    {
        $post = $this->post->where('id',$id)->first();
        return $post;
    }

    public function savePost($inputs,$picturePath = null)
    {
        $post = new Post();

        $post = $this->save($post,$inputs,$picturePath);

        return $post;
    }

    public function save($post,$inputs,$picturePath = null)
    {
        if (isset($inputs['user_id']) && !empty($inputs['user_id']))
        {
            $post->user_id = $inputs['user_id'];
        }

        if(isset($inputs['content']) && !empty($inputs['content']))
        {
            $post->content = $inputs['content'];
        }

        if ($picturePath)
        {
            if($post->pictures)
            {
                $post->pictures .= ','.$picturePath;
            }
            else
            {
                $post->pictures = $picturePath;
            }
        }

        if(isset($inputs['location']) && !empty($inputs['location']))
        {
            $post->visiable = implode(',',$inputs['location']);
        }

        if(isset($inputs['visiable']) && !empty($inputs['visiable']))
        {
            $post->visiable = $inputs['visiable'];
        }

        $post->save();

        return $post;
    }

    public function getTime($created_at)
    {
        $time = strtotime($created_at);
        $date=floor(((time())-$time)/86400);
        $hour=floor(((time())-$time)%86400/3600);
        $minute=floor((time()-$time)%86400/60);
        $second=floor((time()-$time)%86400%60);

        $diff = $date*24*60 + $hour*60 + $minute + $second/60;

        if (($diff>0) && ($diff<60))
        {

            return $diff.'分钟前';
        }elseif (($diff > 60) && ($diff < 1440))
        {
            return round($diff/60).'小时前';
        }elseif (($diff >= 1440) && ($diff < 2880))
        {
            return '昨天'.date('H:i',$time);
        }elseif (($diff >= 2880) && ($diff < 4320))
        {
            return '前天'.date('H:i',$time);
        }elseif($diff > 4320)
        {
            return date('m-d H:i',$time);
        }
    }
}