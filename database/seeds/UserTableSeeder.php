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
            'realname'=>'复旦君',
            'openid' => '1',
            'nickname' => '复旦君',
            'college_id'=>1,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user1->id ),
        ] );

        $user2 = User::create([
            'realname'=>'交大君',
            'openid' => '2',
            'nickname' => '交大君',
            'college_id'=>2,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user2->id ),
        ] );

        $user3 = User::create([
            'realname'=>'同济君',
            'openid' => '3',
            'nickname' => '同济君',
            'college_id'=>3,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user3->id ),
        ] );

        $user4 = User::create([
            'realname'=>'华师君',
            'openid' => '4',
            'nickname' => '华师君',
            'college_id'=>4,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user4->id ),
        ] );

        $user5 = User::create([
            'realname'=>'华理君',
            'openid' => '5',
            'nickname' => '华理君',
            'college_id'=>5,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user5->id ),
        ] );

        $user6 = User::create([
            'realname'=>'上大君',
            'openid' => '6',
            'nickname' => '上大君',
            'college_id'=>6,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user6->id ),
        ] );

        $user7 = User::create([
            'realname'=>'东华君',
            'openid' => '7',
            'nickname' => '东华君',
            'college_id'=>7,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user7->id ),
        ] );

        $user8 = User::create([
            'realname'=>'上财君',
            'openid' => '8',
            'nickname' => '上财君',
            'college_id'=>8,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user8->id ),
        ] );

        $user9 = User::create([
            'realname'=>'二军大君',
            'openid' => '9',
            'nickname' => '二军大君',
            'college_id'=>9,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user9->id ),
        ] );

        $user10 = User::create([
            'realname'=>'上理君',
            'openid' => '10',
            'nickname' => '上理君',
            'college_id'=>10,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user10->id ),
        ] );

        $user11 = User::create([
            'realname'=>'上师君',
            'openid' => '11',
            'nickname' => '上师君',
            'college_id'=>11,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user11->id ),
        ] );

        $user12 = User::create([
            'realname'=>'中医君',
            'openid' => '12',
            'nickname' => '中医君',
            'college_id'=>12,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user12->id ),
        ] );

        

        $user13 = User::create([
            'realname'=>'华政君',
            'openid' => '13',
            'nickname' => '华政君',
            'college_id'=>13,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user13->id ),
        ] );

        $user14 = User::create([
            'realname'=>'海事君',
            'openid' => '14',
            'nickname' => '海事君',
            'college_id'=>14,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user14->id ),
        ] );

        $user15 = User::create([
            'realname'=>'海洋君',
            'openid' => '15',
            'nickname' => '海洋君',
            'college_id'=>15,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user15->id ),
        ] );

        $user16 = User::create([
            'realname'=>'外经贸君',
            'openid' => '16',
            'nickname' => '外经贸君',
            'college_id'=>16,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user16->id ),
        ] );

        $user17 = User::create([
            'realname'=>'工程技术君',
            'openid' => '17',
            'nickname' => '工程技术君',
            'college_id'=>17,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user17->id ),
        ] );

        $user18 = User::create([
            'realname'=>'电力君',
            'openid' => '18',
            'nickname' => '电力君',
            'college_id'=>18,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user18->id ),
        ] );

        $user19 = User::create([
            'realname'=>'应用技术君',
            'openid' => '19',
            'nickname' => '应用技术君',
            'college_id'=>19,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user19->id ),
        ] );

        $user20 = User::create([
            'realname'=>'立信君',
            'openid' => '20',
            'nickname' => '立信君',
            'college_id'=>20,
            'role'=>0,
        ]);

        Profile::create( [
            'user_id' => intval( $user20->id ),
        ] );

    }
}
