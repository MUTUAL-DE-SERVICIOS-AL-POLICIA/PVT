<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanPaymentProceduresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_payment_procedures', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('penal_payment');
            $table->string('description');
            $table->timestamps();
            $table->softDeletes();
        });

        // Agrega la restricción para permitir solo 1 o 2
        DB::statement("ALTER TABLE loan_payment_procedures ADD CONSTRAINT penal_payment_check CHECK (penal_payment IN (1, 2))");

        // Inserta dos registros por defecto
        DB::table('loan_payment_procedures')->insert([
            [
                'penal_payment' => 1,
                'description' => 'Interés sobre saldo capital',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'penal_payment' => 2,
                'description' => 'interes sobre cuotas vencidas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_payment_procedures');
    }
}
