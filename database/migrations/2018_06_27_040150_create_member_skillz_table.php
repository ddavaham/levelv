<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberSkillzTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_skillz', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedInteger('skill_id');
            $table->unsignedTinyInteger('active_skill_level');
            $table->unsignedTinyInteger('trained_skill_level');
            $table->unsignedInteger('skillpoints_in_skill');

            $table->primary(['id', 'skill_id']);

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
        Schema::dropIfExists('member_skillz');
    }
}
