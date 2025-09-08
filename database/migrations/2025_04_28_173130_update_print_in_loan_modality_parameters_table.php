<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePrintInLoanModalityParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('loan_modality_parameters')
        ->whereIn('procedure_modality_id', [32,33,34,35,67,93,94,96])
        ->update([
            'print_contract_platform' => true,
            'print_form_qualification_platform' => false
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('loan_modality_parameters')
        ->whereIn('procedure_modality_id', [32,33,34,35,67,93,94,96])
        ->update([
            'print_contract_platform' => true,
            'print_form_qualification_platform' => false
        ]);
    }
}
