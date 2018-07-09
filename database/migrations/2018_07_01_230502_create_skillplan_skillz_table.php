<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillplanSkillzTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skillplan_skillz', function (Blueprint $table) {
            $table->string('plan_id', 32);
            $table->unsignedTinyInteger('position');
            $table->unsignedInteger('type_id');
            $table->unsignedTinyInteger('level');
            $table->unsignedTinyInteger('rank');
            $table->string('primaryAttribute');
            $table->string('secondaryAttribute');
            $table->timestamps();

            $table->primary(['plan_id','position']);

            $table->index('type_id');

            $table->foreign('plan_id')->references('id')->on('skillplans')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skillplan_skillz');
    }
}
