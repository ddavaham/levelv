<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberSkillQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_skill_queue', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedInteger('queue_position');
            $table->unsignedInteger('skill_id');
            $table->unsignedInteger('finished_level');
            $table->unsignedInteger('level_start_sp')->nullable();
            $table->unsignedInteger('level_end_sp')->nullable();
            $table->unsignedInteger('training_start_sp')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('finish_date')->nullable();
            $table->timestamps();

            $table->primary(['id', 'queue_position']);

            $table->foreign('id')->references('id')->on('members')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_skill_queue');
    }
}
