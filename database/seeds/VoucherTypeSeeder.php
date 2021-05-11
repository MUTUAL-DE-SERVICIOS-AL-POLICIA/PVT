<?php

use Illuminate\Database\Seeder;
use App\VoucherType;
use App\Module;


class VoucherTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = Module::whereName('prestamos')->first();
        $voucher_types = [
            ['name' => 'Efectivo', 'module_id' => $module->id],
            ['name' => 'Depósito Bancario', 'module_id' => $module->id]
        ];
        foreach ($voucher_types as $voucher_type) {
            VoucherType::firstOrCreate($voucher_type);
        }
    }
}
