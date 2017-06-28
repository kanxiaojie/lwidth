<?php

use App\College;
use App\Country;
use Illuminate\Database\Seeder;

class CollegeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        College::create([
            'name'=>'山东农业工程学院'
        ]);
    }
}
