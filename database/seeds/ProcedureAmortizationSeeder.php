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
                    'modalities' => [ ['name'=>'A.D. Cuota pactada','shortened'=>'AD-Cuota-pactada','requirements'=>[]],
                                      ['name'=>'A.D. Liquidar préstamo','shortened'=>'AD-Liquidar-préstamo','requirements'=>[]],
                                      ['name'=>'A.D. Introducir monto','shortened'=>'AD-Indroducir-monto','requirements'=>[]],
                    ]
                ],
                'amortizacion complemento' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización Complemento Económico','second_name'=>'Amort. CE'],
                    'modalities' => [ ['name'=>'A.C.E. Introducir monto','shortened'=>'ACE-Indroducir-monto','requirements'=>[]],
                                      ['name'=>'A.C.E. Liquidar préstamo','shortened'=>'ACE-Liquidar-préstamo','requirements'=>[]],
                    ]
                ],
                'amortizacion fondo' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización Fondo de Retiro','second_name'=>'Amort. FR'],
                    'modalities' => [ ['name'=>'A.F.R. Introducir monto','shortened'=>'AFR-Indroducir-monto','requirements'=>[]],
                                       ['name'=>'A.F.R. Liquidar préstamo','shortened'=>'AFRT','requirements'=>[]],
                    ]
                ],
                'amortizacion automatica' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización Automática','second_name'=>'Amort. AUT'],
                    'modalities' => [ ['name'=>'A.AUT. Cuota pactada','shortened'=>'AA-Cuota-pactada','requirements'=>[]],
                                    ['name'=>'A.AUT. Parcial','shortened'=>'AA-Parcial','requirements'=>[]],
                    ]
                ],
                'amortizacion por ajuste' => [
                    'type' => ['module_id' => $module->id,'name'=>'Amortización por Ajuste','second_name'=>'Amort. AJUST'],
                    'modalities' => [ ['name'=>'A.AJ. Introducir monto','shortened'=>'AAJ-Introducir-monto','requirements'=>[]],
                                    ['name'=>'A.AJ. Liquidar préstamo','shortened'=>'AAJ-Liquidar-préstamo','requirements'=>[]]
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
