<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLoanProcedureId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_global_parameters', function (Blueprint $table) {
            $table->unsignedInteger('min_amount_fund_rotary')->change();
            $table->bigInteger('loan_procedure_id')->nullable();
            $table->foreign('loan_procedure_id')->references('id')->on('loan_procedures');
            $table->float('days_year_calculated',10,6)->nullable();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->bigInteger('loan_procedure_id')->nullable();
            $table->foreign('loan_procedure_id')->references('id')->on('loan_procedures');
        });

        Schema::table('loan_modality_parameters', function (Blueprint $table) {
            $table->bigInteger('loan_procedure_id')->nullable();
            $table->foreign('loan_procedure_id')->references('id')->on('loan_procedures');
            $table->unique(['procedure_modality_id', 'loan_procedure_id']);
            $table->float('max_approved_amount')->nullable();
            $table->float('guarantor_debt_index',5,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_global_parameters', function (Blueprint $table) {
            //
        });

        Schema::table('loans', function (Blueprint $table) {
            //
        });

        Schema::table('loan_modality_parameters', function (Blueprint $table) {
            //
        });
    }
}
