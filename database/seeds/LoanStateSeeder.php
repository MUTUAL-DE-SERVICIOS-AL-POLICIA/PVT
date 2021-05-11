<?php

use Illuminate\Database\Seeder;
use App\Module;
use App\LoanState;

class LoanStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = [
            ['name' => 'En Proceso', 'description' => 'Prestamo en tramite' ],
            ['name' => 'Anulado', 'description' => 'Tramite del prestamo Anulado' ],
            ['name' => 'Vigente', 'description' => 'Préstamo en vigencia' ],
            ['name' => 'Liquidado', 'description' => 'Pago de la deuda en su totalidad' ],
        ];
        foreach ($states as $state) {
            LoanState::firstOrCreate($state);
        }
    }
}
