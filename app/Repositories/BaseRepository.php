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

    public function decryptCode($wesecret)
    {
        $openid = Crypt::decrypt($wesecret);

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
        $bucket= "lovewall";//上传空间名称
        //设置put policy的其他参数
        //$opts=['callbackUrl'=>'http://www.callback.com/','callbackBody'=>'name=$(fname)&hash=$(etag)','returnUrl'=>"http://www.baidu.com"];
        return $auth->uploadToken($bucket);//生成token
    }

    public function uploadToQiniu($inputs)
    {
        $res = ['status' => '','picturePath' => ''];

        $picturePath = '';

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
            $picturePath = 'http://onh8wwwjp.bkt.clouddn.com/'.$ret["key"].','.$picturePath;
        }


        $res['picturePath'] = $picturePath;

        return $res;

    }
}