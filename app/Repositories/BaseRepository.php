<?php

namespace App\Repositories;


use Illuminate\Support\Facades\Crypt;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class BaseRepository
{
    public function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);// "}"
            return $uuid;
        }
    }

    /**
     * @param $wesecret
     * @return string
     * @name $wesecret解密
     */
    public function decryptCode($wesecret)
    {
        $openid = '';
        try{
            $openid = Crypt::decrypt($wesecret);
        }catch (\Exception $exception){
            $openid = '';
        }

        return $openid;
    }

    /**
     * 生成上传凭证
     * @return string
     */
    private function getToken(){
        $accessKey= "WR4zSNb376JZyEq6TQx9pJ9DSherW9xFKO9Ls2zB";
        $secretKey= "z7_Jx8-sCMGUFmrP5bPqM_GOT2FiIq3AEeEoQKZE";
        $auth=new Auth($accessKey, $secretKey);
        $bucket= "life";//上传空间名称
        //设置put policy的其他参数
        //$opts=['callbackUrl'=>'http://www.callback.com/','callbackBody'=>'name=$(fname)&hash=$(etag)','returnUrl'=>"http://www.baidu.com"];
        return $auth->uploadToken($bucket);//生成token
    }

    public function uploadToQiniu($inputs)
    {
        $res = ['status' => '','picturePath' => ''];

        $token=$this->getToken();
        $uploadManager=new UploadManager();
//                $name=$_FILES['file']['name'];
        $name = $this->guid().'.'.$inputs['file']->getClientOriginalExtension();
        $filePath=$_FILES['file']['tmp_name'];
        $type=$_FILES['file']['type'];
        list($ret,$err)=$uploadManager->putFile($token,$name,$filePath,null,$type,false);
        if($err){
            $res['status'] = 201;
            return $res;
        }else{
            $picturePath = 'http://onh8wwwjp.bkt.clouddn.com/'.$ret["key"];
        }


        $res['picturePath'] = $picturePath;

        return $res;

    }

    public function calcAge($birthday) {
        $iage = 0;
        if (!empty($birthday)) {
            $year = date('Y',strtotime($birthday));
            $month = date('m',strtotime($birthday));
            $day = date('d',strtotime($birthday));

            $now_year = date('Y');
            $now_month = date('m');
            $now_day = date('d');

            if ($now_year > $year) {
                $iage = $now_year - $year - 1;
                if ($now_month > $month) {
                    $iage++;
                } else if ($now_month == $month) {
                    if ($now_day >= $day) {
                        $iage++;
                    }
                }
            }
        }
        return $iage;
    }

    public function get_constellation($month, $day)
    {
        $signs = array(
            array('20' => '水瓶座'), array('19' => '双鱼座'),
            array('21' => '白羊座'), array('20' => '金牛座'),
            array('21' => '双子座'), array('22' => '巨蟹座'),
            array('23' => '狮子座'), array('23' => '处女座'),
            array('23' => '天秤座'), array('24' => '天蝎座'),
            array('22' => '射手座'), array('22' => '摩羯座')
        );
        $key = (int)$month - 1;
        list($startSign, $signName) = each($signs[$key]);
        if ($day < $startSign) {
            $key = $month - 2 < 0 ? $month = 11 : $month -= 2;
            list($startSign, $signName) = each($signs[$key]);
        }
        return $signName;
    }
}