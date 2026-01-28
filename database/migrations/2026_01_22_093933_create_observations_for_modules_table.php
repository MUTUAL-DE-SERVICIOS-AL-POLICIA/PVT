<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObservationsForModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('observation_for_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id')->unsigned();
            $table->foreign('module_id')->references('id')->on('modules');
            $table->unsignedBigInteger('observation_type_id')->unsigned();
            $table->foreign('observation_type_id')->references('id')->on('observation_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('observations_for_modules');
    }
}
