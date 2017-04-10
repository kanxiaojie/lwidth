<?php

use App\Grade;
use Illuminate\Database\Seeder;

class GradeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Grade::create([
            'name'=>'2017级',
        ]);
        Grade::create([
            'name'=>'2016级',
        ]);
        Grade::create([
            'name'=>'2015级',
        ]);
        Grade::create([
            'name'=>'2014级',
        ]);
        Grade::create([
            'name'=>'2013级',
        ]);
        Grade::create([
            'name'=>'2017级研',
        ]);
        Grade::create([
            'name'=>'2016级研',
        ]);
        Grade::create([
            'name'=>'2015级研',
        ]);
        Grade::create([
            'name'=>'2014级研',
        ]);
        Grade::create([
            'name'=>'2013级研',
        ]);
    }
}
