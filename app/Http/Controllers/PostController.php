<?php

namespace App\Http\Controllers;

use App\College;
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

    public function getMyLoves(Request $request)
    {
        $wesecret = $request->get('wesecret');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if($user)
        {
            $posts = $this->postRepository->getMyLoves($user);
            $data = array();
            $datas = array();

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
                            $data['images'] = explode(',',$post->pictures);
                        }else
                        {
                            $data['images'] = explode(',',$post->pictures);
                        }

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
                        $userInfo['college'] = College::where('id',(int)($user->college_id))->first()->name;
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
                        $location = explode(',',$post->location);

                        $data['location']['name'] = $location[2];
                        $data['location']['address'] = $location[3];
                        $data['location']['longitude'] = $location[1];
                        $data['location']['latitude'] = $location[0];

                    }
                    else
                    {
                        $data['location'] = '';
                    }

                    $data['read_nums'] = 10;

                    $datas[] = $data;
                }

            }

            return response()->json(['status' => 200,'data' => $datas]);

        }
        else
        {
            return response()->json(['status'=>201,'message' => 'User does not exist,please check the argument!']);
        }
    }

    public function getCollegeLoves(Request $request)
    {
        $wesecret = $request->get('wesecret');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if($user)
        {
            $posts = $this->postRepository->getCollegeLoves($user);
            $data = array();
            $datas = array();

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
                            $data['images'] = explode(',',$post->pictures);
                        }else
                        {
                            $data['images'] = explode(',',$post->pictures);
                        }

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
                        $userInfo['college'] = College::where('id',(int)($user->college_id))->first()->name;
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
                        $location = explode(',',$post->location);

                        $data['location']['name'] = $location[2];
                        $data['location']['address'] = $location[3];
                        $data['location']['longitude'] = $location[1];
                        $data['location']['latitude'] = $location[0];

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
        else
        {
            return response()->json(['status'=>201,'message' => 'User does not exist,please check the argument!']);
        }
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
                            $data['images'] = explode(',',$post->pictures);
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
                        $userInfo['college'] = College::where('id',(int)($user->college_id))->first()->name;
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
                        $location = explode(',',$post->location);

                        $data['location']['name'] = $location[2];
                        $data['location']['address'] = $location[3];
                        $data['location']['longitude'] = $location[1];
                        $data['location']['latitude'] = $location[0];

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

                    $data['id'] = $post->id;
                    $data['content'] = $post->content;
                    if(!empty($post->pictures))
                    {
                        if(substr(trim($post->pictures),-1) == ',')
                        {
                            $data['images'] = explode(',',$post->pictures);
                        }else
                        {
                            $data['images'] = explode(',',$post->pictures);
                        }

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
                        $userInfo['college'] = College::where('id',(int)($user->college_id))->first()->name;
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
                        $location = explode(',',$post->location);

                        $data['location']['name'] = $location[2];
                        $data['location']['address'] = $location[3];
                        $data['location']['longitude'] = $location[1];
                        $data['location']['latitude'] = $location[0];
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

        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        }
        $dataComment = [];

        if(empty($wesecret))
        {
            $post = $this->postRepository->getPost($id);
            $data = array();
            $datas =[];

            $postComments = array();
            $post_Comments = $this->commentRepository->getPostComments($post->id);
            if($post)
            {
                $userInfo = array();

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
                        $data['images'] = explode(',',$post->pictures);
                    }

                }
                else
                {
                    $data['images'] = [];
                }

                if($post->location)
                {
                    $location = explode(',',$post->location);

                    $data['location']['name'] = $location[2];
                    $data['location']['address'] = $location[3];
                    $data['location']['longitude'] = $location[1];
                    $data['location']['latitude'] = $location[0];

                }
                else
                {
                    $data['location'] = '';
                }

                $userInfo['id'] = $post->user->id;
                $userInfo['nickName'] = $post->user->nickname;
                $userInfo['avatarUrl'] = $post->user->avatarUrl;
                if(!empty($post->user->college_id))
                {
                    $userInfo['college'] = College::where('id',(int)($post->user->college_id))->first()->name;
                }
                else
                {
                    $userInfo['college'] = '';
                }
                $data['userInfo'] = $userInfo;

                $data['created_at'] = $this->postRepository->getTime($post->created_at);

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


                $data['if_my_comment'] = 0;



                $data['if_my_praise'] = 0;



                $data['if_my_love'] = 0;


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


                        $postComments['if_my_comment'] = 0;



                        $postComments['if_my_praise'] = 0;


                        //在评论循环操作$data1,2,3,4,5,6,7
                        $dataComment[] = $postComments;
                    }
                    $datas['comments'] = $dataComment;
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

        }elseif((!empty($wesecret)) && ($user))
        {
            $post = $this->postRepository->getPost($id);
            $data = array();
            $datas =[];
            $postComments = array();
            $post_Comments = $this->commentRepository->getPostComments($post->id);
            if($post)
            {
                $userInfo = array();

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
                        $data['images'] = explode(',',$post->pictures);
                    }

                }
                else
                {
                    $data['images'] = [];
                }

                if($post->location)
                {
                    $location = explode(',',$post->location);

                    $data['location']['name'] = $location[2];
                    $data['location']['address'] = $location[3];
                    $data['location']['longitude'] = $location[1];
                    $data['location']['latitude'] = $location[0];
                }
                else
                {
                    $data['location'] = '';
                }

                $userInfo['id'] = $post->user->id;
                $userInfo['nickName'] = $post->user->nickname;
                $userInfo['avatarUrl'] = $post->user->avatarUrl;
                if(!empty($post->user->college_id))
                {
                    $userInfo['college'] = College::where('id',(int)($post->user->college_id))->first()->name;
                }
                else
                {
                    $userInfo['college'] = '';
                }
                $data['userInfo'] = $userInfo;

                $data['created_at'] = $this->postRepository->getTime($post->created_at);

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

                if($user->id == $post->user_id)
                {
                    $data['if_my_love'] = 1;
                }else
                {
                    $data['if_my_love'] = 0;
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
                        $dataComment[] = $postComments;
                    }
                    $datas['comments'] = $dataComment;
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
                    if(!empty($post->user->college_id))
                    {
                        $userInfo['college'] = College::where('id',(int)($post->user->college_id))->first()->name;
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

    public function getLocationLoves(Request $request)
    {
        $wesecret = $request->get('wesecret');

        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        }

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

                    $data['id'] = $post->id;
                    $data['iconPath'] = $post->user->avatarUrl;
                    if($post->location)
                    {
                        $location = explode(',',$post->location);
                        $data['location']['longitude'] = $location[1];
                        $data['location']['latitude'] = $location[0];
                    }
                    else
                    {
                        $data['longitude'] = '';
                        $data['latitude'] = '';
                    }

                    $data['width'] = 50;
                    $data['height'] = 70;

                    $datas[] = $data;
                }

            }

            return response()->json(['status' => 200,'data' => $datas]);
        }elseif ((!empty($wesecret)) && ($user))
        {
            $data = array();
            $datas = array();

            $posts = $this->postRepository->getLocationLoves();

            if(empty($posts))
            {
                $datas = [];
            }
            else
            {
                foreach ($posts as $post)
                {

                    $data['id'] = $post->id;
//                    $data['iconPath'] = $post->user->avatarUrl;
                    if($post->location)
                    {
                        $location = explode(',',$post->location);

                        $data['location']['longitude'] = $location[1];
                        $data['location']['latitude'] = $location[0];
                    }
                    else
                    {
                        $data['longitude'] = '';
                        $data['latitude'] = '';
                    }

                    if($post->user->gender)
                    {
                        if($post->user->gender == 1)
                        {
                            $data['gender'] = "男";
                        }else
                        {
                            $data['gender'] = "女";
                        }
                    }

//                    $data['width'] = 50;
//                    $data['height'] = 70;

                    $datas[] = $data;
                }

            }

            return response()->json(['status' => 200,'data' => $datas]);
        }
    }


}
