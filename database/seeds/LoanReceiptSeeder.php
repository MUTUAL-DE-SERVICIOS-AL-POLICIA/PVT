<?php

use Illuminate\Database\Seeder;
use App\Module;
use App\Role;

class LoanReceiptRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $module = Module::whereName('prestamos')->first();
        $permissions = ['create-affiliate', 'update-affiliate-secondary', 'show-affiliate'];

        if ($module) {
            $role = Role::firstOrCreate([
                'name' => 'area-de-recepcion',
                'display_name' => 'Área de Recepción',
                'action' => 'Recepcionado',
                'module_id' => $module->id
            ]);
            $role->syncPermissions($permissions);
        }
    }
}
