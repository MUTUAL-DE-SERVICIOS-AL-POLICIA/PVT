<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSuggestedDebtIndexInLoanModalityParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('loan_modality_parameters')
        ->where('loan_procedure_id', 3)
        ->update(['suggested_debt_index' => 60]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('loan_modality_parameters')
        ->where('loan_procedure_id', 3)
        ->update(['suggested_debt_index' => 50]);
    }
}
