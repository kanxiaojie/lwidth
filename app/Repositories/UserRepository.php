<?php namespace App\Repositories;


use App\Profile;
use App\User;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\QiniuRepository;


class UserRepository
{
    protected $user;
    protected $baseRepository;
    protected $qiniuRepository;
    /**
     * Create a new UserRepository instance.
     *
     * @param  App\Models\User $user
     * @param  App\Models\Role $role
     * @return void
     */
    public function __construct(
        User $user,
        BaseRepository $baseRepository,
        QiniuRepository $qiniuRepository
    )
    {
        $this->user = $user;
        $this->baseRepository = $baseRepository;
        $this->qiniuRepository = $qiniuRepository;
    }

    public function getUserByOpenId($openId)
    {
        $user = $this->user->where('openid',$openId)->first();

        return $user;
    }

    public function getUserById($id)
    {
        $user = $this->user->where('id',$id)->first();

        return $user;
    }

    public function create($inputs)
    {
        $user = new User();

       $res = $this->saveUser($inputs,$user);

        return $res;
    }

    public function update($inputs, $user)
    {
        $inputs['updateOpenId'] = 1;
       $res = $this->saveUser($inputs,$user);

        return $res;
    }

    public function saveUser($inputs,$user,$user_id = null)
    {
//        $res = array('status'=>1,'error'=>'');

        if(isset($inputs['openId']) && (!isset($inputs['updateOpenId'])))
        {
            $user->openid = $inputs['openId'];
        }

        if(isset($inputs['nickname']))
        {
            $user->nickname = $inputs['nickname'];
        }

        if(isset($inputs['gender']) && (!empty($inputs['gender'])))
        {
            $user->gender = $inputs['gender'];
        }

        if(isset($inputs['language']))
        {
            $user->language_id = 1;
        }

        if(isset($inputs['city']) && !empty($inputs['city']))
        {
            $user->city_id = $inputs['city'];
        }

        if(isset($inputs['province']) && !empty($inputs['province']))
        {
            $user->province_id = $inputs['province'];
        }

        if(isset($inputs['country']) && !empty($inputs['country']))
        {
            $user->country_id = $inputs['country'];
        }

        if(isset($inputs['avatarUrl']))
        {
            $user->avatarUrl = $inputs['avatarUrl'];
        }

        $user->save();

        if($user->profile)
        {
            $profile = $user->profile;
        }
        else
        {
            $profile = new Profile;
            $profile->user_id=intval($user->id);
        }

        $profile->save();

        return $user;
    }

