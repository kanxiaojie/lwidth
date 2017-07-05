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
            'name'=>'华侨大学'
        ]);
        College::create([
            'name'=>'泉州华光职业学院'
        ]);
        College::create([
            'name'=>'南京中医药大学翰林学院'
        ]);
    }
}
