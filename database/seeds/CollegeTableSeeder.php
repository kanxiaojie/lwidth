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
            'name'=>'福州大学'
        ]);
        College::create([
            'name'=>'福建师范大学'
        ]);
        College::create([
            'name'=>'福建师大协和学院'
        ]);
        College::create([
            'name'=>'福建医科大学'
        ]);
        College::create([
            'name'=>'福建中医药大学'
        ]);
        College::create([
            'name'=>'福建农林大学'
        ]);
        College::create([
            'name'=>'福建工程学院'
        ]);
        College::create([
            'name'=>'闽江学院'
        ]);
        College::create([
            'name'=>'江夏学院'
        ]);
        College::create([
            'name'=>'福州教育学院'
        ]);
        College::create([
            'name'=>'华南女子学院'
        ]);
        College::create([
            'name'=>'福州职业技术学院'
        ]);
        College::create([
            'name'=>'平潭海洋大学'
        ]);
        College::create([
            'name'=>'福州大学至诚学院'
        ]);
        College::create([
            'name'=>'福州大学阳光学院'
        ]);
        College::create([
            'name'=>'福建农林大学金山学院'
        ]);
        College::create([
            'name'=>'福建农林大学东方学院'
        ]);
        College::create([
            'name'=>'福建警察学院'
        ]);
        College::create([
            'name'=>'福州外语外贸学院'
        ]);
    }
}
