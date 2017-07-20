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

        $this->call(UserTableSeeder::class);
        $this->call(GenderTableSeeder::class);
        $this->call(BadReportTypesTableSeeder::class);
        $this->call(LanguageTableSeeder::class);
        
        // $this->call(CountryTableSeeder::class);
        // $this->call(CollegeTableSeeder::class);
        // $this->call(GradeTableSeeder::class);
        

        Model::reguard();
    }
}
