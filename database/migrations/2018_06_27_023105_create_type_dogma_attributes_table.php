<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypeDogmaAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_dogma_attributes', function (Blueprint $table) {
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('attribute_id');
            $table->unsignedInteger('value');
            $table->timestamps();

            $table->primary(['type_id', 'attribute_id']);

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
        Schema::dropIfExists('type_dogma_attributes');
    }
}
