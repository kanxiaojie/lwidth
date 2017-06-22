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
            'realname'=>'北大君',
            'openid' => '1',
            'nickname' => '北大君',
            'college_id'=>1,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user1->id ),
        ] );

        $user2 = User::create([
            'realname'=>'清华君',
            'openid' => '2',
            'nickname' => '清华君',
            'college_id'=>2,
            'role'=>0,            
        ]);

        Profile::create( [
            'user_id' => intval( $user2->id ),
        ] );

        $user3 = User::create([
            'realname'=>'人大君',
            'openid' => '3',
            'nickname' => '人大君',
            'college_id'=>3,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user3->id ),
        ] );

        $user4 = User::create([
            'realname'=>'北师君',
            'openid' => '4',
            'nickname' => '北师君',
            'college_id'=>4,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user4->id ),
        ] );

        $user5 = User::create([
            'realname'=>'北航君',
            'openid' => '5',
            'nickname' => '北航君',
            'college_id'=>5,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user5->id ),
        ] );

        $user6 = User::create([
            'realname'=>'农大君',
            'openid' => '6',
            'nickname' => '农大君',
            'college_id'=>6,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user6->id ),
        ] );

        $user7 = User::create([
            'realname'=>'北理君',
            'openid' => '7',
            'nickname' => '北理君',
            'college_id'=>7,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user7->id ),
        ] );

        $user8 = User::create([
            'realname'=>'北交君',
            'openid' => '8',
            'nickname' => '北交君',
            'college_id'=>8,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user8->id ),
        ] );

        $user9 = User::create([
            'realname'=>'北科君',
            'openid' => '9',
            'nickname' => '北科君',
            'college_id'=>9,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user9->id ),
        ] );

        $user10 = User::create([
            'realname'=>'协和君',
            'openid' => '10',
            'nickname' => '协和君',
            'college_id'=>10,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user10->id ),
        ] );

        $user11 = User::create([
            'realname'=>'政法君',
            'openid' => '11',
            'nickname' => '政法君',
            'college_id'=>11,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user11->id ),
        ] );

        $user12 = User::create([
            'realname'=>'北邮君',
            'openid' => '12',
            'nickname' => '北邮君',
            'college_id'=>12,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user12->id ),
        ] );

        

        $user13 = User::create([
            'realname'=>'北化君',
            'openid' => '13',
            'nickname' => '北化君',
            'college_id'=>13,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user13->id ),
        ] );

        $user14 = User::create([
            'realname'=>'首师君',
            'openid' => '14',
            'nickname' => '首师君',
            'college_id'=>14,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user14->id ),
        ] );

        $user15 = User::create([
            'realname'=>'北工君',
            'openid' => '15',
            'nickname' => '北工君',
            'college_id'=>15,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user15->id ),
        ] );

        $user16 = User::create([
            'realname'=>'北林君',
            'openid' => '16',
            'nickname' => '北林君',
            'college_id'=>16,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user16->id ),
        ] );

        $user17 = User::create([
            'realname'=>'民族君',
            'openid' => '17',
            'nickname' => '民族君',
            'college_id'=>17,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user17->id ),
        ] );

        $user18 = User::create([
            'realname'=>'外经贸君',
            'openid' => '18',
            'nickname' => '外经贸君',
            'college_id'=>18,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user18->id ),
        ] );

        $user19 = User::create([
            'realname'=>'首医君',
            'openid' => '19',
            'nickname' => '首医君',
            'college_id'=>19,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user19->id ),
        ] );

        $user20 = User::create([
            'realname'=>'中财君',
            'openid' => '20',
            'nickname' => '中财君',
            'college_id'=>20,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user20->id ),
        ] );

    }
}
