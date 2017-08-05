<?php

use App\Interest;
use Illuminate\Database\Seeder;

class InterestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Interest::create([
            'name' => '全国',
            'description' => '看所有的帖子'
        ]);

        Interest::create([
            'name' => '本省',
            'description' => '看学校所在省份的帖子'
        ]);

        Interest::create([
            'name' => '本市',
            'description' => '看学校所在城市的帖子'
        ]);

        Interest::create([
            'name' => '本校',
            'description' => '看学校的帖子'
        ]);
    }
}
