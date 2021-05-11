<?php

use Illuminate\Database\Seeder;
use App\ProcedureType;
use App\RoleSequence;
use App\Role;

class RoleSequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   //Flujo de Prestamo anticipo
        RoleSequence::flushEventListeners();
        $sequences = [
            'Préstamo Anticipo' => [
                ['PRE-regional-santa-cruz', 'PRE-regional-cochabamba', 'PRE-regional-oruro', 'PRE-regional-potosi', 'PRE-regional-sucre', 'PRE-regional-tarija', 'PRE-regional-trinidad', 'PRE-regional-cobija'],
                ['PRE-recepcion'],
                ['PRE-calificacion'],
                ['PRE-jefatura'],
                ['PRE-revision-legal'],
                ['PRE-aprobacion-direccion'],
                ['PRE-presupuesto'],
                ['PRE-contabilidad'],
                ['PRE-tesoreria'],
                ['PRE-archivo'],   
            ]
        ];
        $this->create($sequences);
        //Flujo de Préstamo a corto plazo Refinanciamiento
        $sequences = [
            'Refinanciamiento Préstamo a Corto Plazo' => [
                ['PRE-regional-santa-cruz', 'PRE-regional-cochabamba', 'PRE-regional-oruro', 'PRE-regional-potosi', 'PRE-regional-sucre', 'PRE-regional-tarija', 'PRE-regional-trinidad', 'PRE-regional-cobija'],
                ['PRE-recepcion'],
                ['PRE-calificacion'],
                ['PRE-cobranzas-corte'],
                ['PRE-aprobacion-calificacion'], 
                ['PRE-jefatura'],
                ['PRE-aprobacion-direccion'],
                ['PRE-revision-legal'],
                ['PRE-presupuesto'],
                ['PRE-contabilidad'],
                ['PRE-tesoreria'],
                ['PRE-archivo'],     
            ]
        ];
        $this->create($sequences);
    //Flujo de Préstamo a corto plazo 
      $sequences = [
            'Préstamo a Corto Plazo' => [
                ['PRE-regional-santa-cruz', 'PRE-regional-cochabamba', 'PRE-regional-oruro', 'PRE-regional-potosi', 'PRE-regional-sucre', 'PRE-regional-tarija', 'PRE-regional-trinidad', 'PRE-regional-cobija'],
                ['PRE-recepcion'],
                ['PRE-calificacion'],
                ['PRE-jefatura'],
                ['PRE-aprobacion-direccion'],
                ['PRE-revision-legal'],
                ['PRE-presupuesto'],
                ['PRE-contabilidad'],
                ['PRE-tesoreria'],
                ['PRE-archivo'],    
            ]
        ];
        $this->create($sequences);
         //Flujo de Préstamo a largo plazo
         $sequences = [
            'Préstamo a Largo Plazo' => [
                ['PRE-regional-santa-cruz', 'PRE-regional-cochabamba', 'PRE-regional-oruro', 'PRE-regional-potosi', 'PRE-regional-sucre', 'PRE-regional-tarija', 'PRE-regional-trinidad', 'PRE-regional-cobija'],
                ['PRE-recepcion'],
                ['PRE-calificacion'],
                ['PRE-jefatura'],
                ['PRE-revision-direccion'],
                ['PRE-revision-legal'],
                ['PRE-presupuesto'],
                ['PRE-contabilidad'],
                ['PRE-tesoreria'],
                ['PRE-archivo'], 
            ]
        ];
        $this->create($sequences);
            //Flujo de Préstamo a largo plazo Refinanciamiento
            $sequences = [
                'Refinanciamiento Préstamo a Largo Plazo' => [
                    ['PRE-regional-santa-cruz', 'PRE-regional-cochabamba', 'PRE-regional-oruro', 'PRE-regional-potosi', 'PRE-regional-sucre', 'PRE-regional-tarija', 'PRE-regional-trinidad', 'PRE-regional-cobija'],
                    ['PRE-recepcion'],
                    ['PRE-calificacion'],
                    ['PRE-cobranzas-corte'],
                    ['PRE-aprobacion-calificacion'],
                    ['PRE-jefatura'],     
                    ['PRE-aprobacion-direccion'],
                    ['PRE-revision-legal'],
                    ['PRE-presupuesto'],
                    ['PRE-contabilidad'],
                    ['PRE-tesoreria'],
                    ['PRE-archivo'], 
                ]
            ];
            $this->create($sequences);
        //Flujo de Préstamo hipotecario
        $sequences = [
            'Préstamo Hipotecario' => [
                ['PRE-regional-santa-cruz', 'PRE-regional-cochabamba', 'PRE-regional-oruro', 'PRE-regional-potosi', 'PRE-regional-sucre', 'PRE-regional-tarija', 'PRE-regional-trinidad', 'PRE-regional-cobija'],
                ['PRE-recepcion'],
                ['PRE-calificacion'],
                ['PRE-jefatura'],
                ['PRE-revision-legal'],
                ['PRE-revision-direccion'],
                ['PRE-aprobacion-direccion'],
                ['PRE-aprobacion-legal'],
                ['PRE-presupuesto'],
                ['PRE-contabilidad'],
                ['PRE-tesoreria'],
                ['PRE-archivo'],   
            ]
        ];
        $this->create($sequences);
        //Flujo de Préstamo hipotecario refinanciamiento
        $sequences = [
            'Refinanciamiento Préstamo Hipotecario' => [
                ['PRE-regional-santa-cruz', 'PRE-regional-cochabamba', 'PRE-regional-oruro', 'PRE-regional-potosi', 'PRE-regional-sucre', 'PRE-regional-tarija', 'PRE-regional-trinidad', 'PRE-regional-cobija'],
                ['PRE-recepcion'],
                ['PRE-calificacion'],
                ['PRE-cobranzas-corte'],
                ['PRE-aprobacion-calificacion'], 
                ['PRE-jefatura'],
                ['PRE-revision-legal'],
                ['PRE-revision-direccion'],
                ['PRE-aprobacion-direccion'],
                ['PRE-aprobacion-legal'],
                ['PRE-presupuesto'],
                ['PRE-contabilidad'],
                ['PRE-tesoreria'],
                ['PRE-archivo'],   
            ]
        ];
        $this->create($sequences);
        //Fujo de Registro de Pago
        RoleSequence::flushEventListeners();
        $sequences = [
            'Amortización Directa' => [
                ['PRE-cobranzas'],
                ['PRE-tesoreria-cobros']
            ]
        ];
        $this->create($sequences); 
    }

    public function create($sequences){
        foreach ($sequences as $procedure_type => $sequence) {
            $procedure = ProcedureType::whereName($procedure_type)->first();
            foreach ($sequence as $i => $current) {
                if ($i > 0) {
                    $j=$i;
                    foreach ($current as $next_role) {
                        $previous = $sequence[$i-1];
                        $curr = Role::whereName($next_role)->first();
                        foreach ($previous as $role) {
                            $prev = Role::whereName($role)->first();
                            if ($curr && $prev) {
                                RoleSequence::firstOrCreate([
                                    'procedure_type_id' => $procedure->id,
                                    'role_id' => $prev->id,
                                    'next_role_id' => $curr->id,
                                    'sequence_number_flow'=>$j
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}
