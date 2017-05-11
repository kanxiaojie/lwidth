<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReportUserToReportPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reportPosts', function (Blueprint $table) {
            $table->integer('report_userId');
            $table->string('report_userName')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reportPosts', function (Blueprint $table) {
            //
        });
    }
}
