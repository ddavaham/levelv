<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorporationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporations', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('name');
            $table->string('ticker');
            $table->unsignedInteger('member_count');
            $table->unsignedBigInteger('ceo_id');
            $table->integer('alliance_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('date_founded')->nullable();
            $table->unsignedBigInteger('creator_id');
            $table->unsignedInteger('home_station_id');
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
        Schema::dropIfExists('corporations');
    }
}
