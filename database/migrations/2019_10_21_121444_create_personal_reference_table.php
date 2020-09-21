<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalReferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_references', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('city_identity_card_id')->nullable();
            $table->foreign('city_identity_card_id')->references('id')->on('cities');
            $table->string('identity_card');
            $table->string('last_name');
            $table->string('mothers_last_name')->nullable();
            $table->string('first_name');
            $table->string('second_name')->nullable();
            $table->string('surname_husband')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('cell_phone_number')->nullable();
            $table->string('adress')->nullable();
            $table->boolval('cosigner')->default(0);
            $table->string('max_cosigner')->default(0);
            $table->unsignedBigInteger('loan_id'); // Nro de prestamo
            $table->foreign('loans_id')->references('id')->on('loans');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_references');
    }
}
