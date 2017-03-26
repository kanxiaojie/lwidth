<?php

namespace App\Http\Controllers;

use App\Repositories\PostRepository;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $postRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PostRepository $postRepository
    )
    {
        $this->postRepository = $postRepository;
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

}
