<?php

use Illuminate\Database\Seeder;

class update_loan_modality_parameters_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try{
            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [73,74,75,76,77,78,79,80])->where('loan_procedure_id', 3)->update([
                'maximum_term_modality' => 24
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [75,79])->where('loan_procedure_id', 3)->update([
                'guarantor_debt_index' => 30
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [83,84,85,86,87,88,89,90,91,92])->where('loan_procedure_id', 3)->update([
                'minimum_amount_modality' => 1,
                'maximum_term_modality' => 60
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [83,88])->where('loan_procedure_id', 3)->update([
                'max_approved_amount' => 30
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [36])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 40,
                'modality_reprogramming_id' => 73
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [45])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 49,
                'modality_reprogramming_id' => 87
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [37])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 66,
                'modality_reprogramming_id' => 74
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [39])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 42,
                'modality_reprogramming_id' => 76
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [40])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 40,
                'modality_reprogramming_id' => 77
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [42])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 42,
                'modality_reprogramming_id' => 80
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [66])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 66,
                'modality_reprogramming_id' => 78
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [68])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 69,
                'modality_reprogramming_id' => 75
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [70])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 71,
                'modality_reprogramming_id' => 86
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [43])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 47,
                'modality_reprogramming_id' => 84
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [46])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 80,
                'modality_reprogramming_id' => 85
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [69])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 69,
                'modality_reprogramming_id' => 79
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [71])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 71,
                'modality_reprogramming_id' => 91
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [47])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 47,
                'modality_reprogramming_id' => 89
            ]);

            DB::table('loan_modality_parameters')->whereIn('procedure_modality_id', [50])->where('loan_procedure_id', '!=', 3)->update([
                'modality_refinancing_id' => 50,
                'modality_reprogramming_id' => 90
            ]);

            DB::table('procedure_requirements')->insert([
                'procedure_modality_id' => 79,
                'procedure_document_id' => 425,
                'number' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('procedure_modalities')->insert([
                [
                    'procedure_type_id' => 20,
                    'name' => 'Reprogramación de Préstamo',
                    'shortened' => 'REPROGRAMACIÓN',
                    'is_valid' => true,
                    'workflow_id' => 13,
                ]
            ]);

            DB::table('procedure_modalities')->whereId(83)->update([
                'name' => 'Reprogramación Largo Plazo con Garantía Personal Para el Sector Activo con un Garante'
            ]);

            DB::table('procedure_modalities')->whereId(84)->update([
                'name' => 'Reprogramación Largo Plazo con Garantía Personal Para el Sector Activo con dos Garantes'
            ]);

            DB::table('procedure_modalities')->whereId(86)->update([
                'name' => 'Reprogramación Largo Plazo con Garantía Personal Sector Pasivo Gestora Pública'
            ]);

            DB::table('procedure_modalities')->whereId(87)->update([
                'name' => 'Reprogramación Largo Plazo con Garantía Personal Sector Pasivo SENASIR'
            ]);

            DB::table('procedure_modalities')->whereId(88)->update([
                'name' => 'Reprogramación del Refinanciamiento Largo Plazo con Garantía Personal Para el Sector Activo con un Garante'
            ]);

            DB::table('procedure_modalities')->whereId(89)->update([
                'name' => 'Reprogramación del Refinanciamiento Largo Plazo con Garantía Personal Para el Sector Activo con dos Garantes'
            ]);

            DB::table('procedure_modalities')->whereId(91)->update([
                'name' => 'Reprogramación del Refinanciamiento Largo Plazo con Garantía Personal Sector Pasivo Gestora Pública'
            ]);

            DB::table('procedure_modalities')->whereId(92)->update([
                'name' => 'Reprogramación del Refinanciamiento Largo Plazo con Garantía Personal Sector Pasivo SENASIR'
            ]);

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
