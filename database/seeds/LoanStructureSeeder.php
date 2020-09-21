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
            'limits' => ['maximum_amount' => 2000,'minimum_amount' => 200,'maximum_term' => 2,'minimum_term' => 1],
            'modalities' => [
                // ANTICIPO SECTOR ACTIVO
                ['name'=>'Anticipo sector activo','shortened'=>'ANT-SA','requirements'=>[
                  ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                  ['name' => 'Última Boleta de pago en copia simple.','number'=>2],
                  ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
                  ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A.','number'=>3],
                  ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>4],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                  ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                  ['name' => 'Conformidad de devolucion de descuento por garantia original o fotocopia legalizada.','number'=>0],
                ],
                'parameters' => [
                    'debt_index' => 90,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1
                ],
                'interest' => ['annual_interest' => 36,'penal_interest' => 6]
            ],
                    // ANTICIPO SECTOR PASIVO Y VIUDAS
                ['name'=>'Anticipo sector pasivo','shortened'=>'ANT-SP','requirements'=>[
                  ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                  ['name' => 'Ultima boleta de pago en copia simple.','number'=>2], //Nuevo *caso pasivo con renta de SENASIR, o AFP
                  ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
                  ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A.','number'=>3], // Nuevo
                  ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>4],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                  ['name' => 'Certificado de Renta de jubilación o fotocopia legalizada emitida por el servicio Nacional del Sistema de Reparto.','number'=>0],  //En caso de perdida de boleta de pago
                  ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                  ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                  ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                  ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses de la unidad de Fondo de Retiro.','number'=>0], // para solicitantes AFPś
                  ['name' => 'Memorándum de agradecimiento de servicios en copia simple emitido por el Comando General de la Policía Boliviana.','number'=>0],  // *caso pasivo con renta en AFP , quien solicita el préstamo
                  ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
                ],
                'parameters' => [
                    'debt_index' => 90,
                    'quantity_ballots' => 1,
                    'guarantors' => 0,
                    'max_lenders' => 1
                ],
                'interest' => ['annual_interest' => 36,'penal_interest' => 6]
            ],
            ]
          ],
          'corto' => [
            // CORTO PLAZO
            'type' => ['module_id' => $module->id,'name'=>'Préstamo a corto plazo','second_name'=>'Corto plazo'],
            'limits' => ['maximum_amount' => 25000,'minimum_amount' => 2001,'maximum_term' => 30,'minimum_term' => 3],
            'modalities' => [
              // CORTO PLAZO SECTOR ACTIVO SERVICIO
              ['name'=>'Corto plazo sector activo','shortened'=>'PCP-SA','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                ['name' => 'Tres últimas Boletas de pago en copia simple.','number'=>2],
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>3],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>4],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              ],
              'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 3,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 20,'penal_interest' => 6]
            ],
              // CORTO PLAZO SECTOR ACTIVO DISPONIBILIDAD LETRA 'A'
              ['name'=>'Corto plazo con disponibilidad de letra "A"','shortened'=>'PCP-DLA','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                ['name' => 'Tres últimas Boletas de pago en copia simple..','number'=>2], //( 3 boletas)
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
            	  ['name' => 'Memorándum de destino a disponibilidad a la letra "A" en copia simple.','number'=>3], // Nuevo
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>4],
                ['name' => 'Certificado de años de servicio desglosado en copia simple emitido por el Comando General de la Policía Boliviana.','number'=>5],
                ['name' => 'Certificado de años de servicio desglosado en copia simple emitido por los Comandos Regionales de la Policía Boliviana.','number'=>5],
                ['name' => 'Certificado de calificación de años de servicio CAS emitido por el Ministerio de Economia y Finanzas Publicas','number'=>6],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>7],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser adicionales*/
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              ],
              'parameters' => [
                'debt_index' => 50,
                'quantity_ballots' => 3,
                'guarantors' => 0,
                'personal_reference' => true,
                'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 20,'penal_interest' => 6]
            ],
               // CORTO PLAZO SECTOR PASIVO Y VIUDAS AFP
              ['name'=>'Corto plazo el sector pasivo y viudas AFPs','shortened'=>'PCP-SP-AFP','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                ['name' => 'Tres últimas boletas de pago con renta en AFP en copia simple','number'=>2],      //Nuevo *caso pasivo con renta en AFP , quien solicita el préstamo
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
                ['name' => 'Tres últimas boletas de pago del garante en copia simple.','number'=>3], //garante solidario
                ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses de la unidad de Fondo de Retiro.','number'=>4], // para solicitantes AFPś
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A.','number'=>5],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>6],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Certificado de Renta de jubilación o fotocopia legalizada emitida por el servicio Nacional del Sistema de Reparto.','number'=>0],  //En caso de perdida de boleta de pago
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Memorándum de agradecimiento de servicios en copia simple emitido por el Comando General de la Policía Boliviana.','number'=>0],  // *caso pasivo con renta en AFP , quien solicita el préstamo
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],

              ],
              'parameters' => [
                'debt_index' => 50,
                'quantity_ballots' => 3,
                'guarantors' => 1,
                'min_guarantor_category' =>0.35,
                'max_guarantor_category' =>0.85,
                'max_lenders' => 1

              ],
              'interest' => ['annual_interest' => 20,'penal_interest' => 6]
            ],
              // CORTO PLAZO SECTOR PASIVO Y VIUDAS SENASIR
              ['name'=>'Corto plazo el sector pasivo y viudas Senasir','shortened'=>'PCP-SP-SEN','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                ['name' => 'Tres últimas boletas de pago con renta en SENASIR en copia simple.','number'=>2], //Nuevo *caso pasivo con renta de SENASIR, quien solicita el préstamo
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A.','number'=>3],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>4],
                  /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Certificado de Renta de jubilación o fotocopia legalizada emitida por el servicio Nacional del Sistema de Reparto.','number'=>0],  //En caso de perdida de boleta de pago
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Memorándum de agradecimiento de servicios en copia simple emitido por el Comando General de la Policía Boliviana.','number'=>0],  // *caso pasivo con renta en AFP , quien solicita el préstamo
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              ],
              'parameters' => [
                'debt_index' => 50,
                'quantity_ballots' => 3,
                'guarantors' => 0,
                'personal_reference' => true,
                'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 20,'penal_interest' => 6]
            ],
              // REFINANCIAMIENTO ACTIVO
              ['name'=>'Refinanciamiento de Préstamo a corto plazo para el sector Activo','shortened'=>'PCP-R-SA','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                ['name' => 'Tres últimas Boletas de pago en copia simple.','number'=>2],
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>3],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>4],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              ],
              'parameters' => [
                'debt_index' => 50,
                'quantity_ballots' => 3,
                'guarantors' => 0,
                'personal_reference' => true,
                'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 20,'penal_interest' => 6]
            ],
               // REFINANCIAMIENTO PASIVOS Y VIUDAS - AFP
              ['name'=>'Refinanciamiento de Préstamo a corto plazo para el sector Pasivo y Viudas AFPs','shortened'=>'PCP-R-SP-AFP','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                ['name' => 'Tres últimas boletas de pago con renta en AFP en copia simple.','number'=>2],      //Nuevo *caso pasivo con renta en AFP , quien solicita el préstamo
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
                ['name' => 'Tres últimas boletas de pago del garante en copia simple.','number'=>3], //garante solidario
                ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses de la unidad de Fondo de Retiro.','number'=>4], // para solicitantes AFPś
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>5],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>6],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Certificado de Renta de jubilación o fotocopia legalizada emitida por el servicio Nacional del Sistema de Reparto.','number'=>0],  //En caso de perdida de boleta de pago
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Memorándum de agradecimiento de servicios en copia simple emitido por el Comando General de la Policía Boliviana.','number'=>0],  // *caso pasivo con renta en AFP , quien solicita el préstamo
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              ],
              'parameters' => [
                'debt_index' => 50,
                'quantity_ballots' => 3,
                'guarantors' => 1,
                'min_guarantor_category' =>0.35,
                'max_guarantor_category' =>0.85,
                'max_lenders' => 1

              ],
              'interest' => ['annual_interest' => 20,'penal_interest' => 6]
            ],
              // REFINANCIAMIENTO PASIVOS Y VIUDAS - SENASIR
              ['name'=>'Refinanciamiento de Préstamo a corto plazo para el sector Pasivo y Viudas Senasir','shortened'=>'PCP-R-SP-SEN',
              'requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],
                ['name' => 'Tres últimas boletas de pago con renta en SENASIR en copia simple.','number'=>2], //Nuevo *caso pasivo con renta de SENASIR, quien solicita el préstamo
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>3],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>4],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Certificado de Renta de jubilación o fotocopia legalizada emitida por el servicio Nacional del Sistema de Reparto.','number'=>0],  //En caso de perdida de boleta de pago
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Memorándum de agradecimiento de servicios en copia simple emitido por el Comando General de la Policía Boliviana.','number'=>0],  // *caso pasivo con renta en AFP , quien solicita el préstamo
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
            	],
                'parameters' => [
                  'debt_index' => 50,
                  'quantity_ballots' => 3,
                  'guarantors' => 0,
                  'personal_reference' => true,
                  'max_lenders' => 1
                ],
                'interest' => ['annual_interest' => 20,'penal_interest' => 6]
            ]
            ],
          ],
          'largo' => [
            'type' => ['module_id' => $module->id,'name'=>'Préstamo a largo plazo','second_name'=>'Largo plazo'],
            'limits' => ['maximum_amount' => 150000,'minimum_amount' => 25001,'maximum_term' => 96,'minimum_term' => 25],
            'modalities' => [
                // LARGO PLAZO SECTOR ACTIVO Y ADMINISTRATIVO CON GARANTIA PERSONAL(2 GARANTES ACTIVOS)
              ['name'=>'Largo Plazo con garantía personal para el sector activo y personal Adm Policial','shortened'=>'PLP-GP-SAYADM','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple','number'=>1],
                ['name' => 'Ultima Boleta de pago en copia simple.','number'=>2], // Tambien de los 2 garantes
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no tener la boleta de pago
                ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando General de la Policía Boliviana','number'=>3],
                ['name' => 'Certificado de años de servicio desglosado en copia legalizada emitido por el Comando General de la Policía Boliviana.','number'=>3],
                ['name' => 'Certificado de años de servicio desglosado emitido por los Comandos Regionales de la Policía Boliviana.','number'=>3],
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>4],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>5],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Fotocopia Testimonio de propiedad.','number'=>0],  // en caso de que el plazo sea mayor a 60 meses un prestamo para vivienda pero no hipotecario
                ['name' => 'Folio Real Actualizado o Información rapida del inmueble.','number'=>0],  // en caso de que el plazo sea mayor a 60 meses un prestamo para vivienda pero no hipotecario
                ['name' => 'Fotocopia de catastro.','number'=>0],  // en caso de que el plazo sea mayor a 60 meses un prestamo para vivienda pero no hipotecario
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              ],
              'parameters' => [
                'debt_index' => 50,
                'quantity_ballots' => 1,
                'guarantors' => 2,
                'min_guarantor_category' =>0.35,
                'max_guarantor_category' =>1.00,
                'personal_reference' => true,
                'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
            ],
                  // LARGO PLAZO SECTOR PASIVO CON GARANTIA PERSONAL
              ['name'=>'Largo Plazo con garantía personal para el sector pasivo','shortened'=>'PLP-GP-SP','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple.','number'=>1],// del solicitante y los garantes
                ['name' => 'Ultima boleta de pago en copia simple.','number'=>2],
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no tener la boleta de pago
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>3],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>4],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Certificado de Renta de jubilación o fotocopia legalizada emitida por el servicio Nacional del Sistema de Reparto.','number'=>0],  //En caso de perdida de boleta de pago
                ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses de la unidad de Fondo de Retiro.','number'=>0], // para solicitantes AFPś
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPO
                ['name' => 'Memorándum de agradecimiento de servicios en copia simple emitido por el Comando General de la Policía Boliviana.','number'=>0],  // *caso pasivo con renta en AFP , quien solicita el préstamo
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],

              ],
              'parameters' => [
                'debt_index' => 50,
                'quantity_ballots' => 1,
                'guarantors' => 1,
                'min_guarantor_category' =>0.35,
                'max_guarantor_category' =>0.85,
                'personal_reference' => true,
                'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
            ],
                // LARGO PLAZO UN SOLO GARANTE SECTOR ACTIVO --Afiliados CPOP
              ['name'=>'Largo Plazo con un solo garante para el sector activo - CPOP','shortened'=>'PLP-CPOP','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple','number'=>1],
                ['name' => 'Ultima Boleta de pago en copia simple.','number'=>2], // Tambien de los 2 garantes
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no tener la boleta de pago
                ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando General de la Policía Boliviana','number'=>3],
                ['name' => 'Certificado de años de servicio desglosado en fotocopia Legalizada emitido por el Comando General de la Policía Boliviana','number'=>3],
	              ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando Regional de la Policía Boliviana','number'=>3],
                ['name' => 'Certificado de años de servicio desglosado en fotocopia Legalizada emitido por el Comando Regional de la Policía Boliviana','number'=>3],
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>4],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>5],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de no ingreso a disponibilidad de las letras “C” y “A” en original emitido por el Comando General de la Policía Boliviana.','number'=>0], //verificacion, no encontrarse en disponibilidad de letra A o C o item 0
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              ]
              ,
              'parameters' => [
                'debt_index' => 60,
                'quantity_ballots' => 1,
                'guarantors' => 1,
                'min_guarantor_category' =>0.35,
                'max_guarantor_category' =>1.00,
                'personal_reference' => true,
                'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
            ],
              // REFINANCIANCIAMIENTO SECTOR ACTIVO CON UN SOLO GARANTE ---> En este caso el garante solo puede ser activo
              ['name'=>'Refinanciamiento de prestamos a largo Plazo para el sector activo - CPOP','shortened'=>'PLP-R-SA-CPOP','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple','number'=>1],
                ['name' => 'Ultima Boleta de pago en copia simple.','number'=>2], // Tambien de los 2 garantes
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no tener la boleta de pago
            		['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando General de la Policía Boliviana','number'=>3],
            		['name' => 'Certificado de años de servicio desglosado en fotocopia Legalizada emitido por el Comando General de la Policía Boliviana','number'=>3],
                ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando Regional de la Policía Boliviana','number'=>3],
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>4],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>5],
                /*condiciones COP MUSERPOL*/
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Fotocopia Testimonio de propiedad.','number'=>0],  // en caso de que el plazo sea mayor a 60 meses un prestamo para vivienda pero no hipotecario
                ['name' => 'Folio Real Actualizado o Información rapida del inmueble.','number'=>0],  // en caso de que el plazo sea mayor a 60 meses un prestamo para vivienda pero no hipotecario
                ['name' => 'Fotocopia de catastro.','number'=>0],  // en caso de que el plazo sea mayor a 60 meses un prestamo para vivienda pero no hipotecario
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],

              ],
              'parameters' => [
                'debt_index' => 60,
                'quantity_ballots' => 1,
                'guarantors' => 1,
                'min_guarantor_category' =>0.35,
                'max_guarantor_category' =>1.00,
                'personal_reference' => true,
                'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
            ],
                 // REFINANCIANCIAMIENTO SECTOR PASIVO CON UN SOLO GARANTE
              ['name'=>'Refinanciamiento de prestamos a largo Plazo para el sector pasivo - CPOP','shortened'=>'PLP-R-SP-CPOP','requirements'=>[
                ['name' => 'Cédula de Identidad del (la) titular en copia simple','number'=>1],
                ['name' => 'Ultima boleta de pago en copia simple.','number'=>2],
                ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no tener la boleta de pago
                ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando General de la Policía Boliviana','number'=>3],
            		['name' => 'Certificado de años de servicio desglosado en fotocopia Legalizada emitido por el Comando General de la Policía Boliviana','number'=>3],
                ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando Regional de la Policía Boliviana','number'=>3],
                ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>4],
                ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>5],
                /*condiciones COP MUSERPOL*/
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
                ['name' => 'Certificado de Renta de jubilación o fotocopia legalizada emitida por el servicio Nacional del Sistema de Reparto.','number'=>0],  //En caso de perdida de boleta de pago
                ['name' => 'Certificado de aportes para el Auxilio Mortuorio de los 3 últimos meses de la unidad de Fondo de Retiro.','number'=>0], // para solicitantes AFPś
                ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
                ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
                ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPO
                ['name' => 'Memorándum de agradecimiento de servicios en copia simple emitido por el Comando General de la Policía Boliviana.','number'=>0],  // *caso pasivo con renta en AFP , quien solicita el préstamo
                ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              ],
              'parameters' => [
                'debt_index' => 60,
                'quantity_ballots' => 1,
                'guarantors' => 1,
                'min_guarantor_category' =>0.35,
                'max_guarantor_category' =>0.85,
                'personal_reference' => true,
                'max_lenders' => 1
              ],
              'interest' => ['annual_interest' => 13.2,'penal_interest' => 6]
            ],
            ]
          ],
          'hipotecario' => [
            'type' => ['module_id' => $module->id,'name'=>'Préstamo hipotecario','second_name'=>'Hipotecario'],
            'limits' => ['maximum_amount' => 700000,'minimum_amount' => 25001,'maximum_term' => 240,'minimum_term' => 25],
            'modalities' => [
               // LARGO PLAZO GARANTIA HIPOTECARIA
              ['name'=>'Préstamo con garantia hipotecaria de bien inmueble para el sector activo','shortened'=>'PLP-GH-SA','requirements'=>[
              ['name' => 'Cédula de Identidad del (la) titular en copia simple','number'=>1],
              ['name' => 'Tres últimas Boletas de pago en copia simple.','number'=>2],
              ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>2],  // En caso de no contar con la boleta de pago
              ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando General de la Policía Boliviana','number'=>3],
              ['name' => 'Certificado de años de servicio desglosado en fotocopia Legalizada emitido por el Comando General de la Policía Boliviana','number'=>3],
              ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando Regional de la Policía Boliviana','number'=>3],
              ['name' => 'Croquis de ubicación del inmueble','number'=>4], //nuevo
              ['name' => 'Factura de Luz del Inmueble','number'=>5], //nuevo
              ['name' => 'Factura de Agua del Inmueble','number'=>6], //nuevo
              ['name' => 'Folio Real','number'=>7], //nuevo
              ['name' => 'Informacion rapida del Inmueble','number'=>8], //nuevo
              ['name' => 'Fotocopia de Testimonio de Propiedad','number'=>9], //nuevo
              ['name' => 'Fotocopia de Impuestos 3 Ultimos Años','number'=>10], //nuevo
              ['name' => 'Fotocopia de Plano de lote','number'=>11], //nuevo
              ['name' => 'Fotocopia de Catastro','number'=>12], //nuevo
              ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>13],
              ['name' => 'Certificado de estado civil en original emitido por el SERECI.','number'=>14],
              ['name' => 'Formulario de Calificación y Aprobación de Préstamos FORM/CAYAP/PTMO/UIP/004.','number'=>15],
              /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
              ['name' => 'Solicitud de aclaración de datos personales en la Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
              ['name' => 'Certificado de no adeudo, emitido por la instancia correspondiente.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
              ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
              ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],
              /**/
            ],
            'parameters' => [
            'debt_index' => 80,
            'quantity_ballots' => 1,
            'guarantors' => 0,
            'personal_reference' => true,
            'max_lenders' => 2
            ],
            'interest' => ['annual_interest' => 9,'penal_interest' => 6]
            ],
             // LARGO PLAZO GARANTIA HIPOTECARIA CPOP
              ['name'=>'Préstamo con garantia hipotecaria de bien inmueble para el sector activo - CPOP','shortened'=>'PLP-GH-CPOP','requirements'=>[
              ['name' =>'Cédula de Identidad del (la) titular en copia simple','number'=>1],
              ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando General de la Policía Boliviana','number'=>2],
              ['name' => 'Certificado de años de servicio desglosado en fotocopia Legalizada emitido por el Comando General de la Policía Boliviana','number'=>2],
              ['name' => 'Certificado de años de servicio desglosado en original emitido por el Comando Regional de la Policía Boliviana','number'=>2],
              ['name' => 'Croquis de ubicación del inmueble','number'=>3], //nuevo
              ['name' => 'Factura de Luz del Inmueble','number'=>4], //nuevo
              ['name' => 'Factura de Agua del Inmueble','number'=>4], //nuevo
              ['name' => 'Folio Real','number'=>5], //nuevo
              ['name' => 'Informacion rapida del Inmueble','number'=>5], //nuevo
              ['name' => 'Fotocopia de Testimonio de Propiedad','number'=>6], //nuevo
              ['name' => 'Fotocopia de Impuestos 3 Ultimos Años','number'=>7], //nuevo
              ['name' => 'Fotocopia de Plano de lote','number'=>8], //nuevo
              ['name' => 'Fotocopia de Catastro','number'=>9], //nuevo
              ['name' => 'Estado de cuenta original, vigente y emitido por el Banco Unión S.A','number'=>10],
              ['name' => 'Certificado de estado civil en original emitido por el SERECI.','number'=>11],
                /*Estos documentos son solicitados por MUSERPOL en caso de ser requeridos*/
              ['name' => 'Boleta de pago en copia simple.','number'=>0],  // *caso garante activo
              ['name' => 'Certificado de haberes considerando el último mes percibido.','number'=>0],  // En caso de no tener la boleta de pago
              ['name' => 'Nota de aclaración de Boleta de Pago.','number'=>0],  // En caso de que el afiliado tenga datos erroneos en su boleta de pago
              ['name' => 'Certificado de no adeudo.','number'=>0],  // en caso de que el afiliado haya tenido deudas de otras entidades (Ej: COMIPOL , COVIPOL)
              ['name' => 'Certificado de pago emitido por la entidad correspondiente.','number'=>0],  // en caso de que el afiliado tenga deudas con otras entidades (Ej: COMIPOL , COVIPOL)
              ['name' => 'Conformidad de devolución de descuento por garantia original o fotocopia legalizada.','number'=>0],

              /**/
            ],
            'parameters' => [
              'debt_index' => 80,
              'quantity_ballots' => 1,
              'guarantors' => 0,
              'personal_reference' => true,
              'max_lenders' => 2
            ],
            'interest' => ['annual_interest' => 9,'penal_interest' => 6]
            ],
            ]
          ],
           ////Procedure Type Prestamo amortización
          'amortizacion' => [
            'type' => ['module_id' => $module->id,'name'=>'Amortización','second_name'=>'Amortización'],
            'limits' => [],
            'modalities' => [ ['name'=>'Amortización','shortened'=>'A','requirements'=>[],
            ]]
          ],
        ]
      ];
      foreach ($data['procedures'] as $procedure) {
        $new_procedure = ProcedureType::firstOrCreate($procedure['type']);
        $new_procedure->interval()->firstOrCreate($procedure['limits']);
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
