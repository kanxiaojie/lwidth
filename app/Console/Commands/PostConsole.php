<?php

namespace App\Console\Commands;

use App\Post;
use Illuminate\Console\Command;

use Illuminate\Database\Capsule\Manager as Capsule;

class PostConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'post.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $posts = Post::all();

        if($posts)
        {
            foreach ($posts as $post)
            {
                try{
                    $created_time = date('Ymd',strtotime($post->created_at));
                    $nowTime = date('Ymd');
                    $diff = $nowTime - $created_time;
                    if($diff <= 1)
                    {
                        $post->commentnum += rand(1000,2000);
                        $post->likenum += rand(20,30);
                    }elseif ($diff == 2)
                    {
                        $post->commentnum += rand(500,100);
                        $post->likenum += rand(10,20);
                    }elseif ($diff == 3)
                    {
                        $post->commentnum += rand(100,500);
                        $post->likenum += rand(10,20);
                    }elseif ($diff == 4)
                    {
                        $post->commentnum += rand(50,100);
                        $post->likenum += rand(5,10);
                    }elseif ($diff > 5)
                    {
                        $post->commentnum += rand(1,50);
                        $post->likenum += rand(1,5);
                    }

                    $post->save();
                }catch (\Exception $exception)
                {

                }
            }
        }
    }
}