    public function updateUser($inputs,$user,$picturePath = null)
    {

        if($picturePath)
        {
            if($user->pictures)
            {
                $user->pictures .= ','.$picturePath;
            }
            else
            {
                $user->pictures = $picturePath;
            }
        }

        if(isset($inputs['pictures']) && (count($inputs['pictures'])))
        {
            $user->pictures = implode(',',$inputs['pictures']);
        }

        if(isset($inputs['pictureOnWall']) && (count($inputs['pictureOnWall'])))
        {
            $user->pictureOnWall = $inputs['pictureOnWall'];
        }

        if(isset($inputs['avatarUrl']) && (!empty($inputs['avatarUrl'])))
        {
            $user->avatarUrl = $inputs['avatarUrl'];

            // 如果头像是放在七牛云上，那么从七牛上删除
            $pictureArray = explode('/', $input['avatarUrl']); 
            $key = $pictureArray[3];
            $start_key = substr($key, 0, 3);
            if ($start_key == 'tmp') {
                $deleteResult = $this->qiniuRepository->deleteImageFormQiniu($key);
            }
        }

        if(isset($inputs['realname']) && (!empty($inputs['realname'])))
        {
            $user->realname = $inputs['realname'];
        }

        if(isset($inputs['nickname']) && (!empty($inputs['nickname'])))
        {
            $user->nickname = $inputs['nickname'];
        }

        if(isset($inputs['college']) && (!empty($inputs['college'])))
        {
            $user->college_id = $inputs['college'];
        }


        if(isset($inputs['major']) && (!empty($inputs['major'])))
        {
            $user->major = $inputs['major'];
        }

        if(isset($inputs['wechat']) && (!empty($inputs['wechat'])))
        {
            $user->wechat = $inputs['wechat'];
        }

        if(isset($inputs['gender']) && (!empty($inputs['gender'])))
        {
            $user->gender = $inputs['gender'];
        }

        if(isset($inputs['qq']) && (!empty($inputs['qq'])))
        {
            $user->QQ = $inputs['qq'];
        }

        if(isset($inputs['weibo']) && (!empty($inputs['weibo'])))
        {
            $user->weibo = $inputs['weibo'];
        }

        if(isset($inputs['mobilePhone']) && (!empty($inputs['mobilePhone'])))
        {
            $user->phone = $inputs['mobilePhone'];
        }

        if(isset($inputs['grade']) && (!empty($inputs['grade'])))
        {
            $user->grade = $inputs['grade'];
        }

        $profile = Profile::where('user_id',$user->id)->first();

        if(!$profile)
        {
            $profile = new  Profile();
        }

        if(isset($inputs['userInfo']) && (!empty($inputs['userInfo'])))
        {
            if($inputs['userInfo']->realname)
            {
                $user->realname = $inputs['userInfo']->realname;
            }


            if($inputs['userInfo']->major)
            {
                $user->major = $inputs['userInfo']->major;
            }

            if($inputs['userInfo']->wechat)
            {
                $user->wechat = $inputs['userInfo']->wechat;
            }

            if($inputs['userInfo']->gender)
            {
                $user->gender = $inputs['userInfo']->gender;
            }

            if($inputs['userInfo']->qq)
            {
                $user->QQ = $inputs['userInfo']->qq;
            }

            if($inputs['userInfo']->weibo)
            {
                $user->weibo = $inputs['userInfo']->weibo;
            }

            if($inputs['userInfo']->mobilePhone)
            {
                $user->phone = $inputs['userInfo']->mobilePhone;
            }

            if($inputs['userInfo']->grade)
            {
                $user->grade = $inputs['userInfo']->grade;
            }

            if($inputs['userInfo']->birthday)
            {
                $profile->birthday = $inputs['userInfo']->birthday;

                $profile->age = $this->baseRepository->calcAge($inputs['userInfo']->birthday);
                $m = date('m',strtotime($inputs['userInfo']->birthday));
                $d = date('d',strtotime($inputs['userInfo']->birthday));
                $profile->constellation = $this->baseRepository->get_constellation($m,$d);
            }

            if($inputs['userInfo']->height)
            {
                $profile->height = $inputs['userInfo']->height;
            }

            if($inputs['userInfo']->weight)
            {
                $profile->weight = $inputs['userInfo']->weight;
            }

            if($inputs['userInfo']->hometown)
            {
                $profile->hometown = $inputs['userInfo']->hometown;
            }

            if($inputs['userInfo']->character)
            {
                $profile->character = $inputs['userInfo']->character;
            }

            if($inputs['userInfo']->hobby)
            {
                $profile->hobby = $inputs['userInfo']->hobby;
            }

            if($inputs['userInfo']->love_history)
            {
                $profile->love_history = $inputs['userInfo']->love_history;
            }

            if($inputs['userInfo']->love_selecting)
            {
                $profile->love_selecting = $inputs['userInfo']->love_selecting;
            }

        }

        $user->save();


        if (isset($inputs['birthday']) && (!empty($inputs['birthday'])))
        {
            $profile->birthday = $inputs['birthday'];

            $profile->age = $this->baseRepository->calcAge($inputs['birthday']);
            $m = date('m',strtotime($inputs['birthday']));
            $d = date('d',strtotime($inputs['birthday']));
            $profile->constellation = $this->baseRepository->get_constellation($m,$d);
        }

        if(isset($inputs['height']) && (!empty($inputs['height'])))
        {
            $profile->height = $inputs['height'];
        }

        if(isset($inputs['weight']) && (!empty($inputs['weight'])))
        {
            $profile->weight = $inputs['weight'];
        }

        if(isset($inputs['hometown']) && (!empty($inputs['hometown'])))
        {
            $profile->hometown = $inputs['hometown'];
        }

        if(isset($inputs['signature']) && (!empty($inputs['signature'])))
        {
            $profile->signature = $inputs['signature'];
        }

        if(isset($inputs['character']) && (!empty($inputs['character'])))
        {
            $profile->character = $inputs['character'];
        }

        if(isset($inputs['hobby']) && (!empty($inputs['hobby'])))
        {
            $profile->hobby = $inputs['hobby'];
        }

        if(isset($inputs['love_history']) && (!empty($inputs['love_history'])))
        {
            $profile->love_history = $inputs['love_history'];
        }

        if(isset($inputs['love_selecting']) && (!empty($inputs['love_selecting'])))
        {
            $profile->love_selecting = $inputs['love_selecting'];
        }

        $profile->save();


        return $user;

    }

