<?php

use Illuminate\Database\Seeder;
use App\RecordType;

class RecordTypeSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RecordType::firstOrCreate([
            'name' => 'sistema'
        ], [
            'display_name' => 'Sistema',
            'description' => 'Cambios al Sistema'
        ]);
    }
}
