<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModalityRefinancingSeasonParametersSeeder extends Seeder
{
    public function run()
    {
        $modality_id = DB::table('procedure_modalities')
            ->where('shortened', 'REF-EST-PAS-CON')
            ->value('id');

        $modality_parameter = [
            'procedure_modality_id' => $modality_id,
            'debt_index' => 70,
            'quantity_ballots' => 1,
            'guarantors' => 0,
            'max_lenders' => 1,
            'min_lender_category' => 0,
            'max_lender_category' => 1,
            'max_cosigner' => 0,
            'personal_reference' => true,
            'maximum_amount_modality' => 300000,
            'minimum_amount_modality' => 1,
            'maximum_term_modality' => 4,
            'minimum_term_modality' => 1,
            'print_contract_platform' => false,
            'print_receipt_fund_rotary' => false,
            'print_form_qualification_platform' => false,
            'loan_procedure_id' => 3,
            'max_approved_amount' => 80001,
            'loan_month_term' => 6,
            'coverage_percentage' => 1,
            'suggested_debt_index' => 60,
            'modality_refinancing_id' => $modality_id,
        ];
        
        DB::table('loan_modality_parameters')
            ->insert($modality_parameter);

        DB::table('loan_modality_parameters')
            ->where('procedure_modality_id', 95)
            ->update(['modality_refinancing_id' => $modality_id]);
    }
}
