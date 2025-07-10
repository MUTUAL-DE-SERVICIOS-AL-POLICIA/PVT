<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanRequirementsRefinancingSeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modality_id = DB::table('procedure_modalities')
            ->where('shortened', 'REF-EST-PAS-CON')
            ->value('id');

        DB::table('procedure_requirements')->insert([
            [
                'procedure_modality_id' => $modality_id,
                'procedure_document_id' => 423,
                'number' => 0,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ],
            [
                'procedure_modality_id' => $modality_id,
                'procedure_document_id' => 297,
                'number' => 1,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ],
            [
                'procedure_modality_id' => $modality_id,
                'procedure_document_id' => 422,
                'number' => 2,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ],
            [
                'procedure_modality_id' => $modality_id,
                'procedure_document_id' => 272,
                'number' => 3,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ],
            [
                'procedure_modality_id' => $modality_id,
                'procedure_document_id' => 367,
                'number' => 4,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ],
            [
                'procedure_modality_id' => $modality_id,
                'procedure_document_id' => 424,
                'number' => 5,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ],
            [
                'procedure_modality_id' => $modality_id,
                'procedure_document_id' => 418,
                'number' => 6,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}
