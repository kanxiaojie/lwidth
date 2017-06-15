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
use App\Comment;
use App\Praise;

class PostRepository
{
    protected $post;

    public function __construct(
        Post $post
    )
    {
        $this->post = $post;
    }

    public function getCollegeLoves($user, $orderby = 'created_at', $direction = 'desc')
    {
        $userIds = User::where('college_id',$user->college_id)->pluck('id')->toArray();

        $posts = $this->post->whereIn('user_id',$userIds)->whereIn('visiable',[0,1,2,3])
            ->orWhere('user_id',$user->id)->where('visiable',4)->orderBy($orderby,$direction)->paginate(10);

        return $posts;
    }

    public function getLocationLoves( $orderby = 'created_at', $direction = 'desc')
    {
        $posts = $this->post->whereNotNull('location')->orderBy($orderby,$direction)->get();

        return $posts;
    }

    // public function getMyLoves($user, $orderby = 'created_at', $direction = 'desc')
    // {
    //     $posts = $this->post->where('user_id',$user->id)->orderBy($orderby,$direction)->get();

    //     return $posts;
    // }

    public function getGenderPosts($gender,$search = null, $orderby = 'created_at', $direction = 'desc')
    {
        $userIds = [];
        if($gender == 0)
        {
            $userIds = User::where('gender',1)->pluck('id')->toArray();
        }elseif($gender ==1)
        {
            $userIds = User::where('gender',0)->pluck('id')->toArray();
        }

        $posts = Post::where(function ($query) use($gender,$search)
            {
                if(!empty($search))
                {
                    $query->whereHas('user',function ($queryUser) use ($search){
                        $queryUser->where('realname','LIKE','%'.$search.'%')
                            ->orWhere('nickname','LIKE','%'.$search.'%');
                    })
                        ->orWhereHas('user.college',function ($queryCollege) use ($search){
                            $queryCollege->where('name','LIKE','%'.$search.'%');
                        })
                        ->orWhere('content','LIKE','%'.$search.'%')
                    ;
                }
            })
            ->whereIn('user_id',$userIds)
            ->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }

