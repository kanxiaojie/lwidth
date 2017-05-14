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
            'realname'=>'上济北校区',
            'openid' => '1',
            'nickname' => '上济北校区',
            'college_id'=>1,
            
        ]);

        Profile::create( [
            'user_id' => intval( $user1->id ),
        ] );

        $user3 = User::create([
            'realname'=>'济南校区',
            'openid' => '2',
            'nickname' => '济南校区',
            'college_id'=>2,

        ]);

        Profile::create( [
            'user_id' => intval( $user3->id ),
        ] );



    }
}
