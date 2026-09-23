<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Registrar el módulo de Asesoramiento Jurídico
        $moduleId = DB::table('modules')->insertGetId([
            'display_name' => 'Asesoramiento Jurídico',
            'description' => 'Dirección de Asesoramiento Jurídico',
            'name' => 'asesoramiento-jurídico',
            'shortened' => 'DAJ',
        ]);

        // Registrar el rol de Dirección DAJAYDI 
        DB::table('roles')->insertGetId([
            'module_id' => $moduleId,
            'display_name' => 'Dirección DAJAYDI',
            'action' => 'Observador',
            'created_at' => now(),
            'updated_at' => now(),
            'correlative' => null,
            'name' => 'DAJ-direccion-dajaydi',
            'sequence_number' => null,
            'description' => null,
            'wf_states_id' => null,
        ]);
         
        // Registrar observación en la tabla observation_types
        $observationTypeId = DB::table('observation_types')->insertGetId([
            'module_id' => $moduleId,
            'name' => 'Excluido - Tiene sentencias condenatorias ejecutoriadas por delitos cometidos contra la MUSERPOL o MUSEPOL.',
            'description' => 'Denegado',
            'type' => 'A',
            'shortened' => 'Sentencias condenatorias ejecutoriadas',
            'active' => true
        ]);

        // Registrar observación en la tabla observation_for_modules
        DB::table('observation_for_modules')->insert([
            'module_id' => 6,
            'observation_type_id' => $observationTypeId
        ]);
    }
}
