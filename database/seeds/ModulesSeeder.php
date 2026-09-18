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
        $roleId = DB::table('roles')->insertGetId([
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
    }
}