    public function getAllPosts($search = null,$orderby = 'created_at', $direction = 'desc')
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        $posts = Post::where(function ($query) use($search, $search_gender){
                    if(!empty($search))
                    {
                        $query->whereHas('user',function ($queryUser) use ($search, $search_gender){
                                $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%')
                                ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            })
                            ->orWhereHas('user.college',function ($queryCollege) use ($search){
                                $queryCollege->where('name','LIKE','%'.$search.'%');
                            })
                            ->orWhere('content','LIKE','%'.$search.'%')
                        ;
                    }
                })->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }












    public function getNewLoves($search = null,$orderby = 'created_at', $direction = 'desc')
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        $posts = Post::where(function ($query) use($search, $search_gender){
                    if(!empty($search))
                    {
                        $query->whereHas('user',function ($queryUser) use ($search, $search_gender){
                                $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%')
                                ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            })
                            ->orWhereHas('user.college',function ($queryCollege) use ($search){
                                $queryCollege->where('name','LIKE','%'.$search.'%');
                            })
                            ->orWhere('content','LIKE','%'.$search.'%');
                    }
                })->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }
    public function getHotLoves($search = null,$orderby = 'commentnum', $direction = 'desc')
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        $posts = Post::where(function ($query) use($search, $search_gender){
                    if(!empty($search))
                    {
                        $query->whereHas('user',function ($queryUser) use ($search, $search_gender){
                                $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%')
                                ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            })
                            ->orWhereHas('user.college',function ($queryCollege) use ($search){
                                $queryCollege->where('name','LIKE','%'.$search.'%');
                            })
                            ->orWhere('content','LIKE','%'.$search.'%');
                    }
                })->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }
    public function getImageLoves($search = null,$orderby = 'created_at', $direction = 'desc')
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        $posts = Post::where(function ($query) use($search, $search_gender){
                    if(!empty($search))
                    {
                        $query->whereHas('user',function ($queryUser) use ($search, $search_gender){
                                $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%')
                                ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            })
                            ->orWhereHas('user.college',function ($queryCollege) use ($search){
                                $queryCollege->where('name','LIKE','%'.$search.'%');
                            })
                            ->orWhere('content','LIKE','%'.$search.'%');
                    }
                })
                ->where('pictures', '<>', null)
                ->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }
    public function getVideoLoves($search = null,$orderby = 'created_at', $direction = 'desc')
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        $posts = Post::where(function ($query) use($search, $search_gender){
                    if(!empty($search))
                    {
                        $query->whereHas('user',function ($queryUser) use ($search, $search_gender){
                                $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%')
                                ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            })
                            ->orWhereHas('user.college',function ($queryCollege) use ($search){
                                $queryCollege->where('name','LIKE','%'.$search.'%');
                            })
                            ->orWhere('content','LIKE','%'.$search.'%');
                    }
                })
                ->where('video_url', '<>', null)
                ->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }
    public function getCommentLoves($search = null, $user = null, $orderby = 'created_at', $direction = 'desc')
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        $postIds = Comment::where('user_id',$user->id)->pluck('post_id');

        $posts = Post::where(function ($query) use($search, $search_gender){
                    if(!empty($search))
                    {
                        $query->whereHas('user',function ($queryUser) use ($search, $search_gender){
                                $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%')
                                ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            })
                            ->orWhereHas('user.college',function ($queryCollege) use ($search){
                                $queryCollege->where('name','LIKE','%'.$search.'%');
                            })
                            ->orWhere('content','LIKE','%'.$search.'%');
                    }
                })
                ->whereIn('id',$postIds)
                ->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }
    public function getPraiseLoves($search = null, $user = null, $orderby = 'created_at', $direction = 'desc')
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        $postIds = Praise::where('user_id',$user->id)->pluck('post_id');

        $posts = Post::where(function ($query) use($search, $search_gender){
                    if(!empty($search))
                    {
                        $query->whereHas('user',function ($queryUser) use ($search, $search_gender){
                                $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%')
                                ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            })
                            ->orWhereHas('user.college',function ($queryCollege) use ($search){
                                $queryCollege->where('name','LIKE','%'.$search.'%');
                            })
                            ->orWhere('content','LIKE','%'.$search.'%');
                    }
                })
                ->whereIn('id',$postIds)
                ->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }
    public function getMyLoves($search = null, $user = null, $orderby = 'created_at', $direction = 'desc')
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        $posts = Post::where(function ($query) use($search, $search_gender){
                    if(!empty($search))
                    {
                        $query->whereHas('user',function ($queryUser) use ($search, $search_gender){
                                $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%')
                                ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            })
                            ->orWhereHas('user.college',function ($queryCollege) use ($search){
                                $queryCollege->where('name','LIKE','%'.$search.'%');
                            })
                            ->orWhere('content','LIKE','%'.$search.'%');
                    }
                })
                ->where('user_id', $user->id)
                ->orderBy($orderby,$direction)->paginate(5);

        return $posts;
    }

















    public function getPostLists($inputs,$user,$orderby = 'created_at', $direction = 'desc')
    {
        if((!$user->gender) && (!$user->college_id))
        {
            $posts = $this->post->where('visiable',0)->orderBy($orderby,$direction)->get();
        }
        elseif($user->college_id)
        {
            $userIds = User::where('college_id',$user->college_id)->pluck('id')->toArray();

            $posts = $this->post->whereIn('user_id',$userIds)->whereIn('visiable',[0,1,2,3])
                ->orWhere('user_id',$user->id)->where('visiable',4)->orderBy($orderby,$direction)->get();
        }elseif ((!$user->college_id) && ($user->gender))
        {
            $userIds = User::where("gender",$user->gender)->pluck('id')->toArray();
            if($user->gender = 1)
            {
                $posts = $this->post->whereIn('user_id',$userIds)->whereIn('visiable',[0,2])
                    ->orWhere('user_id',$user->id)->where('visiable',4)->orderBy($orderby,$direction)->get();
            }else
            {
                $posts = $this->post->whereIn('user_id',$userIds)->whereIn('visiable',[0,3])
                    ->orWhere('user_id',$user->id)->where('visiable',4)->orderBy($orderby,$direction)->get();
            }

        }

        return $posts;
    }

    public function getPostListZero($search = null,$orderby = 'created_at', $direction = 'desc')
    {
        $posts = $this->post->where(function ($query) use($search){
                if(!empty($search))
                {
                    $query->whereHas('user',function ($queryUser) use ($search){
                            $queryUser->where('realname','LIKE','%'.$search.'%')
                                ->orWhere('nickname','LIKE','%'.$search.'%');
                        })
                        ->orWhereHas('user.college',function ($queryCollege) use ($search){
                            $queryCollege->where('name','LIKE','%'.$search.'%');
                        })
                        ->orWhere('content','LIKE','%'.$search.'%');
                }
            })
            ->where('visiable',0)->orderBy($orderby,$direction)->paginate(5);

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

    public function updatePost($inputs, $id,$picturePath = null)
    {
        $post = $this->post->where('id',$id)->firstOrFail();

        $Post = $this->save($post,$inputs,$picturePath);

        return $Post;
    }

    public function updatePostPicture($id, $picturePath = null)
    {
        $post = $this->post->where('id',$id)->firstOrFail();

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

        $post->save();

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

        if(isset($inputs['images']) && !empty($inputs['images']))
        {
            $post->pictures = implode(',',$inputs['images']);
        }

        if(isset($inputs['location']) && !empty($inputs['location']))
        {
            $post->location = implode(',',$inputs['location']);
//            $post->location = json_decode($inputs['location']);
        }

        if(isset($inputs['visiable']) && !empty($inputs['visiable']))
        {
            $post->visiable = $inputs['visiable'];
        }

        if(isset($inputs['video_url']) && !empty($inputs['video_url']))
        {
            $post->video_url = $inputs['video_url'];
        }

        if(isset($inputs['anonymous']) && !empty($inputs['anonymous']))
        {
            $post->anonymous = $inputs['anonymous'];
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

        if($diff<=0)
        {
            return '刚刚';
        }
        elseif (($diff>0) && ($diff<60))
        {

            return round($diff).'分钟前';
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

    public function getNoWesecretHotPost()
    {
        $res = [];
        $posts = $this->getPostListZero();

        $hotPost = [];

        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
//                $hots = $post->commentnum * 2 + $post->likenum;
                $hots = $post->commentnum;

                $hotPost[$post->id] = $hots;
            }

            arsort($hotPost);
            $res['status'] = 200;
            $res['postIds'] = $hotPost;
        }
        else
        {
            $res['status'] = 201;
        }

        return $res;
    }

    public function getHotPost()
    {
        $res = [];
//        $posts = Post::all();
        $posts = Post::paginate(10);

        $hotPost = [];

        if(!empty($posts))
        {
            foreach ($posts as $post)
            {
//                $hots = $post->commentnum * 2 + $post->likenum;
                $hots = $post->commentnum;

                $hotPost[$post->id] = $hots;
            }

            arsort($hotPost);
            $res['status'] = 200;
            $res['postIds'] = $hotPost;
        }
        else
        {
            $res['status'] = 201;
        }

        return $res;
    }
}