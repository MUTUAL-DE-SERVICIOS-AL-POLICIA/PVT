<?php

use Illuminate\Database\Seeder;
use App\Permission;

class AffiliatePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'name' => 'create-affiliate',
                'display_name' => 'Crear afiliados'
            ], [
                'name' => 'update-affiliate-primary',
                'display_name' => 'Editar datos principales de afiliados'
            ], [
                'name' => 'update-affiliate-secondary',
                'display_name' => 'Editar datos secundarios de afiliados'
            ], [
                'name' => 'show-affiliate',
                'display_name' => 'Ver afiliados'
            ], [
                'name' => 'delete-affiliate',
                'display_name' => 'Eliminar afiliados'
            ]
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
