<?php

namespace App\Http\Controllers;

use App\Comment;
use App\CommentToComment;
use App\Post;
use App\Praise;
use App\PraiseToComment;
use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PostController extends Controller
{
    protected $postRepository;
    protected $baseRepository;
    protected $userRepository;
    protected $commentRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PostRepository $postRepository,
        BaseRepository $baseRepository,
        UserRepository $userRepository,
        CommentRepository $commentRepository
    )
    {
        $this->postRepository = $postRepository;
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
        $this->commentRepository = $commentRepository;
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

        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        }

        $inputs = [];

        if(empty($wesecret))
        {
            $data = array();
            $datas = array();

            $posts = $this->postRepository->getPostListZero();

            if(empty($posts))
            {
                $datas = [];
            }
            else
            {
                foreach ($posts as $post)
                {
                    $userInfo = array();

                    $data['id'] = $post->id;
                    $data['content'] = $post->content;

                    if(!empty($post->pictures))
                    {
                        if(substr(trim($post->pictures),-1) == ',')
                        {
                            $data['images'] = explode(',',$post->pictures);dd($data['images']);
                        }else {
                            $data['images'] = explode(',',$post->pictures);
                        }
                    }
                    else
                    {
                        $data['images'] = [];
                    }

                    $userInfo['id'] = $post->user_id;
                    $user =User::where('id',$post->user_id)->first();
                    $userInfo['nickName'] = $user->nickname;
                    $userInfo['avatarUrl'] =  $user->avatarUrl;
                    if(!empty($user->college_id))
                    {
                        $userInfo['college'] = $user->college_id;
                    }
                    else
                    {
                        $userInfo['college'] = '';
                    }
                    $data['userInfo'] = $userInfo;

                    $diff_time = $this->postRepository->getTime($post->created_at);

                    $data['created_at'] = $diff_time;

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

                    if($post->location)
                    {
                        $data['location'] = explode(',',$post->location);
                    }
                    else
                    {
                        $data['location'] = '';
                    }

                    $datas[] = $data;
                }

            }

            return response()->json(['status' => 200,'data' => $datas]);

        }
        elseif((!empty($wesecret)) && ($user))
        {
            $data = array();
            $datas = [];
            $posts = $this->postRepository->getPostLists($inputs,$user);

            if(empty($posts))
            {
                $datas = [];
            }
            else
            {
                foreach ($posts as $post)
                {
                    $userInfo = array();
                    $images = array();

                    $data['id'] = $post->id;
                    $data['content'] = $post->content;
                    if(!empty($post->pictures))
                    {
                        if(substr(trim($post->pictures),-1) == ',')
                        {
                            $data['images'] = explode(',',$post->pictures);
                        }else
                        {
                            $data['images'] = $post->pictures;
                        }

//                        $data['images'] = $images;
                    }
                    else
                    {
                        $data['images'] = [];
                    }

                    $userInfo['id'] = $user->id;
                    $userInfo['nickName'] = $user->nickname;
                    $userInfo['avatarUrl'] = $user->avatarUrl;
                    if(!empty($user->college_id))
                    {
                        $userInfo['college'] = $user->college_id;
                    }
                    else
                    {
                        $userInfo['college'] = '';
                    }
                    $data['userInfo'] = $userInfo;

                    $diff_time = $this->postRepository->getTime($post->created_at);

                    $data['created_at'] = $diff_time;

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

                    if($post->location)
                    {
                        $data['location'] = explode(',',$post->location);
                    }
                    else
                    {
                        $data['location'] = '';
                    }

                    $datas[] = $data;
                }

            }

            return response()->json(['status' => 200,'data' => $datas]);
        }
    }

    public function postAndSelfComments(Request $request,$id)
    {
        $wesecret = $request->get('wesecret');
        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }else
        {
            $post = $this->postRepository->getPost($id);
            $data = array();
            $datas =[];
            $postComments = array();
            $post_Comments = $this->commentRepository->getPostComments($post->id);
            if($post)
            {
                $userInfo = array();
                $images = array();
                $commentOfUserInfo = array();

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

                if($post->location)
                {
                    $data['location'] = explode(',',$post->location);
                }
                else
                {
                    $data['location'] = '';
                }

                $userInfo['id'] = $user->id;
                $userInfo['nickName'] = $user->nickname;
                $userInfo['avatarUrl'] = $user->avatarUrl;
                if(!empty($user->college_id))
                {
                    $userInfo['college'] = $user->college_id;
                }
                else
                {
                    $userInfo['college'] = '';
                }
                $data['userInfo'] = $userInfo;

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

                $datas['love'] = $data;


                if ($post_Comments)
                {
                    foreach ($post_Comments as $postComment)
                    {
                        $postComments['id'] = $postComment->id;
                        $postComments['content'] = $postComment->content;

                        $userOfComment = User::where('id',$postComment->user_id)->first();
                        $commentOfUserInfo['id'] = $userOfComment->id;
                        $commentOfUserInfo['nickName'] = $userOfComment->nickname;
                        $commentOfUserInfo['avatarUrl'] = $userOfComment->avatarUrl;
                        $postComments['userInfo'] = $commentOfUserInfo;

                        $postComments['created_at'] = $this->postRepository->getTime($postComment->created_at);

                        if($postComment->r_likenum)
                        {
                            $postComments['praise_nums'] = $postComment->r_likenum;
                        }
                        else
                        {
                            $postComments['praise_nums'] = 0;
                        }

                        if($postComment->r_commentnum)
                        {
                            $postComments['comment_nums'] = $postComment->r_commentnum;
                        }
                        else
                        {
                            $postComments['comment_nums'] = 0;
                        }


                        $if_my_comment = CommentToComment::where('comment_id',$postComment->id)->where('user_id',$user->id)->first();
                        if($if_my_comment)
                        {
                            $postComments['if_my_comment'] = 1;
                        }
                        else
                        {
                            $postComments['if_my_comment'] = 0;
                        }

                        $if_my_praise = PraiseToComment::where('comment_id',$postComment->id)->where('user_id',$user->id)->first();
                        if($if_my_praise)
                        {
                            $postComments['if_my_praise'] = 1;
                        }
                        else
                        {
                            $postComments['if_my_praise'] = 0;
                        }

                        //在评论循环操作$data1,2,3,4,5,6,7
                        $datas['comments'] = $postComments;
                    }
                }
                else
                {
                    $datas['comments'] = [];
                }

                return response()->json(['status' => 200,'data' => $datas]);

            }
            else
            {
                return response()->json(['status' => 201,'message' => 'post does not exist!']);
            }
        }
    }

    public function getHotPosts(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }else
        {
            $res = $this->postRepository->getHotPost();

            if(isset($res['postId']))
            {
                $post = $this->postRepository->getPost($res['postId']);

                $data = array();
                $datas =[];
                $postComments = array();
                $post_Comments = $this->commentRepository->getPostComments($post->id);
                if($post)
                {
                    $userInfo = array();
                    $images = array();
                    $commentOfUserInfo = array();

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
                    $data['userInfo'] = $userInfo;

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

                    $datas['love'] = $data;


                    if ($post_Comments)
                    {
                        foreach ($post_Comments as $postComment)
                        {
                            $postComments['id'] = $postComment->id;
                            $postComments['content'] = $postComment->content;

                            $userOfComment = User::where('id',$postComment->user_id)->first();
                            $commentOfUserInfo['id'] = $userOfComment->id;
                            $commentOfUserInfo['nickName'] = $userOfComment->nickname;
                            $commentOfUserInfo['avatarUrl'] = $userOfComment->avatarUrl;
                            $postComments['userInfo'] = $commentOfUserInfo;

                            $postComments['created_at'] = $this->postRepository->getTime($postComment->created_at);

                            if($postComment->r_likenum)
                            {
                                $postComments['praise_nums'] = $postComment->r_likenum;
                            }
                            else
                            {
                                $postComments['praise_nums'] = 0;
                            }

                            if($postComment->r_commentnum)
                            {
                                $postComments['comment_nums'] = $postComment->r_commentnum;
                            }
                            else
                            {
                                $postComments['comment_nums'] = 0;
                            }


                            $if_my_comment = CommentToComment::where('comment_id',$postComment->id)->where('user_id',$user->id)->first();
                            if($if_my_comment)
                            {
                                $postComments['if_my_comment'] = 1;
                            }
                            else
                            {
                                $postComments['if_my_comment'] = 0;
                            }

                            $if_my_praise = PraiseToComment::where('comment_id',$postComment->id)->where('user_id',$user->id)->first();
                            if($if_my_praise)
                            {
                                $postComments['if_my_praise'] = 1;
                            }
                            else
                            {
                                $postComments['if_my_praise'] = 0;
                            }

                            //在评论循环操作$data1,2,3,4,5,6,7
                            $datas['comments'] = $postComments;
                        }
                    }
                    else
                    {
                        $datas['comments'] = [];
                    }

                    return response()->json(['status' => 200,'data' => $datas]);

                }
                else
                {
                    return response()->json(['status' => 201,'message' => 'post does not exist!']);
                }
            }

        }
    }

    public function publishPost(Request $request)
    {
        $inputs = $request->all();

        $openid = Crypt::decrypt($inputs['wesecret']);

        $user = $this->userRepository->getUserByOpenId($openid);
        if ($user)
        {
            $inputs['user_id'] = $user->id;

            $post = $this->postRepository->savePost($inputs);

            if($post)
            {
                return response()->json(['status' => 200,'love_id'=>$post->id]);
            }
            else
            {
                return response()->json(['status' => 200,'message' => 'Public love failed,please check the arguments!']);
            }


        }else
        {
            return response()->json(['status' => 200,'message' => 'User does not exist']);
        }
    }

    public function uploadPostImages(Request $request)
    {
        $inputs = $request->all();

        $openid = Crypt::decrypt($inputs['wesecret']);

        $user = $this->userRepository->getUserByOpenId($openid);
        if ($user)
        {
            $inputs['user_id'] = $user->id;

            $res = $this->baseRepository->uploadToQiniu($inputs);

            if($res['status'] == 201)
            {
                return response()->json(['status' => 201,'message' => 'pictures upload failed']);
            }else
            {
                $post = $this->postRepository->updatePost($inputs,$inputs['post_id'],$res['picturePath']);

                if($post)
                {
                    return response()->json(['status' => 200]);
                }
                else
                {
                    return response()->json(['status' => 201,'message' => 'upload failed']);
                }
            }


        }
        else
        {
            return response()->json(['status' => 200,'message' => 'User does not exist']);
        }
    }


}
