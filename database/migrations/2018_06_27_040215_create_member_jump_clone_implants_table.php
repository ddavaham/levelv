<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberJumpCloneImplantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_jump_clone_implants', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id');
            $table->unsignedInteger('clone_id');
            $table->unsignedInteger('implant_id');

            $table->primary(['member_id','clone_id', 'implant_id']);

            $table->foreign(['member_id', 'clone_id'])->references(['id', 'clone_id'])->on('member_jump_clones')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('implant_id')->references('id')->on('types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_jump_clone_implants');
    }
}
