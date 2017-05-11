<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportComments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('badReport_type');
            $table->string('badReport_name');
            $table->string('badReport_content');
            $table->integer('reported_userId');
            $table->string('reported_userName')->nullable();
            $table->integer('comment_id');
            $table->string('comment_content');
            $table->integer('report_userId');
            $table->string('report_userName')->nullable();
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
        Schema::drop('reportComments');
    }
}
