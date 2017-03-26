<?php

namespace App\Http\Controllers;

use App\Repositories\PostRepository;
use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class UploadToQiniuController extends Controller
{
    protected $postRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PostRepository $postRepository
    )
    {
        $this->postRepository = $postRepository;
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

    public function uploadToQiniu(Request $request)
    {
        $inputs = array();

        $inputs['wesecret'] = $request->input('wesecret');
        $inputs['content'] = $request->input('content');
        $inputs['title'] = $request->input('title');

        $openid = Crypt::decrypt($inputs['wesecret']);

        $user = User::where('openid',$openid)->first();
        if($user)
        {
            $inputs['user_id'] = $user->id;

            if(file_exists($request->input('file')))
            {
                $token=$this->getToken();
                $uploadManager=new UploadManager();
                $name=$_FILES['file']['name'];
                $filePath=$_FILES['file']['tmp_name'];
                $type=$_FILES['file']['type'];
                list($ret,$err)=$uploadManager->putFile($token,$name,$filePath,null,$type,false);
                if($err){//上传失败
                    var_dump($err);
                    return response()->json(['status' => '201', 'message' => '照片上传失败']);//返回错误信息到上传页面
                }else{//成功
                    $picturePath = 'http://on9ea4hzu.bkt.clouddn.com/image/jpg/'.$ret["key"];
                    if(!empty($inputs['content']) && !empty($inputs['title']))
                    {
                        $this->postRepository->savePost($inputs,$picturePath);
                    }
                    else
                    {
                        return response()->json(['status' => 201,'message' => '帖子标题或内容需填写']);
                    }

//                    var_dump($ret['key']);
                    return 'http://on9ea4hzu.bkt.clouddn.com/image/jpg/'.$ret["key"];//返回结果到上传页面
                }
            }
            else
            {
                if(!empty($inputs['content']) && !empty($inputs['title']))
                {
                    $this->postRepository->savePost($inputs);
                    return response()->json(['status' => 200,'message' =>'帖子发表成功']);
                }
                else
                {
                    return response()->json(['status' => 201,'message' => '帖子标题或内容需填写']);
                }
            }

        }
        else
        {
            return response()->json(['status'=>200,'message'=>'无此人信息']);
        }


    }

}
