<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypeDogmaEffectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_dogma_effects', function (Blueprint $table) {
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('effect_id');
            $table->boolean('is_default');
            $table->timestamps();

            $table->primary(['type_id', 'effect_id']);

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
        Schema::dropIfExists('type_dogma_effects');
    }
}
