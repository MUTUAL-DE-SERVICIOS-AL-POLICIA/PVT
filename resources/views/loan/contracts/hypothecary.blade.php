<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>PLATAFORMA VIRTUAL ADMINISTRATIVA - MUSERPOL </title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>
@include('partials.header', $header)
<body>
<div class="block">
    <div class="font-semibold leading-tight text-center m-b-10 text-xs">
        CONTRATO DE <font style="text-transform: uppercase;">{{ $title }}</font>
        <div>Nº {{ $loan->code }}</div>
    </div>
</div>

<?php $modality = $loan->modality;
if(($modality->name == 'Refinanciamiento de Préstamo con Garantía Hipotecaria de Bien Inmueble Sector Activo' || $modality->name == 'Refinanciamiento de Préstamo con Garantía Hipotecaria de Bien Inmueble Sector Activo CPOP')){?>
<div class="block text-justify">
<div>
        <b>SEÑOR NOTARIO DE FE PÚBLICA</b>
    </div>
    <div>
        En el Registro de Escrituras Públicas que corren a su cargo, sírvase insertar un <span style="text-transform: uppercase;">CONTRATO DE {{ $title }}</span>, de conformidad a las siguientes cláusulas:
    </div>
    <div>
        <b>PRIMERA.- (PARTES DEL CONTRATO):</b> Las siguientes partes celebran el presente contrato de refinanciamiento:
        <ol>
            <li><b>LA ENTIDAD:</b> La Mutual de Servicios al Policía "MUSERPOL", representada legalmente por su por el {{ $employees[0]['position'] }} Cnl. {{ $employees[0]['name'] }} con C.I. {{ $employees[0]['identity_card'] }} y su {{ $employees[1]['position'] }} Lic. {{ $employees[1]['name'] }} con C.I. {{ $employees[1]['identity_card'] }}, que para fines de este contrato en adelante se denominará MUSERPOL con domicilio en la Z. Sopocachi, Av. 6 de agosto N° 2354.</li>
            @if (count($lenders) == 1)
            @php ($lender = $lenders[0]->disbursable)
            @php ($male_female = Util::male_female($lender->gender))
            <li><span>
                <b> {{ $lender->full_name }}, </b> con C.I. {{ $lender->identity_card_ext}}, {{ $lender->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender->city_birth->name }}, vecin{{ $male_female }} de {{ $lender->address->cityName() }} y con domicilio especial en {{ $lender->address->full_address }}, en adelante denominad{{ $male_female }} DEUDOR{{ $lender->gender == 'M' ? '' : 'A' }} .
            </span>
            </li>
            @endif
            @if (count($lenders) == 2)
            @php ($lender_one = $lenders[0]->disbursable)
            @php ($male_female_one = Util::male_female($lender_one->gender))
            @php ($lender_two = $lenders[1]->disbursable)
            @php ($male_female_two = Util::male_female($lender_two->gender))
                @if($loan->disbursable->id == $lenders[0]->disbursable->id)
                <li>
                    <span>
                        <b>{{ $lender_one->full_name }}, </b> con C.I. {{ $lender_one->identity_card_ext }}, {{ $lender_one->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender_one->city_birth->name }}, vecin{{ $male_female_one }} de {{ $lender_one->address->cityName() }} y con domicilio especial en {{ $lender_one->address->full_address }}, en adelante denominad{{ $male_female_one }} DEUDOR{{ $lender_one->gender == 'M' ? '' : 'A' }}.
                    </span>
                </li>
                <li>
                <span>
                    <b>{{ $lender_two->full_name }}, </b> con C.I. {{ $lender_one->identity_card_ext }}, {{ $lender_one->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender_two->city_birth->name }}, vecin{{ $male_female_two }} de {{ $lender_two->address->cityName() }} y con domicilio especial en {{ $lender_two->address->full_address }}, en adelante denominad{{ $male_female_two }} CODEUDOR{{ $lender_two->gender == 'M' ? '' : 'A' }}.
                </span>
                </li>
                @endif
                @if($loan->disbursable->id == $lenders[1]->disbursable->id)
                <li>
                    <span>
                        <b>{{ $lender_two->full_name }}, </b> con C.I. {{ $lender_two->identity_card_ext }}, {{ $lender_two->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender_two->city_birth->name }}, vecin{{ $male_female_two }} de {{ $lender_two->address->cityName() }} y con domicilio especial en {{ $lender_two->address->full_address }}, en adelante denominad{{ $male_female_two }} DEUDOR{{ $lender_two->gender == 'M' ? '' : 'A' }}.
                    </span>
                </li>
                <li>
                    <span>
                        <b>{{ $lender_one->full_name }}, </b> con C.I. {{ $lender_one->identity_card_ext }}, {{ $lender_one->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender_one->city_birth->name }}, vecin{{ $male_female_one }} de {{ $lender_one->address->cityName() }} y con domicilio especial en {{ $lender_one->address->full_address }}, en adelante denominad{{ $male_female_one }} CODEUDOR{{ $lender_one->gender == 'M' ? '' : 'A' }}.
                    </span>
                </li>
                @endif
            @endif
            @if(count($loan->cosigners)>0)
            @foreach ($loan->cosigners as $cosigner)
            @php ($male_female_cosigner = Util::male_female($cosigner->gender))
            <li>
                <span>
                    <b>{{ $cosigner->full_name }}</b> con C.I. {{ $cosigner->identity_card_ext}}, {{ $cosigner->civil_status_gender }},  mayor de edad, hábil por derecho, natural de XXXX vecin{{ $male_female_cosigner }} de {{ $cosigner->address }} y con domicilio especial en {{ $cosigner->address }}, en adelante denominad{{ $male_female_cosigner }} CODEUDOR{{ $cosigner->gender == 'M' ? '' : 'A' }}.
                </span>
            </li>
            @endforeach
            @endif
        </ol>
    </div>
    <div>
        @php ($plural = count($lenders) > 1)
        {{ $plural ? ' Los domicilios señalados' : 'El domicilio señalado' }} por {{ $plural ? 'los DEUDORES' : 'el DEUDOR'}} {{ $plural ? 'se constituyen en domicilios especiales' : 'se constituye en domicilio especial' }} conforme al Art. 29º parágrafo II del Código Civil, donde se practicarán válida y legalmente todas las citaciones y/o notificaciones judiciales personales y/o por cédula, sin lugar a posterior observación, incidente o recurso alguno.
    </div>

    <div>
        <?php $modality = $loan->modality;
        if($loan->data_loan){ ?>
            <b>SEGUNDA.- (DEL ANTECEDENTE):</b>Mediante contrato de préstamo {{ $loan->data_loan->code }} SISMU suscrito entre MUSERPOL y el PRESTATARIO un préstamo por la suma de Bs. {{ $loan->data_loan->amount_approved }} (<span class="uppercase">{{ Util::money_format($loan->data_loan->amount_approved, true) }}</span> Bolivianos), con garantía de haberes y garantía personal si corresponde.
            <div>
            <b>TERCERA.- (DEL OBJETO):</b>  El objeto del presente contrato es el refinanciamiento del préstamo de dinero que MUSERPOL otorga al PRESTATARIO conforme a calificación, previa evaluación y autorización, de conformidad a los niveles de aprobación respectivos en la suma de Bs. {{ $loan->refinancing_balance }} (<span class="uppercase">{{ Util::money_format($loan->refinancing_balance, true) }}</span> Bolivianos), para lo cual el PRESTATARIO reconoce de manera expresa el saldo anterior de la deuda correspondiente al préstamo contraído con anterioridad, que asciende a la suma de Bs. {{ $loan->balance_parent_refi()}} (<span class="uppercase">{{ Util::money_format($loan->balance_parent_refi(), true) }}</span> Bolivianos), montos que hacen un total efectivo de {{ $loan->amount_approved }} (<span class="uppercase">{{ Util::money_format($loan->amount_approved, true) }}</span> Bolivianos), que representa la nueva obligación contraída sujeta a cumplimiento, en función a la operación de refinanciamiento.
            </div>
            <?php }
        else{?>
            <div>
            <b>SEGUNDA.- (DEL ANTECEDENTE):</b>Mediante contrato de préstamo {{ $parent_loan->code }} con fecha de desembolso {{ Carbon::parse($parent_loan->disbursement_date)->isoFormat('LL') }} y modalidad de  {{strtolower($parent_loan->modality->name)}}, se ha suscrito entre MUSERPOL y el PRESTATARIO un préstamo por la suma de Bs. {{ $parent_loan->amount_approved }} (<span class="uppercase">{{ Util::money_format($parent_loan->amount_approved, true) }}</span> Bolivianos), con garantía de haberes y garantía personal si corresponde.
            </div>
            <div>
            <b>TERCERA.- (DEL OBJETO):</b>  El objeto del presente contrato es el refinanciamiento del préstamo de dinero que MUSERPOL otorga al PRESTATARIO conforme a calificación, previa evaluación y autorización, de conformidad a los niveles de aprobación respectivos en la suma de Bs. {{ $loan->refinancing_balance }} (<span class="uppercase">{{ Util::money_format($loan->refinancing_balance, true) }}</span> Bolivianos), para lo cual el PRESTATARIO reconoce de manera expresa el saldo anterior de la deuda correspondiente al préstamo contraído con anterioridad, que asciende a la suma de Bs. {{ $loan->balance_parent_refi()}} (<span class="uppercase">{{ Util::money_format($loan->balance_parent_refi(), true) }}</span> Bolivianos), montos que hacen un total efectivo de {{ $loan->amount_approved }} (<span class="uppercase">{{ Util::money_format($loan->amount_approved, true) }}</span> Bolivianos), que representa la nueva obligación contraída sujeta a cumplimiento, en función a la operación de refinanciamiento.
            </div>
        <?php }?>
    </div>
    <div>
        <b>CUARTA.- (DESTINO Y USO):</b> LA ENTIDAD concede el préstamo pactado con destino exclusivo a {{ $loan->destiny->name }}. Su uso total o parcial en una finalidad diferente se reputará como incumplimiento y la obligación como vencida, líquida y exigible, obligando {{ $plural ? 'a los DEUDORES' : 'al DEUDOR'}} a restituir el capital e intereses en forma inmediata y determinará la mora {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}. Para esta eventualidad y sin perjuicio del cobro coactivo, LA MUSERPOL se reserva el derecho de iniciar las acciones legales pertinentes.
    </div>
    <div>
        <b>QUINTA.- (DESEMBOLSO):</b> El préstamo concedido quedará perfeccionado mediante un solo desembolso que quedará acreditado mediante comprobante escrito en el que conste el abono efectuado a favor del PRESTATARIO, o a través de cuenta bancaria del Banco Unión a solicitud del prestatario; reconociendo ambas partes que al amparo de este procedimiento se cumple satisfactoriamente la exigencia contenida en el artículo 1331 del Código de Comercio.
    </div>
    <div>
        Dicho desembolso será realizado en Bolivianos, previo cumplimiento por {{ $plural ? 'los DEUDORES' : 'el DEUDOR'}} de todos los requisitos y condiciones establecidos al efecto.
    </div>
    <div>
        <b>SEXTA.- (PLAZO Y AMORTIZACIÓN):</b> {{ $plural ? 'Los DEUDORES se comprometen' : 'El DEUDOR se compromete'}} a que el presente préstamo será cancelado en un plazo de <b><span class="uppercase">{{ Util::number_integer($loan->loan_term,true) }}</span> ({{ ($loan->loan_term) }}) meses</b> computables a partir de la fecha efectiva de desembolso, mediante <b><span class="uppercase">{{ Util::number_integer($loan->loan_term, true) }}</span> ({{ $loan->plan? $loan->loan_term:'no desembolsado' }})</b> amortizaciones mensuales consecutivas e improrrogables y será pagado en capital e intereses convenidos al vencimiento de dichas amortizaciones sin necesidad de aviso ni formalidad previa alguna, conforme al PLAN DE PAGO.
    </div>
    <div>
        La cuota de amortización mensual es de Bs. {{ $loan->estimated_quota }} (<span class="uppercase">{{ Util::money_format($loan->estimated_quota, true) }}</span> Bolivianos).
    </div>
    <div>
        Los intereses generados entre la fecha del desembolso del préstamo y la fecha de primer pago, serán cobrados con la primera cuota; conforme lo establece el Reglamento de Préstamos.
    </div>
    <div>
        <b>SÉPTIMA.- (INTERESES Y GASTOS):</b> El préstamo concedido en virtud del presente contrato de refinanciamiento, estará sujeto, al pago periódico mensual, de una tasa de interés del {{ round($loan->interest->annual_interest) }}% fijo anual sobre saldo deudor.
    </div>
    <div>
        Todos los gastos relativos a la concesión del presente préstamo serán a cargo y cuenta exclusiva {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}.
    </div>
    <div>
        <b>OCTAVA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el DEUDOR, autoriza expresamente a MUSERPOL practicar los descuentos respectivos de los haberes que percibe en forma mensual a través del Comando General de la Policía Boliviana.
    </div>
    <div>
        Si por cualquier motivo la MUSERPOL estuviera imposibilitada de realizar el descuento por el medio señalado, el PRESTATARIO se obliga a cumplir con la cuota de amortización mediante pago directo en la Oficina Central de MUSERPOL de la ciudad de La Paz o efectuar depósito bancario en la cuenta fiscal de la MUSERPOL y enviar la boleta de depósito original a la Oficina Central inmediatamente; sin necesidad de previo aviso; caso contrario el PRESTATARIO se hará pasible al recargo correspondiente a los intereses que se generen al día de pago por la deuda contraída.
    </div>
    <div>
        Asi mismo el PRESTATARIO se compromete a hacer conocer oportunamente a MUSERPOL sobre la omisión del descuento mensual que se hubiera dado a efectos de solicitar al Comando General de la Policía Boliviana se regularice este descuento, sin perjuicio que realice el depósito directo del mes omitido, de acuerdo a lo estipulado en el párrafo precedente.
    </div>
    <div>
        <b>NOVENA.- (GARANTÍAS):</b> {{ $plural ? 'los DEUDORES garantizan el cumplimiento de sus obligaciones' : 'el DEUDOR garantiza el cumplimiento de su obligación'}}  con la generalidad de sus bienes, acciones y derechos  habidos y por haber, y de un modo especial con:
    </div>
    <div>
        <ol>
            <li>
                <b>Garantía Hipotecaria</b>
            </li>
            <div>{{ $plural ? ' Los DEUDORES' : 'El deudor' }}, asimismo LA MUSERPOL reconocen los derechos de la Matrícula descripción del inmueble <b>(Folio Real)</b>, como producto de un crédito el cual será cancelado con el presente préstamo para lo cual {{ $plural ? 'los deudores' : 'el deudor' }} también propietarios y LA MUSERPOL dan su consentimiento para la constitución de la presente hipoteca de inmueble en el grado de prelación que corresponda. </div>
            <div>La hipoteca en el grado de prelación que corresponda debe efectuarse sobre el bien inmueble consistente en un Lote de Terreno asignado como el {{$loan->loan_property->land_lot_number}}, Unidad Vecinal {{$loan->loan_property->neighborhood_unit}} de la {{$loan->loan_property->location}}, con una superficie de {{$loan->loan_property->surface}} {{$loan->loan_property->measurement}}, con Código Catastral {{$loan->loan_property->cadastral_code}}, cuyas colindancias son las siguientes: {{$loan->loan_property->limit}}. El bien inmueble es de propiedad de {{ $plural ? ' los DEUDORES' : 'el deudor' }} 
            @foreach ($lenders as $lender)
            {{ $lender->disbursable->full_name }},
            @endforeach
            de generales descritas en la cláusula primera del presente documento, conforme se evidencia en la Escritura Pública {{$loan->loan_property->public_deed_number}} de {{$loan->loan_property->public_deed_date}}. Extendida por ante Notaría de Fe Pública de Fe Pública a cargo de {{$loan->loan_property->lawyer}} dicho inmueble se encuentra registrado bajo la Matricula Computarizada Nº {{$loan->loan_property->registration_number}}. Bajo el asiento {{$loan->loan_property->real_folio_number}}
            del Folio Real, incluidas todas las construcciones, mejoras incorporadas, presentes y futuras, acciones y derechos correspondientes.</div>
            <div>Asimismo, {{ $plural ? 'los propietarios' : 'el propietario'}} de la garantía autorizan expresamente al registro del presente documento en oficinas de Derechos Reales de la ciudad {{$loan->loan_property->public_deed_date}}</div>
        <ol>
    </div>
    <div>
        <b>DÉCIMA.- (CADUCIDAD Y DERECHOS DE ACELERACIÓN)</b>De acuerdo a lo estipulado en el Art. 569 del Código Civil y 805 del Código de Comercio, antes del vencimiento del plazo pactado, LA MUSERPOL podrá resolver el presente contrato en forma anticipada declarando el monto del préstamo, sus intereses y accesorios pendientes de pago, de plazo vencido, sumas líquidas y exigibles y en mora y proceder a la cobranza judicial sin necesidad de ninguna intimación o requerimiento judicial o extrajudicial si {{ $plural ? ' los DEUDORES incurrieran' : 'El DEUDOR incurriera' }} en cualquiera de las siguientes causales:
    </div>
    <div>
        Incumplimiento de cualquiera de las obligaciones estipuladas en el presente contrato.
    </div>
    <div>
        <ol>
            <li>Falta de pago oportuno de cualquier cuota o amortización del préstamo ya sea pago a capital, intereses, impuestos o accesorios aplicables de acuerdo al Plan de Pagos. </li>
            <li>Uso del préstamo en una finalidad distinta de la pactada. </li>
            <li>Incumplimiento o falta de pago oportuno de otros préstamos y/u obligaciones que {{ $plural ? 'los DEUDORES tengan' : 'el DEUDOR tenga'}} en LA MUSERPOL.</li>
            <li>Disminución de las garantías otorgadas o del patrimonio {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}. </li>
            <li>La suspensión o cierre de las actividades {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}.</li>
            <li>Declaratoria de quiebra voluntaria o judicial, concurso de acreedores o cesión de bienes, promovida por {{ $plural ? 'los DEUDORES' : 'el DEUDOR'}} o por terceros y que involucre {{ $plural ? 'a los DEUDORES' : 'al DEUDOR'}}.</li>
            <li>Disminución de los bienes {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}} o si fueran objeto de embargo, secuestro, incautación o demanda por cualquier ejecución y/o juicio.</li>
            <li>Fuga o persecución judicial {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}</li>
            <li>Presentación de información falsa, inexacta o incorrecta por parte {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}</li>
            <li>Falta de aviso oportuno {{ $plural ? 'de los DEUDORES' : 'el DEUDOR'}} a LA ENTIDAD de cualquier cambio en las condiciones establecidas en el presente contrato en cuanto se refiere a la conservación y ubicación de los bienes otorgados en garantía. De incurrir en cualquiera de las causales anteriormente señaladas y con la sola declaración de caducidad y mora que internamente realice LA MUSERPOL en sus registros, se dará la resolución anticipada del presente contrato.</li>

        </ol>
    </div>
    <div>
        Desde ese momento, la totalidad de la suma adeudada por capital, intereses ordinarios y penales, impuestos, gastos, costas, honorarios y otros se reputarán de plazo vencido, líquido y exigible por lo que LA MUSERPOL podrá perseguir el pago del monto adeudado mediante proceso coactivo u otro que viere conveniente. {{ $plural ? 'los DEUDORES aceptan' : 'el DEUDOR acepta'}} desde ahora como buenas y exactas las cuentas que formule LA MUSERPOL respecto de este préstamo de dinero como líquido y exigible el saldo que se reclame sin necesidad de notificación previa o posterior.
    </div>
    <div>
        <b>DÉCIMA.- (MORA)</b> Se deja expresamente establecido que a la falta de pago total o parcial a capital, intereses en el plazo estipulado o la simple demora,{{ $plural ? 'los DEUDORES' : 'el DEUDOR'}} se constituirán en mora por la totalidad de la obligación, la cual se considerará como vencida, líquida y exigible, sin necesidad de intimación, requerimiento, solicitud u otra formalidad previa judicial o extrajudicial, de acuerdo a lo  establecido por el artículo 341, Núm. 1) del Código Civil, al incumplimiento del pago de cualquier amortización, sin necesidad de intimación o requerimiento alguno, o acto equivalente por parte de la MUSERPOL.
    </div>
    <div>
        En caso de que el PRESTATARIO incurra en mora, se le añadirá al total de la obligación adeudada, un interés moratorio anual del {{ round($loan->interest->penal_interest) }}%, que será efectiva por todo el tiempo que perdure el incumplimiento objeto de la mora, debiendo ser cobrados de acuerdo a la disponibilidad de saldo deudor y/o requerimiento de la MUSERPOL.
    </div>
    <div>
        <b>DÉCIMA PRIMERA.- (OBLIGACIONES ESPECIALES)</b> {{ $plural ? 'Los DEUDORES quedan reatados' : 'El DEUDOR queda reatado'}}  al cumplimiento de las siguientes obligaciones especiales:
    </div>
    <div>
        <ol>
            <li>Realizar con carácter previo al desembolso, la protocolización notarial del presente documento.</li>
            <li>Ejecutar la inscripción del gravamen hipotecario constituido en el respectivo registro público.</li>
            <li>Suministrar a LA MUSERPOL cualquier información y/o documento legal y/o contable que este requiera durante la vigencia del préstamo.</li>
            <li>Informar a LA MUSERPOL en forma oportuna y detallada sobre cualquier cambio de domicilio, cambio de actividad, deterioro o pérdida de los bienes otorgados en garantía, viajes prolongados, fallecimientos o cualquier otro hecho o acontecimiento que de alguna u otra manera pudiera afectar el cumplimiento de la obligación de pago.</li>
        </ol>
    </div>

    <div>
        El incumplimiento por parte {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}} de cualquiera de las obligaciones señaladas, dará lugar a que el préstamo se considere de plazo vencido, líquido y exigible, pudiendo LA MUSERPOL iniciar las acciones judiciales correspondientes a efecto de perseguir el pago del monto adeudado.
    </div>
    <div>
        <b>DÉCIMA SEGUNDA.- (FUERZA COACTIVA)</b> Se deja expresamente establecido que el prestatario, codeudores o fiadores, depositarios, propietarios, garantes hipotecarios y/o prendarios y otros obligados, que a los efectos de una acción judicial que inicie <b>LA MUSERPOL</b> por el incumplimiento al presente contrato, renuncian en forma irrevocable y expresa a los trámites del proceso ejecutivo, reconociendo al presente contrato como título coactivo civil incuestionable, a la que expresamente se someten aplicándose en ejecución lo previsto por la Ley N° 439 deL Código Procesal Civil en sus artículos 404 y siguientes, según corresponda.
    </div>
    <div>
        Sin perjuicio de la renuncia efectuada, LA MUSERPOL se reserva el derecho de efectuar la ejecución judicial para el cobro de lo adeudado, por la vía coactiva o ejecutiva, de acuerdo a su simple elección, a lo cual el prestatario y demás obligados simplemente se adhieren.
    </div>
    <div>
        <b>DÉCIMA TERCERA.- (CESIÓN DE CRÉDITO)</b> {{ $plural ? 'Los DEUDORES autorizan' : 'El DEUDOR autoriza'}} en forma expresa a LA MUSERPOL a ceder, transmitir, pignorar, o delegar el presente contrato, sus accesorios, garantías, intereses, flujos demás activos o cualquier derecho emergente del presente contrato de préstamo a favor de cualquier entidad financiera, sociedad de titularización y/o de terceros que LA MUSERPOL juzgue conveniente, constituyendo la presente cláusula notificación suficiente para el efecto señalado, tal como establece el Art. 818º del  Código de Comercio. En esta eventualidad la gestión efectuada surtirá plenos efectos jurídicos respecto {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}} sin necesidad de requisitos formales y con sujeción a disposiciones legales vigentes.
    </div>
    <div>
        Se encuentran comprendidos dentro de dicha autorización la facultad de ceder y/o transmitir total o parcialmente los derechos derivados de las pólizas de seguro que amparen los bienes objeto de garantía.
    </div>
    <div>
        {{ $plural ? 'Los DEUDORES dan' : 'El DEUDOR da'}} su pleno consentimiento para que se realicen los registros e inscripciones sobre los derechos del presente contrato o de los bienes objeto de las garantías constituidas en el mismo o sobre otros aspectos relacionados con este contrato que sean necesarios como efecto de la celebración, eficacia, perfeccionamiento y/o ejecución de la cesión o transmisión conforme a la presente cláusula.
    </div>
    <div>
        <b>DÉCIMA CUARTA.- (MODIFICACIÓN DE LA SITUACIÓN DEL PRESTATARIO)</b> En caso de fallecimiento, retiro voluntario, retiro forzoso, LOS DEUDORES garantizan con la totalidad de sus Beneficios Fondo de Retiro Policial Solidario  y Complemento Económico que otorga la MUSERPOL, el cumplimiento efectivo de la presente obligación; por cuanto la liquidación de dichos beneficios pasarán a cubrir el monto total de la obligación que resulte adeudada, más los intereses devengados a la fecha, previa las formalidades de ley.
    </div>
    <div>
        En caso de que se haya modificado la situación del DEUDOR, del sector activo al sector pasivo de la Policía Boliviana, teniendo un saldo deudor respecto del préstamo obtenido, este acepta amortizar la deuda con su Complemento Económico, en caso de corresponderle, debiendo al efecto solicitar la reprogramación conforme establece  el  Artículo  90 del Reglamento de Préstamos de la MUSERPOL, por lo que el saldo se sujetará en función a su nuevo liquido pagable, estableciéndose una correlativa modificación de los plazos y cuota según sea el caso.
    </div>
    <div>
        La MUSERPOL, para el caso en que el DEUDOR o sus beneficiarios no inicien el trámite de solicitud de pago de la prestación que corresponda, se encuentra facultada a iniciar por su cuenta el trámite pertinente, cuyos costos serán imputados al total adeudado y descontados del total de la prestación.
    </div>
    <div>
        <b>DÉCIMA QUINTA.- (INVESTIGACIÓN Y SUPERVISIÓN)</b> {{ $plural ? 'Autorizan' : 'Autoriza'}} a LA MUSERPOL y/o a otros a quien este faculte a realizar inspecciones para verificar el destino del préstamo otorgado, la situación de las garantías, así como su situación económica y se compromete/n a suministrar toda la información que LA MUSERPOL requiera.
    </div>
    <div>
        Asimismo {{ $plural ? 'autorizan' : 'autoriza'}} a LA MUSERPOL y/o a otros a quien este faculte a realizar inspecciones para verificar el destino y uso del préstamo otorgado, la situación de las garantías, así como su situación económica y se compromete/n a suministrar toda la información que se requiera.
    </div>
    <div>
        <b>DÉCIMA SEXTA.- (INFORMACIÓN)</b> {{ $plural ? 'Los DEUDORES podrán' : 'El DEUDOR podrá'}} solicitar a LA MUSERPOL en cualquier momento información detallada referida a los pagos y cobros efectuados, el cronograma de pagos, la forma de cálculo y así como la liquidación total o parcial del préstamo.
    </div>
    <div>
        <b>DÉCIMA SÉPTIMA.- (CONFORMIDAD)</b> Nosotros, LA MUSERPOL, como ACREEDORES representado por su {{ $employees[0]['position'] }} Cnl. {{ $employees[0]['name'] }} y su {{ $employees[1]['position'] }} Lic. {{ $employees[1]['name'] }} por una parte y
        @if (count($lenders) == 1)
            @php ($lender = $lenders[0]->disbursable)
            @php ($male_female = Util::male_female($lender->gender))
        <span><b> {{ $lender->full_name }}, </b> con C.I. {{ $lender->identity_card_ext }},</span>
        @endif
        @if (count($lenders)>1)
        @php ($lender = $lenders[0]->disbursable)
        @php ($male_female = Util::male_female($lender->gender))
        <span><b> {{ $lender->full_name }}, </b> con C.I. {{ $lender->identity_card_ext }},</span>
        @php ($lender = $lenders[1]->disbursable)
        @php ($male_female = Util::male_female($lender->gender))
        <span><b> {{ $lender->full_name }}, </b> con C.I. {{ $lender->identity_card_ext }},</span>
        @endif
        EN CALIDAD DE {{ $plural ? 'DEUDORES Y PROPIETARIOS' : 'DEUDOR Y PROPIETARIO'}}, declaramos nuestra plena conformidad con cláusulas precedentes y nos comprometemos en la fecha al fiel y estricto cumplimiento del presente documento.
    </div>
    <div>
        Usted Señor Notario, se servirá agregar las demás cláusulas de seguridad y estilo.
    </div><br><br>
    <div class="text-center">
        <p class="center">
        La Paz, {{ Carbon::parse($loan->request_date)->isoFormat('LL') }}
        </p>
    </div>
    <div></div>
