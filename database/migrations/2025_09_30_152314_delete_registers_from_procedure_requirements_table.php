<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteRegistersFromProcedureRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            DB::table('procedure_requirements')->whereIn('id', [2196,2232,2350])->delete();
        });
        DB::table('procedure_requirements')->insert([
            'procedure_modality_id' => '84',
            'procedure_document_id' => '429',
            'number' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            DB::table('procedure_requirements')->insert([
                ['id' => 2196, 'procedure_modality_id' => 75, 'procedure_document_id' => 425, 'number' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2232, 'procedure_modality_id' => 79, 'procedure_document_id' => 425, 'number' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['id' => 2350, 'procedure_modality_id' => 84, 'procedure_document_id' => 428, 'number' => 0, 'created_at' => now(), 'updated_at' => now()],
            ]);
        });
    }
}
