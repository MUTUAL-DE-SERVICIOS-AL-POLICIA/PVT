<?php

use Illuminate\Database\Seeder;
use App\Module;
use App\ProcedureModality;
use App\ProcedureType;
class ProcedureAmortizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = Module::whereName('prestamos')->first();
        $data = [
            'procedures' => [
                'amortizacion directa' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización Directa','second_name'=>'Amort. DIRECTA'],
                    'modalities' => [ ['name'=>'Directo','shortened'=>'DIRECTO','requirements'=>[]],
                    ]
                ],
                'amortizacion complemento' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización Complemento Económico','second_name'=>'Amort. CE'],
                    'modalities' => [ ['name'=>'Complemento Económico','shortened'=>'COMP-ECO','requirements'=>[]],
                    ]
                ],
                'amortizacion fondo' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización Fondo de Retiro','second_name'=>'Amort. FR'],
                    'modalities' => [ ['name'=>'Fondo de Retiro','shortened'=>'FRP','requirements'=>[]],
                    ]
                ],
                'amortizacion automatica' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización Automática','second_name'=>'Amort. AUT'],
                    'modalities' => [ ['name'=>'Descuento Comando General de la Policia Boliviana','shortened'=>'DES-COMANDO','requirements'=>[]],
                                    ['name'=>'Descuento Servicio Nacional del Sistema de Reparto','shortened'=>'DES-SENASIR','requirements'=>[]],
                    ]
                ],
                'amortizacion por ajuste' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización por Ajuste','second_name'=>'Amort. AJUST'],
                    'modalities' => [ ['name'=>'Descuento Indebido','shortened'=>'DES-INDEBIDO','requirements'=>[]],
                                    ['name'=>'Refinanciamiento de Préstamo','shortened'=>'REFINANCIAMIENTO','requirements'=>[]]
                    ]
                ],
            ]
        ];
        foreach ($data['procedures'] as $procedure) {
            $new_procedure = ProcedureType::firstOrCreate($procedure['type']);
            foreach ($procedure['modalities'] as $modality) {
                $new_modality = ProcedureModality::firstOrCreate([
                    'procedure_type_id' => $new_procedure->id,
                    'name'=>$modality['name'],
                    'shortened'=>$modality['shortened']
                ]);
            }
        }
    }
}
