<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveMinServiceYearsFromLoanGlobalParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_global_parameters', function (Blueprint $table) {
            $table->dropColumn('min_service_years');
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
            $table->unsignedInteger('min_service_years')->default(1);
        });
    }
}
