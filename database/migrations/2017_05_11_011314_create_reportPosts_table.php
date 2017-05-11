<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportPosts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('badReport_type');
            $table->string('badReport_name');
            $table->string('badReport_content');
            $table->integer('reported_userId');
            $table->string('reported_userName')->nullable();
            $table->integer('post_id');
            $table->string('post_content');
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
        Schema::drop('reportPosts');
    }
}
