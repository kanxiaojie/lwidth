<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('city', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('province_id')->unsigned();
            $table->integer('name');
            $table->timestamps();
        });

        // Schema::table('city', function (Blueprint $table) {
        //     $table->foreign('province_id')->references('id')->on('provinces')
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
        // Schema::table('city', function(Blueprint $table) {
        //     $table->dropForeign('city_province_id_foreign');
        // });
        Schema::drop('city');
    }
}
