<?php

use Illuminate\Database\Seeder;
use App\LoanModalityParameter;

class NuevasModalidades2026Seeder extends Seeder
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
            //INHABILITACION DEL ANTIGUO REGLAMENTO 2025 TABLA loan_procedures
            DB::table('loan_procedures')->where('id', '3')->update([
                'is_enable' => false,
            ]);
            //CREACION DEL NUEVO REGLAMENTO EN TABLA loan_procedures Y PONIENDOLO ACTIVO
            $new_procedure_id = DB::table('loan_procedures')->insertGetId(
                [
                    'description' => 'Reglamento de Préstamos 2025',
                    'is_enable' => true,
                    'start_production_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            //CREACION DE PARAMETROS GLOBALES DEL NUEVO REGLAMENTO TABLA loan_global_parameters
            DB::table('loan_global_parameters')->insert([
                [
                    'offset_ballot_day' => 7,
                    'offset_interest_day' => 15,
                    'livelihood_amount' => 0,
                    'min_service_years' => 1,
                    'min_service_years_adm' => 0,
                    'max_guarantor_active' => 3,
                    'max_guarantor_passive' => 2,
                    'date_delete_payment' => 1,
                    'max_loans_active' => 3,
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
                    'loan_procedure_id' => $new_procedure_id,
                    'days_year_calculated' => 1,
                    'days_for_​import' => 20,
                    'numerator' => 365.25,
                    'denominator' => 360, 
                ]
            ]);

            $procedure_hogar_digno_id = DB::table('procedure_types')->insertGetId(
                [   //30
                    'module_id' => 6,
                    'name' => 'Préstamo Hogar Digno',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'second_name' => 'Hogar Digno',
                ],
            );
            $procedure_salud_id = DB::table('procedure_types')->insertGetId(
                [//31
                    'module_id' => 6,
                    'name' => 'Préstamo Salud',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'second_name' => 'Salud',
                ],
            );
            $procedure_primer_id = DB::table('procedure_types')->insertGetId(
                [   //32
                    'module_id' => 6,
                    'name' => 'Mi Primer Préstamo',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'second_name' => 'Mi Primer Préstamo',
                ],
            );

            $modality_hogar_activo = DB::table('procedure_modalities')->insertGetId(
                [   //$modality_hogar_activo
                    'procedure_type_id' => $procedure_hogar_digno_id,
                    'name' => 'Hogar Digno con Garantía Personal para el Sector Activo',
                    'shortened' => 'HD-GP-ACT',
                    'is_valid' => true,
                    'workflow_id' => 10,
                ],
            );
            $modality_ref_hogar_activo = DB::table('procedure_modalities')->insertGetId(
                [   //$modality_ref_hogar_activo
                    'procedure_type_id' => $procedure_hogar_digno_id,
                    'name' => 'Refinanciamiento Hogar Digno con Garantía Personal para el Sector Activo',
                    'shortened' => 'REF-HD-GP-ACT',
                    'is_valid' => true,
                    'workflow_id' => 11,
                ],
            );
            $modality_salud_activo = DB::table('procedure_modalities')->insertGetId(
                [   //$modality_salud_activo
                    'procedure_type_id' => $procedure_salud_id,
                    'name' => 'Salud Sector Activo',
                    'shortened' => 'SAL-ACT',
                    'is_valid' => true,
                    'workflow_id' => 10,
                ],
            );
            $modality_salud_disponibilidad = DB::table('procedure_modalities')->insertGetId(
                [   //$modality_salud_disponibilidad
                    'procedure_type_id' => $procedure_salud_id,
                    'name' => 'Salud en Disponibilidad',
                    'shortened' => 'SAL-DIS',
                    'is_valid' => true,
                    'workflow_id' => 10,
                ],
            );
            $modality_salud_gestora = DB::table('procedure_modalities')->insertGetId(
                [   //$modality_salud_gestora
                    'procedure_type_id' => $procedure_salud_id,
                    'name' => 'Salud Sector Pasivo Gestora Pública',
                    'shortened' => 'SAL-GES',
                    'is_valid' => true,
                    'workflow_id' => 10,
                ],
            );
            $modality_salud_senasir = DB::table('procedure_modalities')->insertGetId(
                [   //$modality_salud_senasir
                    'procedure_type_id' => $procedure_salud_id,
                    'name' => 'Salud Sector Pasivo SENASIR',
                    'shortened' => 'SAL-SEN',
                    'is_valid' => true,
                    'workflow_id' => 10,
                ],
            );
            $modality_primer = DB::table('procedure_modalities')->insertGetId(
                [
                    'procedure_type_id' => $procedure_primer_id,
                    'name' => 'Mi Primer Préstamo con Garantía Personal Sector Activo',
                    'shortened' => 'MPP-GP-ACT',
                    'is_valid' => true,
                    'workflow_id' => 10,
                ],
            );

            $loan_modality_parameters = LoanModalityParameter::where('loan_procedure_id', 3)->get();

            foreach ($loan_modality_parameters as $parameter) {
                $copy_parameter = $parameter->replicate();
                $copy_parameter->loan_procedure_id = $new_procedure_id;
                $copy_parameter->save();
            }

            DB::table('loan_modality_parameters')->insert([
                [
                    'procedure_modality_id' => $modality_hogar_activo,
                    'debt_index' => 60,
                    'quantity_ballots' => 1,
                    'guarantors' => 2,
                    'max_lenders' => 1,
                    'min_guarantor_category' => 0.35,
                    'max_guarantor_category' => 1,
                    'min_lender_category' => 0,
                    'max_lender_category' => 1,
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 300000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 84,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => $new_procedure_id,
                    'guarantor_debt_index' => 30,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => 0.125,
                    'suggested_debt_index' => 60,
                    'modality_refinancing_id' => $modality_ref_hogar_activo,
                    'modality_reprogramming_id' => null,
                ],
                [
                    'procedure_modality_id' => $modality_ref_hogar_activo,
                    'debt_index' => 60,
                    'quantity_ballots' => 1,
                    'guarantors' => 2,
                    'max_lenders' => 1,
                    'min_guarantor_category' => 0.35,
                    'max_guarantor_category' => 1,
                    'min_lender_category' => 0,
                    'max_lender_category' => 1,
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 300000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 84,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => $new_procedure_id,
                    'guarantor_debt_index' => 30,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => 0.125,
                    'suggested_debt_index' => 60,
                    'modality_refinancing_id' => null,
                    'modality_reprogramming_id' => null,
                ],
                [
                    'procedure_modality_id' => $modality_salud_activo,
                    'debt_index' => 80,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1,
                    'min_guarantor_category' => null,
                    'max_guarantor_category' => null,
                    'min_lender_category' => 0,
                    'max_lender_category' => 1,
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 15000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 12,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => $new_procedure_id,
                    'guarantor_debt_index' => 30,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => null,
                    'suggested_debt_index' => 60,
                    'modality_refinancing_id' => null,
                    'modality_reprogramming_id' => null,
                ],
                [
                    'procedure_modality_id' => $modality_salud_disponibilidad,
                    'debt_index' => 80,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1,
                    'min_guarantor_category' => null,
                    'max_guarantor_category' => null,
                    'min_lender_category' => 0,
                    'max_lender_category' => 1,
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 15000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 12,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => $new_procedure_id,
                    'guarantor_debt_index' => 30,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => null,
                    'suggested_debt_index' => 60,
                    'modality_refinancing_id' => null,
                    'modality_reprogramming_id' => null,
                ],
                [
                    'procedure_modality_id' => $modality_salud_gestora,
                    'debt_index' => 80,
                    'quantity_ballots' => 1,
                    'guarantors' => 1,
                    'max_lenders' => 1,
                    'min_guarantor_category' => 0.35,
                    'max_guarantor_category' => 1,
                    'min_lender_category' => 0,
                    'max_lender_category' => 1,
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 8000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 12,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => $new_procedure_id,
                    'guarantor_debt_index' => 30,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => 0.25,
                    'suggested_debt_index' => 60,
                    'modality_refinancing_id' => null,
                    'modality_reprogramming_id' => null,
                ],
                [
                    'procedure_modality_id' => $modality_salud_senasir,
                    'debt_index' => 80,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1,
                    'min_guarantor_category' => null,
                    'max_guarantor_category' => null,
                    'min_lender_category' => 0,
                    'max_lender_category' => 1,
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 8000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 12,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => $new_procedure_id,
                    'guarantor_debt_index' => 30,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => null,
                    'suggested_debt_index' => 60,
                    'modality_refinancing_id' => null,
                    'modality_reprogramming_id' => null,
                ],
                [
                    'procedure_modality_id' => $modality_primer,
                    'debt_index' => 70,
                    'quantity_ballots' => 1,
                    'guarantors' => 1,
                    'max_lenders' => 1,
                    'min_guarantor_category' => 0.35,
                    'max_guarantor_category' => 1,
                    'min_lender_category' => 0,
                    'max_lender_category' => 1,
                    'max_cosigner' => 0,
                    'personal_reference' => true,
                    'maximum_amount_modality' => 20000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 18,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' => false,
                    'print_receipt_fund_rotary' => false,
                    'print_form_qualification_platform' => false,
                    'loan_procedure_id' => $new_procedure_id,
                    'guarantor_debt_index' => 30,
                    'loan_month_term' => 1,
                    'coverage_percentage' => 1,
                    'eval_percentage' => 0.25,
                    'suggested_debt_index' => 60,
                    'modality_refinancing_id' => null,
                    'modality_reprogramming_id' => null,
                ],
            ]);

            DB::table('loan_interests')->insert([
                [
                    'procedure_modality_id' => $modality_hogar_activo,
                    'annual_interest' => 13.2,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'procedure_modality_id' => $modality_ref_hogar_activo,
                    'annual_interest' => 13.2,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'procedure_modality_id' => $modality_salud_activo,
                    'annual_interest' => 20,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'procedure_modality_id' => $modality_salud_disponibilidad,
                    'annual_interest' => 20,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'procedure_modality_id' => $modality_salud_gestora,
                    'annual_interest' => 20,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'procedure_modality_id' => $modality_salud_senasir,
                    'annual_interest' => 20,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'procedure_modality_id' => $modality_primer,
                    'annual_interest' => 20,
                    'penal_interest' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            DB::table('loan_destiny_procedure_type')->insert([
                [
                    'procedure_type_id' => $procedure_hogar_digno_id,
                    'loan_destiny_id' => 4,
                ],
                [
                    'procedure_type_id' => $procedure_hogar_digno_id,
                    'loan_destiny_id' => 5,
                ],
                [
                    'procedure_type_id' => $procedure_hogar_digno_id,
                    'loan_destiny_id' => 6,
                ],
                [
                    'procedure_type_id' => $procedure_hogar_digno_id,
                    'loan_destiny_id' => 7,
                ],
            ]);
            DB::table('loan_destiny_procedure_type')->insert(
                [
                    'procedure_type_id' => $procedure_salud_id,
                    'loan_destiny_id' => 2,
                ],
            );
            DB::table('loan_destiny_procedure_type')->insert(
                [
                    'procedure_type_id' => $procedure_primer_id,
                    'loan_destiny_id' => 1,
                ],
            );

            $folio = DB::table('procedure_documents')->insertGetId(
                [
                    'name' => 'Fotocopia simple del folio real a nombre del solicitante.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            $información = DB::table('procedure_documents')->insertGetId(
                [
                    'name' => 'Información rápida original sin gravamen alguno.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            $presupuesto = DB::table('procedure_documents')->insertGetId(
                [
                    'name' => 'Presupuesto de obra.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            $vinculo = DB::table('procedure_documents')->insertGetId(
                [
                    'name' => 'Documento actualizado (fotocopia y/o original) que acredite el vínculo familiar.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            $respaldo = DB::table('procedure_documents')->insertGetId(
                [
                    'name' => 'Respaldo médico en copia simple.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            // hogar digno
            DB::table('procedure_requirements')->insert([
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 276,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 426,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 427,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 277,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 428,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 429,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 273,
                        'number' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 274,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 366,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 430,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 300,
                        'number' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 301,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 371,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 303,
                        'number' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 304,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 372,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 272,
                        'number' => 7,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 367,
                        'number' => 8,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => 418,
                        'number' => 9,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => $folio,
                        'number' => 10,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => $información,
                        'number' => 11,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_hogar_activo,
                        'procedure_document_id' => $presupuesto,
                        'number' => 12,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
            ]);

            //refinanciamiento hogar digno
            DB::table('procedure_requirements')->insert([
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 419,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 426,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 427,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 277,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 428,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 429,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 273,
                        'number' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 274,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 366,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 430,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 300,
                        'number' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 301,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 371,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 303,
                        'number' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 304,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 372,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 272,
                        'number' => 7,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 367,
                        'number' => 8,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => 418,
                        'number' => 9,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => $folio,
                        'number' => 10,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_ref_hogar_activo,
                        'procedure_document_id' => $información,
                        'number' => 11,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
            ]);

            //salud activo
            DB::table('procedure_requirements')->insert([
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 276,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 277,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => $vinculo,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 297,
                        'number' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 298,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 370,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 430,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => $respaldo,
                        'number' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 272,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 367,
                        'number' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_activo,
                        'procedure_document_id' => 418,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
            ]);

            //salud disponibilidad
            DB::table('procedure_requirements')->insert([
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 276,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 277,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => $vinculo,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 297,
                        'number' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 298,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 370,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 430,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => $respaldo,
                        'number' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 272,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 367,
                        'number' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_disponibilidad,
                        'procedure_document_id' => 418,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
            ]);

            //salud gestora publica
            DB::table('procedure_requirements')->insert([
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 276,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 419,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 421,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => $vinculo,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 297,
                        'number' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 298,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 420,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 293,
                        'number' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 294,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 373,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 425,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => $respaldo,
                        'number' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 272,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 367,
                        'number' => 7,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_gestora,
                        'procedure_document_id' => 418,
                        'number' => 8,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
            ]);

            //salud senasir
            DB::table('procedure_requirements')->insert([
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => 276,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => $vinculo,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => 297,
                        'number' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => 420,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => 298,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => $respaldo,
                        'number' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => 272,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => 367,
                        'number' => 7,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_salud_senasir,
                        'procedure_document_id' => 418,
                        'number' => 8,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],

            ]);
            //mi primer prestamo
            DB::table('procedure_requirements')->insert([
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 276,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 419,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 277,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 421,
                        'number' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 297,
                        'number' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 298,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 370,
                        'number' => 2,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 293,
                        'number' => 3,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 296,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 368,
                        'number' => 4,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 272,
                        'number' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 367,
                        'number' => 6,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'procedure_modality_id' => $modality_primer,
                        'procedure_document_id' => 418,
                        'number' => 7,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
            ]);

            DB::table('workflows')->where('id', 10)->update(['name' => 'Préstamos Regulares']);

            DB::commit();
        
        }catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
