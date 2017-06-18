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
            $unreadSystemNoticeNums = SystemNotice::where(function ($query) {
                $query->where('type', 0)->orWhere('user_id', $user->id);
            })->where('if_read', 0)->get()->count();

            $data['unreadSystemNoticeNums'] = $unreadSystemNoticeNums;
        } else {
            $data['unreadSystemNoticeNums'] = 0;
        }

        return response()->json(['status' => 200,'data' => $data]);
    }

    public function getSystemNotices(Request $request)
    {
        $wesecret = $request->get('wesecret');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        $datas = [];
        if($user)
        {
            $systemNotices = SystemNotice::where('type', 0)->orWhere('user_id', $user->id)->orderBy('created_at','desc')->paginate(5);
            foreach ($systemNotices as $systemNotice) {
                $data = [];
                $data['id'] = $systemNotice->id;
                $data['type'] = $systemNotice->type;
                $data['if_read'] = $systemNotice->if_read;

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
        }

        return response()->json(['status' => 200,'data' => $datas]);

    }
}