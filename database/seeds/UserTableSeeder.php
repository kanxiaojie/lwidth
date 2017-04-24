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
            'realname'=>'福大君',
            'openid' => '1',
            'nickname' => '福大君',
            'college_id'=>1,
            
        ]);

        Profile::create( [
            'user_id' => intval( $user1->id ),
        ] );

        $user2 = User::create([
            'realname'=>'福大君1',
            'openid' => '2',
            'nickname' => '福大君1',
            'college_id'=>2,

        ]);

        Profile::create( [
            'user_id' => intval( $user2->id ),
        ] );

        $user3 = User::create([
            'realname'=>'师大君',
            'openid' => '3',
            'nickname' => '师大君',
            'college_id'=>3,

        ]);

        Profile::create( [
            'user_id' => intval( $user3->id ),
        ] );

        $user4 = User::create([
            'realname'=>'协和君',
            'openid' => '4',
            'nickname' => '协和君',
            'college_id'=>4,

        ]);

        Profile::create( [
            'user_id' => intval( $user4->id ),
        ] );

        $user5 = User::create([
            'realname'=>'医大君',
            'openid' => '5',
            'nickname' => '医大君',
            'college_id'=>5,

        ]);

        Profile::create( [
            'user_id' => intval( $user5->id ),
        ] );

        $user6 = User::create([
            'realname'=>'中医君',
            'openid' => '6',
            'nickname' => '中医君',
            'college_id'=>6,

        ]);

        Profile::create( [
            'user_id' => intval( $user6->id ),
        ] );

        $user7 = User::create([
            'realname'=>'农大君',
            'openid' => '7',
            'nickname' => '农大君',
            'college_id'=>7,

        ]);

        Profile::create( [
            'user_id' => intval( $user7->id ),
        ] );

        $user8 = User::create([
            'realname'=>'工程君',
            'openid' => '8',
            'nickname' => '工程君',
            'college_id'=>8,

        ]);

        Profile::create( [
            'user_id' => intval( $user8->id ),
        ] );

        $user9 = User::create([
            'realname'=>'闽江君',
            'openid' => '9',
            'nickname' => '闽江君',
            'college_id'=>9,

        ]);

        Profile::create( [
            'user_id' => intval( $user9->id ),
        ] );

        $user10 = User::create([
            'realname'=>'江夏君',
            'openid' => '10',
            'nickname' => '江夏君',
            'college_id'=>10,

        ]);

        Profile::create( [
            'user_id' => intval( $user10->id ),
        ] );

        $user11 = User::create([
            'realname'=>'教育君',
            'openid' => '11',
            'nickname' => '教育君',
            'college_id'=>11,

        ]);

        Profile::create( [
            'user_id' => intval( $user11->id ),
        ] );

        $user12 = User::create([
            'realname'=>'女子君',
            'openid' => '12',
            'nickname' => '女子君',
            'college_id'=>12,

        ]);

        Profile::create( [
            'user_id' => intval( $user12->id ),
        ] );

        $user13 = User::create([
            'realname'=>'福职君',
            'openid' => '13',
            'nickname' => '福职君',
            'college_id'=>13,

        ]);

        Profile::create( [
            'user_id' => intval( $user13->id ),
        ] );

        $user14 = User::create([
            'realname'=>'海洋君',
            'openid' => '14',
            'nickname' => '海洋君',
            'college_id'=>14,

        ]);

        Profile::create( [
            'user_id' => intval( $user14->id ),
        ] );

        $user15 = User::create([
            'realname'=>'至诚君',
            'openid' => '15',
            'nickname' => '至诚君',
            'college_id'=>15,

        ]);

        Profile::create( [
            'user_id' => intval( $user15->id ),
        ] );

        $user16 = User::create([
            'realname'=>'阳光君',
            'openid' => '16',
            'nickname' => '阳光君',
            'college_id'=>16,

        ]);

        Profile::create( [
            'user_id' => intval( $user16->id ),
        ] );

        $user17 = User::create([
            'realname'=>'金山君',
            'openid' => '17',
            'nickname' => '金山君',
            'college_id'=>17,

        ]);

        Profile::create( [
            'user_id' => intval( $user17->id ),
        ] );

        $user18 = User::create([
            'realname'=>'东方君',
            'openid' => '18',
            'nickname' => '东方君',
            'college_id'=>18,

        ]);

        Profile::create( [
            'user_id' => intval( $user18->id ),
        ] );

        $user19 = User::create([
            'realname'=>'警察君',
            'openid' => '19',
            'nickname' => '警察君',
            'college_id'=>19,

        ]);

        Profile::create( [
            'user_id' => intval( $user19->id ),
        ] );

        $user20 = User::create([
            'realname'=>'外语外贸君',
            'openid' => '20',
            'nickname' => '外语外贸君',
            'college_id'=>20,

        ]);

        Profile::create( [
            'user_id' => intval( $user20->id ),
        ] );


    }
}
