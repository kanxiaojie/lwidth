<?php

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use App\Repositories\PostRepository;
use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Crypt;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class UploadToQiniuController extends Controller
{
    protected $postRepository;
    protected $baseRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BaseRepository $baseRepository,
        PostRepository $postRepository
    )
    {
        $this->baseRepository = $baseRepository;
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
        $input = $request->all();
        $inputs = array();

        $inputs['wesecret'] = $request->input('wesecret');
        $inputs['content'] = $request->input('content');

        $openid = Crypt::decrypt($inputs['wesecret']);

        $user = User::where('openid',$openid)->first();
        if($user)
        {
            $inputs['user_id'] = $user->id;

//            if(file_exists($request->input('file')))
            if(array_key_exists('file',$input))
            {
                $token=$this->getToken();
                $uploadManager=new UploadManager();
//                $name=$_FILES['file']['name'];
                $name = $this->baseRepository->guid().'.'.$input['file']->getClientOriginalExtension();
                $filePath=$_FILES['file']['tmp_name'];
                $type=$_FILES['file']['type'];
                list($ret,$err)=$uploadManager->putFile($token,$name,$filePath,null,$type,false);
                if($err){//上传失败
                    var_dump($err);
                    return response()->json(['status' => '201', 'message' => 'pictures upload failed']);//返回错误信息到上传页面
                }else{//成功
                    $picturePath = 'http://on9ea4hzu.bkt.clouddn.com/'.$ret["key"];
                    if(!empty($inputs['content']))
                    {
                        $this->postRepository->savePost($inputs,$picturePath);
                    }
                    else
                    {
                        return response()->json(['status' => 201,'message' => 'post content can not be null']);
                    }

//                    var_dump($ret['key']);
                    return 'http://on9ea4hzu.bkt.clouddn.com/'.$ret["key"];//返回结果到上传页面
                }
            }
            else
            {
                if(!empty($inputs['content']))
                {
                    $this->postRepository->savePost($inputs);
                    return response()->json(['status' => 200,'message' =>'posting success']);
                }
                else
                {
                    return response()->json(['status' => 201,'message' => 'posting content should be written']);
                }
            }

        }
        else
        {
            return response()->json(['status'=>200,'message'=>'user not exist']);
        }


    }

}
