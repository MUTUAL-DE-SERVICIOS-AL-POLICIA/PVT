<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateSuggestedDebtIndexProcedureThree extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('loan_modality_parameters')
            ->where('loan_procedure_id', 3)
            ->update([
                'suggested_debt_index' => 50
            ]);
    }
}