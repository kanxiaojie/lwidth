<?php

namespace App\Console\Commands;

use App\Post;
use Illuminate\Console\Command;

use Illuminate\Database\Capsule\Manager as Capsule;

class OtherPostConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'post2.';

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
                        $post->readnum += rand(300,400);
                        $post->likenum += rand(10,20);
                    }elseif ($diff == 2)
                    {
                        $post->readnum += rand(200,300);
                        $post->likenum += rand(5,10);
                    }elseif ($diff == 3)
                    {
                        $post->readnum += rand(100,200);
                        $post->likenum += rand(10,20);
                    }elseif ($diff == 4)
                    {
                        $post->readnum += rand(1,50);
                        $post->likenum += rand(5,10);
                    }elseif ($diff > 5)
                    {
                        $post->readnum += rand(1,20);
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
