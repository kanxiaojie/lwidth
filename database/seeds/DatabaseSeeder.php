<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(LanguageTableSeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(GenderTableSeeder::class);
        $this->call(CollegeTableSeeder::class);
        $this->call(GradeTableSeeder::class);

        Model::reguard();
    }
}
