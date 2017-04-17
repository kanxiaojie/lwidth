<?php
/**
 * Created by PhpStorm.
 * User: yang
 * Date: 17-3-24
 * Time: 下午12:11
 */

namespace App\Repositories;



use App\Praise;

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

}