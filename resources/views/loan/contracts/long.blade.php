<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{$file_title}}</title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>
@include('partials.header', $header)
<body>
<div class="block">
    <div class="font-semibold leading-tight text-center m-b-10 text-base">
        {{$is_refinancing?'CONTRATO DE':'CONTRATO DE PRÉSTAMO'}} <font style="text-transform: uppercase;">{{ $title }}</font>
        <div>Nº {{ $loan->code }}</div>
    </div>
</div>
<?php $modality = $loan->modality;
if(($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Activo' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo AFP'|| $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo SENASIR'|| $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo con un Solo Garante Sector Activo' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo Gestora Pública')){ ?>
<div class="block text-justify">
    <div>
        Conste en el presente contrato de {{ $title }}, que al solo reconocimiento de firmas y rúbricas será elevado a Instrumento Público, por lo que las partes que intervienen lo suscriben al tenor y contenido de las siguientes cláusulas y condiciones:
    </div>
    <div>
        <b>PRIMERA.- (DE LAS PARTES):</b> Intervienen en el presente contrato, por una parte la Mutual de Servicios al Policía (MUSERPOL), representada legalmente por su {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} con C.I. {{ $employees[0]['identity_card'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} con C.I. {{ $employees[1]['identity_card'] }}, que para fines de este contrato en adelante se denominará MUSERPOL o ACREEDOR con domicilio en la Z. Sopocachi, Av. 6 de Agosto Nº 2354 y por otra parte

        @if (count($lenders) == 1)
        @php ($lender = $lenders[0])
        @php ($male_female = Util::male_female($lender->gender))
        <span>
            {{ $lender->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $lender->full_name }}, con C.I. {{ $lender->identity_card_ext }}, {{ $lender->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender->city_birth->name }}, vecin{{ $male_female }} de {{ $lender->address->cityName() }} y con domicilio en {{ $lender->address->full_address }}, en adelante denominad{{ $male_female }} PRESTATARIO.
        </span>
        @endif
    </div>
    <div>
        <?php
        $modality = $loan->modality;
        if(($loan->data_loan)){ ?>
            <b>SEGUNDA.- (DEL ANTECEDENTE):</b>Mediante contrato de préstamo {{ $loan->data_loan->code }} SISMU con fecha de desembolso {{$loan->data_loan->disbursement_date? Carbon::parse($loan->data_loan->disbursement_date)->isoFormat('LL'):'_________________'}}, se ha suscrito entre la MUSERPOL y el PRESTATARIO un préstamo por la suma de Bs.{{ Util::money_format($loan->data_loan->amount_approved) }} (<span class="uppercase">{{ Util::money_format($loan->data_loan->amount_approved, true) }} Bolivianos</span>), con garantía de rentas o haberes y garantía personal segun corresponda.
            <div>
            <b>TERCERA.- (DEL OBJETO):</b>  El objeto del presente contrato es el refinanciamiento del préstamo de dinero que la MUSERPOL otorga al PRESTATARIO conforme a calificación, previa evaluación y autorización, de conformidad a los niveles de aprobación respectivos en la suma de Bs.{{ Util::money_format($loan->refinancing_balance) }} (<span class="uppercase">{{ Util::money_format($loan->refinancing_balance, true) }} Bolivianos</span>), para lo cual el PRESTATARIO reconoce de manera expresa el saldo anterior de la deuda correspondiente al préstamo contraído con anterioridad, que asciende a la suma de Bs.{{ Util::money_format($loan->balance_parent_refi())}} <span class="uppercase">({{ Util::money_format($loan->balance_parent_refi(), true) }} Bolivianos)</span>, montos que hacen un total efectivo de Bs.{{ Util::money_format($loan->amount_approved) }} <span class="uppercase">({{ Util::money_format($loan->amount_approved, true) }} Bolivianos)</span>, que representa la nueva obligación contraída sujeta a cumplimiento, en función a la operación de refinanciamiento.
            </div>
            <?php }
        else{ ?>
            <div>
            <b>SEGUNDA.- (DEL ANTECEDENTE):</b>Mediante contrato de préstamo {{ $parent_loan->code }} con fecha de desembolso {{ Carbon::parse($parent_loan->disbursement_date)->isoFormat('LL') }} y modalidad de  {{strtolower($parent_loan->modality->name)}}, se ha suscrito entre la MUSERPOL y el PRESTATARIO un préstamo por la suma de Bs.{{ Util::money_format($parent_loan->amount_approved) }} <span class="uppercase">({{ Util::money_format($parent_loan->amount_approved, true) }} Bolivianos)</span>, con garantía de rentas o haberes y garantía personal segun corresponda.
            </div>
            <div>
            <b>TERCERA.- (DEL OBJETO):</b>  El objeto del presente contrato es el refinanciamiento del préstamo de dinero que la MUSERPOL otorga al PRESTATARIO conforme a calificación, previa evaluación y autorización, de conformidad a los niveles de aprobación respectivos en la suma de Bs.{{ Util::money_format($loan->refinancing_balance) }} (<span class="uppercase">{{ Util::money_format($loan->refinancing_balance, true) }} Bolivianos</span>), para lo cual el PRESTATARIO reconoce de manera expresa el saldo anterior de la deuda correspondiente al préstamo contraído con anterioridad, que asciende a la suma de Bs.{{ $loan->balance_parent_refi()}} (<span class="uppercase">{{ Util::money_format($loan->balance_parent_refi(), true) }} Bolivianos</span>), montos que hacen un total efectivo de Bs.{{ Util::money_format($loan->amount_approved) }} <span class="uppercase">({{ Util::money_format($loan->amount_approved, true) }} Bolivianos)</span>, que representa la nueva obligación contraída sujeta a cumplimiento, en función a la operación de refinanciamiento.
            </div>
        <?php } ?>
    </div>
    <div>
        <b>CUARTA.- (DEL INTERÉS):</b> El préstamo objeto del presente contrato, devengará un interés ordinario del {{ $loan->interest->annual_interest }}%, anual sobre saldo deudor, el mismo que se recargará con el interés penal, en caso de mora de una o más amortizaciones.
    </div>
    <div>
        <b>QUINTA.- (DEL PLAZO Y LA CUOTA DE AMORTIZACIÓN):</b>El plazo fijo e improrrogable para el cumplimiento de la obligación contraída por el PRESTATARIO en virtud al préstamo otorgado es de {{ $loan->loan_term }} meses computables a partir de la fecha de desembolso. La cuota de amortización mensual es de Bs.{{ Util::money_format($loan->estimated_quota) }} (<span class="uppercase">{{ Util::money_format($loan->estimated_quota, true) }} Bolivianos</span>).
    </div>
    <div>
        Los intereses generados entre la fecha del desembolso del préstamo y la fecha del primer pago serán cobrados con la primera cuota; conforme establece el Reglamento de Préstamos.
    </div>
    <div>
        <b>SEXTA.- (DEL DESEMBOLSO):</b>El desembolso del préstamo de dinero en la moneda pactada se acredita mediante comprobante escrito en el que conste el abono efectuado a favor del PRESTATARIO, a través de una cuenta bancaria señalada por el mismo, reconociendo ambas partes que al amparo de este procedimiento se cumple satisfactoriamente la exigencia contenida en el artículo 1331 del Código de Comercio.
    </div>
    <div>
    <?php $modality = $loan->modality;
        if(($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo AFP' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo Gestora Pública')){ ?>
        <b>SEPTIMA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, se obliga a cumplir con la cuota de amortización en forma mensual mediante pago directo en la oficina central de la MUSERPOL de la ciudad de La Paz o efectuar el depósito en la cuenta fiscal de la MUSERPOL y enviar la boleta de depósito original a la oficina central inmediatamente; caso contrario el PRESTATARIO se hará pasible al recargo correspondiente a los intereses que se generen al día de pago por la deuda contraída y consecuentemente se procedera al descuento del garante personal incluidos los intereses penales pasados los 30 dias de incumplimiento sin necesidad de previo aviso.
        <?php }
        else{
            if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Activo'|| $modality->name =='Refinanciamiento de Préstamo a Largo Plazo con un Solo Garante Sector Activo'){
                $septima = 'Comando General de la Policía Boliviana';
            }
            if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo SENASIR'){
                $septima = 'Servicio Nacional del Sistema de Reparto SENASIR';
            }
            ?>
        <b>SEPTIMA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, autoriza expresamente a la MUSERPOL practicar los descuentos respectivos {{$is_active? 'de los haberes':'de las rentas'}} que percibe en forma mensual a través del {{ $septima }} conforme al Reglamento de Prestamos.
        <div>
        Si por cualquier motivo la MUSERPOL estuviera imposibilitada de realizar el descuento por el medio señalado, el PRESTATARIO se obliga a cumplir con la cuota de amortización mediante pago directo en la Oficina Central de la MUSERPOL de la ciudad de La Paz o efectuar el depósito en la cuenta fiscal de la MUSERPOL y enviar la boleta de depósito original a la Oficina Central inmediatamente. <br>
        Caso contrario el PRESTATARIO se hará pasible al recargo correspondiente a los intereses que se generen al día de pago por la deuda contraída.
        </div>
        <div>
            Asimismo, el PRESTATARIO se compromete a hacer conocer oportunamente a la MUSERPOL sobre la omisión del descuento mensual que se hubiera dado a efectos de solicitar al {{ $septima }} regularice este descuento sin perjuicio que realice el depósito directo del mes omitido de acuerdo a lo estipulado en el párrafo precedente. Caso contrario se procedera al descuento {{count($guarantors)>1 ? 'a los garantes personales':'del garante personal'}} incluido los intereses penales pasado los 30 dias de incumplimiento sin necesidad de previo aviso.
        </div>
        <?php }?>
    </div>
    <div>
        <b>OCTAVA.- (DERECHOS DEL PRESTATARIO):</b> Conforme al artículo 10 del Reglamento de Préstamos las partes reconocen expresamente como derechos del PRESTATARIO, lo siguiente:
    </div>
    <div>
        <ol type="a" style="margin:0;">
            <li>Recibir buena atención, trato equitativo y digno por parte de los funcionarios de la MUSERPOL sin discriminación de ninguna naturaleza, asimismo recibir información y orientación precisa, comprensible, oportuna y accesible con relación a requisitos, características y condiciones del préstamo con calidad y calidez.</li>
            <li>A la confidencialidad, información detallada y precisa concerniente a los préstamos bajo su titularidad en el marco estricto de la normativa legal vigente.</li>
            <li>A presentar queja formal por el servicio recibido si no se ajusta al presente reglamento.</li>
        </ol>
    </div>
    <div>
        <b>NOVENA.- (OBLIGACIONES DEL PRESTATARIO):</b> Conforme al artículo 11 del Reglamento de Préstamos, las partes reconocen expresamente como obligaciones del PRESTATARIO, lo siguiente:
        <ol type="a" style="margin:0;">
            <li>Proporcionar información y documentación veraz y legítima para la correcta tramitación del préstamo.</li>
            <li>Cumplir con los requisitos, condiciones y lineamientos del préstamo.</li>
            <li>Cumplir con el contrato de préstamo suscrito entre la MUSERPOL y el afiliado.</li>
            <li>Amortizar mensualmente la deuda contraída con la MUSERPOL, hasta cubrir el capital adeudado y los intereses correspondientes por la deuda contraída.</li>
        </ol>
    </div>
    <div>
     <?php $modality = $loan->modality;
        if(($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo SENASIR')){ ?>
        <b>DECIMA.- (DE LA GARANTÍA):</b>El PRESTATARIO y GARANTE, garantizan el pago de lo adeudado con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme lo determina el artículo 1335 del Código Civil y así como también con su renta de vejez en curso de pago, ademas este acepta amortizar la deuda con su Complemento Económico.
        <br> Asimismo se {{ count($guarantors) > 1 ? 'constituyen como garantes personales, solidarios, mancomunados, e indivisibles' : 'constituye como garante personal, solidario, mancomunado e indivisible' }}:
        <?php } else{
        if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo AFP' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo Gestora Pública'){ ?>
        <b>DECIMA.- (DE LA GARANTÍA):</b>El PRESTATARIO y {{ count($guarantors)>1 ? 'GARANTES':'GARANTE' }}, garantizan el pago de lo adeudado con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme lo determina el artículo 1335 del Código Civil, además el PRESTATARIO, con los beneficios otorgados por la MUSERPOL.
        <br> Asimismo se {{ count($guarantors) > 1 ? 'constituyen como garantes personales, solidarios, mancomunados, e indivisibles' : 'constituye como garante personal, solidario, mancomunado e indivisible' }}:
        <?php }
        if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Activo' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo con un Solo Garante Sector Activo'){ ?>
        <b>DECIMA.- (DE LA GARANTÍA):</b>El PRESTATARIO y {{ count($guarantors)>1 ? 'GARANTES':'GARANTE' }}, garantizan el pago de lo adeudado con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme lo determina el artículo 1335 del Código Civil, además el PRESTATARIO con los beneﬁcios otorgados por la MUSERPOL.
        <br> Asimismo se {{ count($guarantors) > 1 ? 'constituyen como garantes personales, solidarios, mancomunados, e indivisibles' : 'constituye como garante personal, solidario, mancomunado e indivisible' }}:
        <?php }?>
        <?php } ?>
        <?php $cont = 0; $concat_guarantor = "";
            foreach($guarantors as $guarantor){
                $male_female_guarantor = Util::male_female($guarantor->gender);
                $cont ++; $concat_guarantor = " (garante Nº ".$cont.")";

            ?>
            <span>
            {{ $guarantor->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $guarantor->full_name }}, con C.I. {{ $guarantor->identity_card_ext }}, {{ $guarantor->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $guarantor->city_birth->name }}, vecin{{ $male_female_guarantor }} de {{ $guarantor->address->cityName() }} y con domicilio en {{ $guarantor->address->full_address }}{{ count($guarantors) > 1 ? $concat_guarantor : '' }},
            </span>
        <?php } ?>
        <?php $modality = $loan->modality;
        if(($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo SENASIR')){ ?>
         {{ count($guarantors) > 1 ? 'quienes' : 'quien' }} en amparo del artículo 16 del Reglamento de Préstamos de la MUSERPOL {{ count($guarantors) > 1 ? 'garantizan' : 'garantiza' }} el cumplimiento de la obligación y en caso que el PRESTATARIO, incumpliera con el pago de sus obligaciones o se constituyera en mora al incumplimiento de una o más cuotas de amortización, {{ count($guarantors) > 1 ? 'autorizan' : 'autoriza' }} el descuento mensual de su renta en curso de pago y/o haberes en calidad de {{ count($guarantors) > 1 ? 'GARANTES' : 'GARANTE' }} bajo las mismas condiciones en las que se procedería a descontar al PRESTATARIO, hasta cubrir el pago total de la obligación pendiente de cumplimiento. Excluyendo a la MUSERPOL de toda responsabilidad o reclamo posterior, sin perjuicio de que {{ count($guarantors) > 1 ? 'estos puedan' : 'este pueda' }}  iniciar las acciones legales correspondientes en contra del PRESTATARIO.
        <?php } else{
        if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Activo' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo con un Solo Garante Sector Activo'){ ?>
         {{ count($guarantors) > 1 ? 'quienes' : 'quien' }} {{$is_active? 'en amparo del artículo 16 del Reglamento de Préstamos de la MUSERPOL':''}} {{ count($guarantors) > 1 ? 'garantizaran' : 'garantiza' }} el cumplimiento de la obligación y en caso que el PRESTATARIO, incumpliera con el pago de sus obligaciones o se constituyera en mora al incumplimiento de una o más cuotas de amortización, {{ count($guarantors) > 1 ? 'autorizan el descuento mensual de sus haberes' : 'autoriza el descuento mensual de sus haberes' }} en calidad de {{ count($guarantors) > 1 ? 'GARANTES' : 'GARANTE' }} bajo las mismas condiciones en las que se procedería a descontar al PRESTATARIO, hasta cubrir el pago total de la obligación pendiente de cumplimiento. Excluyendo a la MUSERPOL de toda responsabilidad o reclamo posterior, sin perjuicio de que {{ count($guarantors) > 1 ? 'estos puedan' : 'este pueda' }} iniciar las acciones legales correspondientes en contra del PRESTATARIO.
        <?php }
        if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo AFP' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo Gestora Pública'){ ?>
         {{ count($guarantors) > 1 ? 'quienes' : 'quien' }} {{$is_afp || $is_gestora? 'en amparo del artículo 16 del Reglamento de Préstamos de la MUSERPOL':''}} {{ count($guarantors) > 1 ? 'garantizaran' : 'garantiza' }} el cumplimiento de la obligación y en caso que el PRESTATARIO, incumpliera con el pago de sus obligaciones o se constituyera en mora al incumplimiento de una o más cuotas de amortización, {{ count($guarantors) > 1 ? 'autorizan el descuento mensual de sus haberes' : 'autoriza el descuento mensual de sus haberes' }} en calidad de {{ count($guarantors) > 1 ? 'GARANTES' : 'GARANTE' }}, hasta cubrir el pago total de la obligación pendiente de cumplimiento. Excluyendo a la MUSERPOL de toda responsabilidad o reclamo posterior, sin perjuicio de que {{ count($guarantors) > 1 ? 'estos puedan' : 'este pueda' }} iniciar las acciones legales correspondientes en contra del PRESTATARIO.
        <?php }?>
        <?php } ?>
    </div>
    <div>
    <?php
        if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo AFP' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo SENASIR' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo Gestora Pública'){ ?>
        <div>
            <b>DÉCIMA PRIMERA.- (CONTINGENCIAS POR FALLECIMIENTO):</b>El PRESTATARIO en caso de fallecimiento acepta amortizar para el cumplimiento efectivo de la presente obligación con el beneﬁcio del Complemento Económico{{$is_senasir ? ' en caso de corresponderle':''}}; por cuanto la liquidación de dicho beneﬁcio pasará a cubrir el monto total de la obligación que resulte adeudada, más los intereses devengados a la fecha, cobrados a los derechohabientes, previas las formalidades de ley.
        </div>
        <?php }
            else{
                if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Activo' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo con un Solo Garante Sector Activo'){ ?>
                 <div>
                    <b>DECIMA PRIMERA.- (MODIFICACIÓN DE LA SITUACIÓN DEL PRESTATARIO):</b> El PRESTATARIO en caso de fallecimiento, retiro voluntario o retiro forzoso garantiza el cumplimiento efectivo de la presente obligación con la totalidad del beneﬁcio de  Fondo de Retiro Policial Solidario  otorgado por la MUSERPOL; por cuanto la liquidación de dicho beneﬁcio pasará a cubrir el monto total de la obligación que resulte adeudada, más los intereses devengados a la fecha, previas las formalidades de ley.
                </div>
                <div>
                    En caso de que se haya modificado la situación del PRESTATARIO del sector activo al sector pasivo de la Policía Boliviana, teniendo un saldo deudor respecto del préstamo obtenido, acepta amortizar la deuda con su Complemento Económico, en caso de corresponderle.
                </div>
                <div>
                    Asimismo, en caso de que el monto de sus beneficios del PRESTATARIO, no alcanzare a cubrir el total del monto adeudado, se descontará a los GARANTES el saldo deudor que quedare pendiente.
                </div>
                <?php }?>
        <?php   } ?>
    </div>
    <div>
        <b>DÉCIMA SEGUNDA.- (DE LA MORA):</b> El PRESTATARIO se constituirá en mora automática sin intimación o requerimiento alguno, de acuerdo a lo establecido por el artículo 341, Núm. 1) del Código Civil, al incumplimiento del pago de cualquier amortización de capital o intereses, sin necesidad de intimación o requerimiento alguno, o acto equivalente por parte de la MUSERPOL.
    </div>
    <div>
        Además del interés acordado contractualmente, el préstamo generará en caso de mora un interés moratorio anual del 6% sobre saldos de capital de las cuotas impagas, aún cuando fuere exigible todo el capital del préstamo.
    </div>
    <div>
        <b>DÉCIMA TERCERA.- (DE LOS EFECTOS DEL INCUMPLIMIENTO Y DE LA ACCIÓN EJECUTIVA):</b> El incumplimiento de pago mensual por parte del PRESTATARIO, dará lugar a que la totalidad de la obligación, incluidos los intereses moratorios, se determinen líquidos, exigibles y de plazo vencido quedando la MUSERPOL facultada de iniciar las acciones legales correspondientes al amparo de los artículos 519 y 1465 del Código Civil así como de los artículos 378 y 379, Núm. 2) del Código Procesal Civil, que otorgan a este documento la calidad de Título Ejecutivo, demandando el pago de la totalidad de la obligación contraída, más la cancelación de intereses convencionales y penales, incluyendo los relacionados y emergentes de la cobranza judicial, honorarios, derechos, costas y otros, sin excepción por efecto de la acción legal que la MUSERPOL  instaure para lograr el cumplimiento total de la obligación.
    </div>
    <?php $modality = $loan->modality;
        if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Activo' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo con un Solo Garante Sector Activo'){ ?>
          <div>
          {{ count($guarantors) > 1 ? 'Los GARANTES están facultados' : 'El GARANTE esta facultado' }} a realizar el trámite de recuperación de los montos que se le hubieran sido descontados en función a la obligación objeto del presente contrato pudiendo recaer sobre él Beneficio de Fondo de Retiro Policial Solidario otorgado por la MUSERPOL y reconocidos al PRESTATARIO, conforme al artículo 64 del Reglamento de Préstamos.
        </div>
        <?php } ?>
    <div>
        En caso de incumplimiento de los pagos mensuales estipulados en el presente contrato que generen mora de la obligación, el PRESTATARIO no tendrá derecho a acceder a otro crédito, hasta la cancelación total de la deuda.
    </div>
    <div>
        <b>DÉCIMA CUARTA.- (DOMICILIO ESPECIAL):</b> Para efectos legales, incluida la acción judicial u otra, se tendrá como domicilio especial del PRESTATARIO y {{ count($guarantors)>1 ? 'GARANTES':'GARANTE'}} el señalado en la cláusula primera y decima de conformidad al artículo 29 parágrafo II del Código Civil, donde se efectuarán las citaciones y notificaciones judiciales o cualquier otra comunicación, con plena validez legal y sin lugar a posterior observación o recurso alguno.
    </div>
    <div>
        <b>DÉCIMA QUINTA.- (DE LA CONFORMIDAD Y ACEPTACIÓN):</b> Por una parte en calidad de ACREEDOR la Mutual de Servicios al Policía (MUSERPOL), representada por su {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} y por otra parte en calidad de
        @if (count($lenders) == 1)
        <span>PRESTATARIO{{ $lender->gender == 'M' ? '' : 'A' }} {{ $lender->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $lender->full_name }} de generales ya señaladas; </span>
        asimismo en calidad de {{ count($guarantors)>1 ? 'garantes personales':'garante personal'}}
        <?php $c=0; count($guarantors)>1 ? $sw=' y': $sw = ',';
        foreach($guarantors as $guarantor){ ?>
            <span>
            {{ $guarantor->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $guarantor->full_name }}{{ $c==0 ? $sw : ',' }}
            </span>
        <?php } ?>
            damos nuestra plena conformidad con todas y cada una de las cláusulas precedentes, obligándonos a su fiel y estricto cumplimiento. En señal de lo cual suscribimos el presente contrato de refinancimiento de préstamo de dinero con garantia personal en manifestación de nuestra libre y espontánea voluntad y sin que medie vicio de consentimiento alguno.
        </span>
        @endif
    </div><br><br>
    <div class="text-center">
        <p class="center">
        La Paz, {{ Carbon::now()->isoFormat('LL') }}
        </p>
    </div>