</div>
<div class = "m-t-100">
    <div>
        @if (count($lenders) == 1)
        <table>
            <tr>
                <td>
                @php ($lender = $lenders[0]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'PRESTATARIO'
                ])
                </td>
            </tr>
        </table>
        @endif
        @if (count($lenders) == 2)
        @if($loan->disbursable->id == $lenders[0]->disbursable->id)
        <table>
            <tr>
                <td width="50%">
                @php ($lender = $lenders[0]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'DEUDOR'
                ])
                </td>
                <td width="50%">
                @php ($lender = $lenders[1]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
            </tr>
        </table>
        @endif
        @if($loan->disbursable->id == $lenders[1]->disbursable->id)
        <table>
            <tr>
                <td width="50%">
                @php ($lender = $lenders[1]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'DEUDOR'
                ])
                </td>
                <td width="50%">
                @php ($lender = $lenders[0]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
            </tr>
        </table>
        @endif
        @endif
    </div>


    <div class = "m-t-100">
        @if (count($loan->cosigners) == 1)
        <table>
            <tr>
                <td>
                @php ($lender = $loan->cosigners[0])
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
            </tr>
        </table>
        @endif
        @if (count($loan->cosigners) == 2)
        <table>
            <tr>
                <td width="50%">
                @php ($lender = $loan->cosigners[0])
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
                <td width="50%">
                @php ($lender = $loan->cosigners[1])
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
            </tr>
        </table>
        @endif
    </div>
    <div class="m-t-75">
        <table>
            <tr>
                @foreach ($employees as $key => $employee)
                <td width="50%">
                    @include('partials.signature_box', [
                        'full_name' => $employee['name'],
                        'identity_card' => $employee['identity_card'],
                        'position' => $employee['position'],
                        'employee' => true
                    ])
                @endforeach
                </td>
            </tr>
        </table>
    </div>
</div>
<?php }else{?>
<div class="block text-justify">
    <div>
        <b>SEÑOR NOTARIO DE FE PÚBLICA</b>
    </div>
    <div>
        En el Registro de Escrituras Públicas que corren a su cargo, sírvase insertar un <span style="text-transform: uppercase;">CONTRATO DE {{ $title }}</span>, de conformidad a las siguientes cláusulas:
    </div>
    <div>
        <b>PRIMERA.- (PARTES DEL CONTRATO):</b> Las siguientes partes celebran el presente contrato:
        <ol>
            <li><b>LA ENTIDAD:</b> La Mutual de Servicios al Policía "MUSERPOL", representada legalmente por su por el {{ $employees[0]['position'] }} Cnl. {{ $employees[0]['name'] }} con C.I. {{ $employees[0]['identity_card'] }} y su {{ $employees[1]['position'] }} Lic. {{ $employees[1]['name'] }} con C.I. {{ $employees[1]['identity_card'] }}, que para fines de este contrato en adelante se denominará MUSERPOL con domicilio en la Z. Sopocachi, Av. 6 de agosto N° 2354.</li>
            @if (count($lenders) == 1)
            @php ($lender = $lenders[0]->disbursable)
            @php ($male_female = Util::male_female($lender->gender))
            <li><span>
                <b> {{ $lender->full_name }}, </b> con C.I. {{ $lender->identity_card_ext }}, {{ $lender->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender->city_birth->name }}, vecin{{ $male_female }} de {{ $lender->address->cityName() }} y con domicilio especial en {{ $lender->address->full_address }}, en adelante denominad{{ $male_female }} DEUDOR{{ $lender->gender == 'M' ? '' : 'A' }} .
            </span>
            </li>
            @endif
            @if (count($lenders) == 2)
            @php ($lender_one = $lenders[0]->disbursable)
            @php ($male_female_one = Util::male_female($lender_one->gender))
            @php ($lender_two = $lenders[1]->disbursable)
            @php ($male_female_two = Util::male_female($lender_two->gender))
                @if($loan->disbursable->id == $lenders[0]->disbursable->id)
                <li>
                    <span>
                        <b>{{ $lender_one->full_name }}, </b> con C.I. {{ $lender_one->identity_card_ext }}, {{ $lender_one->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender_one->city_birth->name }}, vecin{{ $male_female_one }} de {{ $lender_one->address->cityName() }} y con domicilio especial en {{ $lender_one->address->full_address }}, en adelante denominad{{ $male_female_one }} DEUDOR{{ $lender_one->gender == 'M' ? '' : 'A' }}.
                    </span>
                </li>
                <li>
                <span>
                    <b>{{ $lender_two->full_name }}, </b> con C.I. {{ $lender_one->identity_card_ext }}, {{ $lender_one->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender_two->city_birth->name }}, vecin{{ $male_female_two }} de {{ $lender_two->address->cityName() }} y con domicilio especial en {{ $lender_two->address->full_address }}, en adelante denominad{{ $male_female_two }} CODEUDOR{{ $lender_two->gender == 'M' ? '' : 'A' }}.
                </span>
                </li>
                @endif
                @if($loan->disbursable->id == $lenders[1]->disbursable->id)
                <li>
                    <span>
                        <b>{{ $lender_two->full_name }}, </b> con C.I. {{ $lender_two->identity_card_ext }}, {{ $lender_two->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender_two->city_birth->name }}, vecin{{ $male_female_two }} de {{ $lender_two->address->cityName() }} y con domicilio especial en {{ $lender_two->address->full_address }}, en adelante denominad{{ $male_female_two }} DEUDOR{{ $lender_two->gender == 'M' ? '' : 'A' }}.
                    </span>
                </li>
                <li>
                    <span>
                        <b>{{ $lender_one->full_name }}, </b> con C.I. {{ $lender_one->identity_card_ext }}, {{ $lender_one->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender_one->city_birth->name }}, vecin{{ $male_female_one }} de {{ $lender_one->address->cityName() }} y con domicilio especial en {{ $lender_one->address->full_address }}, en adelante denominad{{ $male_female_one }} CODEUDOR{{ $lender_one->gender == 'M' ? '' : 'A' }}.
                    </span>
                </li>
                @endif
            @endif
            @if(count($loan->cosigners)>0)
            @foreach ($loan->cosigners as $cosigner)
            @php ($male_female_cosigner = Util::male_female($cosigner->gender))
            <li>
                <span>
                    <b>{{ $cosigner->full_name }}</b> con C.I. {{ $cosigner->identity_card_ext}}, {{ $cosigner->civil_status_gender }},  mayor de edad, hábil por derecho, natural de XXXX vecin{{ $male_female_cosigner }} de {{ $cosigner->address }} y con domicilio especial en {{ $cosigner->address }}, en adelante denominad{{ $male_female_cosigner }} CODEUDOR{{ $cosigner->gender == 'M' ? '' : 'A' }}.
                </span>
            </li>
            @endforeach
            @endif
        </ol>
    </div>
    <div>
        @php ($plural = count($lenders) > 1)
        {{ $plural ? ' Los domicilios señalados' : 'El domicilio señalado' }} por {{ $plural ? 'los DEUDORES' : 'el DEUDOR'}} {{ $plural ? 'se constituyen en domicilios especiales' : 'se constituye en domicilio especial' }} conforme al Art. 29º parágrafo II del Código Civil, donde se practicarán válida y legalmente todas las citaciones y/o notificaciones judiciales personales y/o por cédula, sin lugar a posterior observación, incidente o recurso alguno.
    </div>
    <div>
        <b>SEGUNDA.- (OBJETO DEL CONTRATO):</b> En virtud al presente contrato, LA MUSERPOL concede a favor {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}} un préstamo de dinero por la suma de Bs. {{ $loan->amount_approved }} (<span class="uppercase">{{ Util::money_format($loan->amount_approved, true) }}</span> Bolivianos), y que {{ $plural ? 'los DEUDORES declaran' : 'el DEUDOR declara'}} recibir a su entera satisfacción, y se {{ $plural ? 'obligan' : 'obliga'}} a restituir conforme a las condiciones y términos del presente contrato.
    </div>
    <div>
        <b>TERCERA.- (DESTINO Y USO):</b> LA ENTIDAD concede el préstamo pactado con destino exclusivo a {{ $loan->destiny->name }}. Su uso total o parcial en una finalidad diferente se reputará como incumplimiento y la obligación como vencida, líquida y exigible, obligando al/a los DEUDOR/ES a restituir el capital e intereses en forma inmediata y determinará la mora del/de los DEUDOR/ES. Para esta eventualidad y sin perjuicio del cobro coactivo, LA MUSERPOL se reserva el derecho de iniciar las acciones legales pertinentes.
    </div>
    <div>
        <b>CUARTA.- (DESEMBOLSO):</b> El préstamo concedido quedará perfeccionado mediante un solo desembolso que quedará acreditado mediante comprobante escrito en el que conste el abono efectuado a favor del PRESTATARIO, o a través de cuenta bancaria del Banco Unión a solicitud del prestatario; reconociendo ambas partes que al amparo de este procedimiento se cumple satisfactoriamente la exigencia contenida en el artículo 1331 del Código de Comercio.
    </div>
    <div>
        Dicho desembolso será realizado en Bolivianos, previo cumplimiento por {{ $plural ? 'los DEUDORES' : 'el DEUDOR'}} de todos los requisitos y condiciones establecidos al efecto.
    </div>
    <div>
        <b>QUINTA.- (PLAZO Y AMORTIZACIÓN):</b> {{ $plural ? 'Los DEUDORES se comprometen' : 'El DEUDOR se compromete'}} a que el presente préstamo será cancelado en un plazo de <b><span class="uppercase">{{ Util::number_integer($loan->loan_term,true) }}</span> ({{ ($loan->loan_term) }}) meses</b> computables a partir de la fecha efectiva de desembolso, mediante <b><span class="uppercase">{{ Util::number_integer($loan->loan_term, true) }}</span> ({{ $loan->plan? $loan->loan_term:'no desembolsado' }})amortizaciones </b> mensuales consecutivas e improrrogables y será pagado en capital e intereses convenidos al vencimiento de dichas amortizaciones sin necesidad de aviso ni formalidad previa alguna, conforme al PLAN DE PAGO.
    </div>
    <div>
        La cuota de amortización mensual es de Bs. {{ $loan->estimated_quota }} (<span class="uppercase">{{ Util::money_format($loan->estimated_quota, true) }}</span> Bolivianos).
    </div>
    <div>
        Los intereses generados entre la fecha del desembolso del préstamo y la fecha de primer pago, serán cobrados con la primera cuota; conforme lo establece el Reglamento de Préstamos.
    </div>
    <div>
        <b>SEXTA.- (INTERESES Y GASTOS):</b> El préstamo concedido en virtud del presente contrato, estará sujeto, al pago periódico mensual, de una tasa de interés del {{ round($loan->interest->annual_interest) }}% fijo anual sobre saldo deudor.
    </div>
    <div>
        Todos los gastos relativos a la concesión del presente préstamo serán a cargo y cuenta exclusiva {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}.
    </div>
    <div>
        <b>SÉPTIMA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el DEUDOR, autoriza expresamente a MUSERPOL practicar los descuentos respectivos de los haberes que percibe en forma mensual a través del Comando General de la Policía Boliviana.
    </div>
    <div>
        Si por cualquier motivo la MUSERPOL estuviera imposibilitada de realizar el descuento por el medio señalado, el PRESTATARIO se obliga a cumplir con la cuota de amortización mediante pago directo en la Oficina Central de MUSERPOL de la ciudad de La Paz o efectuar depósito bancario en la cuenta fiscal de la MUSERPOL y enviar la boleta de depósito original a la Oficina Central inmediatamente; sin necesidad de previo aviso; caso contrario el PRESTATARIO se hará pasible al recargo correspondiente a los intereses que se generen al día de pago por la deuda contraída.
    </div>
    <div>
        Asi mismo el PRESTATARIO se compromete a hacer conocer oportunamente a MUSERPOL sobre la omisión del descuento mensual que se hubiera dado a efectos de solicitar al Comando General de la Policía Boliviana se regularice este descuento, sin perjuicio que realice el depósito directo del mes omitido, de acuerdo a lo estipulado en el párrafo precedente.
    </div>
    <div>
        <b>OCTAVA.- (GARANTÍAS):</b> {{ $plural ? 'los DEUDORES garantizan el cumplimiento de sus obligaciones' : 'el DEUDOR garantiza el cumplimiento de su obligación'}}  con la generalidad de sus bienes, acciones y derechos  habidos y por haber, y de un modo especial con:
    </div>
    <div>
        <ol>
            <li>
                <b>Garantía Hipotecaria</b>
            </li>
            <div>{{ $plural ? ' Los DEUDORES' : 'El deudor' }}, asimismo LA MUSERPOL reconocen los derechos de la Matrícula descripción del inmueble <b>(Folio Real)</b>, como producto de un crédito el cual será cancelado con el presente préstamo para lo cual {{ $plural ? 'los deudores' : 'el deudor' }} también propietarios y LA MUSERPOL dan su consentimiento para la constitución de la presente hipoteca de inmueble en el grado de prelación que corresponda. </div>
            <div>La hipoteca en el grado de prelación que corresponda debe efectuarse sobre el bien inmueble consistente en un Lote de Terreno asignado como el {{$loan->loan_property->land_lot_number}}, Unidad Vecinal {{$loan->loan_property->neighborhood_unit}} de la {{$loan->loan_property->location}}, con una superficie de {{$loan->loan_property->surface}} {{$loan->loan_property->measurement}}, con Código Catastral {{$loan->loan_property->cadastral_code}}, cuyas colindancias son las siguientes: {{$loan->loan_property->limit}}. El bien inmueble es de propiedad de {{ $plural ? ' los DEUDORES' : 'el deudor' }} 
            @foreach ($lenders as $lender)
            {{ $lender->disbursable->full_name }},
            @endforeach
            de generales descritas en la cláusula primera del presente documento, conforme se evidencia en la Escritura Pública {{$loan->loan_property->public_deed_number}} de {{$loan->loan_property->public_deed_date}}. Extendida por ante Notaría de Fe Pública de Fe Pública a cargo de {{$loan->loan_property->lawyer}} dicho inmueble se encuentra registrado bajo la Matricula Computarizada Nº {{$loan->loan_property->registration_number}}. Bajo el asiento {{$loan->loan_property->real_folio_number}}
            del Folio Real, incluidas todas las construcciones, mejoras incorporadas, presentes y futuras, acciones y derechos correspondientes.</div>
            <div>Asimismo, {{ $plural ? 'los propietarios' : 'el propietario'}} de la garantía autorizan expresamente al registro del presente documento en oficinas de Derechos Reales de la ciudad {{$loan->loan_property->public_deed_date}}</div>
        <ol>
    </div>
    <div>
        <b>NOVENA.- (CADUCIDAD Y DERECHOS DE ACELERACIÓN)</b>De acuerdo a lo estipulado en el Art. 569 del Código Civil y 805 del Código de Comercio, antes del vencimiento del plazo pactado, LA MUSERPOL podrá resolver el presente contrato en forma anticipada declarando el monto del préstamo, sus intereses y accesorios pendientes de pago, de plazo vencido, sumas líquidas y exigibles y en mora y proceder a la cobranza judicial sin necesidad de ninguna intimación o requerimiento judicial o extrajudicial si {{ $plural ? ' los DEUDORES incurrieran' : 'El DEUDOR incurriera' }} en cualquiera de las siguientes causales:
    </div>
    <div>
        Incumplimiento de cualquiera de las obligaciones estipuladas en el presente contrato.
    </div>
    <div>
        <ol>
            <li>Falta de pago oportuno de cualquier cuota o amortización del préstamo ya sea pago a capital, intereses, impuestos o accesorios aplicables de acuerdo al Plan de Pagos. </li>
            <li>Uso del préstamo en una finalidad distinta de la pactada. </li>
            <li>Incumplimiento o falta de pago oportuno de otros préstamos y/u obligaciones que {{ $plural ? 'los DEUDORES tengan' : 'el DEUDOR tenga'}} en LA MUSERPOL.</li>
            <li>Disminución de las garantías otorgadas o del patrimonio {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}. </li>
            <li>La suspensión o cierre de las actividades {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}.</li>
            <li>Declaratoria de quiebra voluntaria o judicial, concurso de acreedores o cesión de bienes, promovida por {{ $plural ? 'los DEUDORES' : 'el DEUDOR'}} o por terceros y que involucre {{ $plural ? 'a los DEUDORES' : 'al DEUDOR'}}.</li>
            <li>Disminución de los bienes {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}} o si fueran objeto de embargo, secuestro, incautación o demanda por cualquier ejecución y/o juicio.</li>
            <li>Fuga o persecución judicial {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}</li>
            <li>Presentación de información falsa, inexacta o incorrecta por parte {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}}</li>
            <li>Falta de aviso oportuno {{ $plural ? 'de los DEUDORES' : 'el DEUDOR'}} a LA ENTIDAD de cualquier cambio en las condiciones establecidas en el presente contrato en cuanto se refiere a la conservación y ubicación de los bienes otorgados en garantía. De incurrir en cualquiera de las causales anteriormente señaladas y con la sola declaración de caducidad y mora que internamente realice LA MUSERPOL en sus registros, se dará la resolución anticipada del presente contrato.</li>

        </ol>
    </div>
    <div>
        Desde ese momento, la totalidad de la suma adeudada por capital, intereses ordinarios y penales, impuestos, gastos, costas, honorarios y otros se reputarán de plazo vencido, líquido y exigible por lo que LA MUSERPOL podrá perseguir el pago del monto adeudado mediante proceso coactivo u otro que viere conveniente. {{ $plural ? 'los DEUDORES aceptan' : 'el DEUDOR acepta'}} desde ahora como buenas y exactas las cuentas que formule LA MUSERPOL respecto de este préstamo de dinero como líquido y exigible el saldo que se reclame sin necesidad de notificación previa o posterior.
    </div>
    <div>
        <b>DÉCIMA.- (MORA)</b> Se deja expresamente establecido que a la falta de pago total o parcial a capital, intereses en el plazo estipulado o la simple demora,{{ $plural ? 'los DEUDORES' : 'el DEUDOR'}} se constituirán en mora por la totalidad de la obligación, la cual se considerará como vencida, líquida y exigible, sin necesidad de intimación, requerimiento, solicitud u otra formalidad previa judicial o extrajudicial, de acuerdo a lo  establecido por el artículo 341, Núm. 1) del Código Civil, al incumplimiento del pago de cualquier amortización, sin necesidad de intimación o requerimiento alguno, o acto equivalente por parte de la MUSERPOL.
    </div>
    <div>
        En caso de que el PRESTATARIO incurra en mora, se le añadirá al total de la obligación adeudada, un interés moratorio anual del {{ round($loan->interest->penal_interest) }}%, que será efectiva por todo el tiempo que perdure el incumplimiento objeto de la mora, debiendo ser cobrados de acuerdo a la disponibilidad de saldo deudor y/o requerimiento de la MUSERPOL.
    </div>
    <div>
        <b>DÉCIMA PRIMERA.- (OBLIGACIONES ESPECIALES)</b> {{ $plural ? 'Los DEUDORES quedan reatados' : 'El DEUDOR queda reatado'}}  al cumplimiento de las siguientes obligaciones especiales:
    </div>
    <div>
        <ol>
            <li>Realizar con carácter previo al desembolso, la protocolización notarial del presente documento.</li>
            <li>Ejecutar la inscripción del gravamen hipotecario constituido en el respectivo registro público.</li>
            <li>Suministrar a LA MUSERPOL cualquier información y/o documento legal y/o contable que este requiera durante la vigencia del préstamo.</li>
            <li>Informar a LA MUSERPOL en forma oportuna y detallada sobre cualquier cambio de domicilio, cambio de actividad, deterioro o pérdida de los bienes otorgados en garantía, viajes prolongados, fallecimientos o cualquier otro hecho o acontecimiento que de alguna u otra manera pudiera afectar el cumplimiento de la obligación de pago.</li>
        </ol>
    </div>

    <div>
        El incumplimiento por parte {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}} de cualquiera de las obligaciones señaladas, dará lugar a que el préstamo se considere de plazo vencido, líquido y exigible, pudiendo LA MUSERPOL iniciar las acciones judiciales correspondientes a efecto de perseguir el pago del monto adeudado.
    </div>
    <div>
        <b>DÉCIMA SEGUNDA.- (FUERZA COACTIVA)</b> Se deja expresamente establecido que el prestatario, codeudores o fiadores, depositarios, propietarios, garantes hipotecarios y/o prendarios y otros obligados, que a los efectos de una acción judicial que inicie <b>LA MUSERPOL</b> por el incumplimiento al presente contrato, renuncian en forma irrevocable y expresa a los trámites del proceso ejecutivo, reconociendo al presente contrato como título coactivo civil incuestionable, a la que expresamente se someten aplicándose en ejecución lo previsto por la Ley N° 439 deL Código Procesal Civil en sus artículos 404 y siguientes, según corresponda.
    </div>
    <div>
        Sin perjuicio de la renuncia efectuada, LA MUSERPOL se reserva el derecho de efectuar la ejecución judicial para el cobro de lo adeudado, por la vía coactiva o ejecutiva, de acuerdo a su simple elección, a lo cual el prestatario y demás obligados simplemente se adhieren.
    </div>
    <div>
        <b>DÉCIMA TERCERA.- (CESIÓN DE CRÉDITO)</b> {{ $plural ? 'Los DEUDORES autorizan' : 'El DEUDOR autoriza'}} en forma expresa a LA MUSERPOL a ceder, transmitir, pignorar, o delegar el presente contrato, sus accesorios, garantías, intereses, flujos demás activos o cualquier derecho emergente del presente contrato de préstamo a favor de cualquier entidad financiera, sociedad de titularización y/o de terceros que LA MUSERPOL juzgue conveniente, constituyendo la presente cláusula notificación suficiente para el efecto señalado, tal como establece el Art. 818º del  Código de Comercio. En esta eventualidad la gestión efectuada surtirá plenos efectos jurídicos respecto {{ $plural ? 'de los DEUDORES' : 'del DEUDOR'}} sin necesidad de requisitos formales y con sujeción a disposiciones legales vigentes.
    </div>
    <div>
        Se encuentran comprendidos dentro de dicha autorización la facultad de ceder y/o transmitir total o parcialmente los derechos derivados de las pólizas de seguro que amparen los bienes objeto de garantía.
    </div>
    <div>
        {{ $plural ? 'Los DEUDORES dan' : 'El DEUDOR da'}} su pleno consentimiento para que se realicen los registros e inscripciones sobre los derechos del presente contrato o de los bienes objeto de las garantías constituidas en el mismo o sobre otros aspectos relacionados con este contrato que sean necesarios como efecto de la celebración, eficacia, perfeccionamiento y/o ejecución de la cesión o transmisión conforme a la presente cláusula.
    </div>
    <div>
        <b>DÉCIMA CUARTA.- (MODIFICACIÓN DE LA SITUACIÓN DEL PRESTATARIO)</b> En caso de fallecimiento, retiro voluntario, retiro forzoso, LOS DEUDORES garantizan con la totalidad de sus Beneficios Fondo de Retiro Policial Solidario  y Complemento Económico que otorga la MUSERPOL, el cumplimiento efectivo de la presente obligación; por cuanto la liquidación de dichos beneficios pasarán a cubrir el monto total de la obligación que resulte adeudada, más los intereses devengados a la fecha, previa las formalidades de ley.
    </div>
    <div>
        En caso de que se haya modificado la situación del DEUDOR, del sector activo al sector pasivo de la Policía Boliviana, teniendo un saldo deudor respecto del préstamo obtenido, este acepta amortizar la deuda con su Complemento Económico, en caso de corresponderle, debiendo al efecto solicitar la reprogramación conforme establece  el  Artículo  90 del Reglamento de Préstamos de la MUSERPOL, por lo que el saldo se sujetará en función a su nuevo liquido pagable, estableciéndose una correlativa modificación de los plazos y cuota según sea el caso.
    </div>
    <div>
        La MUSERPOL, para el caso en que el DEUDOR o sus beneficiarios no inicien el trámite de solicitud de pago de la prestación que corresponda, se encuentra facultada a iniciar por su cuenta el trámite pertinente, cuyos costos serán imputados al total adeudado y descontados del total de la prestación.
    </div>
    <div>
        <b>DÉCIMA QUINTA.- (INVESTIGACIÓN Y SUPERVISIÓN)</b> {{ $plural ? 'Autorizan' : 'Autoriza'}} a LA MUSERPOL y/o a otros a quien este faculte a realizar inspecciones para verificar el destino del préstamo otorgado, la situación de las garantías, así como su situación económica y se compromete/n a suministrar toda la información que LA MUSERPOL requiera.
    </div>
    <div>
        Asimismo {{ $plural ? 'autorizan' : 'autoriza'}} a LA MUSERPOL y/o a otros a quien este faculte a realizar inspecciones para verificar el destino y uso del préstamo otorgado, la situación de las garantías, así como su situación económica y se compromete/n a suministrar toda la información que se requiera.
    </div>
    <div>
        <b>DÉCIMA SEXTA.- (INFORMACIÓN)</b> {{ $plural ? 'Los DEUDORES podrán' : 'El DEUDOR podrá'}} solicitar a LA MUSERPOL en cualquier momento información detallada referida a los pagos y cobros efectuados, el cronograma de pagos, la forma de cálculo y así como la liquidación total o parcial del préstamo.
    </div>
    <div>
        <b>DÉCIMA SÉPTIMA.- (CONFORMIDAD)</b> Nosotros, LA MUSERPOL, como ACREEDORES representado por su {{ $employees[0]['position'] }} Cnl. {{ $employees[0]['name'] }} y su {{ $employees[1]['position'] }} Lic. {{ $employees[1]['name'] }} por una parte y
        @if (count($lenders) == 1)
            @php ($lender = $lenders[0]->disbursable)
            @php ($male_female = Util::male_female($lender->gender))
        <span><b> {{ $lender->full_name }}, </b> con C.I. {{ $lender->identity_card_ext }},</span>
        @endif
        @if (count($lenders)>1)
        @php ($lender = $lenders[0]->disbursable)
        @php ($male_female = Util::male_female($lender->gender))
        <span><b> {{ $lender->full_name }}, </b> con C.I. {{ $lender->identity_card_ext }},</span>
        @php ($lender = $lenders[1]->disbursable)
        @php ($male_female = Util::male_female($lender->gender))
        <span><b> {{ $lender->full_name }}, </b> con C.I. {{ $lender->identity_card_ext }},</span>
        @endif
        EN CALIDAD DE {{ $plural ? 'DEUDORES Y PROPIETARIOS' : 'DEUDOR Y PROPIETARIO'}}, declaramos nuestra plena conformidad con cláusulas precedentes y nos comprometemos en la fecha al fiel y estricto cumplimiento del presente documento.
    </div>


    <div>
        Usted Señor Notario, se servirá agregar las demás cláusulas de seguridad y estilo.
    </div><br><br>
    <div class="text-center">
        <p class="center">
        La Paz, {{ Carbon::parse($loan->request_date)->isoFormat('LL') }}
        </p>
    </div>
    <div></div>
