<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('openid');
            $table->string('unionId');
            $table->string('avatarUrl');
            $table->string('nickname');  
            $table->integer('gender');
            $table->string('realname');
            $table->string('password', 60);

            $table->integer('role')->default(1);
            $table->integer('trust')->default(1);
            $table->integer('available')->default(1);
            $table->string('disabled_reason');
            $table->string('pictures',4000);
            $table->integer('pictureOnWall')->default(1); 
            $table->bigInteger('praiseNums')->default(0);

            $table->string('wechat')
            $table->string('QQ')
            $table->string('phone')
            $table->string('weibo')

            $table->string('college_id');
            $table->string('major');
            $table->string('grade',400);

            $table->string('city_id');
            $table->string('province_id');
            $table->string('country_id');
            $table->integer('language_id')->default(1);
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
