<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('systemNotices', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('type')->default(0);
            $table->integer('if_read')->default(0);
            $table->integer('user_id')->nullable();
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->text('content');

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
        Schema::drop('systemNotices');
    }
}
