<?php

use App\Profile;
use App\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::create([
            'nickname' => '墙君',
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user1->id ),
        ] );

    }
}
