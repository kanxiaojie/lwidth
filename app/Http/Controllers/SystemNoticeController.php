<?php

namespace App\Http\Controllers;

use App\SystemNotice;
use App\User;
use App\Repositories\BaseRepository;
use App\Repositories\UserRepository;
use App\Repositories\PostRepository;

use Illuminate\Http\Request;

class SystemNoticeController extends Controller
{

    protected $baseRepository;
    protected $userRepository;
    protected $postRepository;


    public function __construct(
        BaseRepository $baseRepository,
        UserRepository $userRepository,
        PostRepository $postRepository
    )
    {
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    public function getUnreadSystemNoticeNums(Request $request) {
        $wesecret = $request->get('wesecret');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        $data = [];
        if($user)
        {
            // $unreadSystemNoticeNums = SystemNotice::where(function ($query) use($user) {
            //     $query->where('type', 0)->orWhere('user_id', $user->id);
            // })->where('if_read', 0)->get()->count();
            $unreadSystemNoticeNums = SystemNotice::where(['if_read' => 0, 'user_id' => $user->id])->get()->count();

            $data['unreadSystemNoticeNums'] = $unreadSystemNoticeNums;
        } else {
            $data['unreadSystemNoticeNums'] = 0;
        }

        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getSystemNotices(Request $request)
    {
        // $wesecret = $request->get('wesecret');

        // $openid = $this->baseRepository->decryptCode($wesecret);
        // $user = $this->userRepository->getUserByOpenId($openid);

        $datas = [];
        // if($user)
        // {
            $systemNotices = SystemNotice::whereIn('type', [0, 1])->orderBy('created_at','desc')->paginate(5);
            $dataLength = SystemNotice::whereIn('type', [0, 1])->get()->count();
            foreach ($systemNotices as $systemNotice) {
                $data = [];
                $data['id'] = $systemNotice->id;
                $data['type'] = $systemNotice->type;
                $data['user_id'] = $systemNotice->user_id;
                $user = User::find($systemNotice->user_id);
                $userInfo = array();
                if ($user) {
                    $userInfo['id'] = $user->id;
                    $userInfo['nickname'] = $user->nickname;
                }
                $data['userInfo'] = $userInfo;
                // $data['if_read'] = $systemNotice->if_read;

                $diff_time = $this->postRepository->getTime($systemNotice->created_at);
                $data['created_at'] = $diff_time;

                if (!empty($systemNotice->title)) {
                    $data['title'] = $systemNotice->title;
                } else {
                    $data['title'] = '';
                }
                if (!empty($systemNotice->image)) {
                    $data['image'] = $systemNotice->image;
                } else {
                    $data['image'] = '';
                }
                if (!empty($systemNotice->video_url)) {
                    $data['video_url'] = $systemNotice->video_url;
                } else {
                    $data['video_url'] = '';
                }
                if (!empty($systemNotice->content)) {
                    $data['content'] = $systemNotice->content;
                } else {
                    $data['content'] = '';
                }
                
                $datas[] = $data;
            }
        // }

        return response()->json(['status' => 200,'data' => $datas, 'dataLength' => $dataLength ]);

    }

    public function getRelatedApplets(Request $request)
    {
        // $wesecret = $request->get('wesecret');

        // $openid = $this->baseRepository->decryptCode($wesecret);
        // $user = $this->userRepository->getUserByOpenId($openid);

        $datas = [];
        // if($user)
        // {
            $systemNotices = SystemNotice::where('type', 10)->get();
            $dataLength = count($systemNotices);
            foreach ($systemNotices as $systemNotice) {
                $data = [];
                $data['id'] = $systemNotice->id;
                $data['appId'] = $systemNotice->video_url;
                
                if (!empty($systemNotice->title)) {
                    $data['name'] = $systemNotice->title;
                } else {
                    $data['name'] = '';
                }
                if (!empty($systemNotice->image)) {
                    $data['avatarUrl'] = $systemNotice->image;
                } else {
                    $data['avatarUrl'] = '';
                }
                if (!empty($systemNotice->content)) {
                    $data['summary'] = $systemNotice->content;
                } else {
                    $data['summary'] = '';
                }
                
                $datas[] = $data;
            }
        // }

        return response()->json(['status' => 200,'data' => $datas, 'dataLength' => $dataLength ]);

    }

    public function getAboutLoveWalls(Request $request)
    {
        // $wesecret = $request->get('wesecret');

        // $openid = $this->baseRepository->decryptCode($wesecret);
        // $user = $this->userRepository->getUserByOpenId($openid);

        $datas = [];
        // if($user)
        // {
            $systemNotices = SystemNotice::where('type', 11)->get();
            $dataLength = count($systemNotices);
            foreach ($systemNotices as $systemNotice) {
                $data = [];

                $data['id'] = $systemNotice->id;
                // $diff_time = $this->postRepository->getTime($systemNotice->created_at);
                // $data['created_at'] = $diff_time;
                // if (!empty($systemNotice->title)) {
                //     $data['title'] = $systemNotice->title;
                // } else {
                //     $data['title'] = '';
                // }
                if (!empty($systemNotice->image)) {
                    $data['image'] = $systemNotice->image;
                } else {
                    $data['image'] = '';
                }
                if (!empty($systemNotice->video_url)) {
                    $data['video_url'] = $systemNotice->video_url;
                } else {
                    $data['video_url'] = '';
                }
                if (!empty($systemNotice->content)) {
                    $data['content'] = $systemNotice->content;
                } else {
                    $data['content'] = '';
                }
                
                $datas[] = $data;
            }
        // }

        return response()->json(['status' => 200,'data' => $datas, 'dataLength' => $dataLength]);

    }

    public function postSystemNotices(Request $request)
    {
        $params = $request->get('params');

        if (empty($params['id'])) {
            $systemNotice = new SystemNotice();
        } else {
            $systemNotice = SystemNotice::find($params['id']);
        }

        $systemNotice->type = $params['type'];
        $systemNotice->user_id = $params['user_id'];
        $systemNotice->image = $params['image'];
        $systemNotice->video_url = $params['video_url'];
        $systemNotice->content = $params['content'];

        $systemNotice->save();
        
        $systemNotice_id = $systemNotice->id;

        return response()->json(['status' => 200,'systemNotice_id' => $systemNotice_id]);

    }

    public function postRelatedApplets(Request $request)
    {
        $params = $request->get('params');

        if (empty($params['id'])) {
            $systemNotice = new SystemNotice();
        } else {
            $systemNotice = SystemNotice::find($params['id']);
        }

        $systemNotice->type = 10;
        $systemNotice->video_url = $params['appId'];
        $systemNotice->title = $params['name'];
        $systemNotice->image = $params['avatarUrl'];
        $systemNotice->content = $params['summary'];

        $systemNotice->save();
        
        $systemNotice_id = $systemNotice->id;

        return response()->json(['status' => 200,'relatedApplet_id' => $systemNotice_id]);

    }

    public function postAboutLoveWalls(Request $request)
    {
        $params = $request->get('params');

        if (empty($params['id'])) {
            $systemNotice = new SystemNotice();
        } else {
            $systemNotice = SystemNotice::find($params['id']);
        }

        $systemNotice->type = 11;
        $systemNotice->image = $params['image'];
        $systemNotice->video_url = $params['video_url'];
        $systemNotice->content = $params['content'];

        $systemNotice->save();
        
        $systemNotice_id = $systemNotice->id;

        return response()->json(['status' => 200,'aboutLoveWall_id' => $systemNotice_id]);

    }

    public function deleteSystemNotice(Request $request)
    {
        $params = $request->get('params');

        $systemNotice = SystemNotice::find($params['id']);
        if ($systemNotice) {
            $systemNotice_id = $systemNotice->id;
            
            $systemNotice->delete();
        }

        return response()->json(['status' => 200,'systemNotice_id' => $systemNotice_id]);

    }

    public function deleteRelatedApplet(Request $request)
    {
        $params = $request->get('params');

        $systemNotice = SystemNotice::find($params['id']);
        if ($systemNotice) {
            $systemNotice_id = $systemNotice->id;
            
            $systemNotice->delete();
        }

        return response()->json(['status' => 200,'relatedApplet_id' => $systemNotice_id]);

    }

    public function deleteAboutLoveWall(Request $request)
    {
        $params = $request->get('params');

        $systemNotice = SystemNotice::find($params['id']);
        if ($systemNotice) {
            $systemNotice_id = $systemNotice->id;
            
            $systemNotice->delete();
        }

        return response()->json(['status' => 200,'aboutLoveWall_id' => $systemNotice_id]);

    }

    public function labelRead(Request $request) {
        $wesecret = $request->get('wesecret');
        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        $systemNotice_id = $request->get('systemNotice_id');
        $the_systemNotice = SystemNotice::find($systemNotice_id);

        if($user)
        {
            $systemNotices = SystemNotice::where('user_id', $user->id)->get();  
            foreach ($systemNotices as $systemNotice) {
                if ($systemNotice->created_at <= $the_systemNotice->created_at) {
                    $systemNotice->if_read = 1;
                    $systemNotice->save();
                }
            }          
            
        }

        return response()->json(['status' => 200,'data' => '标注已读成功']);
    }
}