</div>
<div>
    <div>
        @if (count($guarantors) == 1)
        @php ($guarantor = $guarantors[0])
        <table>
            <tr>
                <td  with = "50%">
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'PRESTATARIO'
                ])
                </td>
                <td  with = "50%">
                @include('partials.signature_box', [
                    'full_name' => $guarantor->full_name,
                    'identity_card' => $guarantor->identity_card_ext,
                    'position' => 'GARANTE'
                ])
                </td>
            </tr>
        </table>
        @endif
    </div>
    @if (count($guarantors) == 2)
    <div>
        @include('partials.signature_box', [
            'full_name' => $lender->full_name,
            'identity_card' => $lender->identity_card_ext,
            'position' => 'PRESTATARIO'
        ])
    </div>
    <div class = "no-page-break">
        <div>
            <table>
                <tr>
                    @foreach ($guarantors as $guarantor)
                    <td with = "50%">
                        @include('partials.signature_box', [
                            'full_name' => $guarantor->full_name,
                            'identity_card' => $guarantor->identity_card_ext,
                            'position' => 'GARANTE'
                        ])
                    @endforeach
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif
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
<?php }else{
    ?>
<div class="block text-justify">
    <div>
        Conste en el presente contrato de préstamo de {{ ucfirst($title) }}, que al solo reconocimiento de firmas y rúbricas será elevado a Instrumento Público, por lo que las partes que intervienen lo suscriben al tenor y contenido de las siguientes cláusulas y condiciones:
    </div>
    <div>
        <b>PRIMERA.- (DE LAS PARTES):</b> Intervienen en el presente contrato, por una parte la Mutual de Servicios al Policía (MUSERPOL), representada legalmente por su {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} con C.I. {{ $employees[0]['identity_card'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} con C.I. {{ $employees[1]['identity_card'] }}, que para fines de este contrato en adelante se denominará MUSERPOL o ACREEDOR, con domicilio en la Z. Sopocachi, Av. 6 de Agosto Nº 2354 y por otra parte

        @if (count($lenders) == 1)
        @php ($lender = $lenders[0])
        @php ($male_female = Util::male_female($lender->gender))
        <span>
            {{ $lender->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $lender->full_name }}, con C.I. {{ $lender->identity_card_ext }}, {{ $lender->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender->city_birth->name }}, vecin{{ $male_female }} de {{ $lender->address->cityName() }} y con domicilio en {{ $lender->address->full_address }}, en adelante denominad{{ $male_female }} PRESTATARIO.
        </span>
        @endif
    </div>
    <div>
        <b>SEGUNDA.- (DEL OBJETO):</b>  El objeto del presente contrato es el préstamo de dinero que la Mutual de Servicios al Policía (MUSERPOL) otorga al PRESTATARIO conforme a niveles de aprobación respectivos, en la suma de Bs.{{ Util::money_format($loan->amount_approved) }} (<span class="uppercase">{{ Util::money_format($loan->amount_approved, true) }} Bolivianos</span>).
    </div>
    <div>
        <b>TERCERA.- (DEL INTERÉS):</b> El préstamo objeto del presente contrato, devengará un interés ordinario del {{ $loan->interest->annual_interest }}% anual sobre saldo deudor, el mismo que se recargará con el interés penal en caso de mora de una o más amortizaciones.
    </div>
    <div>
        <b>CUARTA.- (DEL PLAZO Y LA CUOTA DE AMORTIZACIÓN):</b> El plazo ﬁjo e improrrogable para el cumplimiento de la obligación contraída por el PRESTATARIO en virtud al préstamo otorgado es de {{ $loan->loan_term }} meses computables a partir de la fecha de desembolso. La cuota de amortización mensual es de Bs.{{ Util::money_format($loan->estimated_quota) }} (<span class="uppercase">{{ Util::money_format($loan->estimated_quota, true) }} Bolivianos).</span>
    </div>
    <div>
        Los intereses generados entre la fecha del desembolso del préstamo y la fecha del primer pago serán cobrados con la primera cuota; conforme establece el Reglamento de Préstamos.
    </div>
    <div>
        <b>QUINTA.- (DEL DESEMBOLSO):</b> El desembolso del préstamo de dinero en la moneda pactada se acredita mediante comprobante escrito en el que conste el abono efectuado a favor del  PRESTATARIO, a través de una cuenta bancaria señalada por el mismo, reconociendo ambas partes que al amparo de este procedimiento se cumple satisfactoriamente la exigencia contenida en el artículo 1331 del Código de Comercio.
    </div>
    <div>
    <?php $modality = $loan->modality;
        if(($modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo AFP' || $modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo Gestora Pública')){ ?>
        <b>SEXTA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, se obliga a cumplir con la cuota de amortización en forma mensual mediante pago directo en la oﬁcina central de la MUSERPOL de la ciudad de La Paz o efectuar el depósito en la cuenta fiscal de la MUSERPOL y enviar la boleta de depósito original a la oﬁcina central inmediatamente; caso contrario el PRESTATARIO se hará pasible al recargo correspondiente a los intereses que se generen al día de pago por la deuda contraída y consecuentemente se procederá al descuento al garante personal incluido los intereses penales pasados los 30 días de incumplimiento sin necesidad de previo aviso.
        <?php }
        else{
            if($modality->name == 'Largo Plazo con Garantía Personal en Comisión'){
            ?>
            <div>
                <b>SEXTA. - (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, se obliga a cumplir con la cuota de amortización en forma mensual mediante pago directo en la oﬁcina central de la MUSERPOL de la ciudad de La Paz o efectuar el depósito en la cuenta fiscal de la MUSERPOL y enviar la boleta de depósito original a la oﬁcina central inmediatamente; caso contrario el PRESTATARIO se hará pasible al recargo correspondiente a los intereses que se generen al día de pago por la deuda contraída y consecuentemente se procederá al descuento de los garantes personales incluido los intereses penales pasando los 30 días de incumplimiento sin necesidad de previo aviso.
            </div>
            <div>
                Una vez concluida la designación en Comision, para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, autoriza expresamente a la MUSERPOL practicar los descuentos respectivos de los haberes que percibe en forma mensual a través del Comando General de la Policía Boliviana. Asimismo, el PRESTATARIO se compromete hacer conocer oportunamente a la MUSERPOL sobre la omisión del descuento mensual que se hubiera dado a efectos de solicitar al Comando General de la Policía  Boliviana se regularice este descuento, sin perjuicio que realice el depósito directo del mes omitido. Caso contrario se procederá al descuento de los garantes personales incluido los intereses penales pasados los 30 días de incumplimiento,  sin  necesidad  de  previo aviso.
            </div>
            <?php }
            else{
                if($modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Activo' || $modality->name=='Largo Plazo con Garantía Personal Sector Activo' || $modality->name=='Refinanciamiento de Préstamo a largo Plazo con un Solo Garante Sector Activo' || $modality->name == 'Largo Plazo con un Solo Garante Sector Activo' || $modality->name == 'Largo Plazo con Garantía Personal en Disponibilidad'){
                    $sexta = 'Comando General de la Policía Boliviana';
                }
                if($modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo SENASIR' || $modality->name == 'Refinanciamiento de Préstamo a Largo Plazo Sector Pasivo SENASIR'){
                    $sexta = 'Servicio Nacional del Sistema de Reparto SENASIR';
                }?>
            <b>SEXTA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, autoriza expresamente a la MUSERPOL practicar los descuentos respectivos {{$is_active? 'de los haberes':'de las rentas'}} que percibe en forma mensual a través del {{ $sexta }} conforme al Reglamento de Préstamos.
            <div>
            Si por cualquier motivo la MUSERPOL estuviera imposibilitada de realizar el descuento por el medio señalado, el PRESTATARIO se obliga a cumplir con la cuota de amortización mediante pago directo en la Oficina Central de la MUSERPOL de la ciudad de La Paz o efectuar el depósito en la cuenta fiscal de la MUSERPOL y enviar la boleta de depósito original a la Oficina Central inmediatamente. Caso contrario el PRESTATARIO se hará pasible al recargo correspondiente a los intereses que se generen al día de pago por la deuda contraída.
            </div>
            <div>
                Asimismo, el PRESTATARIO se compromete hacer conocer oportunamente a la MUSERPOL sobre la omisión del descuento mensual que se hubiera dado a efectos de solicitar al {{ $sexta }} regularice este descuento, sin perjuicio que realice el depósito directo del mes omitido, de acuerdo a lo estipulado en el párrafo precedente. Caso contrario se procedera al descuento {{ count($guarantors) > 1 ? 'de los garantes personales' : 'del garante personal' }} incluido los intereses penales pasados los 30 dias de incumplimiento, sin necesidad de previo aviso.
            </div>
        <?php }
    } ?>
    </div>
    <div>
        <b>SÉPTIMA.- (DERECHOS DEL PRESTATARIO):</b> Conforme al Artículo 10 del Reglamento de Préstamos las partes reconocen expresamente como derechos del PRESTATARIO, lo siguiente:
    </div>
    <div>
        <ol type="a" style="margin:0;">
            <li>Recibir buena atención, trato equitativo y digno por parte de los funcionarios de la MUSERPOL sin discriminación de ninguna naturaleza, asimismo recibir información y orientación precisa, comprensible, oportuna y accesible con relación a requisitos, características y condiciones del préstamo con calidad y calidez.</li>
            <li>A la confidencialidad, información detallada y precisa concerniente a los préstamos bajo su titularidad en el marco estricto de la normativa legal vigente.</li>
            <li>A presentar queja formal por el servicio recibido si no se ajusta al presente reglamento.</li>
        </ol>
    </div>
    <div>
        <b>OCTAVA.- (OBLIGACIONES DEL PRESTATARIO):</b> Conforme al Artículo 11 del Reglamento de Préstamos, las partes reconocen expresamente como obligaciones del PRESTATARIO, lo siguiente:
    </div>
    <div>
        <ol type="a" style="margin:0;">
            <li>Proporcionar información y documentación veraz y legítima para la correcta tramitación del préstamo.</li>
            <li>Cumplir con los requisitos, condiciones y lineamientos del préstamo.</li>
            <li>Cumplir con el Contrato de Préstamo suscrito entre la MUSERPOL y el afiliado.</li>
            <li>Amortizar mensualmente la deuda contraída con la MUSERPOL, hasta cubrir el capital adeudado y los intereses correspondientes por la deuda contraída.</li>
        </ol>
    </div>
    <div>
     <?php $modality = $loan->modality;
        if(($modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo SENASIR')){ ?>
        <div>
            <b>NOVENA.-  (DE  LA  GARANTÍA):</b> El PRESTATARIO y GARANTE, garantizan el pago de lo adeudado con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme lo determina el artículo 1335 del Código Civil, así como también con su renta de vejez en curso de pago, asimismo este acepta amortizar la deuda con su Complemento Económico.
        </div>
        Se {{ count($guarantors) > 1 ? 'constituyen como garantes personales, solidarios, mancomunados, e indivisibles' : 'constituye como garante personal, solidario, mancomunado e indivisible' }}:
        <?php } else{
            if($modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo AFP' || $modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo Gestora Pública'){ ?>
            <b>NOVENA.- (DE LA GARANTÍA):</b>El PRESTATARIO y GARANTES, garantizan el pago de lo adeudado con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme lo determina el artículo 1335 del Código Civil, además el PRESTATARIO con los beneficios otorgados por la MUSERPOL
            <br>Asimismo se {{ count($guarantors) > 1 ? 'constituyen como garantes personales, solidarios, mancomunados, e indivisibles' : 'constituye como garante personal, solidario, mancomunado e indivisible' }}:
            <?php }
            if($modality->name == 'Largo Plazo con Garantía Personal Sector Activo' || $modality->name == 'Largo Plazo con Garantía Personal en Comisión' || $modality->name == 'Largo Plazo con Garantía Personal en Disponibilidad'){ ?>
            <b>NOVENA.- (DE LA GARANTÍA):</b>El PRESTATARIO y GARANTES, garantizan el pago de lo adeudado con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme lo determina el artículo 1335 del Código Civil y además el PRESTATARIO con los beneficios otorgados por la MUSERPOL.
            <br> Asimismo se {{ count($guarantors) > 1 ? 'constituyen como garantes personales, solidarios, mancomunados, e indivisibles' : 'constituye como garante personal, solidario, mancomunado e indivisible' }}:
        <?php } ?>
        <?php 
            if($modality->name == 'Largo Plazo con un Solo Garante Sector Activo'){ ?>
            <b>NOVENA.- (DE LA GARANTÍA):</b>El PRESTATARIO y GARANTE, garantizan el pago de lo adeudado con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme lo determina el artículo 1335 del Código Civil y además el PRESTATARIO con los beneficios otorgados por la MUSERPOL.
            <br> Asimismo se {{ count($guarantors) > 1 ? 'constituyen como garantes personales, solidarios, mancomunados, e indivisibles' : 'constituye como garante personal, solidario, mancomunado e indivisible' }}:
        <?php } ?>
        <?php }
        $cont = 0; $concat_guarantor = "";
        foreach($guarantors as $guarantor){
            $male_female_guarantor = Util::male_female($guarantor->gender);
            $cont ++; $concat_guarantor = "(garante Nº ".$cont.")";
            ?>
            <span>
            {{ $guarantor->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $guarantor->full_name }}, con C.I. {{ $guarantor->identity_card_ext }}, {{ $guarantor->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $guarantor->city_birth->name }}, vecin{{ $male_female_guarantor }} de {{ $guarantor->address->cityName() }} y con domicilio en {{ $guarantor->address->full_address }}{{ count($guarantors) > 1 ? $concat_guarantor : '' }},
            </span>
        <?php } ?>
        <?php $modality = $loan->modality;
        if(($modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo SENASIR')){ ?>
         {{ count($guarantors) > 1 ? 'quienes' : 'quien' }} en amparo del artículo 16 del Reglamento de Préstamos de la MUSERPOL {{ count($guarantors) > 1 ? 'garantizan' : 'garantiza' }} el cumplimiento de la obligación y en caso que el PRESTATARIO, incumpliera con el pago de sus obligaciones o se constituyera en mora al incumplimiento de una o más cuotas de amortización, {{ count($guarantors) > 1 ? 'autorizan el descuento mensual de sus haberes' : 'autoriza el descuento mensual de su renta en curso de pago y/o haberes' }} en calidad de {{ count($guarantors) > 1 ? 'GARANTES' : 'GARANTE' }} bajo las mismas condiciones en las que se procedería a descontar al PRESTATARIO, hasta cubrir el pago total de la obligación pendiente de cumplimiento. Excluyendo a la MUSERPOL de toda responsabilidad o reclamo posterior, sin perjuicio de que {{ count($guarantors) > 1 ? 'estos puedan' : 'este pueda' }}  iniciar las acciones legales correspondientes en contra del PRESTATARIO.
        <?php } else{
        if($modality->name == 'Largo Plazo con Garantía Personal Sector Activo' || $modality->name == 'Largo Plazo con un Solo Garante Sector Activo' || $modality->name == 'Largo Plazo con Garantía Personal en Comisión' || $modality->name == 'Largo Plazo con Garantía Personal en Disponibilidad'){ ?>
         {{ count($guarantors) > 1 ? 'quienes' : 'quien' }} en amparo del artículo 16 del Reglamento de Préstamos de la MUSERPOL {{ count($guarantors) > 1 ? 'garantizan' : 'garantiza' }} el cumplimiento de la obligación y en caso que el PRESTATARIO, incumpliera con el pago de sus obligaciones o se constituyera en mora al incumplimiento de una o más cuotas de amortización, {{ count($guarantors) > 1 ? 'autorizan el descuento mensual de sus haberes' : 'autoriza el descuento mensual de sus haberes' }} en calidad de {{ count($guarantors) > 1 ? 'GARANTES' : 'GARANTE' }} bajo las mismas condiciones en las que se procedería a descontar al PRESTATARIO, hasta cubrir el pago total de la obligación pendiente de cumplimiento. Excluyendo a la MUSERPOL de toda responsabilidad o reclamo posterior, sin perjuicio de que {{ count($guarantors) > 1 ? 'estos puedan' : 'este pueda' }} iniciar las acciones legales correspondientes en contra del PRESTATARIO.
        <?php }
         if($modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo AFP' || $modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo Gestora Pública'){ ?>
            {{ count($guarantors) > 1 ? 'quienes' : 'quien' }} en amparo del artículo 16 del Reglamento de Préstamos de la MUSERPOL {{ count($guarantors) > 1 ? 'garantizaran' : 'garantiza' }} el cumplimiento de la obligación y en caso que el PRESTATARIO, incumpliera con el pago de sus obligaciones o se constituyera en mora al incumplimiento de una o más cuotas de amortización, {{ count($guarantors) > 1 ? 'autorizan el descuento mensual de sus haberes' : 'autoriza el descuento mensual de sus haberes' }} en calidad de {{ count($guarantors) > 1 ? 'GARANTES' : 'GARANTE' }}, hasta cubrir el pago total de la obligación pendiente de cumplimiento. Excluyendo a la MUSERPOL de toda responsabilidad o reclamo posterior, sin perjuicio de que estos puedan iniciar las acciones legales correspondientes en contra del PRESTATARIO.
           <?php }?>
        <?php } ?>
    </div>
    <div>
    <?php
        if($modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo AFP' || $modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo SENASIR' || $modality->name == 'Largo Plazo con Garantía Personal Sector Pasivo Gestora Pública'){ ?>
        <div>
            <b>DECIMA.- (CONTINGENCIAS POR FALLECIMIENTO):</b> El PRESTATARIO en caso de fallecimiento acepta amortizar para el cumplimiento efectivo de la presente obligación con el beneﬁcio del Complemento Económico<?php if($modality->name != 'Largo Plazo con Garantía Personal Sector Pasivo AFP'){ ?> en caso de corresponderle<?php } ?>; por cuanto la liquidación de dicho beneﬁcio pasará a cubrir el monto total de la obligación que resulte adeudada, más los intereses devengados a la fecha, cobrados a los derechohabientes, previas las formalidades de ley.
        </div>
        <?php }
            else{
                if($modality->name == 'Largo Plazo con Garantía Personal Sector Activo' || $modality->name == 'Largo Plazo con Garantía Personal en Comisión' || $modality->name == 'Largo Plazo con Garantía Personal en Disponibilidad' || $modality->name == 'Largo Plazo con un Solo Garante Sector Activo'){ ?>
                 <div>
                    <b>DECIMA.- (MODIFICACIÓN DE LA SITUACIÓN DEL PRESTATARIO):</b> El PRESTATARIO en caso de fallecimiento, retiro voluntario o retiro forzoso garantiza el cumplimiento efectivo de la presente obligación con la totalidad del beneficio de Fondo de Retiro Policial Solidario otorgado por la MUSERPOL; por cuanto la liquidación de dicho beneficio pasará a cubrir el monto total de la obligación que resulte adeudada, más los intereses devengados a la fecha, previas las formalidades de ley.
                </div>
                <div>
                    En caso de que se haya modificado la situación del PRESTATARIO del sector activo al sector pasivo de la Policía Boliviana teniendo un saldo deudor respecto del préstamo obtenido, acepta amortizar la deuda con su Complemento Económico, en caso de corresponderle.
                </div>
                <div>
                    Asimismo, en caso de que el monto de sus beneficios del PRESTATARIO, no alcanzare a cubrir el total del monto adeudado, se descontará {{ count($guarantors) > 1 ? 'a los GARANTES':'al GARANTE' }} el saldo deudor que quedare pendiente.
                </div>
                <?php }?>
        <?php   } ?>
    </div>
    <div>
        <b>DÉCIMA PRIMERA.- (DE LA MORA):</b> El PRESTATARIO se constituirá en mora automática sin intimación o requerimiento alguno, de acuerdo a lo establecido por el artículo 341, Núm. 1) del Código Civil, al incumplimiento del pago de cualquier amortización de capital o intereses, sin necesidad de intimación o requerimiento alguno, o acto equivalente por parte de la MUSERPOL. 
    </div>
    <div>
        Además del interés acordado contractualmente, el préstamo generará en caso de mora un interés moratorio anual del 6% sobre saldos de capital de las cuotas impagas, aún cuando fuere exigible todo el capital del préstamo.
    </div>
    <div>
        <b>DÉCIMA SEGUNDA.- (DE LOS EFECTOS DEL INCUMPLIMIENTO Y DE LA ACCIÓN EJECUTIVA):</b> El incumplimiento de pago mensual por parte del PRESTATARIO dará lugar a que la totalidad de la obligación, incluidos los intereses moratorios, se determinen líquidos, exigibles y de plazo vencido quedando la MUSERPOL facultada de iniciar las acciones legales correspondientes al amparo de los artículos 519 y 1465 del Código Civil así como de los artículos 378 y 379, Núm. 2) del Código Procesal Civil, que otorgan a este documento la calidad de Título Ejecutivo, demandando el pago de la totalidad de la obligación contraída, más la cancelación de intereses convencionales y penales, incluyendo los relacionados y emergentes de la cobranza judicial, honorarios, derechos costas y otros, sin excepción por efecto de la acción legal que la MUSERPOL instaure para lograr el cumplimiento total de la obligación.
    </div>
    <?php $modality = $loan->modality;
        if($modality->name == 'Largo Plazo con Garantía Personal Sector Activo' || $modality->name == 'Largo Plazo con un Solo Garante Sector Activo' || $modality->name == 'Largo Plazo con Garantía Personal en Comisión' || $modality->name == 'Largo Plazo con Garantía Personal en Disponibilidad'){ ?>
          <div>
          {{ count($guarantors) > 1 ? 'Los GARANTES están facultados' : 'El GARANTE esta facultado' }} a realizar el trámite de recuperación de los montos que se le hubieran sido descontados en función a la obligación objeto del presente contrato pudiendo recaer sobre él Beneficio de Fondo de Retiro Policial Solidario otorgado por la MUSERPOL y reconocidos al PRESTATARIO, conforme al artículo 64 del Reglamento de Préstamos.
        </div>
        <?php } ?>
    <div>
        En caso de incumplimiento de los pagos mensuales estipulados en el presente contrato que generen mora de la obligación, el PRESTATARIO no tendrá derecho a acceder a otro crédito, hasta la cancelación total de la deuda.
    </div>
    <div>
        <b>DÉCIMA TERCERA.- (DOMICILIO ESPECIAL):</b> Para efectos legales, incluida la acción judicial u otra, se tendrá como domicilio especial del PRESTATARIO y {{count($guarantors)>1 ? 'GARANTES':'GARANTE'}} el señalado en la cláusula primera y novena de conformidad al artículo 29 parágrafo II del Código Civil, donde se efectuarán las citaciones y notificaciones judiciales o cualquier otra comunicación, con plena validez legal y sin lugar a posterior observación o recurso alguno.
    </div>
    <div>
        <b>DÉCIMA CUARTA.- (DE LA CONFORMIDAD Y ACEPTACIÓN):</b> Por una parte en calidad de ACREEDOR la Mutual de Servicios al Policía (MUSERPOL), representada por su {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} y por otra parte en calidad de
        @if (count($lenders) == 1)
        <span>PRESTATARIO {{ $lender->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $lender->full_name }} de generales ya señaladas; </span>
            @if(count($guarantors) > 1)
            <span>asimismo en calidad de garantes personales</span>
            @else
            <span>asimismo en calidad de garante personal</span>
            @endif
        <?php $c=0; count($guarantors)>1 ? $sw=' y': $sw = ',';
        foreach($guarantors as $guarantor){ ?>
            <span>
            {{ $guarantor->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $guarantor->full_name }}{{ $c==0 ? $sw : ',' }}
            </span>
        <?php $c++;} ?>
            damos nuestra plena conformidad con todas y cada una de las cláusulas precedentes, obligándonos a su fiel y estricto cumplimiento. En señal de lo cual suscribimos el presente contrato de préstamo de dinero con garantía personal en manifestación de nuestra libre y espontánea voluntad y sin que medie vicio de consentimiento alguno.
        </span>
        @endif
    </div><br><br>
    <div class="text-center">
        <p class="center">
        La Paz, {{ Carbon::now()->isoFormat('LL') }}
        </p>
    </div>
</div>
<div>
    <div>
        @if (count($guarantors) == 1)
        @php ($guarantor = $guarantors[0])
        <table>
            <tr>
                <td  with = "50%">
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card_ext,
                    'position' => 'PRESTATARIO'
                ])
                </td>
                <td  with = "50%">
                @include('partials.signature_box', [
                    'full_name' => $guarantor->full_name,
                    'identity_card' => $guarantor->identity_card_ext,
                    'position' => 'GARANTE'
                ])
                </td>
            </tr>
        </table>
        @endif
    </div>
    @if (count($guarantors) == 2)
    <div>
        @include('partials.signature_box', [
            'full_name' => $lender->full_name,
            'identity_card' => $lender->identity_card_ext,
            'position' => 'PRESTATARIO'
        ])
    </div>
    <div class = "no-page-break">
        <div>
            <table>
                <tr>
                    @foreach ($guarantors as $guarantor)
                    <td with = "50%">
                        @include('partials.signature_box', [
                            'full_name' => $guarantor->full_name,
                            'identity_card' => $guarantor->identity_card_ext,
                            'position' => 'GARANTE'
                        ])
                    @endforeach
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif
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
<?php } ?>
</body>
</html>
