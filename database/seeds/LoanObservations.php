<?php

use Illuminate\Database\Seeder;

class LoanObservations extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $loan_observations = [
            ['module_id' => 6, 'observation_type_id' => 1],
            ['module_id' => 6, 'observation_type_id' => 2],
            ['module_id' => 6, 'observation_type_id' => 6],
            ['module_id' => 6, 'observation_type_id' => 8],
            ['module_id' => 6, 'observation_type_id' => 9],
            ['module_id' => 6, 'observation_type_id' => 13],
            ['module_id' => 6, 'observation_type_id' => 21],
            ['module_id' => 6, 'observation_type_id' => 26],
            ['module_id' => 6, 'observation_type_id' => 31],
            ['module_id' => 6, 'observation_type_id' => 46],
        ];
        DB::table('observation_for_modules')->insert($loan_observations);
    }
}
