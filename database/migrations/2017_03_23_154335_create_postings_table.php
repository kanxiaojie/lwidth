<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('title',255);
            $table->text('content');
            $table->bigInteger('likenum')->default(0);
            $table->bigInteger('commentnum')->default(0);
            $table->string('pictures',4000);
            $table->timestamps();
        });

        Schema::table('postings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('postings', function(Blueprint $table) {
            $table->dropForeign('postings_user_id_foreign');
        });

        Schema::drop('postings');
    }
}
