<?php

use Illuminate\Database\Seeder;

class ProcedureModalityRefinancingSeasonSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $procedure_modality = [
            'procedure_type_id' => 29,
            'name' => 'Refinanciamiento Estacional para el Sector Pasivo de la Policía Boliviana con Cónyuge',
            'shortened' => 'REF-EST-PAS-CON',
            'is_valid' => true,
            'workflow_id' => 11,
        ];
        $existing = DB::table('procedure_modalities')
            ->where('shortened', $procedure_modality['shortened'])
            ->first();
        if (!$existing) {
            DB::table('procedure_modalities')->insert($procedure_modality);
        }
    }
}