<?php

namespace App\Http\Controllers;

use App\BadReportType;
use App\College;
use App\Comment;
use App\CommentToComment;
use App\Post;
use App\Praise;
use App\PraiseToComment;
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

}
