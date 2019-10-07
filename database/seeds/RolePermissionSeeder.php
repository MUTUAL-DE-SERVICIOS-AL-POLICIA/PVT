<?php

use Illuminate\Database\Seeder;
use App\Permission;

class RolePermissionSeeder extends Seeder
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
                'name' => 'update-role',
                'display_name' => 'Editar roles'
            ], [
                'name' => 'show-role',
                'display_name' => 'Ver roles'
            ]
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
