<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\ProcedureRequirement;

class UpdateNewReglament2026Part2 extends Seeder
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
            DB::statement('SELECT setval(\'procedure_requirements_id_seq\', (SELECT COALESCE(MAX(id), 0) FROM procedure_requirements))');
            DB::statement('SELECT setval(\'procedure_documents_id_seq\', (SELECT COALESCE(MAX(id), 0) FROM procedure_documents))');

            // CREACIÓN DE NUEVOS DOCUMENTOS
            $documentId1 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Cédula de identidad del cónyuge.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId2 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Cédula de Identidad del Garante dos.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId3 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Cédula de Identidad del Garante uno.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId4 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Cédula de Identidad del Garante.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId5 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Cédula de identidad del Solicitante.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId6 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Cédula de identidad.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId8 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Certificado de Pensión de jubilación del solicitante.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId9 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Última boleta de pago del garante dos cargada en la herramienta informática PVT.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId10 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Respaldo médico.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId11 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Certificado de Pensión de jubilación.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId12 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Certificado de Pensión de jubilación del Garante.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId13 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Certificado de Renta de jubilación del solicitante.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId14 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Certificado de renta o pensión de jubilación del Garante.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId15 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Certificado de no adeudo, emitido por la instancia correspondiente del solicitante.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId16 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Certificado de Unión Libre.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId17 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Copia Legalizada del Contrato de Préstamo Origen.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId18 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Nota de Solicitud de Reprogramación.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId19 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Última boleta de pago de renta o pensión del Garante.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId20 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Última boleta de pensión de jubilación.',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $documentId21 = DB::table('procedure_documents')->insertGetId([
                'name' => 'Última boleta de renta de jubilación.',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ACTUALIZACIÓN DE REQUERIMIENTOS SEGÚN NUEVO REGLAMENTO DE TODAS LAS MODALIDADES
            // Anticipo Sector Activo
            ProcedureRequirement::where('id', 1324)->delete();
            ProcedureRequirement::where('id', 2134)->delete();
            ProcedureRequirement::where('id', 1321)->delete();
            ProcedureRequirement::where('id', 1322)->delete();
            ProcedureRequirement::where('id', 1790)->delete();
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 32,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 32,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            // Anticipo en Disponibilidad
            ProcedureRequirement::where('id', 1332)->delete();
            ProcedureRequirement::where('id', 2136)->delete();
            ProcedureRequirement::where('id', 1328)->delete();
            ProcedureRequirement::where('id', 1329)->delete();
            ProcedureRequirement::where('id', 1793)->delete();
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 33,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 33,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            // Anticipo Sector Pasivo Gestora Pública
            ProcedureRequirement::where('id', 2138)->delete();
            ProcedureRequirement::where('id', 1985)->delete();
            ProcedureRequirement::where('id', 2142)->delete();
            ProcedureRequirement::where('id', 1986)->delete();
            ProcedureRequirement::where('id', 1991)->delete();
            ProcedureRequirement::where('id', 1992)->delete();
            ProcedureRequirement::where('id', 1996)->delete();
            ProcedureRequirement::where('id', 2145)->delete();
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 67,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 67,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 67,
                    'procedure_document_id' => $documentId20,
                    'number' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 67,
                    'procedure_document_id' => $documentId8,
                    'number' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 67,
                    'procedure_document_id' => $documentId4,
                    'number' => 3,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 67,
                    'procedure_document_id' => $documentId19,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 67,
                    'procedure_document_id' => $documentId14,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);  
            
            // Anticipo Sector Pasivo SENASIR
            ProcedureRequirement::where('id', 2143)->delete();
            ProcedureRequirement::where('id', 1344)->delete();
            ProcedureRequirement::where('id', 1345)->delete();
            ProcedureRequirement::where('id', 1799)->delete();
            DB::table('procedure_requirements')->where('id', '1800')->update([
                'number' => 5,
            ]);
            DB::table('procedure_requirements')->where('id', '1801')->update([
                'number' => 6,
            ]);
            DB::table('procedure_requirements')->where('id', '2144')->update([
                'number' => 7,
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => 419,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => 421,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => $documentId21,
                    'number' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => $documentId13,
                    'number' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => $documentId4,
                    'number' => 3,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => $documentId19,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => $documentId14,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 35,
                    'procedure_document_id' => $documentId9,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            
            // Corto Plazo Sector Activo
            ProcedureRequirement::where('id', 1357)->delete();
            ProcedureRequirement::where('id', 2146)->delete();
            ProcedureRequirement::where('id', 1351)->delete();
            ProcedureRequirement::where('id', 1803)->delete();
            ProcedureRequirement::where('id', 1802)->delete();

            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 36,
                    'procedure_document_id' => $documentId10,
                    'number' => 5,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 36,
                    'procedure_document_id' => $documentId6,
                    'number' => 5,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            // Corto Plazo en Disponibilidad
            ProcedureRequirement::where('id', 1371)->delete();
            ProcedureRequirement::where('id', 2148)->delete();
            ProcedureRequirement::where('id', 1363)->delete();
            ProcedureRequirement::where('id', 1806)->delete();
            ProcedureRequirement::where('id', 1807)->delete();

            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 37,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 37,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            // Corto Plazo Sector Pasivo Gestora Pública
            ProcedureRequirement::where('id', 2152)->delete();     
            ProcedureRequirement::where('id', 1997)->delete();
            ProcedureRequirement::where('id', 2153)->delete();
            ProcedureRequirement::where('id', 2004)->delete();
            ProcedureRequirement::where('id', 1999)->delete();
            ProcedureRequirement::where('id', 2005)->delete();
            ProcedureRequirement::where('id', 2000)->delete();
            ProcedureRequirement::where('id', 2154)->delete();
            
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 68,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 68,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 68,
                    'procedure_document_id' => $documentId20,
                    'number' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 68,
                    'procedure_document_id' => $documentId8,
                    'number' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 68,
                    'procedure_document_id' => $documentId4,
                    'number' => 3,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 68,
                    'procedure_document_id' => $documentId19,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 68,
                    'procedure_document_id' => $documentId14,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            // Corto Plazo Sector Pasivo SENASIR
            ProcedureRequirement::where('id', 2156)->delete();
            ProcedureRequirement::where('id', 1388)->delete();
            ProcedureRequirement::where('id', 1814)->delete();
            ProcedureRequirement::where('id', 1815)->delete();
            
            DB::table('procedure_requirements')->where('id', '1816')->update([
                'number' => 5,
            ]);
            DB::table('procedure_requirements')->where('id', '1817')->update([
                'number' => 6,
            ]);
            DB::table('procedure_requirements')->where('id', '2157')->update([
                'number' => 7,
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => 419,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => 421,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => $documentId21,
                    'number' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => $documentId13,
                    'number' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => $documentId4,
                    'number' => 3,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => $documentId19,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => $documentId14,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 39,
                    'procedure_document_id' => $documentId9,
                    'number' => 4,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            
            // Refinanciamiento de Préstamo a Corto Plazo Sector Activo
            ProcedureRequirement::where('id', 1404)->delete();
            ProcedureRequirement::where('id', 2158)->delete();
            ProcedureRequirement::where('id', 1398)->delete();
            ProcedureRequirement::where('id', 1818)->delete();
            ProcedureRequirement::where('id', 1819)->delete();
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 40,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 40,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            // Refinanciamiento de Préstamo a Corto Plazo en Disponibilidad
            ProcedureRequirement::where('id', 1897)->delete();
            ProcedureRequirement::where('id', 2160)->delete();
            ProcedureRequirement::where('id', 1900)->delete();
            ProcedureRequirement::where('id', 1902)->delete();
            ProcedureRequirement::where('id', 1901)->delete();
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 66,
                    'procedure_document_id' => $documentId10,
                    'number' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);
            DB::table('procedure_requirements')->insert([
                [
                    'procedure_modality_id' => 66,
                    'procedure_document_id' => $documentId6,
                    'number' => 1,
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
//php artisan db:seed --class=UpdateNewReglament2026Part2