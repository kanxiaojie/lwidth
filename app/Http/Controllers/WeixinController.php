<?php

namespace App\Http\Controllers;

include_once "wxBizDataCrypt.php";

use App\Gender;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use WXBizDataCrypt;

class WeixinController extends Controller
{
    protected $userRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    public function login()
    {

    }

    public function useInfo()
    {
        $appid = 'wx3bf755439a8b5173';
        $sessionKey = 'ukjJb2IOwUfXnxzjlrNHBg==';

        $encryptedData="GgQOBnjaA78Cc+pMhOCtM6ZoS7lRHitdpEALXY69IIeNOE4UT0Klf1LLa7x4ZZnulPyLUQfCMM7SQu58YNc4BYdX/xnHze6LJfq0nDFKVZjhTxv0aIaSXQg2j4qe7wLnrnv49U2ogV1jgHVNN/iDu1YBAiEP+QgA4UQAB+lghBWTr4oatiCjsM85id+j1p7Ze+I7jcZPBKBCgZhW5fWk309Hxe/aR7QZRA6xJ1fOuITX3V01q9n3jPm2yo5GJH/Jr2jJ5kaYgMr7yBwoqRbjBeh/rp+EXj8JaoD7Lf0w+2+HgQe06/kruZtphW2mhQyt7mpgncP9QhoKMgL0VZgu7pI65q8/lDGH9JszpKLJ9WncP5cajr0+9IFsl581VrwxWAvPfs3TSv5okxlnwfBPpqBm+dBnpL8x2/WtYDXXR6QB2pMCMeK6MjV/5b001vQeJ22OwyNxFiIVUwAmVQJqdA==";

        $iv = '+1RXA4xYWH0BYscZHsHXNQ==';

        $pc = new wxBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        if ($errCode == 0) {
//            print($data. "\n");
            return json_decode($data,true);
        } else {
            return $errCode;
//            print($errCode . "\n");
        }
    }

    public function getOpenId($code, $appid, $secretid)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secretid."&js_code=".$code."&grant_type=authorization_code");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        $output = json_decode($output,TRUE);

        $session_key = $output["session_key"];
        curl_close($ch);

        return $session_key;
    }

    public function decryptUserInfo($appid,$sessionKey,$encryptedData,$iv)
    {
        $pc = new wxBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );

        if ($errCode == 0)
        {
            return json_decode($data,true);
        }
        else
        {
            return array();
        }
    }

    public function firstLogin(Request $request)
    {
        $code = $request->get('code');
        $encryptedData = $request->get('encryptedData');
        $iv = $request->get('iv');

        $appid = env('WEIXIN_APP_ID','wx7f02895dae7c918a');
        $secretid = env('WEIXIN_SECRET_ID','9bac8ed86a35fcd067e1d5d04365d399');

        $sessionKey = $this->getOpenId($code,$appid,$secretid);

//        $appid = 'wx3bf755439a8b5173';
//        $sessionKey = 'ukjJb2IOwUfXnxzjlrNHBg==';

//        $encryptedData="GgQOBnjaA78Cc+pMhOCtM6ZoS7lRHitdpEALXY69IIeNOE4UT0Klf1LLa7x4ZZnulPyLUQfCMM7SQu58YNc4BYdX/xnHze6LJfq0nDFKVZjhTxv0aIaSXQg2j4qe7wLnrnv49U2ogV1jgHVNN/iDu1YBAiEP+QgA4UQAB+lghBWTr4oatiCjsM85id+j1p7Ze+I7jcZPBKBCgZhW5fWk309Hxe/aR7QZRA6xJ1fOuITX3V01q9n3jPm2yo5GJH/Jr2jJ5kaYgMr7yBwoqRbjBeh/rp+EXj8JaoD7Lf0w+2+HgQe06/kruZtphW2mhQyt7mpgncP9QhoKMgL0VZgu7pI65q8/lDGH9JszpKLJ9WncP5cajr0+9IFsl581VrwxWAvPfs3TSv5okxlnwfBPpqBm+dBnpL8x2/WtYDXXR6QB2pMCMeK6MjV/5b001vQeJ22OwyNxFiIVUwAmVQJqdA==";
//
//        $iv = '+1RXA4xYWH0BYscZHsHXNQ==';

        $datas = $this->decryptUserInfo($appid,$sessionKey,$encryptedData,$iv);

        $data = array();
        $userInfo = [];

        if(!empty($datas))
        {
            $user = $this->userRepository->getUserByOpenId($datas['openId']);

            if(!$user)
            {
                $res = $this->userRepository->create($datas);
            }
            else
            {
                $res = $this->userRepository->update($datas,$user);
            }

            $token = Crypt::encrypt($datas['openId']);

            $data['id'] = $res->id;
            $data['nickName'] = $res->nickname;
            $data['avatarUrl'] = $res->avatarUrl;
            if(!empty($res->gender))
            {
                $data['gender'] = Gender::where('id',$res->gender)->first()->name;
            }
            else
            {
                $data['gender'] = '未知';
            }

            if(!empty($res->province_id))
            {
//                $data['province'] = $res->country->province->name;
                $data['province'] = "福建";
            }
            else
            {
                $data['province'] = '';
            }
            if(!empty($res->city_id))
            {
//                $data['city'] = $res->country->city->name;
                $data['city'] = "厦门";
            }else
            {
                $data['city'] = '';
            }
            if(!empty($res->country_id))
            {
//                $data['country'] = $res->country->name;
                $data['country'] = "中国";
            }else
            {
                $data['country'] = '';
            }

            $userInfo['wesecret'] = $token;
            $userInfo['userInfo'] = json_encode($data);

            return response()->json(['status'=> 200,'data' => $userInfo]);
//            return json_decode($userInfo);
        }
        else
        {
            return response()->json(['status'=>201,"wesecret" => "invalid code"]);
        }
    }


}
