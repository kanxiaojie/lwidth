<?php

namespace App\Http\Controllers;

use App\BadReportType;
use App\College;
use App\Comment;
use App\CommentToComment;
use App\Post;
use App\Praise;
use App\PraiseToComment;
use App\Report;
use App\ReportComment;
use App\ReportPost;
use App\ReportReply;
use App\ReportUser;
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
                $reportPost->report_userId = $user->id;
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

    public function reportComment(Request $request, $id)
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
            $comment = $this->commentRepository->getCommentById($id);
            $bad_report_type = BadReportType::where('id',$badReport_type)->first();
            if(count($comment))
            {
                $reportComment = new ReportComment();
                $reportComment->badReport_type = $badReport_type;
                $reportComment->badReport_name = $bad_report_type->name;
                $reportComment->badReport_content = $badReport_content;
                $reportComment->reported_userId = $comment->user_id;
                if($comment->user->nickname)
                {
                    $reportComment->reported_userName = $comment->user->nickname;
                }

                $reportComment->comment_id = $comment->id;
                $reportComment->comment_content =$comment->content ;
                $reportComment->report_userId = $user->id;
                if($user->nickname)
                {
                    $reportComment->report_userName = $user->nickname;
                }
                $reportComment->save();

                return response()->json(['code' => 200,'message' => 'report successfully.']);

            }
            else
            {
                return response()->json(['code' => 201,'message' => 'Post does not exist.']);
            }
        }
    }

    public function reportReply(Request $request, $id)
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
            $reply = $this->commentRepository->getCommentToCommentById($id);
            $bad_report_type = BadReportType::where('id',$badReport_type)->first();
            if(count($reply))
            {
                $reportReply = new ReportReply();
                $reportReply->badReport_type = $badReport_type;
                $reportReply->badReport_name = $bad_report_type->name;
                $reportReply->badReport_content = $badReport_content;
                $reportReply->reported_userId = $reply->user_id;
                $User = User::where('id',$reply->user_id)->first();
                if($User->nickname)
                {
                    $reportReply->reported_userName = $User->nickname;
                }

                $reportReply->reply_id = $reply->id;
                $reportReply->reply_content =$reply->content ;
                $reportReply->report_userId = $user->id;
                if($user->nickname)
                {
                    $reportReply->report_userName = $user->nickname;
                }
                $reportReply->save();

                return response()->json(['code' => 200,'message' => 'report successfully.']);

            }
            else
            {
                return response()->json(['code' => 201,'message' => 'Post does not exist.']);
            }
        }
    }

    public function reportUser(Request $request, $id)
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
            $ReportedUser = $this->userRepository->getUserById($id);
            $bad_report_type = BadReportType::where('id',$badReport_type)->first();
            if(count($ReportedUser))
            {
                $reportUser = new ReportUser();
                $reportUser->badReport_type = $badReport_type;
                $reportUser->badReport_name = $bad_report_type->name;
                $reportUser->badReport_content = $badReport_content;
                $reportUser->reported_userId = $ReportedUser->id;
                if($ReportedUser->nickname)
                {
                    $reportUser->reported_userName = $ReportedUser->nickname;
                }

                $reportUser->report_userId = $user->id;
                if($user->nickname)
                {
                    $reportUser->report_userName = $user->nickname;
                }
                $reportUser->save();

                return response()->json(['code' => 200,'message' => 'report successfully.']);

            }
            else
            {
                return response()->json(['code' => 201,'message' => 'Post does not exist.']);
            }
        }
    }

    public function report($typeId, $id, Request $request)
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
            $ReportedUser = $this->userRepository->getUserById($id);
            $bad_report_type = BadReportType::where('id',$badReport_type)->first();
            if(count($ReportedUser))
            {
                $reportUser = new Report();
                $reportUser->type = $typeId;
                $reportUser->badReport_type = $badReport_type;
                $reportUser->badReport_name = $bad_report_type->name;
                $reportUser->badReport_content = $badReport_content;
                $reportUser->reported_userId = $ReportedUser->id;
                if($ReportedUser->nickname)
                {
                    $reportUser->reported_userName = $ReportedUser->nickname;
                }

                $reportUser->report_userId = $user->id;
                if($user->nickname)
                {
                    $reportUser->report_userName = $user->nickname;
                }
                $reportUser->save();

                return response()->json(['code' => 200,'message' => 'report successfully.']);

            }
            else
            {
                return response()->json(['code' => 201,'message' => 'Post does not exist.']);
            }
        }
    }

}
