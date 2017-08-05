<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePraisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('praises', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('post_id')->unsigned();
            $table->integer('like')->default(0);
            $table->timestamps();
        });

        // Schema::table('praises', function (Blueprint $table) {
        //     $table->foreign('user_id')->references('id')->on('users')
        //         ->onDelete('cascade')
        //         ->onUpdate('cascade');
        //     $table->foreign('post_id')->references('id')->on('postings')
        //         ->onDelete('cascade')
        //         ->onUpdate('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('praises', function(Blueprint $table) {
        //     $table->dropForeign('praises_user_id_foreign');
        //     $table->dropForeign('praises_post_id_foreign');
        // });
        Schema::drop('praises');
    }
}
