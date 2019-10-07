<?php

use Illuminate\Database\Seeder;
use App\City;
use App\Role;
use App\User;
use App\Permission;

class AdminSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $user = User::whereUsername('admin')->first();
    if (!$user) {
      $user = User::create([
        'username' => 'admin',
        'first_name' => 'Administrador',
        'last_name' => 'Administrador',
        'password' => Hash::make('admin'),
        'position' => 'Administrador',
        'status' => 'active',
        'city_id' => City::first()->id
      ]);
    }
    $role = Role::whereName('admin')->first();
    $user->roles()->sync($role);
    $role->syncPermissions(['create-user', 'update-user', 'show-user', 'delete-user', 'show-record', 'delete-record', 'update-role', 'show-role']);
  }
}
