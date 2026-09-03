<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateNewReglament2026 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            DB::statement('SELECT setval(\'loan_procedures_id_seq\', (SELECT COALESCE(MAX(id), 0) FROM loan_procedures))');
            DB::statement('SELECT setval(\'loan_global_parameters_id_seq\', (SELECT COALESCE(MAX(id), 0) FROM loan_global_parameters))');
            DB::statement('SELECT setval(\'procedure_types_id_seq\', (SELECT COALESCE(MAX(id), 0) FROM procedure_types))');
            DB::statement('SELECT setval(\'procedure_modalities_id_seq\', (SELECT COALESCE(MAX(id), 0) FROM procedure_modalities))');
            DB::statement('SELECT setval(\'loan_interests_id_seq\', (SELECT COALESCE(MAX(id), 0) FROM loan_interests))');

            // INHABILITACIÓN DEL ANTIGUO REGLAMENTO - TABLA loan_procedures
            DB::table('loan_procedures')->where('id', '3')->update([
                'is_enable' => false,
            ]);

            // CREACIÓN DEL NUEVO REGLAMENTO EN TABLA loan_procedures Y PONIENDOLO ACTIVO
            DB::table('loan_procedures')->insert([
                [
                    'description' => 'Reglamento de Préstamos 2026',
                    'is_enable' => true,
                    'start_production_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            
            // CREACIÓN DE PARÁMETROS GLOBALES DEL NUEVO REGLAMENTO TABLA loan_global_parameters
            DB::table('loan_global_parameters')->insert([
                [
                    'offset_ballot_day' => 7,
                    'offset_interest_day' => 15,
                    'livelihood_amount' => 0,
                    'min_service_years_adm' => 0,
                    'max_guarantor_active' => 3,
                    'max_guarantor_passive' => 2,
                    'date_delete_payment' => 1,
                    'max_loans_active' => 2,
                    'max_loans_process' => 1,
                    'days_current_interest' => 31,
                    'grace_period' => 3,
                    'consecutive_manual_payment' => 3,
                    'max_months_go_back' => 3,
                    'min_percentage_paid' => 25,
                    'min_remaining_installments' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'min_amount_fund_rotary' => 100000,
                    'loan_procedure_id' => 4,       //ID DEL NUEVO REGLAMENTO
                    'days_year_calculated' => 1,      
                    'days_for_import' => 20,            
                    'numerator' => 365.25,
                    'denominator' => 360,
                ]
            ]);
            
            // CREACIÓN DE MODALIDADES EN LA TABLA procedure_types
            DB::table('procedure_types')->insert([
                [
                    'module_id' => 6,
                    'name' => 'Mi Primer Préstamo con Garantía Personal Sector Activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'second_name' => 'Mi Primer Préstamo',
                ],
                [
                    'module_id' => 6,
                    'name' => 'Préstamo Fidelidad para el Sector Activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'second_name' => 'Fidelidad',
                ],
            ]);

            // CREACIÓN DE SUBMODALIDADES EN LA TABLA procedure_modalities
            /* procedure_modalities id
                id = 30 --> Mi Primer Préstamo con Garantía Personal Sector Activo      //NUEVO
                id = 31 --> Préstamo Fidelidad para el Sector Activo                    //NUEVO
            */
            DB::table('procedure_modalities')->insert([
                // id = 30 --> Mi Primer Préstamo con Garantía Personal Sector Activo    //NUEVO
                [
                    'procedure_type_id' => 30,
                    'name' => 'Mi Primer Préstamo con Garantía Personal Sector Activo',
                    'shortened' => 'PRI-ACT',
                    'is_valid' => true,
                    'workflow_id' => 10,
                ],
                // id = 31 --> Préstamo Fidelidad para el Sector Activo     //NUEVO
                [
                    'procedure_type_id' => 31,
                    'name' => 'Préstamo Fidelidad para el Sector Activo',
                    'shortened' => 'FID-ACT',
                    'is_valid' => true,
                    'workflow_id' => 10,
                ],
                [
                    'procedure_type_id' => 31,
                    'name' => 'Refinanciamiento Fidelidad para el Sector Activo',
                    'shortened' => 'REF-FID-ACT',
                    'is_valid' => true,
                    'workflow_id' => 11,
                ]
            ]);

            DB::table('loan_modality_parameters')->insert([
                // Mi Primer Préstamo con Garantía Personal Sector Activo
                [
                    'procedure_modality_id' => 106,
                    'debt_index' => 70,
                    'quantity_ballots' => 1,
                    'guarantors' => 1,
                    'max_lenders' => 1,
                    'min_guarantor_category' => 0,
                    'max_guarantor_category' => 1,
                    'min_lender_category' => 0,  // ?
                    'max_lender_category' => 1,     // ?
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 20000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 18,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => 4,
                    'max_approved_amount' => null,
                    'guarantor_debt_index' => null,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => 0.25,
                    'suggested_debt_index' => 50,
                    'modality_refinancing_id' => null,
                    'modality_reprogramming_id' => null,
                    'min_service_years' => 0,
                ],
                // Préstamo Fidelidad para el Sector Activo 
                [
                    'procedure_modality_id' => 107,
                    'debt_index' => 70,
                    'quantity_ballots' => 1,
                    'guarantors' => 1,
                    'max_lenders' => 1,
                    'min_guarantor_category' => 0.35,
                    'max_guarantor_category' => 1,
                    'min_lender_category' => 0,  // ?
                    'max_lender_category' => 1,     // ?
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 300000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 72,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => 4,
                    'max_approved_amount' => 80001,
                    'guarantor_debt_index' => null,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => 0.25,
                    'suggested_debt_index' => 50,
                    'modality_refinancing_id' => null,
                    'modality_reprogramming_id' => null,
                    'min_service_years' => 1, 
                ],
                // Refinanciamiento Préstamo Fidelidad para el Sector Activo
                [
                    'procedure_modality_id' => 108,
                    'debt_index' => 70,  
                    'quantity_ballots' => 1,
                    'guarantors' => 1,
                    'max_lenders' => 1,
                    'min_guarantor_category' => 0.35,
                    'max_guarantor_category' => 1,
                    'min_lender_category' => 0,     // ?
                    'max_lender_category' => 1,     // ?
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 300000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 72,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => 4,
                    'max_approved_amount' => 80001,
                    'guarantor_debt_index' => null,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => 0.25,
                    'suggested_debt_index' => 50,
                    'modality_refinancing_id' => 107,
                    'modality_reprogramming_id' => null,
                    'min_service_years' => 1,
                ],
            ]);
            // CREACIÓN DE INTERÉS NUEVAS SUBMODALIDADES EN LA TABLA loan_interests
            // Mi Primer Préstamo con Garantía Personal Sector Activo
            DB::table('loan_interests')->insert([
                [
                    'procedure_modality_id' => 106,
                    'annual_interest' => 20,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            // Préstamo Fidelidad para el Sector Activo 
            DB::table('loan_interests')->insert([
                [
                    'procedure_modality_id' => 107,
                    'annual_interest' => 13.2,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            // Refinanciamiento Préstamo Fidelidad para el Sector Activo
            DB::table('loan_interests')->insert([
                [
                    'procedure_modality_id' => 108,
                    'annual_interest' => 13.2,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::commit();
        } catch (\Exception $e) {
            // Revertir todas las operaciones en caso de error
            DB::rollBack();
            dd($e->getMessage());
        }
    }
}

// php artisan db:seed --class=UpdateNewReglament2026
