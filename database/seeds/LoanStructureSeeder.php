<?php

use Illuminate\Database\Seeder;
use App\ProcedureModality;
use App\ProcedureType;
use App\ProcedureDocument;
use App\ProcedureRequirement;
use App\Module;
class LoanStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $module = Module::whereName('prestamos')->first();
        $data = [
        'procedures' => [
          'anticipo' => [
            'type' => ['module_id' => $module->id,'name'=>'Préstamo Anticipo','second_name'=>'Anticipo'],
            'modalities' => [
              // ANTICIPO SECTOR ACTIVO
              ['name'=>'Anticipo Sector Activo','shortened'=>'ANT-ACT','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Última boleta de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                ],
                'parameters' => [
                    'debt_index' => 90,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1,
                    'max_cosigner'=>0,
                    'maximum_amount_modality' => 2000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 2,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' =>true
                ],
                'interest' => ['annual_interest' => 36,'penal_interest' => 6]
              ],
              // ANTICIPO SECTOR ACTIVO EN DISPONIBILIDAD
              ['name'=>'Anticipo en Disponibilidad','shortened'=>'ANT-DIS','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Última boleta de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  ['name' => 'Memorándum de asignación a la letra en copia simple.','number'=>3],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                ],
                'parameters' => [
                    'debt_index' => 90,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1,
                    'max_cosigner'=>0,
                    'maximum_amount_modality' => 2000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 2,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' =>true
                ],
                'interest' => ['annual_interest' => 36,'penal_interest' => 6]
              ],              
              // ANTICIPO SECTOR PASIVO AFP
              ['name'=>'Anticipo Sector Pasivo AFP','shortened'=>'ANT-AFP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1], 
                  ['name' => 'Última boleta de pago en copia simple','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>3],
                  ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses.','number'=>4],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                ],
                'parameters' => [
                    'debt_index' => 90,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1,
                    'max_cosigner'=>0,
                    'maximum_amount_modality' => 2000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 2,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' =>true
                ],
                'interest' => ['annual_interest' => 36,'penal_interest' => 6]
              ],
              // ANTICIPO SECTOR PASIVO SENASIR
              ['name'=>'Anticipo Sector Pasivo SENASIR','shortened'=>'ANT-SEN','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1], 
                  ['name' => 'Última boleta de pago en copia simple','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>3],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                ],
                'parameters' => [
                    'debt_index' => 90,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1,
                    'max_cosigner'=>0,
                    'maximum_amount_modality' => 2000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 2,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' =>true
                ],
                'interest' => ['annual_interest' => 36,'penal_interest' => 6]
              ]
            ]
          ],
          'corto' => [
            'type' => ['module_id' => $module->id,'name'=>'Préstamo a Corto Plazo','second_name'=>'Corto Plazo'],
            'modalities' => [
              // CORTO PLAZO SECTOR ACTIVO
              ['name'=>'Corto Plazo Sector Activo','shortened'=>'COR-ACT','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de haberes.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3], 
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Certificado de años de servicio (Categoría 100%).','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
                ],
                'parameters' => [
                    'debt_index' => 50,
                    'quantity_ballots' => 3,
                    'guarantors' => 0,
                    'personal_reference' => true,
                    'max_lenders' => 1,
                    'max_cosigner'=>0,
                    'maximum_amount_modality' => 25000,
                    'minimum_amount_modality' => 1,
                    'maximum_term_modality' => 30,
                    'minimum_term_modality' => 1,
                    'print_contract_platform' =>true
                ],
                'interest' => ['annual_interest' => 20,'penal_interest' => 6]
              ],
              // CORTO PLAZO SECTOR ACTIVO DISPONIBILIDAD
              ['name'=>'Corto Plazo en Disponibilidad','shortened'=>'COR-DIS','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de haberes.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3],
                  ['name' => 'Memorándum de asignación a la letra en copia simple.','number'=>4],
                  ['name' => 'Certificado de años de servicio.','number'=>5],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser adicionales*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 3,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 25000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 30,
                  'minimum_term_modality' => 1,
                  'print_contract_platform' =>true
                ],
                'interest' => ['annual_interest' => 20,'penal_interest' => 6]
              ],
              // CORTO PLAZO SECTOR PASIVO AFP
              ['name'=>'Corto Plazo Sector Pasivo AFP','shortened'=>'COR-AFP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Cédula de Identidad del Garante en copia simple.','number'=>3],
                  ['name' => 'Última Boleta de Pago del Garante en copia simple.','number'=>4],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>5],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>5],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>6],
                  ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses.','number'=>7],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 3,
                  'guarantors' => 1,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>0.85,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 25000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 12,
                  'minimum_term_modality' => 1,
                  'print_contract_platform' =>true
                ],
                'interest' => ['annual_interest' => 20,'penal_interest' => 6]
              ],
              // CORTO PLAZO SECTOR PASIVO SENASIR
              ['name'=>'Corto Plazo Sector Pasivo SENASIR','shortened'=>'COR-SEN','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>4],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 3,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 25000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 12,
                  'minimum_term_modality' => 1,
                  'print_contract_platform' =>true
                ],
                'interest' => ['annual_interest' => 20,'penal_interest' => 6]
              ]
            ]
          ],
          'refinanciamiento corto' => [
            'type' => ['module_id' => $module->id,'name'=>'Refinanciamiento Préstamo a Corto Plazo','second_name'=>'Ref. Corto Plazo'],
            'modalities' => [
              // REFINANCIAMIENTO CORTO PLAZO ACTIVO
              ['name'=>'Refinanciamiento de Préstamo a Corto Plazo Sector Activo','shortened'=>'REF-COR-ACT','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de haberes.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3], 
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Certificado de años de servicio (Categoría 100%).','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
                ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 3,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 25000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 30,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 20,'penal_interest' => 6]
              ],
              // REFINANCIAMIENTO CORTO PLAZO PASIVOS AFP
              ['name'=>'Refinanciamiento de Préstamo a Corto Plazo sector Pasivo AFP','shortened'=>'REF-COR-AFP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Cédula de Identidad del garante en copia simple.','number'=>3],
                  ['name' => 'Última Boleta de Pago del garante en copia simple.','number'=>4],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>5],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>5],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>6],
                  ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses.','number'=>7],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 3,
                  'guarantors' => 1,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>0.85,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 25000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 12,
                  'minimum_term_modality' => 1

                ],
                'interest' => ['annual_interest' => 20,'penal_interest' => 6]
              ],
              // REFINANCIAMIENTO PASIVOS SENASIR
              ['name'=>'Refinanciamiento de Préstamo a Corto Plazo Sector Pasivo SENASIR','shortened'=>'REF-COR-SEN','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>4],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
            	],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 3,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 25000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 12,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 20,'penal_interest' => 6]
              ]
            ]
          ],
          'largo' => [
            'type' => ['module_id' => $module->id,'name'=>'Préstamo a Largo Plazo','second_name'=>'Largo Plazo'],
            'modalities' => [
              // LARGO PLAZO SECTOR ACTIVO
              ['name'=>'Largo Plazo con Garantía Personal Sector Activo','shortened'=>'LAR-ACT','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple del Solicitante.','number'=>1],
                  ['name' => 'Última boleta de pago en copia simple del Solicitante.','number'=>2],
                  ['name' => 'Certificado de haberes del Solicitante.','number'=>2],
                  ['name' => 'Cédula de Identidad en copia simple Garante uno.','number'=>3],
                  ['name' => 'Última boleta de pago en copia simple Garante uno.','number'=>4],
                  ['name' => 'Certificado de haberes Garante uno.','number'=>4],
                  ['name' => 'Cédula de Identidad en copia simple Garante dos.','number'=>5],
                  ['name' => 'Última boleta de pago en copia simple Garante dos.','number'=>6],
                  ['name' => 'Certificado de haberes Garante dos.','number'=>6],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>7],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>7],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Certificado de años de servicio (Categoría 100%).','number'=>0],
                  ['name' => 'Certificado de años de servicio (Personal Administrativo).','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 1,
                  'guarantors' => 2,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>1.00,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 96,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
              ],
              // LARGO PLAZO SECTOR PASIVO AFP
              ['name'=>'Largo Plazo con Garantía Personal Sector Pasivo AFP','shortened'=>'LAR-AFP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple del Solicitante.','number'=>1], 
                  ['name' => 'Última boleta de pago en copia simple del Solicitante.','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Cédula de Identidad del Garante en copia simple.','number'=>3],
                  ['name' => 'Última Boleta de Pago del Garante en copia simple.','number'=>4],
                  ['name' => 'Certificado de haberes del Garante.','number'=>4],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>5],
                  ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses.','number'=>6],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>7],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>7],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 1,
                  'guarantors' => 1,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>0.85,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 24,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
              ],
              // LARGO PLAZO SECTOR PASIVO SENASIR
              ['name'=>'Largo Plazo con Garantía Personal Sector Pasivo SENASIR','shortened'=>'LAR-SEN','requirements'=>[      
                  ['name' => 'Cédula de identidad en copia simple del Solicitante.','number'=>1], 
                  ['name' => 'Última boleta de pago en copia simple del Solicitante.','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Cédula de Identidad del Garante en copia simple.','number'=>3],
                  ['name' => 'Última Boleta de Pago del Garante en copia simple.','number'=>4],
                  ['name' => 'Certificado de haberes del Garante.','number'=>4],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>5],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>6],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>6],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 1,
                  'guarantors' => 1,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>0.85,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 24,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
              ],
              // LARGO PLAZO UN SOLO GARANTE SECTOR ACTIVO CPOP
              ['name'=>'Largo Plazo con un Solo Garante Sector Activo CPOP','shortened'=>'LAR-CPOP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple del Solicitante.','number'=>1],
                  ['name' => 'Última boleta de pago en copia simple del Solicitante.','number'=>2],
                  ['name' => 'Certificado de haberes del Solicitante.','number'=>2],
                  ['name' => 'Cédula de Identidad en copia simple del Garante.','number'=>3],
                  ['name' => 'Última boleta de pago en copia simple del Garante.','number'=>4],
                  ['name' => 'Certificado de haberes del Garante.','number'=>4],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>5],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>5],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Certificado de años de servicio (Categoría 100%).','number'=>0],
                  ['name' => 'Certificado de años de servicio (Personal Administrativo).','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 60,
                  'quantity_ballots' => 1,
                  'guarantors' => 1,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>1.00,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 96,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
              ]
            ]
          ],
          'refinanciamiento largo' => [
            'type' => ['module_id' => $module->id,'name'=>'Refinanciamiento Préstamo a Largo Plazo','second_name'=>'Ref. Largo Plazo'],
            'modalities' => [
              //REFINANCIAMIENTO LARGO PLAZO SECTOR ACTIVO
              ['name'=>'Refinanciamiento de Préstamo a Largo Plazo Sector Activo','shortened'=>'REF-LAR-ACT','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple del Solicitante.','number'=>1],
                  ['name' => 'Última boleta de pago en copia simple del Solicitante.','number'=>2],
                  ['name' => 'Certificado de haberes del Solicitante.','number'=>2],
                  ['name' => 'Cédula de Identidad en copia simple Garante uno.','number'=>3],
                  ['name' => 'Última boleta de pago en copia simple Garante uno.','number'=>4],
                  ['name' => 'Certificado de haberes Garante uno.','number'=>4],
                  ['name' => 'Cédula de Identidad en copia simple Garante dos.','number'=>5],
                  ['name' => 'Última boleta de pago en copia simple Garante dos.','number'=>6],
                  ['name' => 'Certificado de haberes Garante dos.','number'=>6],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>7],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>7],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Certificado de años de servicio (Categoría 100%).','number'=>0],
                  ['name' => 'Certificado de años de servicio (Personal Administrativo).','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 1,
                  'guarantors' => 2,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>1.00,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 96,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
              ],
              // REFINANCIANCIAMIENTO LARGO PLAZO SECTOR PASIVO AFP
              ['name'=>'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo AFP','shortened'=>'REF-LAR-AFP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple del Solicitante.','number'=>1], 
                  ['name' => 'Última boleta de pago en copia simple del Solicitante.','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Cédula de Identidad del Garante en copia simple.','number'=>3],
                  ['name' => 'Última Boleta de Pago del Garante en copia simple.','number'=>4],
                  ['name' => 'Certificado de haberes del Garante.','number'=>4],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>5],
                  ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses.','number'=>6],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>7],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>7],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 1,
                  'guarantors' => 1,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>0.85,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 24,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
              ],
              // REFINANCIANCIAMIENTO SECTOR PASIVO SENASIR
              ['name'=>'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo SENASIR','shortened'=>'REF-LAR-SEN','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple del Solicitante.','number'=>1], 
                  ['name' => 'Última boleta de pago en copia simple del Solicitante.','number'=>2],
                  ['name' => 'Certificado de Renta de jubilación.','number'=>2],
                  ['name' => 'Cédula de Identidad del Garante en copia simple.','number'=>3],
                  ['name' => 'Última Boleta de Pago del Garante en copia simple.','number'=>4],
                  ['name' => 'Certificado de haberes del Garante.','number'=>4],
                  ['name' => 'Memorándum de agradecimiento en copia simple.','number'=>5],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>6],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>6],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 1,
                  'guarantors' => 1,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>0.85,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 24,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
              ],
              // REFINANCIANCIAMIENTO SECTOR ACTIVO CON UN SOLO GARANTE CPOP
              ['name'=>'Refinanciamiento de Préstamo a largo Plazo con un Solo Garante Sector Activo CPOP','shortened'=>'REF-ACT-CPOP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple del Solicitante.','number'=>1],
                  ['name' => 'Última boleta de pago en copia simple del Solicitante.','number'=>2],
                  ['name' => 'Certificado de haberes del Solicitante.','number'=>2],
                  ['name' => 'Cédula de Identidad en copia simple del Garante.','number'=>3],
                  ['name' => 'Última boleta de pago en copia simple del Garante.','number'=>4],
                  ['name' => 'Certificado de haberes del Garante.','number'=>4],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>5],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>5],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Certificado de años de servicio (Categoría 100%).','number'=>0],
                  ['name' => 'Certificado de años de servicio (Personal Administrativo).','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 60,
                  'quantity_ballots' => 1,
                  'guarantors' => 1,
                  'min_guarantor_category' =>0.35,
                  'max_guarantor_category' =>1.00,
                  'personal_reference' => true,
                  'max_lenders' => 1,
                  'max_cosigner'=>0,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 1,
                  'maximum_term_modality' => 96,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
              ]
            ]
          ],
          'hipotecario' => [
            'type' => ['module_id' => $module->id,'name'=>'Préstamo Hipotecario','second_name'=>'Hipotecario'],
            'modalities' => [
              // GARANTÍA HIPOTECARIA ACTIVOS
              ['name'=>'Préstamo con Garantía Hipotecaria de Bien Inmueble Sector Activo','shortened'=>'HIP-ACT','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de haberes.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3],
                  ['name' => 'Croquis de ubicación del inmueble.','number'=>4],
                  ['name' => 'Factura de Agua o Luz del inmueble (Con antigüedad no mayor a 60 días).','number'=>5],
                  ['name' => 'Folio Real actualizado (No mayor a 90 días de su emisión).','number'=>6],
                  ['name' => 'Información rápida del inmueble - Original (En caso que el Folio Real este desactualizado, mayor a 90 días).','number'=>7],
                  ['name' => 'Fotocopia de Testimonio de Propiedad.','number'=>8],
                  ['name' => 'Fotocopia de Pago de Impuestos de los 3 últimos años.','number'=>9],
                  ['name' => 'Fotocopia del Plano del Lote.','number'=>10],
                  ['name' => 'Fotocopia del Catastro.','number'=>11],
                  ['name' => 'Certificado de Estado Civil o Soltería de todos los participantes del Préstamo (Original).','number'=>12],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 80,
                  'quantity_ballots' => 3,
                  'min_lender_category'=> 0.35,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 2,
                  'max_cosigner'=>2,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 100000,
                  'maximum_term_modality' => 180,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 9,'penal_interest' => 6]
              ],
              // LARGO PLAZO GARANTÍA HIPOTECARIA CPOP //Eliminar en caso de que no proceda
              ['name'=>'Préstamo con Garantía Hipotecaria de Bien Inmueble Sector Activo CPOP','shortened'=>'HIP-ACT-CPOP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de haberes.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3],
                  ['name' => 'Croquis de ubicación del inmueble.','number'=>4],
                  ['name' => 'Factura de Agua o Luz del inmueble (Con antigüedad no mayor a 60 días).','number'=>5],
                  ['name' => 'Folio Real actualizado (No mayor a 90 días de su emisión).','number'=>6],
                  ['name' => 'Información rápida del inmueble - Original (En caso que el Folio Real este desactualizado, mayor a 90 días).','number'=>7],
                  ['name' => 'Fotocopia de Testimonio de Propiedad.','number'=>8],
                  ['name' => 'Fotocopia de Pago de Impuestos de los 3 últimos años.','number'=>9],
                  ['name' => 'Fotocopia del Plano del Lote.','number'=>10],
                  ['name' => 'Fotocopia del Catastro.','number'=>11],
                  ['name' => 'Certificado de Estado Civil o Soltería de todos los participantes del Préstamo (Original).','number'=>12],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 80,
                  'quantity_ballots' => 3,
                  'min_lender_category'=> 0.35,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 2,
                  'max_cosigner'=>2,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 100000,
                  'maximum_term_modality' => 180,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 9,'penal_interest' => 6]
              ],
            ]
          ],
          'refinanciamiento hipotecario' => [
            'type' => ['module_id' => $module->id,'name'=>'Refinanciamiento Préstamo Hipotecario','second_name'=>'Ref. Hipotecario'],
            'modalities' => [
              //REFINANCIAMIENTO DE PRÉSTAMOS A LARGO PLAZO GARANTÍA HIPOTECARIA PARA EL SECTOR ACTIVO //Eliminar en caso de que no proceda
              ['name'=>'Refinanciamiento de Préstamo con Garantía Hipotecaria de Bien Inmueble Sector Activo','shortened'=>'REF-HIP-ACT','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de haberes.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3],
                  ['name' => 'Croquis de ubicación del inmueble.','number'=>4],
                  ['name' => 'Factura de Agua o Luz del inmueble (Con antigüedad no mayor a 60 días).','number'=>5],
                  ['name' => 'Folio Real actualizado (No mayor a 90 días de su emisión).','number'=>6],
                  ['name' => 'Información rápida del inmueble - Original (En caso que el Folio Real este desactualizado, mayor a 90 días).','number'=>7],
                  ['name' => 'Fotocopia de Testimonio de Propiedad.','number'=>8],
                  ['name' => 'Fotocopia de Pago de Impuestos de los 3 últimos años.','number'=>9],
                  ['name' => 'Fotocopia del Plano del Lote.','number'=>10],
                  ['name' => 'Fotocopia del Catastro.','number'=>11],
                  ['name' => 'Certificado de Estado Civil o Soltería de todos los participantes del Préstamo (Original).','number'=>12],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 80,
                  'quantity_ballots' => 3,
                  'min_lender_category'=> 0.35,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 2,
                  'max_cosigner'=>2,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 100000,
                  'maximum_term_modality' => 180,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 9,'penal_interest' => 6]
              ],
              //REFINANCIAMIENTO DE PRÉSTAMOS A LARGO PLAZO GARANTÍA HIPOTECARIA CPOP * //Eliminar en caso de que no proceda
              ['name'=>'Refinanciamiento de Préstamo con Garantía Hipotecaria de Bien Inmueble Sector Activo CPOP','shortened'=>'REF-HIP-ACT-CPOP','requirements'=>[
                  ['name' => 'Cédula de identidad en copia simple.','number'=>1],
                  ['name' => 'Tres últimas boletas de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes.','number'=>2],
                  ['name' => 'Boletas de pago en copia simple y Certificado de haberes.','number'=>2],
                  ['name' => 'Formulario de Beneficiario SIGEP y estado de cuenta emitido por una institución financiera.','number'=>3],
                  ['name' => 'Nota de Solicitud de desembolso en cheque y documento que acredite cuentas bloqueadas.','number'=>3],
                  ['name' => 'Croquis de ubicación del inmueble.','number'=>4],
                  ['name' => 'Factura de Agua o Luz del inmueble (Con antigüedad no mayor a 60 días).','number'=>5],
                  ['name' => 'Folio Real actualizado (No mayor a 90 días de su emisión).','number'=>6],
                  ['name' => 'Información rápida del inmueble - Original (En caso que el Folio Real este desactualizado, mayor a 90 días).','number'=>7],
                  ['name' => 'Fotocopia de Testimonio de Propiedad.','number'=>8],
                  ['name' => 'Fotocopia de Pago de Impuestos de los 3 últimos años.','number'=>9],
                  ['name' => 'Fotocopia del Plano del Lote.','number'=>10],
                  ['name' => 'Fotocopia del Catastro.','number'=>11],
                  ['name' => 'Certificado de Estado Civil o Soltería de todos los participantes del Préstamo (Original).','number'=>12],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser necesario*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía original.','number'=>0],
                  ['name' => 'Conformidad de devolución de descuento por garantía copia legalizada.','number'=>0],
                  ['name' => 'Memorándum de alta (Categoría 0%).','number'=>0],
              ],
                'parameters' => [
                  'debt_index' => 80,
                  'quantity_ballots' => 3,
                  'min_lender_category'=> 0.35,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 2,
                  'max_cosigner'=>2,
                  'maximum_amount_modality' => 700000,
                  'minimum_amount_modality' => 100000,
                  'maximum_term_modality' => 180,
                  'minimum_term_modality' => 1
                ],
                'interest' => ['annual_interest' => 9,'penal_interest' => 6]
              ]
            ]
          ]
        ]
      ];
      foreach ($data['procedures'] as $procedure) {
        $new_procedure = ProcedureType::firstOrCreate($procedure['type']);
        foreach ($procedure['modalities'] as $modality) {
          $new_modality = ProcedureModality::firstOrCreate([
            'procedure_type_id' => $new_procedure->id,
            'name'=>$modality['name'],
            'shortened'=>$modality['shortened']
          ]);
          foreach ($modality['requirements'] as $requirement) {
            $new_document = ProcedureDocument::firstOrCreate([
              'name'=>$requirement['name']
            ]);
            ProcedureRequirement::firstOrCreate([
              'procedure_modality_id'=>$new_modality->id,
              'procedure_document_id'=>$new_document->id,
              'number'=>$requirement['number']
            ]);
          }
          if (isset($modality['parameters']))
            $new_modality->loan_modality_parameter()->firstOrCreate($modality['parameters']);
          if (isset($modality['interest']))
            $new_modality->loan_interests()->firstOrCreate($modality['interest']);
        }
      }
    }
}