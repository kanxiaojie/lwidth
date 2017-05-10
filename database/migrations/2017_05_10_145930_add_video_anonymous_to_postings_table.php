<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVideoAnonymousToPostingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('postings', function (Blueprint $table) {
            $table->string('video_url')->nullable()->after('pictures');
            $table->integer('anonymous')->default(0)->after('visiable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('postings', function (Blueprint $table) {
            //
        });
    }
}
