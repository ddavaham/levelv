<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillplanMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skillplan_members', function (Blueprint $table) {
            $table->string('plan_id');
            $table->unsignedBigInteger('member_id');
            $table->enum('member_type', ['character', 'corporation', 'alliance']);
            $table->enum('role', ['member', 'operator', 'administrator']);
            $table->timestamps();

            $table->primary(['plan_id', 'member_id']);

            $table->foreign('plan_id')->references('id')->on('skillplans')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skillplan_members');
    }
}
