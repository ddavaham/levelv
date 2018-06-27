<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('name');
            $table->timestamp('birthday')->nullable();
            $table->string('gender');
            $table->float('sec_status', 15, 13);
            $table->integer('corporation_id');
            $table->integer('alliance_id')->nullable();
            $table->integer('ancestry_id');
            $table->integer('bloodline_id');
            $table->integer('race_id');
            $table->timestamp('cached_until')->nullable();
            $table->timestamps();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('characters');
    }
}
