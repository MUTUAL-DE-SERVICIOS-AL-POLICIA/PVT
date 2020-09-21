<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanModalityParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_modality_parameters', function (Blueprint $table) {
            $table->unsignedBigInteger('procedure_modality_id'); // id modalidad de prestamo
            $table->foreign('procedure_modality_id')->references('id')->on('procedure_modalities');
            $table->float('debt_index',5,2)->nullable(); // indice de endeudamiento
            $table->unsignedTinyInteger('quantity_ballots');// cantidad de boletas
            $table->unsignedTinyInteger('guarantors');// cantidad de garantes
            $table->unsignedTinyInteger('max_lenders');// maxima cantidad de titulares
            $table->float('min_guarantor_category',3,2)->nullable(); //categoria mínima de garante
            $table->float('max_guarantor_category',3,2)->nullable(); //categoria máxima de garante
            $table->boolean('personal_reference')->default(false);//cantidad de referencia de personas
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_modality_parameters');
    }
}
