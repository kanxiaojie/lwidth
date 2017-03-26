<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePraiseToCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('praiseToComments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('comment_id')->unsigned();
            $table->integer('like')->default(0);
            $table->timestamps();
        });

        Schema::table('praiseToComments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')
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
        Schema::table('praiseToComments', function(Blueprint $table) {
            $table->dropForeign('praiseToComments_user_id_foreign');
            $table->dropForeign('praiseToComments_comment_id_foreign');
        });

        Schema::drop('praiseToComments');
    }
}
