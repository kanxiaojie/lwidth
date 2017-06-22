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
            'name'=>'北京大学'
        ]);
        College::create([
            'name'=>'清华大学'
        ]);
        College::create([
            'name'=>'中国人民大学'
        ]);
        College::create([
            'name'=>'北京师范大学'
        ]);
        College::create([
            'name'=>'北京航空航天大学'
        ]);
        College::create([
            'name'=>'中国农业大学'
        ]);
        College::create([
            'name'=>'北京理工大学'
        ]);
        College::create([
            'name'=>'北京交通大学'
        ]);
        College::create([
            'name'=>'北京科技大学'
        ]);
        College::create([
            'name'=>'北京协和医学院'
        ]);
        College::create([
            'name'=>'中国政法大学'
        ]);
        College::create([
            'name'=>'北京邮电大学'
        ]);
        College::create([
            'name'=>'北京化工大学'
        ]);
        College::create([
            'name'=>'首都师范大学'
        ]);
        College::create([
            'name'=>'北京工业大学'
        ]);
        College::create([
            'name'=>'北京林业大学'
        ]);
        College::create([
            'name'=>'中央民族大学'
        ]);
        College::create([
            'name'=>'对外经济贸易大学'
        ]);
        College::create([
            'name'=>'首都医科大学'
        ]);
        College::create([
            'name'=>'中央财经大学'
        ]);
    }
}