</div>
<div class = "m-t-100">
    <div>
        @if (count($lenders) == 1)
        <table>
            <tr>
                <td>
                @php ($lender = $lenders[0]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'PRESTATARIO'
                ])
                </td>
            </tr>
        </table>
        @endif
        @if (count($lenders) == 2)
        @if($loan->disbursable->id == $lenders[0]->disbursable->id)
        <table>
            <tr>
                <td width="50%">
                @php ($lender = $lenders[0]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'DEUDOR'
                ])
                </td>
                <td width="50%">
                @php ($lender = $lenders[1]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
            </tr>
        </table>
        @endif
        @if($loan->disbursable->id == $lenders[1]->disbursable->id)
        <table>
            <tr>
                <td width="50%">
                @php ($lender = $lenders[1]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'DEUDOR'
                ])
                </td>
                <td width="50%">
                @php ($lender = $lenders[0]->disbursable)
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
            </tr>
        </table>
        @endif
        @endif
    </div>


    <div class = "m-t-100">
        @if (count($loan->cosigners) == 1)
        <table>
            <tr>
                <td>
                @php ($lender = $loan->cosigners[0])
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
            </tr>
        </table>
        @endif
        @if (count($loan->cosigners) == 2)
        <table>
            <tr>
                <td width="50%">
                @php ($lender = $loan->cosigners[0])
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
                <td width="50%">
                @php ($lender = $loan->cosigners[1])
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'CODEUDOR'
                ])
                </td>
            </tr>
        </table>
        @endif
    </div>
    <div class="m-t-75">
        <table>
            <tr>
                @foreach ($employees as $key => $employee)
                <td width="50%">
                    @include('partials.signature_box', [
                        'full_name' => $employee['name'],
                        'identity_card' => $employee['identity_card'],
                        'position' => $employee['position'],
                        'employee' => true
                    ])
                @endforeach
                </td>
            </tr>
        </table>
    </div>
</div>
<?php }?>
</body>
</html>
