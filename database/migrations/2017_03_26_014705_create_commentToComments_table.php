<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentToCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commentToComments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('post_id')->unsigned();
            $table->integer('comment_id')->unsigned();
            $table->integer('parent_id')->unsigned()->default(0);
            $table->text('content');
            $table->timestamps();
        });

        Schema::table('commentToComments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('post_id')->references('id')->on('postings')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('comment_id')->references('id')->on('comments')
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
        Schema::table('commentToComments', function(Blueprint $table) {
            $table->dropForeign('commentToComments_user_id_foreign');
            $table->dropForeign('commentToComments_post_id_foreign');
            $table->dropForeign('commentToComments_comments_id_foreign');
        });

        Schema::drop('commentToComments');
    }
}
