<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypeSkillzTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_skillz', function (Blueprint $table) {
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('skill_id');
            $table->unsignedTinyInteger('level');
            $table->timestamps();

            $table->primary(['type_id', 'skill_id']);

            $table->foreign('type_id')->references('id')->on('types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('type_skillz');
    }
}
