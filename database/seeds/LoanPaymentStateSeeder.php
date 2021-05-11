<?php

use Illuminate\Database\Seeder;
use App\LoanPaymentState;


class LoanPaymentStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = [
            ['name' => 'Anulado', 'description' => 'Registro de pago Anulado' ],
            ['name' => 'Pendiente de Pago', 'description' => 'Registro de pago pendiente' ],
            ['name' => 'Pendiente por confirmar', 'description' => 'Registro de pendiente por confirmar' ],
            ['name' => 'Pagado', 'description' => 'Registro de pago validado y confirmado' ],
        ];
        foreach ($states as $state) {
            LoanPaymentState::firstOrCreate($state);
        }
    }
}
