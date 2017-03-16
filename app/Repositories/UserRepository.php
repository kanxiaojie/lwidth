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
        $res = array('status'=>1,'error'=>'');

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

        return $res;
    }
}
