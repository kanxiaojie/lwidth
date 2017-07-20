<?php

use App\PostingType;
use Illuminate\Database\Seeder;

class PostingTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PostingType::create([
            'name' => '表白'
        ]);

        PostingType::create([
            'name' => '活动'
        ]);

        PostingType::create([
            'name' => '求助'
        ]);

        PostingType::create([
            'name' => '物品'
        ]);

        PostingType::create([
            'name' => '吐槽'
        ]);

        PostingType::create([
            'name' => '兼职'
        ]);
    }
}
