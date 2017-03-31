<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Post;
use App\Praise;
use App\Repositories\BaseRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $postRepository;
    protected $baseRepository;
    protected $userRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PostRepository $postRepository,
        BaseRepository $baseRepository,
        UserRepository $userRepository
    )
    {
        $this->postRepository = $postRepository;
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
    }

    public function index(Request $request)
    {
        $inputs['search'] = $request->get('search');

        $posts = $this->postRepository->getPostLists($inputs);

        $datas = array();
        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
                $datas['id'] = $post->id;
                $datas['title'] = $post->title;
                $datas['content'] = $post->content;
                $datas['user_id'] = $post->user_id;
                $datas['likenum'] = $post->likenum;
                $datas['commentnum'] = $post->commentnum;
                if(!empty($post->pictures))
                {
                    $datas['pictures'] = $post->pictures;
                }
                else
                {
                    $datas['pictures'] = '';
                }
                $datas['created_at'] = $post->created_at;
                $datas['updated_at'] = $post->updated_at;
            }
        }

        return response()->json(['code' => 200,'data' => $datas]);
    }

    public function lists(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }
        else
        {
            $data = array();
            $userInfo = array();
            $images = array();
            $posts = $this->postRepository->getLovesOfOneUser($user->id);

            $userInfo['id'] = $user->id;
            $userInfo['nickName'] = $user->nickname;
            $userInfo['avatarUrl'] = $user->avatarUrl;
            if(!empty($user->college_id))
            {
                $userInfo['college'] = $user->college->name;
            }
            else
            {
                $userInfo['college'] = '';
            }

            if(empty($posts))
            {
                $data = [];
            }
            else
            {
                foreach ($posts as $post)
                {
                    $data['id'] = $post->id;
                    $data['content'] = $post->content;
                    if(!empty($post->pictures))
                    {
                        if(substr(trim($post->pictures),-1) == ',')
                        {
                            $data['images'] = explode(',',$post->pictures);
                        }else
                        {
                            $images['images'] = $post->pictures;
                        }

                        $data['images'] = $images;
                    }
                    else
                    {
                        $data['images'] = [];
                    }
                    $data['created_at'] = $post->created_at;
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

//                $data['location'] = '';
                }

            }

            return response()->json(['status' => 200,'userInfo'=>$userInfo,'data' => $data]);
        }
    }

}
