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
            $table->string('realname');
            $table->string('nickname');
            $table->string('openid');
            $table->string('major');
            $table->string('college_id');
            $table->string('city_id');
            $table->string('province_id');
            $table->string('country_id');
            $table->string('unionId');
            $table->string('avatarUrl');
            $table->integer('language_id')->default(1);
            $table->integer('gender');
            $table->integer('trust')->default(1);
            $table->integer('available')->default(1);
            $table->string('disabled_reason');
            $table->string('pictures',4000);

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
