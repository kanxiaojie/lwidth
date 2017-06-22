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
            'name'=>'复旦大学'
        ]);
        College::create([
            'name'=>'上海交通大学'
        ]);
        College::create([
            'name'=>'同济大学'
        ]);
        College::create([
            'name'=>'华东师范大学'
        ]);
        College::create([
            'name'=>'华东理工大学'
        ]);
        College::create([
            'name'=>'上海大学'
        ]);
        College::create([
            'name'=>'东华大学'
        ]);
        College::create([
            'name'=>'上海财经大学'
        ]);
        College::create([
            'name'=>'解放军第二军医大学'
        ]);
        College::create([
            'name'=>'上海理工大学'
        ]);
        College::create([
            'name'=>'上海师范大学'
        ]);
        College::create([
            'name'=>'上海中医药大学'
        ]);
        College::create([
            'name'=>'华东政法大学'
        ]);
        College::create([
            'name'=>'上海海事大学'
        ]);
        College::create([
            'name'=>'上海海洋大学'
        ]);
        College::create([
            'name'=>'上海对外经贸大学'
        ]);
        College::create([
            'name'=>'上海工程技术大学'
        ]);
        College::create([
            'name'=>'上海电力学院'
        ]);
        College::create([
            'name'=>'上海应用技术学院'
        ]);
        College::create([
            'name'=>'上海立信会计金融学院'
        ]);
    }
}
