<?php

use Illuminate\Database\Seeder;
use App\Permission;

class RecordPermissionSeeder extends Seeder
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
                'name' => 'show-record',
                'display_name' => 'Ver registros de actividad'
            ], [
                'name' => 'delete-record',
                'display_name' => 'Eliminar registros de actividad'
            ]
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}