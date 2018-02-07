<?php

namespace App\Http\Controllers;

use App\College;
use App\Comment;
use App\CommentToComment;
use App\Post;
use App\Praise;
use App\PraiseToComment;
use App\PraiseToReply;
use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Repositories\QiniuRepository;
use App\User;
use App\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PostController extends Controller
{
    protected $postRepository;
    protected $baseRepository;
    protected $userRepository;
    protected $commentRepository;
    protected $qiniuRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PostRepository $postRepository,
        BaseRepository $baseRepository,
        UserRepository $userRepository,
        CommentRepository $commentRepository,
        QiniuRepository $qiniuRepository
    )
    {
        $this->postRepository = $postRepository;
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
        $this->commentRepository = $commentRepository;
        $this->qiniuRepository = $qiniuRepository;
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
                    if($post->user_id == $user->id)
                    {
                        $data['belongsToMe'] = 1;
                    }else
                    {
                        $data['belongsToMe'] = 0;
                    }
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

                    if($post->anonymous == 1)
                    {
                        $anonymousUser = User::where('college_id',$user->college_id)->first();
                        $userInfo['id'] = $anonymousUser->id;
                        $userInfo['nickname'] = $anonymousUser->nickname;
                        $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

                    }else
                    {
                        $userInfo['id'] = $user->id;
                        $userInfo['nickname'] = $user->nickname;
                        $userInfo['avatarUrl'] = $user->avatarUrl;
                    }

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

                    $data['read_nums'] = $post->readnum;

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

                    if($post->user->available)
                    {
                        $data['id'] = $post->id;
                        $data['content'] = $post->content;
                        if($post->user_id == $user->id)
                        {
                            $data['belongsToMe'] = 1;
                        }else
                        {
                            $data['belongsToMe'] = 0;
                        }
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
                        if($post->anonymous == 1)
                        {
                            // $anonymousUser = User::where('college_id',$post->user->college_id)->first();
                            $anonymousUser = User::where(['college_id' => $post->user->college_id, 'role' => 0])->first();
                            $userInfo['id'] = $anonymousUser->id;
                            $userInfo['nickname'] = $anonymousUser->nickname;
                            $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

                        }else
                        {
                            $userInfo['id'] = $post->user_id;
                            $userInfo['nickname'] = $post->user->nickname;
                            $userInfo['avatarUrl'] = $post->user->avatarUrl;
                        }

                        if(!empty($post->user->college_id))
                        {
                            $userInfo['college'] = College::where('id',(int)($post->user->college_id))->first()->name;
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
        $search = $request->get('search');
        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        }

        if(empty($wesecret))
        {
            $data = array();
            $datas = array();

            $posts = $this->postRepository->getPostListZero($search);

            if(count($posts) == 0)
            {
                $datas = [];
            }
            else
            {
                foreach ($posts as $post)
                {
                    $userInfo = array();
                    if($post->user->available)
                    {
                        $data['id'] = $post->id;
                        $data['content'] = $post->content;
                        $data['belongsToMe'] = 0;
                        $data['video_url'] = $post->video_url;

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

                        $user =User::where('id',$post->user_id)->first();
                        if($post->anonymous == 1)
                        {
                            // $anonymousUser = User::where('college_id',$post->user->college_id)->first();
                            $anonymousUser = User::where(['college_id' => $post->user->college_id, 'role' => 0])->first();
                            $userInfo['id'] = $anonymousUser->id;
                            $userInfo['nickname'] = $anonymousUser->nickname;
                            $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

                        }else
                        {
                            $userInfo['id'] = $post->user_id;
                            $userInfo['nickname'] = $user->nickname;
                            $userInfo['avatarUrl'] =  $user->avatarUrl;
                        }


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

            }

            return response()->json(['status' => 200,'data' => $datas]);

        }
        elseif((!empty($wesecret)) && ($user))
        {
            $data = array();
            $datas = [];
            $posts = $this->postRepository->getAllPosts($search);

            if(empty($posts))
            {
                $datas = [];
            }
            else
            {
                foreach ($posts as $post)
                {
                    $userInfo = array();
                    if($post->user->available)
                    {
                        $data['id'] = $post->id;
                        $data['content'] = $post->content;
                        if($post->user_id == $user->id)
                        {
                            $data['belongsToMe'] = 1;
                        }else
                        {
                            $data['belongsToMe'] = 0;
                        }
                        $data['video_url'] = $post->video_url;
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

                        if($post->anonymous == 1)
                        {
                            // $anonymousUser = User::where('college_id',$post->user->college_id)->first();
                            $anonymousUser = User::where(['college_id' => $post->user->college_id, 'role' => 0])->first();
                            $userInfo['id'] = $anonymousUser->id;
                            $userInfo['nickname'] = $anonymousUser->nickname;
                            $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

                        }else
                        {
                            $userInfo['id'] = $post->user_id;
                            $userInfo['nickname'] = $post->user->nickname;
                            $userInfo['avatarUrl'] = $post->user->avatarUrl;
                        }

                        if(!empty($post->user->college_id))
                        {
                            $userInfo['college'] = College::where('id',(int)($post->user->college_id))->first()->name;
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

                $userInfo['id'] = $post->user_id;
                $userInfo['nickname'] = $post->user->nickname;
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
                        if($postComment->available)
                        {
                            $postComments['id'] = $postComment->id;
                            $postComments['content'] = $postComment->content;

                            $userOfComment = User::where('id',$postComment->user_id)->first();
                            $commentOfUserInfo['id'] = $userOfComment->id;
                            $commentOfUserInfo['nickname'] = $userOfComment->nickname;
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

                $userInfo['id'] = $post->user_id;
                $userInfo['nickname'] = $post->user->nickname;
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
                        if($postComment->available)
                        {
                            $postComments['id'] = $postComment->id;
                            $postComments['content'] = $postComment->content;

                            $userOfComment = User::where('id',$postComment->user_id)->first();
                            $commentOfUserInfo['id'] = $userOfComment->id;
                            $commentOfUserInfo['nickname'] = $userOfComment->nickname;
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

        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        }

        if(empty($wesecret))
        {
            $res = $this->postRepository->getNoWesecretHotPost();

            if(isset($res['postIds']) && !empty($res['postIds']))
            {
                $datas =[];
                $data = array();

                foreach ($res['postIds'] as $postId=>$hot)
                {
                    $post = $this->postRepository->getPost($postId);

                    $userInfo = array();
                    if($post->user->available)
                    {
                        $data['id'] = $post->id;
                        $data['content'] = $post->content;

                        $data['belongsToMe'] = 0;

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

                        $user =User::where('id',$post->user_id)->first();

                        if($post->anonymous == 1)
                        {
                            // $anonymousUser = User::where('college_id',$post->user->college_id)->first();
                            $anonymousUser = User::where(['college_id' => $post->user->college_id, 'role' => 0])->first();
                            $userInfo['id'] = $anonymousUser->id;
                            $userInfo['nickname'] = $anonymousUser->nickname;
                            $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;
                        }else
                        {
                            $userInfo['id'] = $post->user_id;
                            $userInfo['nickname'] = $user->nickname;
                            $userInfo['avatarUrl'] =  $user->avatarUrl;
                        }

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

            }else
            {
                return response()->json(['status' => 201,'message' => 'post does not exist!']);
            }

        }
        elseif ((!empty($wesecret)) && ($user))
        {

            $res = $this->postRepository->getHotPost();
            $datas =[];
            $data = array();
            if(isset($res['postIds']) && !empty($res['postIds']))
            {
                foreach ($res['postIds'] as $postId=>$hot)
                {
                    $post = $this->postRepository->getPost($postId);

                    $userInfo = array();

                    if($post->user->available)
                    {
                        $data['id'] = $post->id;
                        $data['content'] = $post->content;
                        if($post->user_id == $user->id)
                        {
                            $data['belongsToMe'] = 1;
                        }else
                        {
                            $data['belongsToMe'] = 0;
                        }
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

                        if($post->anonymous == 1)
                        {
                            // $anonymousUser = User::where('college_id',$post->user->college_id)->first();
                            $anonymousUser = User::where(['college_id' => $post->user->college_id, 'role' => 0])->first();
                            $userInfo['id'] = $anonymousUser->id;
                            $userInfo['nickname'] = $anonymousUser->nickname;
                            $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;
                        }else
                        {
                            $userInfo['id'] = $post->user_id;
                            $userInfo['nickname'] = $post->user->nickname;
                            $userInfo['avatarUrl'] = $post->user->avatarUrl;
                        }


                        if(!empty($post->user->college_id))
                        {
                            $userInfo['college'] = College::where('id',(int)($post->user->college_id))->first()->name;
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

            }else
            {
                return response()->json(['status' => 201,'message' => 'post does not exist!']);
            }
        }
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 发布表白墙
     */
    public function publishPost(Request $request)
    {
        $inputs = $request->all();

        try{
            $openid = Crypt::decrypt($inputs['wesecret']);
        }catch (\Exception $exception){
            return ['status' => 201,'message' => 'wesecret invalid'];
        }

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
            return response()->json(['status' => 201,'message' => 'User does not exist']);
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
                    if($post->user->available)
                    {
                        if($post->location)
                        {
                            $data['id'] = $post->id;
                            $location = explode(',',$post->location);
                            $data['latitude'] = floatval($location[0]);
                            $data['longitude'] = floatval($location[1]);
                            if($post->user->gender == 1) {
                                $data['iconPath'] = "/pages/images/map_female.png";
                            } else {
                                $data['iconPath'] = "/pages/images/map_male.png";
                            }
                            // $data['title'] = $post->user->nickname;
                            $data['width'] = 70;
                            $data['height'] = 70;
                            $datas[] = $data;
                        }
                    }

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
                    if($post->user->available)
                    {
                        if($post->location)
                        {
                            $data['id'] = $post->id;
                            $location = explode(',',$post->location);
                            $data['latitude'] = floatval($location[0]);
                            $data['longitude'] = floatval($location[1]);
                            if($post->user->gender == 1) {
                                $data['iconPath'] = "/pages/images/map_female.png";
                            } else {
                                $data['iconPath'] = "/pages/images/map_male.png";
                            }
                            // $data['title'] = $post->user->nickname;
                            $data['width'] = 70;
                            $data['height'] = 70;
                            $datas[] = $data;
                        }
                    }

                    
                }

            }

            return response()->json(['status' => 200,'data' => $datas]);
        }
    }

    public function deletePost(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $post_id = $request->get('love_id');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 200,'message' => 'User Does Not Exist!']);
        }else{
            $post = $this->postRepository->getPost($post_id);

            if($post)
            {
               if($post->user_id == $user->id)
               {
                   $comments = Comment::where('post_id',$post_id)->get();
                   $commentIds = Comment::where('post_id', $post_id)->pluck('id')->toArray();
                   $comment_notices = Notice::where('source_type', 1)->whereIn('source_id', $commentIds)->get();
                   if(count($comments))
                   {
                       foreach ($comments as $comment)
                       {
                           $comment->delete();
                       }
                   }
                   if(count($comment_notices)) {
                       foreach ($comment_notices as $comment_notice) {
                           $comment_notice->delete();
                       }
                   }
                   $replies = CommentToComment::where('post_id',$post_id)->get();
                   $replyIds = CommentToComment::where('post_id',$post_id)->pluck('id')->toArray();
                   $reply_notices = Notice::whereIn('source_type', [2, 3])->whereIn('source_id', $replyIds)->get();
                   if(count($replies))
                   {
                       foreach ($replies as $reply)
                       {
                           $reply->delete();
                       }
                   }
                   if(count($reply_notices)) {
                       foreach ($reply_notices as $reply_notice) {
                           $reply_notice->delete();
                       }
                   }

                   if(!empty($post->pictures)){
                        $needDelete_pictures = explode(',',$post->pictures);
                        if(count($needDelete_pictures))
                        {
                            foreach ($needDelete_pictures as $needDelete_picture)
                            {
                                $pictureArray = explode('/', $needDelete_picture); 
                                $key = $pictureArray[3]."/".$pictureArray[4];
                                $deleteResult = $this->qiniuRepository->deleteImageFormQiniu($key);
                            }
                        }
                   }
                    
                   $post->delete();
                   return response()->json(['status' => 200]);

               }else
               {
                   return response()->json(['status' => 200,'message' => 'You have no authorize to delete this love!']);

               }
            }
            else
            {
                return response()->json(['status' => 200,'message' => 'Love Does Not Exist!']);

            }
        }

    }

    public function virtualPublishPost(Request $request)
    {
        $inputs = $request->all();

        $post = $this->postRepository->savePost($inputs);

        return response()->json(['status' => 200,'love_id'=>$post->id]);


    }

    public function virtualUploadPostImages(Request $request)
    {
        $inputs = $request->all();

        $res = $this->baseRepository->uploadToQiniu($inputs);

        if($res['status'] == 201)
        {
            return response()->json(['status' => 201,'message' => 'pictures upload failed']);
        }else
        {
            $post = $this->postRepository->updatePostPicture($inputs['post_id'],$res['picturePath']);

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

    public function getUnreadLoveNums(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $post_id = $request->get('love_id');
        $postingType_id = $request->get('postingType_id');

        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        }

        if(empty($wesecret))
        {
            $post = $this->postRepository->getPost($post_id);

            if($post)
            {
                $created_time = $post->created_at;

                $nums = count(Post::where(['available' => 1, 'postingType_id' => $postingType_id ])->where('created_at','>',$created_time)->get());
                if($nums)
                {
                    $unreadLoveNums = $nums;
                }else
                {
                    $unreadLoveNums = 0;
                }

                return response()->json(['status' => 200,'message' => 'success','unreadLoveNums' => $unreadLoveNums]);

            }else
            {
                return response()->json(['status' => 201,'message' => 'Post Does Not Exist.','unreadLoveNums' => 0]);
            }
        }elseif ((!empty($wesecret)) && ($user))
        {
            $post = $this->postRepository->getPost($post_id);

            if($post)
            {
                $created_time = $post->created_at;

                if ($user->interest_id = 2) {
                    $unread_loves = Post::where(['available' => 1, 'postingType_id' => $postingType_id, 'province_id' => $user->college->city->province->id ])->where('created_at','>',$created_time)->get();
                } elseif($user->interest_id = 3) {
                    $unread_loves = Post::where(['available' => 1, 'postingType_id' => $postingType_id, 'city_id' => $user->college->city->id ])->where('created_at','>',$created_time)->get();
                } elseif($user->interest_id = 4) {
                    $unread_loves = Post::where(['available' => 1, 'postingType_id' => $postingType_id, 'college_id' => $user->college->id ])->where('created_at','>',$created_time)->get();
                } else {
                    $unread_loves = Post::where(['available' => 1, 'postingType_id' => $postingType_id ])->where('created_at','>',$created_time)->get();
                }

                $nums = count($unread_loves);

                if($nums)
                {
                    $unreadLoveNums = $nums;
                }else
                {
                    $unreadLoveNums = 0;
                }

                return response()->json(['status' => 200,'message' => 'success','unreadLoveNums' => $unreadLoveNums]);

            }else
            {
                return response()->json(['status' => 201,'message' => 'Post Does Not Exist.','unreadLoveNums' => 0]);
            }

            
        }

    }

    public function getLonePost(Request $request, $id)
    {
        $wesecret = $request->get('wesecret');

        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        }

        $data = array();
        $datas = array();

        if(empty($wesecret))
        {
            $post = $this->postRepository->getPost($id);

            if(count($post))
            {
                $userInfo = array();
                if($post->user->available)
                {
                    $data['id'] = $post->id;
                    $data['content'] = $post->content;
                    $data['video_url'] = $post->video_url;
                    // $data['muted'] = true;                    

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

                    if($post->anonymous == 1)
                    {
                        $anonymousUser = User::where('role', 0)->first();
                        $userInfo['id'] = $anonymousUser->id;
                        $userInfo['nickname'] = $anonymousUser->nickname;
                        $userInfo['avatarUrl'] =  $anonymousUser->avatarUrl;
                    }else
                    {
                        $userInfo['id'] = $post->user_id;
                        $userInfo['nickname'] = $post->user->nickname;
                        $userInfo['avatarUrl'] = $post->user->avatarUrl;
                    }
                    $userInfo['college'] = $post->college->name;
                    
                    $data['userInfo'] = $userInfo;

                    $diff_time = $this->postRepository->getTime($post->created_at);

                    $data['created_at'] = $diff_time;

                    $data['readnum'] = $post->readnum;

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


                }
            }
            else
            {
                $data = [];
            }

            return response()->json(['status' => 200,'data' => $data]);
        }elseif ((!empty($wesecret)) && ($user))
        {
            $post = $this->postRepository->getPost($id);

            if(count($post))
            {
                $userInfo = array();
                if($post->user->available)
                {
                    $data['id'] = $post->id;
                    $data['content'] = $post->content;
                    if($post->user_id == $user->id)
                    {
                        $data['belongsToMe'] = 1;
                    }else
                    {
                        $data['belongsToMe'] = 0;
                    }
                    $data['video_url'] = $post->video_url;

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


                    if($post->anonymous)
                    {
                        $postuser =User::where('id',$post->user_id)->first();
                        $anonymousUser = User::where('college_id',$postuser->college_id)->first();
                        $userInfo['id'] = $anonymousUser->id;
                        $userInfo['nickname'] = $anonymousUser->nickname;
                        $userInfo['avatarUrl'] =  $anonymousUser->avatarUrl;
                        if(!empty($anonymousUser->college_id))
                        {
                            $userInfo['college'] = College::where('id',(int)($anonymousUser->college_id))->first()->name;
                        }
                        else
                        {
                            $userInfo['college'] = '';
                        }
                    }else
                    {
                        $postuser =User::where('id',$post->user_id)->first();
                        $userInfo['id'] = $postuser->id;
                        $userInfo['nickname'] = $postuser->nickname;
                        $userInfo['avatarUrl'] =  $postuser->avatarUrl;

                        if(!empty($postuser->college_id))
                        {
                            $userInfo['college'] = College::where('id',(int)($postuser->college_id))->first()->name;
                        }
                        else
                        {
                            $userInfo['college'] = '';
                        }
                    }
                    $data['userInfo'] = $userInfo;

                    $diff_time = $this->postRepository->getTime($post->created_at);

                    $data['created_at'] = $diff_time;

                    $data['readnum'] = $post->readnum;

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


                }
            }
            else
            {
                $data = [];
            }

            return response()->json(['status' => 200,'data' => $data]);
        }
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 获取某个表白的所有评论
     */
    public function getPostAllComments(Request $request, $id)
    {
        $wesecret = $request->get('wesecret');

        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        }

        $data = array();
        $datas = array();

        $commentUserInfo = array();
        $replyUserInfo = array();

        $objectUserInfo = [];

        $comments = Comment::where(['post_id' => $id, 'available' => 1])->orderBy('created_at','desc')->paginate(PostRepository::pagesize);
        
        if(empty($wesecret))
        {
            if(count($comments))
            {
                foreach ($comments as $comment)
                {
                    $data['id'] =  $comment->id;
                    $data['content'] = $comment->content;
                    $commentuser =User::where('id',$comment->user_id)->first();

                    $commentUserInfo['id'] = $commentuser->id;
                    $commentUserInfo['openid'] = $commentuser->openid;
                    $commentUserInfo['nickname'] = $commentuser->nickname;
                    $commentUserInfo['avatarUrl'] =  $commentuser->avatarUrl;
                    $data['userInfo'] = $commentUserInfo;

                    $diff_time = $this->postRepository->getTime($comment->created_at);
                    $data['created_at'] = $diff_time;

                    // $data['reply_nums'] = $comment->r_commentnum;
                    $data['reply_nums'] = CommentToComment::where(['comment_id' => $comment->id, 'available' => 1])->get()->count();
                    $replies = CommentToComment::where(['comment_id' => $comment->id, 'available' => 1])
                        ->orderBy('created_at','desc')->limit(3)->get();
                    $data['replies'] = [];
                    if(count($replies))
                    {
                        foreach ($replies as $reply)
                        {
                            $replys['id'] = $reply->id;
                            $replys['content'] = $reply->content;

                            $user1 = $this->userRepository->getUserById($reply->user_id);
                            $replyUserInfo['id'] = $user1->id;
                            $replyUserInfo['nickname'] = $user1->nickname;
                            $replyUserInfo['avatarUrl'] = $user1->avatarUrl;
                            $replys['userInfo'] = $replyUserInfo;

                            $objectUserInfo['id'] = $reply->parent_id;
                            $objectUser = $this->userRepository->getUserById($reply->parent_id);
                            $objectUserInfo['nickname'] = $objectUser->nickname;
                            $replys['objectUserInfo'] = $objectUserInfo;
                            $replys['praise_nums'] = $reply->praise_nums;
                            $replys['if_my_praise'] = 0;

                            $diff_time = $this->postRepository->getTime($reply->created_at);
                            $replys['created_at'] = $diff_time;
                            $data['replies'][] = $replys;
                        }
                    }else
                    {
                        $data['replies'] = [];
                    }

                    $data['praise_nums'] = $comment->r_likenum;

                    $data['if_my_comment'] = 0;

                    $data['if_my_praise'] = 0;

                    $datas[] = $data;
                }

                return response()->json(['status' => 200,'data' => $datas]);
            }
            else
            {
                return response()->json(['status' => 200,'data' => $datas]);
            }
        }
        elseif ((!empty($wesecret)) && ($user))
        {
            if(count($comments))
            {
                foreach ($comments as $comment)
                {
                    $data['id'] =  $comment->id;
                    $data['content'] = $comment->content;
                    $commentuser =User::where('id',$comment->user_id)->first();

                    $commentUserInfo['id'] = $commentuser->id;
                    $commentUserInfo['openid'] = $commentuser->openid;
                    $commentUserInfo['nickname'] = $commentuser->nickname;
                    $commentUserInfo['avatarUrl'] =  $commentuser->avatarUrl;
                    $data['userInfo'] = $commentUserInfo;

                    $diff_time = $this->postRepository->getTime($comment->created_at);
                    $data['created_at'] = $diff_time;

                    // $data['reply_nums'] = $comment->r_commentnum;
                    $data['reply_nums'] = CommentToComment::where(['comment_id' => $comment->id, 'available' => 1])->get()->count();
                    $replies = CommentToComment::where(['comment_id' => $comment->id, 'available' => 1])
                        ->orderBy('created_at','desc')->limit(3)->get();
                    $data['replies'] = [];
                    if(count($replies))
                    {
                        foreach ($replies as $reply)
                        {
                            $replys['id'] = $reply->id;
                            $replys['content'] = $reply->content;

                            $user1 = $this->userRepository->getUserById($reply->user_id);
                            $replyUserInfo['id'] = $user1->id;
                            $replyUserInfo['nickname'] = $user1->nickname;
                            $replyUserInfo['avatarUrl'] = $user1->avatarUrl;
                            $replys['userInfo'] = $replyUserInfo;

                            $objectUserInfo['id'] = $reply->parent_id;
                            $objectUser = $this->userRepository->getUserById($reply->parent_id);
                            $objectUserInfo['nickname'] = $objectUser->nickname;
                            $replys['objectUserInfo'] = $objectUserInfo;
                            $replys['praise_nums'] = $reply->praise_nums;

                            $praiseToReply = PraiseToReply::where('reply_id',$reply->id)
                                ->where('user_id',$user->id)->first();
                            if(count($praiseToReply))
                            {
                                $replys['if_my_praise'] = 1;
                            }else
                            {
                                $replys['if_my_praise'] = 0;
                            }

                            $diff_time = $this->postRepository->getTime($reply->created_at);
                            $replys['created_at'] = $diff_time;
                            $data['replies'][] = $replys;
                        }
                    }else
                    {
                        $data['replies'] = [];
                    }

                    $data['praise_nums'] = $comment->r_likenum;

                    $if_my_comment = Comment::where('post_id',$id)->where('user_id',$user->id)->first();
                    if($if_my_comment)
                    {
                        $data['if_my_comment'] = 1;
                    }
                    else
                    {
                        $data['if_my_comment'] = 0;
                    }

                    $if_my_praise = PraiseToComment::where('comment_id',$comment->id)->where('user_id',$user->id)->first();
                    if($if_my_praise)
                    {
                        $data['if_my_praise'] = 1;
                    }
                    else
                    {
                        $data['if_my_praise'] = 0;
                    }

                    $datas[] = $data;
                }
                return response()->json(['status' => 200,'data' => $datas]);
            }
            else
            {
                return response()->json(['status' => 200,'data' => $datas]);
            }
        }
    }

    public function getGenderLoves(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $search = $request->get('search');
        $openid = Crypt::decrypt($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        $data = [];
        $datas = [];
        if ($user)
        {
           if(($user->gender == 0) || ($user->gender == 1))
           {
               $posts = $this->postRepository->getGenderPosts($user->gender,$search);

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

                       if($post->anonymous == 1)
                       {
                           $anonymousUser = User::where('college_id',$user->college_id)->first();
                           $userInfo['id'] = $anonymousUser->id;
                           $userInfo['nickname'] = $anonymousUser->nickname;
                           $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

                       }else
                       {
                           $userInfo['id'] = $user->id;
                           $userInfo['nickname'] = $user->nickname;
                           $userInfo['avatarUrl'] = $user->avatarUrl;
                       }

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

                       $data['read_nums'] = $post->readnum;

                       $datas[] = $data;
                   }

               }
           }

           return response()->json(['status'=>200,'message'=>'successful.','data'=>$datas]);
        }
        else
        {
            return response()->json(['status'=>201,'message'=>'user not exist']);
        }
    }


    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 获取帖子,根据type区分
     */
    public function getLoves(Request $request)
    {   
        $wesecret = $request->get('wesecret');
        $search = $request->get('search');
        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        } else {
            $user = null;
        }

        $type = $request->get('type');
        switch ($type) 
        {
            case 'newLoves': 
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_ONE, 'created_at');
                break;
            case 'hotLoves': 
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_ONE, 'commentnum');
                break;
            case 'topLoves':
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_ONE, 'commentnum');
                break;
            case 'newActivities': 
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_TWO, 'created_at');
                break;
            case 'hotActivities': 
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_TWO, 'commentnum');
                break;
            case 'topActivities':
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_TWO, 'commentnum');
                break;
            case 'newQuestions': 
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_THREE, 'created_at');
                break;
            case 'hotQuestions': 
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_THREE, 'commentnum');
                break;
            case 'topQuestions':
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_THREE, 'commentnum');
                break;
            case 'goods': 
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_FOUR, 'created_at');
                break;
            case 'complaints': 
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_FIVE, 'created_at');
                break;
            case 'jobs':
                $posts = $this->postRepository->getNewLoves($search, $user, PostRepository::POSTINGTYPE_SIX, 'created_at');
                break;
            
            case 'commentLoves':
                $posts = $this->postRepository->getCommentLoves($search, $user);
                break;
            case 'praiseLoves':
                $posts = $this->postRepository->getPraiseLoves($search, $user);
                break;
            case 'myLoves':
                $posts = $this->postRepository->getMyLoves($search, $user);
                break;
            default:
                $posts = $this->postRepository->getNewLoves($search);
        }
          

        $datas = [];

        if(!count($posts))
        {
            $datas = [];
        }
        else
        {
            foreach ($posts as $post)
            {
                
                if($post->user->available == 1 && $post->available == 1)
                {
                    $data = [];

                    $data['id'] = $post->id;
                    $data['postingType_id'] = $post->postingType_id;
                    $data['postingType_name'] = $post->postingType->name;
                    $data['content'] = $post->content;
                    if(!empty($user) && $post->user_id == $user->id)
                    {
                        $data['belongsToMe'] = 1;
                    }else
                    {
                        $data['belongsToMe'] = 0;
                    }
                    $data['video_url'] = $post->video_url;
                    // $data['muted'] = true;
                    if(!empty($post->pictures))
                    {
                        $data['images'] = explode(',',$post->pictures);
                    }
                    else
                    {
                        $data['images'] = [];
                    }

                    $userInfo = [];
                    if($post->anonymous == 1)
                    {
                        $anonymousUser = User::where('role', 0)->first();
                        $userInfo['id'] = $anonymousUser->id;
                        $userInfo['openid'] = $post->user->openid;
                        $userInfo['nickname'] = $anonymousUser->nickname;
                        $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

                    }else
                    {
                        $userInfo['id'] = $post->user_id;
                        $userInfo['openid'] = $post->user->openid;
                        $userInfo['nickname'] = $post->user->nickname;
                        $userInfo['avatarUrl'] = $post->user->avatarUrl;
                    }
                    $userInfo['college_name'] = $post->college->name;
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
                        // $data['comment_nums'] = $post->commentnum;
                        $data['comment_nums'] = Comment::where(['post_id' => $post->id, 'available' => 1])->get()->count();
                    }
                    else
                    {
                        $data['comment_nums'] = 0;
                    }

                    if (!empty($user)) {
                        $if_my_comment = Comment::where('post_id',$post->id)->where('user_id',$user->id)->first();
                        if($if_my_comment)
                        {
                            $data['if_my_comment'] = 1;
                        }
                        else
                        {
                            $data['if_my_comment'] = 0;
                        }
                    } else {
                        $data['if_my_comment'] = 0;
                    }               
                    if (!empty($user)) {
                        $if_my_praise = Praise::where('post_id',$post->id)->where('user_id',$user->id)->first();
                        if($if_my_praise)
                        {
                            $data['if_my_praise'] = 1;
                        }
                        else
                        {
                            $data['if_my_praise'] = 0;
                        }
                    } else {
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

        }

        $ip = $request->getClientIp();

        return response()->json(['status' => 200,'data' => $datas,'ip'=>$ip]);

    }


    public function getLove(Request $request, $id)
    {
        $wesecret = $request->get('wesecret');

        if (!empty($wesecret))
        {
            $openid = $this->baseRepository->decryptCode($wesecret);
            $user = $this->userRepository->getUserByOpenId($openid);
        } else {
            $user = null;
        }

        $post = $this->postRepository->getPost($id);
        
        $data = [];

        $data['id'] = $post->id;
        $data['postingType_id'] = $post->postingType_id;
        $data['postingType_name'] = $post->postingType->name;
        $data['content'] = $post->content;
        if(!empty($user) && $post->user_id == $user->id)
        {
            $data['belongsToMe'] = 1;
        }else
        {
            $data['belongsToMe'] = 0;
        }
        $data['video_url'] = $post->video_url;
        // $data['muted'] = true;
        if(!empty($post->pictures))
        {
            $data['images'] = explode(',',$post->pictures);
        }
        else
        {
            $data['images'] = [];
        }

        $userInfo = [];
        if($post->anonymous == 1)
        {
            $anonymousUser = User::where('role', 0)->first();
            $userInfo['id'] = $anonymousUser->id;
            $userInfo['openid'] = $post->user->openid;
            $userInfo['nickname'] = $anonymousUser->nickname;
            $userInfo['avatarUrl'] = $anonymousUser->avatarUrl;

        }else
        {
            $userInfo['id'] = $post->user_id;
            $userInfo['openid'] = $post->user->openid;
            $userInfo['nickname'] = $post->user->nickname;
            $userInfo['avatarUrl'] = $post->user->avatarUrl;
        }
        
        $userInfo['college_name'] = $post->college->name;
        
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
            // $data['comment_nums'] = $post->commentnum;
            $data['comment_nums'] = Comment::where(['post_id' => $post->id, 'available' => 1])->get()->count();
        }
        else
        {
            $data['comment_nums'] = 0;
        }

        if (!empty($user)) {
            $if_my_comment = Comment::where('post_id',$post->id)->where('user_id',$user->id)->first();
            if($if_my_comment)
            {
                $data['if_my_comment'] = 1;
            }
            else
            {
                $data['if_my_comment'] = 0;
            }
        } else {
            $data['if_my_comment'] = 0;
        }               
        if (!empty($user)) {
            $if_my_praise = Praise::where('post_id',$post->id)->where('user_id',$user->id)->first();
            if($if_my_praise)
            {
                $data['if_my_praise'] = 1;
            }
            else
            {
                $data['if_my_praise'] = 0;
            }
        } else {
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

        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getip(){
//        return $this->getRealIp();
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    echo $ip."\n";
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
    }















// ----------------------------------------------后台管理系统api-----------------------------------------------------------------------------------------------------------------------



    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 获取帖子,根据type区分
     */
    public function getLoves_backsystem(Request $request)
    {   
        // $params = $request->get('params');
        // $search = $params['search'];
        // $manage_level_id = $params['manage_level_id'];
        // $manage_college_id = $params['manage_college_id'];
        $search = $request->get('search');
        $manage_level_id = $request->get('manage_level_id');
        $manage_college_id = $request->get('manage_college_id');
        
        $postsBefore = $this->getLovesByManageLevel($search, $manage_level_id, $manage_college_id);
        $dataLength = $postsBefore->get()->count();
        
        $posts= $postsBefore->paginate(10);

        $datas = [];

        if(!count($posts))
        {
            $datas = [];
        }
        else
        {
            foreach ($posts as $post)
            {
                $data = [];

                $data['id'] = $post->id;
                $data['postingType_id'] = $post->postingType_id;
                $data['postingType_name'] = $post->postingType->name;
                $data['content'] = $post->content;
            
                $data['video_url'] = $post->video_url;
                // if(!empty($post->pictures))
                // {
                //     $data['images'] = explode(',',$post->pictures);
                // }
                // else
                // {
                //     $data['images'] = [];
                // }
                $data['images'] = $post->pictures;
                if(!empty($post->pictures))
                {
                    $data['images_array'] = explode(',',$post->pictures);
                }
                else
                {
                    $data['images_array'] = [];
                }

                $userInfo = [];
                $userInfo['id'] = $post->user_id;
                $userInfo['openid'] = $post->user->openid;
                $userInfo['nickname'] = $post->user->nickname;
                $userInfo['avatarUrl'] = $post->user->avatarUrl;
                $userInfo['college_name'] = $post->college->name;
                $data['userInfo'] = $userInfo;

                $data['user_id'] = $userInfo['id'];

                // $data['created_at'] = $post->created_at;
                $data['created_at'] = $post->created_at->format('Y-m-d H:i:s');
                // $data['created_at'] = date('Y-m-d H:i:s', $post->created_at);

                $data['anonymous'] = $post->anonymous;
                if ($post->available == 1 && $post->user->available == 1) {
                    $data['available'] = 1;
                } else {
                    $data['available'] = 0;
                }

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
                    // $data['comment_nums'] = $post->commentnum;
                    $data['comment_nums'] = Comment::where(['post_id' => $post->id, 'available' => 1])->get()->count();
                }
                else
                {
                    $data['comment_nums'] = 0;
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

        $ip = $request->getClientIp();

        return response()->json(['status' => 200,'data' => $datas, 'dataLength' => $dataLength,'ip'=>$ip]);

    }
    public function getLovesByManageLevel($search, $manage_level_id, $manage_college_id) {
        if ($manage_level_id == 4) {
            $posts = Post::where(function ($query) use($search){
                if(!empty($search))
                {
                    $query->whereHas('user',function ($queryUser) use ($search){
                            $queryUser->where('realname','LIKE','%'.$search.'%')
                            ->orWhere('nickname','LIKE','%'.$search.'%');
                        })
                        ->orWhereHas('college',function ($queryCollege) use ($search){
                            $queryCollege->where('name','LIKE','%'.$search.'%');
                        })
                        ->orWhereHas('user.gender',function ($queryGender) use ($search){
                            $queryGender->where('name','LIKE','%'.$search.'%');
                        })
                        ->orWhere('content','LIKE','%'.$search.'%');
                }
            })->where(['postingType_id' => 1, 'college_id' => $manage_college_id])->orderBy('created_at', 'desc');
        } else {
            $posts = Post::where(function ($query) use($search){
                if(!empty($search))
                {
                    $query->whereHas('user',function ($queryUser) use ($search){
                            $queryUser->where('realname','LIKE','%'.$search.'%')
                            ->orWhere('nickname','LIKE','%'.$search.'%');
                        })
                        ->orWhereHas('college',function ($queryCollege) use ($search){
                            $queryCollege->where('name','LIKE','%'.$search.'%');
                        })
                        ->orWhereHas('user.gender',function ($queryGender) use ($search){
                            $queryGender->where('name','LIKE','%'.$search.'%');
                        })
                        ->orWhere('content','LIKE','%'.$search.'%');
                }
            })->where('postingType_id', 1)->orderBy('created_at', 'desc');
        }
        

        return $posts;
    }


    public function addImagePost(Request $request)
    {
        $inputs = $request->get('params');

        $post = Post::find($inputs['love_id']);
        if ($post) {
            if(!empty($post->pictures))
            {
                $old_imamges = explode(',',$post->pictures);
            }
            else
            {
                $old_imamges = [];
            }
            $old_imamges[] = $inputs['key'];
            $post->pictures = implode(',', $old_imamges);
            $post->save();
        }

    }

    public function removeImagePost(Request $request)
    {
        $inputs = $request->get('params');
        $the_delete_picture = $inputs['key'];

        $post = Post::find($inputs['love_id']);
        if ($post && !empty($the_delete_picture)) {
            if(!empty($post->pictures))
            {
                $old_imamges = explode(',',$post->pictures);
            }
            else
            {
                $old_imamges = [];
            }

            function delByValue($arr, $value){  
                if(!is_array($arr)){  
                    return $arr;  
                }  
                foreach($arr as $k=>$v){  
                    if($v == $value){  
                        unset($arr[$k]);  
                    }  
                }  
                return $arr;  
            }  

            $new_images = delByValue($old_imamges, $the_delete_picture);
    
            $post->pictures = implode(',', $new_images);
            $post->save();

            // 从七牛云上删除照片  $input['the_delete_picture']
            $pictureArray = explode('/', $the_delete_picture); 
            $key = $pictureArray[3]."/".$pictureArray[4];
            $deleteResult = $this->qiniuRepository->deleteImageFormQiniu($key);
        }

    }

    public function publishPost_backsystem(Request $request)
    {
        $inputs = $request->get('params');
        
        $user = User::where('id', $inputs['user_id'])->first();
        if ($user)
        {
            $inputs['user_id'] = $user->id;

            if (empty($inputs['id'])){
                $post = $this->postRepository->savePost($inputs);
            } else {
                $post = $this->postRepository->updatePost($inputs, $inputs['id']);
            }

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


}
