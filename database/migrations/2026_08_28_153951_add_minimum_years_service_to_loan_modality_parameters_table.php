<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinimumYearsServiceToLoanModalityParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_modality_parameters', function (Blueprint $table) {
            $table->unsignedInteger('minimum_years_service')->default(1);
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
            $table->dropColumn('minimum_years_service');
        });
    }
}
