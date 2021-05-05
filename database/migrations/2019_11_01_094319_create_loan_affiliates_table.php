<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAffiliatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_affiliates', function (Blueprint $table) {
            $table->unsignedBigInteger('loan_id')->unsigned();
            $table->foreign('loan_id')->references('id')->on('loans');
            $table->unsignedBigInteger('affiliate_id')->unsigned();
            $table->foreign('affiliate_id')->references('id')->on('affiliates');
            $table->float('payment_percentage',5,2);// porcentaje de descuento
            $table->boolean('guarantor')->default(false);//si es garante
            $table->float('payable_liquid_calculated',10,2); //promedio liquido pagable calculado individual
            $table->float('bonus_calculated',5,2); //total bonos calculado
            $table->float('quota_previous',5,2); //cuota de refinanciamiento o reprogramación individual
            $table->float('quota_treat',5,2)->nullable(); //cuota pactada del afiliado
            $table->float('indebtedness_calculated',5,2)->nullable(); //indice de endeudamiento calculado individual
            $table->float('liquid_qualification_calculated',10,2); //liquido para calificación calculado individual
            $table->json('contributionable_ids')->nullable(); // ids de las contribuciones si es requerido se definira
            $table->enum('contributionable_type', ['contributions', 'aid_contributions','loan_contribution_adjusts'])->nullable(); // si es requerido se definira
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_affiliates');
    }
}
