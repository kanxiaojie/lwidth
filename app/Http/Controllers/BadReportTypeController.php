<?php

namespace App\Http\Controllers;

use App\BadReportType;
use App\College;
use App\Comment;
use App\CommentToComment;
use App\Post;
use App\Praise;
use App\PraiseToComment;
use App\ReportPost;
use App\Repositories\BaseRepository;
use App\Repositories\CommentRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class BadReportTypeController extends Controller
{
    protected $postRepository;
    protected $baseRepository;
    protected $userRepository;
    protected $commentRepository;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PostRepository $postRepository,
        BaseRepository $baseRepository,
        UserRepository $userRepository,
        CommentRepository $commentRepository
    )
    {
        $this->postRepository = $postRepository;
        $this->baseRepository = $baseRepository;
        $this->userRepository = $userRepository;
        $this->commentRepository = $commentRepository;
    }


    public function getBadReportTypes(Request $request)
    {
        $reportTypes = BadReportType::all();

        $data = [];

        foreach ($reportTypes as $reportType)
        {
            $data[] = $reportType->name;
        }

        return response()->json(['code' => 200,'data' => $data]);
    }

    public function reportPost(Request $request,$id)
    {
        $wesecret = $request->get('wesecret');
        $badReport_type = $request->get('badReport_type');
        $badReport_content = $request->get('badReport_content');

        $openid = $this->baseRepository->decryptCode($wesecret);
        $user = $this->userRepository->getUserByOpenId($openid);

        if(!$user)
        {
            return response()->json(['status' => 201,'message' => 'User Does Not Exist!']);
        }
        else
        {
            $post = $this->postRepository->getPost($id);
            $bad_report_type = BadReportType::where('id',$badReport_type)->first();
            if(count($post))
            {
                $reportPost = new ReportPost();
                $reportPost->badReport_type = $badReport_type;
                $reportPost->badReport_name = $bad_report_type->name;
                $reportPost->badReport_content = $badReport_content;
                $reportPost->reported_userId = $post->user_id;
                if($post->user->nickname)
                {
                    $reportPost->reported_userName = $post->user->nickname;
                }

                $reportPost->post_id = $post->id;
                $reportPost->post_content =$post->content ;
                $reportPost->report_userId = $user->nickname;
                if($user->nickname)
                {
                    $reportPost->report_userName = $user->nickname;

                }
                $reportPost->save();

                return response()->json(['code' => 200,'message' => 'report successfully.']);

            }
            else
            {
                return response()->json(['code' => 201,'message' => 'Post does not exist.']);
            }
        }
    }

}
