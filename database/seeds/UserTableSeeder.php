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
            'realname'=>'华大君',
            'openid' => '1',
            'nickname' => '华大君',
            'college_id'=>1,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user1->id ),
        ] );

        $user2 = User::create([
            'realname'=>'华光君',
            'openid' => '2',
            'nickname' => '华光君',
            'college_id'=>2,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user2->id ),
        ] );

        $user3 = User::create([
            'realname'=>'南中医翰林君',
            'openid' => '3',
            'nickname' => '南中医翰林君',
            'college_id'=>3,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user3->id ),
        ] );
    }
}
