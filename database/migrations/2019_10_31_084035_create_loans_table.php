<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->bigIncrements('id');// id unico
            $table->string('code')->nullable(); // para el correlativo
            $table->unsignedBigInteger('disbursable_id');// id affiliado, id espouse
            $table->enum('disbursable_type',['affiliates', 'spouses']); // a quien se hara del desembolso//afiliado, conyugue
            $table->unsignedBigInteger('procedure_modality_id'); // id modalidad
            $table->foreign('procedure_modality_id')->references('id')->on('procedure_modalities');
            $table->date('disbursement_date')->nullable(); //fecha de desembolso
            $table->unsignedBigInteger('parent_loan_id')->nullable();  // id padre , loan padre
            $table->enum('parent_reason', ['REFINANCIAMIENTO', 'REPROGRAMACIÓN'])->nullable();// para indicar si es reprogramación y refinanciamiento 
            $table->date('request_date'); //fecha de solicitud
            $table->unsignedMediumInteger('amount_requested'); // monto solicitado
            $table->unsignedBigInteger('city_id');  // id lugar de la solicitud
            $table->foreign('city_id')->references('id')->on('cities');
            $table->unsignedBigInteger('interest_id')->nullable(false); // id del interes
            $table->foreign('interest_id')->references('id')->on('loan_interests');
            $table->unsignedBigInteger('state_id')->nullable(false); //id estado del tramite
            $table->foreign('state_id')->references('id')->on('loan_states'); // estado de prestamo
            $table->unsignedMediumInteger('amount_approved')->nullable(); // monto aprobado
            $table->float('payable_liquid_calculated',10,2); //promedio liquido pagable calculado
            $table->unsignedMediumInteger('bonus_calculated'); //total bonos calculado
            $table->float('indebtedness_calculated',5,2); //indice de endeudamiento calculado
            $table->float('liquid_qualification_calculated',10,2); //liquido para calificación calculado
            $table->unsignedSmallInteger('loan_term'); // plazo del prestamo en meses
            $table->unsignedBigInteger('payment_type_id'); // id tipo de desembolso
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->unsignedBigInteger('number_payment_type')->nullable(); // numero de cheque o numero cuenta del titular para el desembolso
            /*$table->unsignedBigInteger('personal_reference_id')->nullable(); // persona de referencia
            $table->foreign('personal_reference_id')->references('id')->on('personal_references');*/
            $table->unsignedBigInteger('destiny_id'); // id tipo de desembolso
            $table->foreign('destiny_id')->references('id')->on('loan_destinies');
            $table->unsignedBigInteger('financial_entity_id')->nullable(); // id tipo de tipo de entidad financiera
            $table->foreign('financial_entity_id')->references('id')->on('financial_entities');
            $table->unsignedBigInteger('role_id');  // id rol bandeja actual
            $table->foreign('role_id')->references('id')->on('roles');
            $table->boolean('validated')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
