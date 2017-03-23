<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class UploadToQiniuController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        return view('images.upload');
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

    public function uploadToQiniu()
    {
        $data = array();
        $token=$this->getToken();
        $uploadManager=new UploadManager();
        $name=$_FILES['file']['name'];
        $filePath=$_FILES['file']['tmp_name'];
        $type=$_FILES['file']['type'];
        list($ret,$err)=$uploadManager->putFile($token,$name,$filePath,null,$type,false);
        if($err){//上传失败
            var_dump($err);
            return response()->json(['name' => 'Abigail', 'state' => 'CA']);//返回错误信息到上传页面
        }else{//成功
            //添加信息到数据库
            var_dump($ret['key']);
//            $data['path'] = "http://on4a1hdp1.bkt.clouddn.com/";
//            return response()->json(["path" => 'http://on4a1hdp1.bkt.clouddn.com/'.$ret["key"], 'state' => 'success']);//返回结果到上传页面
            return 'http://on9ea4hzu.bkt.clouddn.com/'.$ret["key"];//返回结果到上传页面
        }
    }

}
