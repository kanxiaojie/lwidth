<?php namespace App\Repositories;


use App\Profile;
use App\User;

class UserRepository
{
    protected $user;
    /**
     * Create a new UserRepository instance.
     *
     * @param  App\Models\User $user
     * @param  App\Models\Role $role
     * @return void
     */
    public function __construct(
        User $user
    )
    {
        $this->user = $user;
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

        if(isset($inputs['nickName']))
        {
            $user->nickname = $inputs['nickName'];
        }

        if(isset($inputs['gender']))
        {
            $user->gender = $inputs['gender'];
        }

        if(isset($inputs['language']))
        {
            $user->language_id = 1;
        }

        if(isset($inputs['city']))
        {
            $user->city_id = 1;
            $user->province_id = 1;
            $user->country_id = 1;
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

    public function updateUser($inputs,$user,$picturePath)
    {

        $user->pictures = $picturePath;

        if(isset($inputs['realname']) && (!empty($inputs['realname'])))
        {
            $user->realname = $inputs['realname'];
        }

        if(isset($inputs['college']) && (!empty($inputs['college'])))
        {
            $user->college_id = 1;
        }

        if(isset($inputs['major']) && (!empty($inputs['major'])))
        {
            $user->major = $inputs['major'];
        }

        if(isset($inputs['wechat']) && (!empty($inputs['wechat'])))
        {
            $user->wechat = $inputs['wechat'];
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

        $user->save();

        $profile = Profile::where('user_id',$user->id)->first();

        if(!$profile)
        {
            $profile = new  Profile();
        }

        if (isset($inputs['birthday']) && (!empty($inputs['birthday'])))
        {
            $profile->birthday = $inputs['birthday'];
            //推算年纪,生肖
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

    public function getPictures()
    {
        $users = User::all();

        return $users;
    }
}
