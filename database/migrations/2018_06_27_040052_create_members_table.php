<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('main')->nullable();
            $table->unsignedInteger('total_sp')->nullable();
            $table->unsignedBigInteger('clone_location_id')->nullable();
            $table->enum('clone_location_type', ['station', 'structure'])->nullable();
            $table->string('raw_hash');
            $table->string('hash', 64);
            $table->string('access_token')->nullable();
            $table->string('refresh_token', 512)->nullable();
            $table->json('scopes')->nullable();
            $table->unsignedInteger('token_error_count')->default(0);
            $table->boolean('disabled')->default(0);
            $table->text('disabled_reason')->nullable();
            $table->timestamp('disabled_timestamp')->nullable();
            $table->timestamp('expires')->nullable();
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
        Schema::dropIfExists('members');
    }
}
