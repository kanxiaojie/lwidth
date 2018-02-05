<?php

namespace App\Http\Controllers;

use App\Models\PrivateChat;
use App\Models\TemplateMessage;
use App\Models\RadioStationInfo;
use App\Repositories\UserRepository;
use App\Repositories\PostRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    const page_size = 15;
    protected $userRepository;
    protected $postRepository;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        PostRepository $postRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @param Request $request
     * @return array
     * @name 存储私信
     */
    public function savePrivateMessage(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $to_user_id = $request->get('to_user_id');
        $content = $request->get('content');

        if (empty($wesecret) || empty($to_user_id) || empty($content)){
            return [
                'code' => 201,
                'message' => "wesecret,to_user_id,content其中参数不可为空"
            ];
        }

        try{
            $openid = Crypt::decrypt($wesecret);
        }catch (\Exception $exception){
            return ['status' => 201,'message' => 'wesecret invalid'];
        }

        $from_user = $this->userRepository->getUserByOpenId($openid);
        $to_user = $this->userRepository->getUserById(intval($to_user_id));

        if ($from_user && $to_user){
            $params = [
                'from_user_id' => $from_user->id,
                'to_user_id' => $to_user_id,
                'content' => $content
            ];

            if (PrivateChat::saveRecord($params)){
                return [
                    'code' => 200,
                    'message' => 'save success'
                ];
            }
        }else{
            return [
                'code' => 201,
                'message' => 'from_user or to_user does not exist'
            ];
        }
    }

    public function readPrivateMessage(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $id = $request->get('id');

        if (empty($wesecret) || empty($id)){
            return [
                'code' => 201,
                'message' => "wesecret,id其中参数不可为空"
            ];
        }

        try{
            $openid = Crypt::decrypt($wesecret);
        }catch (\Exception $exception){
            return ['status' => 201,'message' => 'wesecret invalid'];
        }

        $user = $this->userRepository->getUserByOpenId($openid);
        $message = PrivateChat::find($id);
        if ($user->id == $message->to_user_id) {
            $message->if_read = 1;
            $message->save();
        }

        return [
            'code' => 200,
            'message' => 'save success'
        ];
    }

    public function readAllPrivateMessages(Request $request)
    {
        $wesecret = $request->get('wesecret');

        if (empty($wesecret)){
            return [
                'code' => 201,
                'message' => "wesecret其中参数不可为空"
            ];
        }

        try{
            $openid = Crypt::decrypt($wesecret);
        }catch (\Exception $exception){
            return ['status' => 201,'message' => 'wesecret invalid'];
        }

        $user = $this->userRepository->getUserByOpenId($openid);

        if($user)
        {
            $sql = 'update private_chat_log set if_read=1 WHERE to_user_id=?';
            DB::update($sql,[$user->id]);

            return response()->json(['status' => 200,'message'=>'success']);
        }
        else
        {
        return response()->json(['status'=>201,'message' => 'User Does Not Exist.']);
        }


        // $message = PrivateChat::find($id);
        // if ($user->id == $message->to_user_id) {
        //     $message->if_read = 1;
        //     $message->save();
        // }

        // return [
        //     'code' => 200,
        //     'message' => 'save success'
        // ];
    }

    

    /**
     * @param Request $request
     * @return array
     * @name 获取私信
     * $type 1,发送，2，接收
     */
    public function getPrivateMessages(Request $request)
    {
        $wesecret = $request->get('wesecret');
        $type = $request->get('type');

        if (empty($wesecret) || empty($type)){
            return [
                'code' => 201,
                'message' => "wesecret,type其中参数不可为空"
            ];
        }

        try{
            $openid = Crypt::decrypt($wesecret);
        }catch (\Exception $exception){
            return ['status' => 201,'message' => 'wesecret invalid'];
        }

        $user = $this->userRepository->getUserByOpenId($openid);
        if ($user){
            $datas = [];
            if ($type == 1){
                $messages = PrivateChat::where('to_user_id', $user->id)->orderBy('id', 'desc')->paginate(self::page_size);

                foreach ($messages as $message){
                    $data = [];
                    $data['id'] = $message->id;
                    $data['from_user_id'] = $message->from_user_id;
                    $from_user = $this->userRepository->getUserById($message->from_user_id);

                    $fromUserInfo = [];
                    $fromUserInfo['id'] = $from_user->id;
                    $fromUserInfo['openid'] = $from_user->openid;
                    $fromUserInfo['nickname'] = $from_user->nickname;
                    $fromUserInfo['avatarUrl'] =  $from_user->avatarUrl;
                    if ($from_user->college_id) {
                        $fromUserInfo['college_name'] = $from_user->college->name;
                    } else {
                        $fromUserInfo['college_name'] = '';
                    }
                    $data['userInfo'] = $fromUserInfo;


                    // if ($from_user){
                    //     $data['from_user_info']['nickname'] = $from_user->nickname;
                    //     $data['from_user_info']['avatarUrl'] = $from_user->avatarUrl;
                    // }else{
                    //     $data['from_user_info'] = [];
                    // }

                    // $to_user = $this->userRepository->getUserById($message->to_user_id);
                    // if ($from_user){
                    //     $data['to_user_info']['id'] = $to_user->id;
                    //     $data['to_user_info']['nickname'] = $to_user->nickname;
                    //     $data['to_user_info']['avatarUrl'] = $to_user->avatarUrl;
                    // }else{
                    //     $data['to_user_info'] = [];
                    // }

                    $data['content'] = $message->content;
                    $data['if_read'] = $message->if_read;
                    
                    $the_time = date('Y-m-d H:i:s',$message->created_at);
                    $diff_time = $this->postRepository->getTime($the_time);
                    $data['created_at'] = $diff_time;

                    $datas[] = $data;
                }
            }elseif($type == 2){
                $messages = PrivateChat::where('from_user_id', $user->id)->orderBy('id', 'desc')->paginate(self::page_size);

                foreach ($messages as $message){
                    $data = [];
                    $data['id'] = $message->id;
                    $data['to_user_id'] = $message->to_user_id;
                    // $from_user = $this->userRepository->getUserById($message->from_user_id);
                    // if ($from_user){
                    //     $data['from_user_info']['nickname'] = $from_user->nickname;
                    //     $data['from_user_info']['avatarUrl'] = $from_user->avatarUrl;
                    // }else{
                    //     $data['from_user_info'] = [];
                    // }

                    $to_user = $this->userRepository->getUserById($message->to_user_id);

                    $toUserInfo = [];
                    $toUserInfo['id'] = $to_user->id;
                    $toUserInfo['openid'] = $to_user->openid;
                    $toUserInfo['nickname'] = $to_user->nickname;
                    $toUserInfo['avatarUrl'] =  $to_user->avatarUrl;
                    if ($to_user->college_id) {
                        $toUserInfo['college_name'] = $to_user->college->name;
                    } else {
                        $toUserInfo['college_name'] = '';
                    }
                    $data['userInfo'] = $toUserInfo;

                    $data['content'] = $message->content;
                    $data['if_read'] = $message->if_read;
                    
                    $the_time = date('Y-m-d H:i:s',$message->created_at);
                    $diff_time = $this->postRepository->getTime($the_time);
                    $data['created_at'] = $diff_time;

                    // if ($from_user){
                    //     $data['to_user_info']['id'] = $to_user->id;
                    //     $data['to_user_info']['nickname'] = $to_user->nickname;
                    //     $data['to_user_info']['avatarUrl'] = $to_user->avatarUrl;
                    // }else{
                    //     $data['to_user_info'] = [];
                    // }

                    // $data['content'] = $message->content;
                    // $data['created_at'] = date('Y-m-d H:i:s',$message->created_at);
                    // $data['if_read'] = $message->if_read;

                    $datas[] = $data;
                }
            }

            return [
                'code' => 200,
                'data' => $datas
            ];

        }else {
            return [
                'code' => 201,
                'message' => 'user does not exist'
            ];
        }

    }

    public function getUnPrivateMessages(Request $request) {
        $wesecret = $request->get('wesecret');

        if (empty($wesecret)){
            return [
                'code' => 201,
                'message' => "wesecret其中参数不可为空"
            ];
        }

        try{
            $openid = Crypt::decrypt($wesecret);
        }catch (\Exception $exception){
            return ['status' => 201,'message' => 'wesecret invalid'];
        }

        $user = $this->userRepository->getUserByOpenId($openid);
        if ($user){
            $unreadMessages = PrivateChat::where(['to_user_id' => $user->id, 'if_read' => 0])->get()->count();
        } else {
            $unreadMessages = 0;
        }

        return [
            'code' => 200,
            'unreadMessages' => $unreadMessages
        ];
    }

    //电台
    public function getRadios(Request $request)
    {
        $datas = [];
        $square_imgs = [
            'http://mmbiz.qpic.cn/mmbiz/wJ1zCBmADTGcvaOfIId1RyZ5QctTGuic7LvsuBR5LSebkuicyN01TKMk7uy2wdiaia2PDZaaWQsZkItI6JC0qPyK7Q/0',
            'http://mmbiz.qpic.cn/mmbiz/wJ1zCBmADTG51P470aib7ZiburTZ41jdqX8thOo4pibZ5ibiaQaKoGG5bibCDJ5D6Sfxrrgc7G9tW4RdShcmKmuhTXpw/0',
            'http://mmbiz.qpic.cn/mmbiz/wJ1zCBmADTGS87cogy6cEPmxNqfMsTdCHDxEaLBCTmAmUXicfdlcQxa6P6h8UFoqB98ia0WjVtN8CVACulXHyH0w/0',
        ];
        $square_title = 'FM 80℃ 电台';
        $dataLength = RadioStationInfo::all()->count();
        $radiolists = RadioStationInfo::orderBy("id", "desc")->paginate(10);

        if ($radiolists){
            foreach ($radiolists as $radiolist){
                $data = [];
                $data['id'] = $radiolist->id;
                $data['title'] = $radiolist->title;
                $data['author'] = $radiolist->author;
                $ut = strval($radiolist->upload_time);
                $tut = substr($ut,0,4).'-'.substr($ut,4,6).'-'.substr($ut,6,8);
                $data['upload_time'] = $tut;
                // $data['url'] = $radiolist->url;
                $data['duration'] = $radiolist->duration;
                $data['praise_number'] = $radiolist->praise_number;
                $data['play_number'] = $radiolist->play_number;
                $data['img_url'] = $radiolist->img_url;
                // $data['article_author'] = $radiolist->article_author;
                // $data['article_content'] = $radiolist->article_content;
                // $data['article_remark'] = $radiolist->article_remark;
                $datas[] = $data;
            }
        }

        return [
            'code' => 200,
            'square_imgs'=> $square_imgs,
            'square_title'=> $square_title,
            'dataLength' => $dataLength,
            'data' => $datas
        ];
    }

    public function getRadio(Request $request, $id)
    {
        $radio = RadioStationInfo::find($id);
        
        $data = [];
        if ($radio) {
            $data['id'] = $radio->id;
            $data['title'] = $radio->title;
            $data['epname'] = 'FM 80℃ 电台';
            $data['author'] = $radio->author;
            $ut = strval($radio->upload_time);
            $tut = substr($ut,0,4).'-'.substr($ut,4,2).'-'.substr($ut,6,2);
            $data['upload_time'] = $tut;
            $data['url'] = $radio->url;
            $data['duration'] = $radio->duration;
            $data['praise_number'] = $radio->praise_number;
            $data['play_number'] = $radio->play_number;
            $data['img_url'] = $radio->img_url;
            $data['article_author'] = $radio->article_author;
            $data['article_content'] = $radio->article_content;
            $data['article_remark'] = $radio->article_remark;
        }

        return [
            'code' => 200,
            'data' => $data
        ];
    }



    //保存模版消息formId
    public function save_templateMessages(Request $request) {
        $wesecret = $request->get('wesecret');
        $form_id = $request->get('form_id');

        if (empty($wesecret) || empty($form_id)){
            return [
                'code' => 201,
                'message' => "wesecret,form_id 其中参数不可为空"
            ];
        }

        try{
            $openid = Crypt::decrypt($wesecret);
        }catch (\Exception $exception){
            return ['status' => 201,'message' => 'wesecret invalid'];
        }

        $user = $this->userRepository->getUserByOpenId($openid);
        if ($user){
            $templateMessage = $this->userRepository->saveTemplateMessage($user->id, $user->openid, $form_id);

            return [
                'code' => 200,
                'templateMessage' => $templateMessage
            ];
        } else {
            return ['status' => 201,'message' => 'user not found'];
        }

        

    }
    // 发送模版消息
    public function send_templateMessage(Request $request) {
        $appId = env('WEIXIN_APP_ID','wx6700db6c36e6eed1');
        $appSecret = env('WEIXIN_SECRET_ID','3220627137eaa581d7061a7bc4b11c66');
        $access_token = $this->get_access_token($appId, $appSecret);

        $url  = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$access_token; 
        
        $inputs = $request->all(); 

        $this->send_templateMessage_by_wechat($url, $inputs);
    }
    public function send_templateMessage_by_wechat($url, $inputs) {
        $the_gotten_templateMessage = $this->get_right_templateMessage();
        if ($the_gotten_templateMessage == 0) {
            return response()->json(['status' => 200,'message' => 'no form_id anymore']);
        } else {
            $inputs['form_id'] = $the_gotten_templateMessage->form_id;
            $data = json_encode($inputs);   
            $response = $this->http_post_data($url, $data);
            $response_json = json_decode($response[1], true);
            $errcode = $response_json['errcode'];
            if ($errcode == 41028 || $errcode == 41029) {
                $the_gotten_templateMessage->delete();
                $this->send_templateMessage_by_wechat($url, $inputs);
            } else if($errcode == 0)  {
                $the_gotten_templateMessage->delete();
                return response()->json(['status' => 200,'response' => $response]);
            } else {
                return response()->json(['status' => 200,'response' => $response]);
            }
        }
    }
    public function get_right_templateMessage() {
        $the_templateMessages = TemplateMessage::where('openid', $inputs['touser'])->orderBy('id', 'asc')->get();
        $now_time = time();
        foreach ($the_templateMessages as $the_templateMessage)
        {
            $the_end_time = $the_templateMessage->end_time;
            if ($now_time > $the_end_time) {
                $the_templateMessage->delete();                
            } else {
                return $the_templateMessage;
            }
        }
        return 0;
    }
    public function get_access_token($appId, $appSecret)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appSecret);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($ch);
        $output = json_decode($output,TRUE);

        $access_token = $output["access_token"];
        curl_close($ch);

        return $access_token;
    }
    public function http_post_data($url, $data_string) {  
  
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);  
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(  
            'Content-Type: application/json; charset=utf-8',  
            'Content-Length: ' . strlen($data_string))  
        );  
        ob_start();  
        curl_exec($ch);  
        $return_content = ob_get_contents();  
        ob_end_clean();  
  
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);  

        return array($return_code, $return_content);  
    }  


    



    // 后台管理系统api----------------------------------------------------------------------------------------------------------------------
    
    /**
     * @param Request $request
     * @name 获取电台列表
     */
    public function getRadioList(Request $request)
    {
        $condition = '1=1';
        $datas = [];
        $dataLength = RadioStationInfo::all()->count();

        $radiolists = RadioStationInfo::orderBy("upload_time", "desc")->paginate(10);

        if ($radiolists){
            foreach ($radiolists as $radiolist){
                $data = [];
                $data['id'] = $radiolist->id;
                $data['title'] = $radiolist->title;
                $data['author'] = $radiolist->author;
                $ut = strval($radiolist->upload_time);
                $tut = substr($ut,0,4).'-'.substr($ut,4,2).'-'.substr($ut,6,2);
                $data['upload_time'] = $tut;
                // $data['upload_time'] = !empty($radiolist->upload_time)?date('Y-m-d',$radiolist->upload_time):'';
                $data['url'] = $radiolist->url;
                $data['duration'] = $radiolist->duration;
                $data['praise_number'] = $radiolist->praise_number;
                $data['play_number'] = $radiolist->play_number;
                $data['img_url'] = $radiolist->img_url;
                $data['article_author'] = $radiolist->article_author;
                $data['article_content'] = $radiolist->article_content;
                $data['article_remark'] = $radiolist->article_remark;
                $datas[] = $data;
            }
        }

        return [
            'code' => 200,
            'dataLength' => $dataLength,
            'data' => $datas
        ];
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 新增/更新电台
     */
    public function postRadio(Request $request)
    {
        $params = $request->get('params');
        if (empty($params['id'])) {
            $radio = new RadioStationInfo();
        } else {
            $radio = RadioStationInfo::find($params['id']);
        }

        $radio->title = $params['title'];
        $radio->author = $params['author'];
        $radio->upload_time = $params['upload_time'];
        $radio->url = $params['url'];
        $radio->duration = $params['duration'];
        if(isset($params['praise_number']))
        {
            $radio->praise_number = $params['praise_number'];
        }
        if(isset($params['play_number']))   
        {
            $radio->play_number = $params['play_number'];
        }
        $radio->img_url = $params['img_url'];
        $radio->article_author = $params['article_author'];
        $radio->article_content = $params['article_content'];
        $radio->article_content = $params['article_content'];
        $radio->article_remark = $params['article_remark'];
        $radio->save();
        
        $radio_id = $radio->id;
        return response()->json(['status' => 200,'radio_id' => $radio_id]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @name 删除电台
     */
    public function deleteRadio(Request $request)
    {
        $radio_id = 0;
        $params = $request->get('params',0);
        $radio = RadioStationInfo::find($params['id']);
        if ($radio) {
            $radio_id = $radio->id;
            
            $radio->delete();
        }
        return response()->json(['status' => 200,'radio_id' => $radio_id]);
    }




}
