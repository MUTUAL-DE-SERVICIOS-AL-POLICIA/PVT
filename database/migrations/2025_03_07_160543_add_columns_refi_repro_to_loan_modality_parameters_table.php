<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsRefiReproToLoanModalityParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_modality_parameters', function (Blueprint $table) {
            $table->unsignedBigInteger('modality_refinancing_id')->nullable();
            $table->foreign('modality_refinancing_id')->references('id')->on('procedure_modalities');
            $table->unsignedBigInteger('modality_reprogramming_id')->nullable();
            $table->foreign('modality_reprogramming_id')->references('id')->on('procedure_modalities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_modality_parameters', function (Blueprint $table) {
            $table->dropColumn('modality_refinancing_id');
            $table->dropColumn('modality_reprogramming_id');
        });
    }
}
