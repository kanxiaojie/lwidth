<?php

namespace App\Http\Controllers;

use App\BlackList;
use App\College;
use App\Country;
use App\Gender;
use App\Grade;
use App\PraiseUser;
use App\Profile;
use App\Repositories\BaseRepository;
use App\Repositories\UserRepository;
use App\Repositories\QiniuRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class UserController extends Controller
{
    protected $userRepository;
    protected $baseRepository;
    protected $qiniuRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        BaseRepository $baseRepository,
        QiniuRepository $qiniuRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->baseRepository = $baseRepository;
        $this->qiniuRepository = $qiniuRepository;
    }

    public function encryptCode(Request $request)
    {
        $code = $request->get('code');

        $secret = Crypt::encrypt($code);

        return response()->json(['key' => $secret]);
    }

    public function decryptCode(Request $request)
    {
        $secret = $request->get('secret');

        $code = Crypt::decrypt($secret);

        return response()->json(['key' => $code]);

    }

    public function updateUser(Request $request)
    {
        $inputs = $request->all();

        $openid = Crypt::decrypt($inputs['wesecret']);
        $input = $inputs['userInfo'];

        if (!empty($inputs['just_college_changed'])) {
            $just_college_changed = $inputs['just_college_changed'];
        } else {
            $just_college_changed = null;
        }

        $user = $this->userRepository->getUserByOpenId($openid);
        $old_college_id = $user->college_id;
        if($user)
        {
            $this->userRepository->updateUser($input,$user);

            if (!empty($just_college_changed)) {
                if ($user->interest_id == 1) {
                    $need_refresh_loves = 0;
                } elseif ($user->interest_id == 2) {
                    $old_college = College::find($old_college_id);
                    $college = College::find($input['college_id']);

                    if ($old_college->city->province->id == $college->city->province->id) {
                        $need_refresh_loves = 0;
                    } else {
                        $need_refresh_loves = 1;
                    }
                } elseif ($user->interest_id == 3) {
                    $old_college = College::find($old_college_id);
                    $college = College::find($input['college_id']);

                    if ($old_college->city->id == $college->city->id) {
                        $need_refresh_loves = 0;
                    } else {
                        $need_refresh_loves = 1;
                    }
                } else {
                    $need_refresh_loves = 1;
                }

                return response()->json(['status' => 200, 'need_refresh_loves' => $need_refresh_loves]);
            } else {
                return response()->json(['status' => 200]);
            }


        }
        else
        {
            return response()->json(['status'=>201,'message'=>'user not exist']);
        }
    }

    public function getUserInfo(Request $request, $id)
    {
        $user = $this->userRepository->getUserById($id);
        $wesecret = $request->get('wesecret');

        $userInfo = [];
        if($user)
        {
            $updateUser = $user;

            $userInfo['id'] = $updateUser->id ;
            // $userInfo['praise_nums'] = count(PraiseUser::where('praised_user_id',$updateUser->id)->get());
            $userInfo['praise_nums'] = $updateUser->praiseNums;
            $userInfo['nickname'] = $updateUser->nickname ;
            $userInfo['avatarUrl'] = $updateUser->avatarUrl ;

            $userInfo['background_image'] = 'http://lifecdn.collhome.com/user_background_image.jpg';

            $userInfo['gender_id'] = $updateUser->gender_id;
            if ($updateUser->gender_id == 1)
            {
                $userInfo['gender_name'] = "男";
            }elseif($updateUser->gender_id == 2)
            {
                $userInfo['gender_name'] = "女";
            }else
            {
                $userInfo['gender_name'] = "";
            }

            if(!empty($updateUser->pictures))
            {
                if(substr(trim($updateUser->pictures),-1) == ',')
                {
                    $userInfo['pictures'] = explode(',',$updateUser->pictures);
                }else
                {
                    $userInfo['pictures'] = explode(',',$updateUser->pictures);
                }

            }
            else
            {
                $userInfo['pictures'] = [];
            }


            if($updateUser->province_id)
            {
                $userInfo['province'] = $updateUser->province_id;
            }
            else
            {
                $userInfo['province'] = "";
            }

            if($updateUser->city_id)
            {
                $userInfo['city'] = $updateUser->city_id;
            }
            else
            {
                $userInfo['city'] = "";
            }


            if($updateUser->country_id)
            {
                $userInfo['country'] = $updateUser->country_id;
            }
            else
            {
                $userInfo['country'] = "中国";
            }

            if($updateUser->realname)
            {
                $userInfo['realname'] = $updateUser->realname;
            }
            else
            {
                $userInfo['realname'] = '';
            }

            if(!$updateUser->college_id)
            {
                $userInfo['college_id'] = '';
                $userInfo['college_name'] = '';
            }
            else
            {
                $userInfo['college_id'] = $updateUser->college_id;
                $userInfo['college_name'] = $updateUser->college->name;
            }

            if(!$updateUser->interest_id)
            {
                $userInfo['interest_id'] = '';
                $userInfo['interest_name'] = '';
            }
            else
            {
                $userInfo['interest_id'] = $updateUser->interest_id;
                $userInfo['interest_name'] = $updateUser->interest->name;
            }


            if($updateUser->major)
            {
                $userInfo['major'] = $updateUser->major;
            }
            else
            {
                $userInfo['major'] = '';
            }

            if($updateUser->grade)
            {
                $userInfo['grade'] = $updateUser->grade;
            }
            else
            {
                $userInfo['grade'] = '';
            }

            if($updateUser->wechat)
            {
                $userInfo['wechat'] = $updateUser->wechat;
            }else
            {
                $userInfo['wechat'] = '';
            }

            if($updateUser->QQ)
            {
                $userInfo['qq'] = $updateUser->QQ;
            }else
            {
                $userInfo['qq'] = '';
            }

            if($updateUser->weibo)
            {
                $userInfo['weibo'] = $updateUser->weibo;
            }else
            {
                $userInfo['weibo'] = '';
            }

            if($updateUser->phone)
            {
                $userInfo['mobilePhone'] = $updateUser->phone;
            }else
            {
                $userInfo['mobilePhone'] = '';
            }
            $userInfo['role'] = $updateUser->role;
            $userInfo['trust'] = $updateUser->trust;
            $userInfo['available'] = $updateUser->available;


            $profile = Profile::where('user_id',$updateUser->id)->first();

            if($profile->birthday)
            {
                $userInfo['birthday'] = $profile->birthday;
            }
            else
            {
                $userInfo['birthday'] = '';
            }

            if($profile->height)
            {
                $userInfo['height'] = $profile->height;
            }
            else
            {
                $userInfo['height'] = '';
            }

            if($profile->weight)
            {
                $userInfo['weight'] = $profile->weight;
            }
            else
            {
                $userInfo['weight'] = '';
            }

            if($profile->hometown)
            {
                $userInfo['hometown'] = $profile->hometown;
            }
            else
            {
                $userInfo['hometown'] = '';
            }

            if($profile->signature)
            {
                $userInfo['signature'] = $profile->signature;
            }
            else
            {
                $userInfo['signature'] = '';
            }

            if($profile->character)
            {
                $userInfo['character'] = $profile->character;
            }
            else
            {
                $userInfo['character'] = '';
            }

            if($profile->hobby)
            {
                $userInfo['hobby'] = $profile->hobby;
            }
            else
            {
                $userInfo['hobby'] = '';
            }

            if($profile->love_history)
            {
                $userInfo['love_history'] = $profile->love_history;
            }
            else
            {
                $userInfo['love_history'] = '';
            }

            if($profile->love_selecting)
            {
                $userInfo['love_selecting'] = $profile->love_selecting;
            }
            else
            {
                $userInfo['love_selecting'] = '';
            }

            if($profile->age)
            {
                $userInfo['age'] = $profile->age;
            }else
            {
                $userInfo['age'] = '';
            }
            $userInfo['constellation'] = $profile->constellation;
            if($wesecret)
            {
                $openid = $this->baseRepository->decryptCode($wesecret);
                $whoPraise = $this->userRepository->getUserByOpenId($openid);

                $praiseUser = PraiseUser::where('praise_user_id',$whoPraise->id)->where('praised_user_id',$user->id)->first();
                if($praiseUser)
                {
                    $userInfo['if_my_praise'] = 1;
                }else
                {
                    $userInfo['if_my_praise'] = 0;
                }
                $inBlacklistUserIds = BlackList::where('own_user_id', $whoPraise->id)->pluck('black_user_id')->toArray();
                if (in_array($id, $inBlacklistUserIds)) {
                    $userInfo['inMyBlackList'] = 1;
                } else {
                    $userInfo['inMyBlackList'] = 0;
                }
            }
            else
            {
                $userInfo['if_my_praise'] = 0;
                $userInfo['inMyBlackList'] = 0;
            }



            return response()->json(['status'=>200,'data'=>$userInfo]);
        }else
        {
            return response()->json(['status'=>201,'message'=>'user not exist']);
        }
    }

    public function getUserInfoByOpenId(Request $request)
    {
        $inputs = $request->all();
        $openid = Crypt::decrypt($inputs['wesecret']);
        $user = $this->userRepository->getUserByOpenId($openid);

        $userInfo = [];
        if ($user)
        {
            $updateUser = $user;

            $userInfo['id'] = $updateUser->id ;
            // $userInfo['praise_nums'] = count(PraiseUser::where('praised_user_id',$updateUser->id)->get());
            $userInfo['praise_nums'] = $updateUser->praiseNums;
            $userInfo['nickname'] = $updateUser->nickname ;
            $userInfo['avatarUrl'] = $updateUser->avatarUrl ;
            $userInfo['pictureOnWall'] = $updateUser->pictureOnWall ;

            $userInfo['gender_id'] = $updateUser->gender_id;
            if ($updateUser->gender_id == 1)
            {
                $userInfo['gender_name'] = "男";
            }elseif($updateUser->gender_id == 2)
            {
                $userInfo['gender_name'] = "女";
            }else
            {
                $userInfo['gender_name'] = "";
            }

            if(!empty($updateUser->pictures))
            {
                if(substr(trim($updateUser->pictures),-1) == ',')
                {
                    $userInfo['pictures'] = explode(',',$updateUser->pictures);
                }else
                {
                    $userInfo['pictures'] = explode(',',$updateUser->pictures);
                }

            }
            else
            {
                $userInfo['pictures'] = [];
            }

            if($updateUser->province_id)
            {
                $userInfo['province'] = $updateUser->province_id;
            }
            else
            {
                $userInfo['province'] = "";
            }

            if($updateUser->city_id)
            {
                $userInfo['city'] = $updateUser->city_id;
            }
            else
            {
                $userInfo['city'] = "";
            }


            if($updateUser->country_id)
            {
                $userInfo['country'] = $updateUser->country_id;
            }
            else
            {
                $userInfo['country'] = "中国";
            }

            if($updateUser->realname)
            {
                $userInfo['realname'] = $updateUser->realname;
            }
            else
            {
                $userInfo['realname'] = '';
            }

            if(!$updateUser->college_id)
            {
                $userInfo['college_id'] = '';
                $userInfo['college_name'] = '';
            }
            else
            {
                $userInfo['college_id'] = $updateUser->college_id;
                $userInfo['college_name'] = $updateUser->college->name;
            }

            if(!$updateUser->interest_id)
            {
                $userInfo['interest_id'] = '';
                $userInfo['interest_name'] = '';
            }
            else
            {
                $userInfo['interest_id'] = $updateUser->interest_id;
                $userInfo['interest_name'] = $updateUser->interest->name;
            }

            if($updateUser->major)
            {
                $userInfo['major'] = $updateUser->major;
            }
            else
            {
                $userInfo['major'] = '';
            }

            if($updateUser->grade)
            {
                $userInfo['grade'] = $updateUser->grade;
            }
            else
            {
                $userInfo['grade'] = '';
            }

            if($updateUser->wechat)
            {
                $userInfo['wechat'] = $updateUser->wechat;
            }else
            {
                $userInfo['wechat'] = '';
            }

            if($updateUser->QQ)
            {
                $userInfo['qq'] = $updateUser->QQ;
            }else
            {
                $userInfo['qq'] = '';
            }

            if($updateUser->weibo)
            {
                $userInfo['weibo'] = $updateUser->weibo;
            }else
            {
                $userInfo['weibo'] = '';
            }

            $userInfo['role'] = $user->role;
            $userInfo['trust'] = $user->trust;
            $userInfo['available'] = $user->available;
            $userInfo['disabled_reason'] = $user->disabled_reason;

            if($updateUser->phone)
            {
                $userInfo['mobilePhone'] = $updateUser->phone;
            }else
            {
                $userInfo['mobilePhone'] = '';
            }

            $profile = Profile::where('user_id',$updateUser->id)->first();

            if($profile->birthday)
            {
                $userInfo['birthday'] = $profile->birthday;
            }
            else
            {
                $userInfo['birthday'] = '';
            }

            if($profile->height)
            {
                $userInfo['height'] = $profile->height;
            }
            else
            {
                $userInfo['height'] = '';
            }

            if($profile->weight)
            {
                $userInfo['weight'] = $profile->weight;
            }
            else
            {
                $userInfo['weight'] = '';
            }

            if($profile->hometown)
            {
                $userInfo['hometown'] = $profile->hometown;
            }
            else
            {
                $userInfo['hometown'] = '';
            }

            if($profile->signature)
            {
                $userInfo['signature'] = $profile->signature;
            }
            else
            {
                $userInfo['signature'] = '';
            }

            if($profile->character)
            {
                $userInfo['character'] = $profile->character;
            }
            else
            {
                $userInfo['character'] = '';
            }

            if($profile->hobby)
            {
                $userInfo['hobby'] = $profile->hobby;
            }
            else
            {
                $userInfo['hobby'] = '';
            }

            if($profile->love_history)
            {
                $userInfo['love_history'] = $profile->love_history;
            }
            else
            {
                $userInfo['love_history'] = '';
            }

            if($profile->love_selecting)
            {
                $userInfo['love_selecting'] = $profile->love_selecting;
            }
            else
            {
                $userInfo['love_selecting'] = '';
            }

            if($profile->age)
            {
                $userInfo['age'] = $profile->age;
            }else
            {
                $userInfo['age'] = '';
            }

            $userInfo['constellation'] = $profile->constellation;

            return response()->json(['status'=>200,'data'=>$userInfo]);
        }
        else{
            return response()->json(['status'=>201,'message'=>'user not exist']);
        }
    }

    public function uploadUserImage(Request $request)
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
                $user = $this->userRepository->updateUser($inputs,$user,$res['picturePath']);

                if($user)
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
            return response()->json(['status' => 201,'message' => 'User does not exist']);
        }
    }

    public function getPictures(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $search = $request->get('search');
        // if($wesecret)
        // {
        //     $users = $this->userRepository->getMaleOrFemalePictures($wesecret, $search);
        // }else
        // {
        //     $users = $this->userRepository->getPictures($search);
        // }

        $users = $this->userRepository->getPictures($search);
        

        // $data = [];
        $datas = [];

        if($users)
        {
            foreach ($users as $user)
            {
                if($user->available == 1 && $user->pictureOnWall == 1 && $user->role == 1)
                {
                    if (!empty($user->pictures) || !empty($user->avatarUrl)) {
                        $data = [];       

                        $data['id'] = $user->id;
                        $data['nickname'] = $user->nickname;
                        $data['gender_id'] = $user->gender_id;
                        $data['gender_name'] = $user->gender->name;
                        // $data['gender'] = $user->gender;
                        $data['avatarUrl'] = $user->avatarUrl;


                        if(empty($user->college_id)) {
                            $data['college_name'] = '';
                        } else {
                            $data['college_name'] = $user->college->name;
                            // $data['college_name'] = College::find($user->college_id)->name;
                            // $data['college_name'] = College::where('id',(int)($user->college_id))->first()->name;             
                        }

                        if(empty($user->pictures))
                        { 
                            $data['pictures'] = [$user->avatarUrl];                  
                        } else {
                            $data['pictures'] = explode(',',$user->pictures);
                        }

                        $datas[] = $data;
                    }
                }

            }

            return response()->json(['status' => 200,'data' => $datas]);
        }else
        {
            return response()->json(['status' => 200,'data' => $datas]);
        }

    }

    public function deletePicture(Request $request)
    {
        $inputs = $request->all();
        $input = $inputs['userInfo'];

        $openid = Crypt::decrypt($inputs['wesecret']);

        $user = $this->userRepository->getUserByOpenId($openid);
        if ($user)
        {
            $user = $this->userRepository->getUserById($user->id);

            $user->pictures = implode(',', $input['remain_pictures']);
            $user->save();

            // 从七牛云上删除照片  $input['the_delete_picture']
            if(!empty($input['the_delete_picture'])){
                $pictureArray = explode('/', $input['the_delete_picture']); 
                $key = $pictureArray[3]."/".$pictureArray[4];
                $deleteResult = $this->qiniuRepository->deleteImageFormQiniu($key);
            }





            // if(!empty($user->pictures))
            // {
            //     $pictures = explode(',',$user->pictures);
            //     if(in_array($inputs['picture'],$pictures))
            //     {
            //         $key = array_search($inputs['picture'],$pictures);

            //         array_splice($pictures,$key,1);

            //         $user->pictures = implode(',',$pictures);
            //         $user->save();

            //         return response()->json(['status' => 200]);
            //     }else
            //     {
            //         return response()->json(['status' => 201,'message' => $inputs['picture'].' does not exist in user pictures']);
            //     }
            // }else
            // {
            //     return response()->json(['status' => 201,'message' => "User's pictures do not exist"]);

            // }

        }
        else
        {
            return response()->json(['status' => 201,'message' => 'User does not exist']);
        }
    }

    public function addOrRemoveBlackLists(Request $request)
    {
        $objectUser_id = $request->get('objectUser_id');
        $inBlackLists = $request->get('inBlacklists');
        $wesecret = $request->get('wesecret');
        $openid = Crypt::decrypt($wesecret);

        $user = $this->userRepository->getUserByOpenId($openid);
        if ($user)
        {
            $blackUser = User::where('id',$objectUser_id)->first();
            if($blackUser)
            {
                $blacklist = BlackList::where('own_user_id',$user->id)
                ->where('black_user_id',$objectUser_id)->first();
                if($inBlackLists == 1)
                {
                    if(!$blacklist)
                    {
                        $blacklist = new BlackList();
                        $blacklist->own_user_id = $user->id;
                        $blacklist->black_user_id = $objectUser_id;
                        $blacklist->save();
                    }
                }elseif ($inBlackLists == 0)
                {
                    if($blacklist)
                    {
                        $blacklist->delete();
                    }
                }

                return response()->json(['status'=>200,'message'=>'successful.']);
            }
            else
            {
                return response()->json(['status' => 201,'message' => 'Black user does not exist']);
            }
        }
        else
        {
            return response()->json(['status' => 201,'message' => 'User does not exist']);
        }
    }

    public function getBlackLists(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $openid = Crypt::decrypt($wesecret);
        $data = [];
        $datas = [];

        $user = $this->userRepository->getUserByOpenId($openid);
        if ($user)
        {
            $blackListUsers = BlackList::where('own_user_id',$user->id)->get();
            if($blackListUsers)
            {
                foreach ($blackListUsers as $blackListUser)
                {
                    $blackUser = User::where('id',$blackListUser->black_user_id)->first();
                    $data['id'] = $blackUser->id;
                    $data['nickname'] = $blackUser->nickname;
                    $data['avatarUrl'] = $blackUser->avatarUrl;
                    if (!$blackUser->gender)
                    {
                        $data['gender_name'] = "";
                    }elseif($blackUser->gender == 1)
                    {
                        $data['gender_name'] = "男";
                    }else
                    {
                        $data['gender_name'] = "女";
                    }

                    if(!$blackUser->college_id)
                    {
                        $data['college_name'] = '';
                    }
                    else
                    {
                        $data['college_name'] = College::where('id',(int)($blackUser->college_id))->first()->name;
                    }
                    $datas[] = $data;
                }
            }

            return response()->json(['status'=>200,'message'=>'successful.','data'=>$datas]);
        }
        else
        {
            return response()->json(['status' => 201,'message' => 'User does not exist']);
        }
    }







    // 后台管理api-------------------------------------------------------------------------------------------------------------------------------------------
    
    public function getUsers(Request $request)
    {
        $users = User::where('role', 1)->where('trust', '>', -1)->paginate(10);
        $allUsers = User::where('role', 1)->where('trust', '>', -1)->get();
        $dataLength = count($allUsers);
        $datas = [];
        foreach ($users as $user) {
            $userInfo = [];
            $updateUser = $user;
            $userInfo['id'] = $updateUser->id ;
            // $userInfo['praise_nums'] = count(PraiseUser::where('praised_user_id',$updateUser->id)->get());
            $userInfo['praise_nums'] = $updateUser->praiseNums;
            $userInfo['nickname'] = $updateUser->nickname ;
            $userInfo['avatarUrl'] = $updateUser->avatarUrl ;
            $userInfo['background_image'] = 'http://lifecdn.collhome.com/user_background_image.jpg';
            $userInfo['gender_id'] = $updateUser->gender_id;
            if ($updateUser->gender_id == 1)
            {
                $userInfo['gender_name'] = "男";
            }elseif($updateUser->gender_id == 2)
            {
                $userInfo['gender_name'] = "女";
            }else
            {
                $userInfo['gender_name'] = "";
            }
            if(!empty($updateUser->pictures))
            {
                if(substr(trim($updateUser->pictures),-1) == ',')
                {
                    $userInfo['pictures'] = explode(',',$updateUser->pictures);
                }else
                {
                    $userInfo['pictures'] = explode(',',$updateUser->pictures);
                }
            }
            else
            {
                $userInfo['pictures'] = [];
            }
            if($updateUser->province_id)
            {
                $userInfo['province'] = $updateUser->province_id;
            }
            else
            {
                $userInfo['province'] = "";
            }
            if($updateUser->city_id)
            {
                $userInfo['city'] = $updateUser->city_id;
            }
            else
            {
                $userInfo['city'] = "";
            }
            if($updateUser->country_id)
            {
                $userInfo['country'] = $updateUser->country_id;
            }
            else
            {
                $userInfo['country'] = "中国";
            }
            if($updateUser->realname)
            {
                $userInfo['realname'] = $updateUser->realname;
            }
            else
            {
                $userInfo['realname'] = '';
            }
            if(!$updateUser->college_id)
            {
                $userInfo['college_id'] = '';
                $userInfo['college_name'] = '';
            }
            else
            {
                $userInfo['college_id'] = $updateUser->college_id;
                $userInfo['college_name'] = $updateUser->college->name;
            }
            if(!$updateUser->interest_id)
            {
                $userInfo['interest_id'] = '';
                $userInfo['interest_name'] = '';
            }
            else
            {
                $userInfo['interest_id'] = $updateUser->interest_id;
                $userInfo['interest_name'] = $updateUser->interest->name;
            }
            if($updateUser->major)
            {
                $userInfo['major'] = $updateUser->major;
            }
            else
            {
                $userInfo['major'] = '';
            }
            if($updateUser->grade)
            {
                $userInfo['grade'] = $updateUser->grade;
            }
            else
            {
                $userInfo['grade'] = '';
            }
            if($updateUser->wechat)
            {
                $userInfo['wechat'] = $updateUser->wechat;
            }else
            {
                $userInfo['wechat'] = '';
            }
            if($updateUser->QQ)
            {
                $userInfo['qq'] = $updateUser->QQ;
            }else
            {
                $userInfo['qq'] = '';
            }
            if($updateUser->weibo)
            {
                $userInfo['weibo'] = $updateUser->weibo;
            }else
            {
                $userInfo['weibo'] = '';
            }
            if($updateUser->phone)
            {
                $userInfo['mobilePhone'] = $updateUser->phone;
            }else
            {
                $userInfo['mobilePhone'] = '';
            }
            $userInfo['role'] = $updateUser->role;
            $userInfo['trust'] = $updateUser->trust;
            $userInfo['available'] = $updateUser->available;
            $userInfo['disabled_reason'] = $updateUser->disabled_reason;
            $profile = Profile::where('user_id',$updateUser->id)->first();
            if($profile->birthday)
            {
                $userInfo['birthday'] = $profile->birthday;
            }
            else
            {
                $userInfo['birthday'] = '';
            }
            if($profile->height)
            {
                $userInfo['height'] = $profile->height;
            }
            else
            {
                $userInfo['height'] = '';
            }
            if($profile->weight)
            {
                $userInfo['weight'] = $profile->weight;
            }
            else
            {
                $userInfo['weight'] = '';
            }
            if($profile->hometown)
            {
                $userInfo['hometown'] = $profile->hometown;
            }
            else
            {
                $userInfo['hometown'] = '';
            }
            if($profile->signature)
            {
                $userInfo['signature'] = $profile->signature;
            }
            else
            {
                $userInfo['signature'] = '';
            }
            if($profile->character)
            {
                $userInfo['character'] = $profile->character;
            }
            else
            {
                $userInfo['character'] = '';
            }
            if($profile->hobby)
            {
                $userInfo['hobby'] = $profile->hobby;
            }
            else
            {
                $userInfo['hobby'] = '';
            }
            if($profile->love_history)
            {
                $userInfo['love_history'] = $profile->love_history;
            }
            else
            {
                $userInfo['love_history'] = '';
            }
            if($profile->love_selecting)
            {
                $userInfo['love_selecting'] = $profile->love_selecting;
            }
            else
            {
                $userInfo['love_selecting'] = '';
            }
            if($profile->age)
            {
                $userInfo['age'] = $profile->age;
            }else
            {
                $userInfo['age'] = '';
            }
            $userInfo['constellation'] = $profile->constellation;
           
            
            $datas[] = $userInfo;
        }
        return response()->json(['status'=>200,'data'=>$datas, 'dataLength' => $dataLength]);
        
    }
    public function editUser(Request $request) {
        $params = $request->get('params');
        $user = User::find($params['id']);
        $user->trust = $params['trust'];
        $user->available = $params['available'];
        $user->disabled_reason = $params['disabled_reason'];
        $user->save();
        
        return response()->json(['status'=>200,'user_id'=>$user->id]);
    }

    
}
