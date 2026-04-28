<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPaymentProcedureToLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->unsignedBigInteger('loan_payment_procedures_id')->nullable()->after('id');

            $table->foreign('loan_payment_procedures_id', 'loans_payment_procedures_fk')
                ->references('id')
                ->on('loan_payment_procedures')
                ->onDelete('set null');
        });

         DB::statement("
            UPDATE loans
            SET loan_payment_procedures_id = 2
            WHERE loans.state_id = 3
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign('loans_payment_procedures_fk');
            $table->dropColumn('loan_payment_procedures_id');
        });
    }
}
