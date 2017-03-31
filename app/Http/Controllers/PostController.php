<?php

namespace App\Http\Controllers;

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
                $data['id'] = '';
                $data['content'] = '';
                $data['images'] = '';
//                $data['location'] = '';
            }

            return response()->json(['status' => 200,'data' => $data]);
        }
    }

}
