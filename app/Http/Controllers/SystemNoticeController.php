<?php

namespace App\Http\Controllers;

use App\SystemNotice;
use App\User;
use App\Repositories\BaseRepository;
use App\Repositories\UserRepository;

use Illuminate\Http\Request;

class SystemNoticeController extends Controller
{

    protected $baseRepository;
    protected $userRepository;

    public function __construct(
        BaseRepository $baseRepository,
        UserRepository $userRepository
    )
    {
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
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
                $data['type'] = $systemNotice->type;
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