    public function getPictures($search)
    {
        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }
        $users = User::where(function ($query) use($search, $search_gender){
                            if(!empty($search))
                            {
                                $query->whereHas('college',function ($queryCollege) use ($search, $search_gender){
                                        $queryCollege->where('name','LIKE','%'.$search.'%');
                                    })
                                    ->orWhere('nickname','LIKE','%'.$search.'%')
                                    ->orWhere('realname','LIKE','%'.$search.'%')
                                    ->orWhere('gender','LIKE','%'.$search_gender.'%');
                            }
                        })
                        
                        ->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->paginate(5);

        return $users;
    }

    // public function getMaleOrFemalePictures($wesecret, $search)
    // {
    //     $openid = Crypt::decrypt($wesecret);
    //     $user = $this->getUserByOpenId($openid);

    //     if($user->gender)
    //     {
    //         if($user->gender == 1)
    //         {
    //             $users1 = User::where('gender',2)->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->get();
    //             $users2 = User::where('gender',1)->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->get();
    //             $users3 = User::whereNotIn('gender',[1,2])->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->get();
                
    //             // $users = array_merge($users1, $users2, $users3);
    //             $users = $users1->merge($users2)->merge($users3);
    //         } elseif ($user->gender == 2){
    //             $users1 = User::where('gender',1)->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->get();
    //             $users2 = User::where('gender',2)->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->get();
    //             $users3 = User::whereNotIn('gender',[1,2])->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->get();

    //             $users = $users1->merge($users2)->merge($users3);
    //         } else {
    //             // $users = User::orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->get();
    //             $users = User::where(function ($query) use($search){
    //                             if(!empty($search))
    //                             {
    //                                 $query->whereHas('college',function ($queryCollege) use ($search){
    //                                         $queryCollege->where('name','LIKE','%'.$search.'%');
    //                                     })
    //                                     ->orWhere('nickname','LIKE','%'.$search.'%')
    //                                     ->orWhere('realname','LIKE','%'.$search.'%');
    //                             }
    //                         })
    //                         ->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->paginate(5);
                
    //         }
    //     }
    //     else
    //     {
    //         // $users = User::orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->get();
    //          $users = User::where(function ($query) use($search){
    //                             if(!empty($search))
    //                             {
    //                                 $query->whereHas('college',function ($queryCollege) use ($search){
    //                                         $queryCollege->where('name','LIKE','%'.$search.'%');
    //                                     })
    //                                     ->orWhere('nickname','LIKE','%'.$search.'%')
    //                                     ->orWhere('realname','LIKE','%'.$search.'%');
    //                             }
    //                         })
    //                         ->orderBy('praiseNums', 'desc')->orderBy('created_at', 'desc')->paginate(5);
    //     }
    //     return $users;
    // }
}
