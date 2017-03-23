<?php

use App\Country;
use Illuminate\Database\Seeder;

class CountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Country::create([
            'tag'=>'china',
            'title' => '中国',
        ]);
        Country::create([
            'tag'=>'america',
            'title' => '美国',
        ]);
    }
}
