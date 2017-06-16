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
use App\User;
use App\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

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
                            $accessKey= "WR4zSNb376JZyEq6TQx9pJ9DSherW9xFKO9Ls2zB";
                            $secretKey= "z7_Jx8-sCMGUFmrP5bPqM_GOT2FiIq3AEeEoQKZE";
                            $auth=new Auth($accessKey, $secretKey);
                            $bucketMgr = new BucketManager($auth);
                            $bucket= "lovewall";//上传空间名称
                            
                            foreach ($needDelete_pictures as $needDelete_picture)
                            {
                                $pictureArray = explode('/', $needDelete_picture); 
                                $key = $pictureArray[3];
                                $err = $bucketMgr->delete($bucket, $key);
                                echo "\n====> delete $key : \n";
                                if ($err !== null) {
                                    var_dump($err);
                                } else {
                                    echo "Success!";
                                }
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

                $nums = count(Post::where('visiable',0)->where('created_at','>',$created_time)->get());
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
                return response()->json(['status' => 201,'message' => 'Post Does Not Exist.']);
            }
        }elseif ((!empty($wesecret)) && ($user))
        {
            $post = $this->postRepository->getPost($post_id);

            if($post)
            {
                $created_time = $post->created_at;

                $nums1 = count(Post::where('visiable',0)->where('created_at','>',$created_time)->get());
                $nums2 = 0;

                if($user->college_id)
                {
                    $userIds = User::where('college_id',$user->college_id)->pluck('id')->toArray();

                    $nums2 += count(Post::whereIn('user_id',$userIds)->where('visiable',1)
                        ->where('created_at','>',$created_time)->get());
                }

                $nums3 = 0;
                if(($user->gender = 0) ||($user->gender = 1))
                {

                    $userIds = User::where("gender",$user->gender)->pluck('id')->toArray();

                    if($user->gender = 1)
                    {
                        $nums3 += count(Post::whereIn('user_id',$userIds)->where('visiable',2)
                            ->where('created_at','>',$created_time)->get());
                    }elseif ($user->gender = 0)
                    {
                        $nums3 += count(Post::whereIn('user_id',$userIds)->where('visiable',3)
                            ->where('created_at','>',$created_time)->get());
                    }
                }

                $unreadLoveNums = $nums1+$nums2+$nums3;
                return response()->json(['status' => 200,'message' => 'success','unreadLoveNums' => $unreadLoveNums]);

            }else
            {
                return response()->json(['status' => 201,'message' => 'Post Does Not Exist.']);
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
        $replys = [];
        $objectUserInfo = [];

        if(empty($wesecret))
        {
            $comments = Comment::where('post_id',$id)->orderBy('created_at','desc')->paginate(5);
            if(count($comments))
            {
                foreach ($comments as $comment)
                {
                    $data['id'] =  $comment->id;
                    $data['content'] = $comment->content;
                    $commentuser =User::where('id',$comment->user_id)->first();

                    $commentUserInfo['id'] = $commentuser->id;
                    $commentUserInfo['nickname'] = $commentuser->nickname;
                    $commentUserInfo['avatarUrl'] =  $commentuser->avatarUrl;
                    $data['userInfo'] = $commentUserInfo;

                    $diff_time = $this->postRepository->getTime($comment->created_at);
                    $data['created_at'] = $diff_time;

                    $data['reply_nums'] = $comment->r_commentnum;
                    $replies = CommentToComment::where('comment_id',$comment->id)
                        ->orderBy('created_at','desc')->limit(3)->get();
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
            $comments = Comment::where('post_id',$id)->orderBy('created_at','desc')->paginate(5);
            if(count($comments))
            {
                foreach ($comments as $comment)
                {
                    $data['id'] =  $comment->id;
                    $data['content'] = $comment->content;
                    $commentuser =User::where('id',$comment->user_id)->first();

                    $commentUserInfo['id'] = $commentuser->id;
                    $commentUserInfo['nickname'] = $commentuser->nickname;
                    $commentUserInfo['avatarUrl'] =  $commentuser->avatarUrl;
                    $data['userInfo'] = $commentUserInfo;

                    $diff_time = $this->postRepository->getTime($comment->created_at);
                    $data['created_at'] = $diff_time;

                    $data['reply_nums'] = $comment->r_commentnum;
                    $replies = CommentToComment::where('comment_id',$comment->id)
                        ->orderBy('created_at','desc')->limit(3)->get();
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
            case 'hotLoves': 
                $posts = $this->postRepository->getHotLoves($search);
                break;
            case 'imageLoves':
                $posts = $this->postRepository->getImageLoves($search);
                break;
            case 'videoLoves':
                $posts = $this->postRepository->getVideoLoves($search);
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
        // if (type == 'newestLoves') {

        // } elseif (type == 'hottestLoves') {

        // } elseif (type == 'imageLoves') {

        // } 



        // $posts = $this->postRepository->getAllPosts($search);
        
        

        $datas = [];

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
                    $data = [];

                    $data['id'] = $post->id;
                    $data['content'] = $post->content;
                    if(!empty($user) && $post->user_id == $user->id)
                    {
                        $data['belongsToMe'] = 1;
                    }else
                    {
                        $data['belongsToMe'] = 0;
                    }
                    $data['video_url'] = $post->video_url;
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

        return response()->json(['status' => 200,'data' => $datas]);

    }


}
