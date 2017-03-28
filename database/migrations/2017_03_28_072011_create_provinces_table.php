<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvincesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->unsigned()->default(1);
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('provinces', function (Blueprint $table) {
            $table->foreign('country_id')->references('id')->on('countries')
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
        Schema::table('provinces', function(Blueprint $table) {
            $table->dropForeign('provinces_country_id_foreign');
        });
        Schema::drop('provinces');
    }
}
