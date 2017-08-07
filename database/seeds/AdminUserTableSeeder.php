<?php

use App\AdminUser;
use App\Profile;
use App\User;
use Illuminate\Database\Seeder;

class AdminUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = AdminUser::create([
            'name' => 'admin',
            'password'=>md5("admin"),
            'role' => 2,
        ]);


    }
}
