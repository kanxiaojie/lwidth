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
            'name' => '全国'
        ]);

        Interest::create([
            'name' => '本省'
        ]);

        Interest::create([
            'name' => '本市'
        ]);

        Interest::create([
            'name' => '本校'
        ]);
    }
}
