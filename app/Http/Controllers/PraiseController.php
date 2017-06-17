<?php

namespace App\Http\Controllers;

use App\PraiseUser;
use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PraiseRepository;
use App\Repositories\UserRepository;
use App\User;
use Illuminate\Http\Request;

class PraiseController extends Controller
{
    protected $praiseRepository;
    protected $baseRepository;
    protected $userRepository;

    public function __construct(
        PraiseRepository $praiseRepository,
        BaseRepository $baseRepository,
        UserRepository $userRepository
    )
    {
        $this->praiseRepository = $praiseRepository;
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
    }

    public function praiseToComment(Request $request, $id)
    {
        $inputs = [];
        $inputs['wesecret'] = $request->get('wesecret');
        $inputs['praise'] = $request->get('praise');

        $openid = $this->baseRepository->decryptCode($inputs['wesecret']);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }
        else
        {
            $inputs['user_id'] = $user->id;
            $res = $this->praiseRepository->praiseToComment($inputs,$id);

            if ($res['status'] == 200)
            {
                return response()->json(['status' => 200]);
            }else
            {
                return response()->json(['status' => 201,'message' => 'Praise failed,please check the argument!']);
            }

        }
    }

    public function praiseToPost(Request $request, $id)
    {
        $inputs = [];
        $inputs['wesecret'] = $request->get('wesecret');
        $inputs['praise'] = $request->get('praise');

        $openid = $this->baseRepository->decryptCode($inputs['wesecret']);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }
        else
        {
            $inputs['user_id'] = $user->id;
            $res = $this->praiseRepository->praiseToPost($inputs,$id);

            if ($res['status'] == 200)
            {
                return response()->json(['status' => 200]);
            }else
            {
                return response()->json(['status' => 201,'message' => 'Praise failed,please check the argument!']);
            }

        }

    }

    public function praiseToReplies(Request $request, $id)
    {
        $inputs = [];
        $inputs['wesecret'] = $request->get('wesecret');
        $inputs['praise'] = $request->get('praise');

        $openid = $this->baseRepository->decryptCode($inputs['wesecret']);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }
        else
        {
            $inputs['user_id'] = $user->id;
            $res = $this->praiseRepository->praiseToReply($inputs,$id);

            if ($res['status'] == 200)
            {
                return response()->json(['status' => 200]);
            }else
            {
                return response()->json(['status' => 201,'message' => 'Praise failed,please check the argument!']);
            }

        }
    }

    public function praiseToUser(Request $request,$id)
    {
        $inputs = [];
        $inputs['wesecret'] = $request->get('wesecret');
        $inputs['praise'] = $request->get('praise');

        $openid = $this->baseRepository->decryptCode($inputs['wesecret']);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'user does not exist!']);
        }
        else
        {
            $praisedUser = User::where('id',$id)->first();
            if($praisedUser)
            {
                if($inputs['praise'] == 1)
                {
                    $praisedToUser = PraiseUser::where('praise_user_id',$user->id)
                        ->where('praised_user_id',$praisedUser->id)->first();
                    if(!$praisedToUser)
                    {
                        $praiseToUser = new PraiseUser();
                        $praiseToUser->praise_user_id = $user->id;
                        $praiseToUser->praised_user_id = $praisedUser->id;
                        $praiseToUser->save();

                        $praisedUser->praiseNums = $praisedUser->praiseNums + 1;
                        $praisedUser->save();
                    }
                }else
                {
                    $praisedToUser = PraiseUser::where('praise_user_id',$user->id)
                        ->where('praised_user_id',$praisedUser->id)->first();

                    if ($praisedToUser) {
                        $praisedToUser->delete();
                        
                        $praisedUser->praiseNums = $praisedUser->praiseNums - 1;
                        $praisedUser->save();
                    }

                    // if($praisedToUsers)
                    // {
                    //     foreach ($praisedToUsers as $praisedToUser)
                    //     {
                    //         $praisedToUser->delete();
                    //     }
                    // }
                }

                return response()->json(['status' =>200,'message' => 'success']);
            }
            else
            {
                return response()->json(['status' => 201,'message' => 'The user you praise does not exist.']);
            }

        }
    }

    public function getPraiseMeUsers(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $search = $request->get('search');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if($search == '男') {
            $search_gender = 1;
        } elseif($search == '女') {
            $search_gender = 2;
        } else {
            $search_gender = '哈哈哈';
        }

        if($user)
        {
            $datas = array();

            // $praiseToUsers = PraiseUser::where('praised_user_id',$user->id)->get();
            $praiseUserIds = PraiseUser::where('praised_user_id',$user->id)->pluck('praise_user_id')->toArray();

            $praiseToUsers = User::where(function ($query) use($search, $search_gender){
                                if(!empty($search))
                                {
                                    $query->whereHas('college',function ($queryCollege) use ($search){
                                            $queryCollege->where('name','LIKE','%'.$search.'%');
                                        })
                                        ->orWhere('nickname','LIKE','%'.$search.'%')
                                        ->orWhere('realname','LIKE','%'.$search.'%')
                                        ->orWhere('gender','LIKE','%'.$search_gender.'%');
                                }
                            })
                            ->whereIn('id',$praiseUserIds)
                            ->orderBy('created_at', 'desc')->paginate(5);

            if(count($praiseToUsers))
            {
                foreach ($praiseToUsers as $praiseToUser)
                {
                    // $praise_user = User::where('id',$praiseToUser->praise_user_id)->first();
                    $data = array();

                    $data['id']=$praise_user->id;
                    $data['nickname']=$praise_user->nickname;
                    $data['avatarUrl']=$praise_user->avatarUrl;
                    $data['college_name']=$praise_user->college->name;

                    if (!$praise_user->gender)
                    {
                        $data['gender_name'] = "";
                    }elseif($praise_user->gender == 1)
                    {
                        $data['gender_name'] = "男";
                    }else
                    {
                        $data['gender_name'] = "女";
                    }

                    $datas[] = $data;
                }
            }

            return response(['status'=>200,'message'=>'successful.','data'=>$datas]);
        }
        else
        {
            return response()->json(['status'=>201,'message' => 'User does not exist,please check the argument!']);
        }
    }


}
