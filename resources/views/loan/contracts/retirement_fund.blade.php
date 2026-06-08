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
        CONTRATO DE PRÉSTAMO <font style="text-transform: uppercase;">{{ $title }}</font>
        <div>Nº {{ $loan->code }}</div>
    </div>
</div>
<div class="block text-justify">
    <div>
        Conste en el presente contrato de préstamo al {{ $title }}, que al solo reconocimiento de firmas y rúbricas será elevado a Instrumento Público, 
        por lo que las partes que intervienen lo suscriben al tenor y contenido de las siguientes cláusulas y condiciones:
    </div>
    <div>
        <b>PRIMERA.- (DE LAS PARTES):</b>

        Intervienen en el presente contrato, por una parte la Mutual de Servicios al Policía (MUSERPOL), representada legalmente por su {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} 
        con C.I. {{ $employees[0]['identity_card'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} con C.I. {{ $employees[1]['identity_card'] }} 
        que para fines de este contrato en  adelante se denominará MUSERPOL o ACREEDOR con domicilio en la Z. Sopocachi, Av. 6 de agosto N° 2354 y por otra parte el 
        @if (count($lenders) == 1)
            @php
                $lender = $lenders[0];
                $male_female = Util::male_female($lender->gender);
            @endphp
        {{ $lender->gender == 'M' ? 'Sr.' : 'Sra.' }} {{ $lender->full_name }}, con C.I. {{ $lender->identity_card }}, {{ $lender->civil_status_gender }}, mayor de edad,
        hábil por derecho, natural de {{ $lender->city_birth->name }}, vecin{{ $male_female }} de {{ $lender->address->cityName() }} y con domicilio 
        en {{ $lender->address->full_address }}, en adelante denominad{{ $male_female }} PRESTATARIO.
        @endif
    </div>
    <div>
        <b>SEGUNDA.- (DEL OBJETO):</b>  El objeto del presente contrato es el préstamo de dinero que la Mutual de Servicios al Policía (MUSERPOL) 
        otorga al PRESTATARIO conforme a niveles de aprobación respectivos, en la suma de Bs.{{ Util::money_format($loan->amount_approved)}}
        (<span class="uppercase">{{ Util::money_format($loan->amount_approved, true) }} BOLIVIANOS).</span>
    </div>
    <div>
        <b>TERCERA.- (DEL INTERÉS):</b> El préstamo objeto del presente contrato, devengará un interés ordinario del {{ $loan->interest->annual_interest }}% 
        anual sobre saldo deudor, el mismo que se recargará con el interés penal en caso de mora de una o más amortizaciones.
    </div>
    <div>
        <b>CUARTA.- (DEL PLAZO Y LA CUOTA DE AMORTIZACIÓN):</b> El plazo fijo e improrrogable para el cumplimiento de la obligación contraída por el PRESTATARIO en 
        virtud al préstamo otorgado es de {{ $loan->loan_term}} meses computables a partir de la fecha de desembolso. La cuota de amortización mensual es de 
        Bs.{{ Util::money_format($loan->estimated_quota) }} (<span class="uppercase">{{ Util::money_format($loan->estimated_quota, true) }} BOLIVIANOS).</span>
        <br>
        Los intereses generados entre la fecha del desembolso del préstamo y la fecha del primer pago serán cobrados con la primera cuota; conforme establece el
        Reglamento de Préstamos.
    </div>
    <div>
        <b>QUINTA.- (DEL DESEMBOLSO):</b> El desembolso del préstamo de dinero en la moneda pactada se acredita mediante comprobante escrito en el que conste el 
        abono efectuado a favor del PRESTATARIO, a través de una cuenta bancaria señalada por el mismo, reconociendo ambas partes que al amparo de este procedimiento 
        se cumple satisfactoriamente la exigencia contenida en el artículo 1331 del Código de Comercio.</span>
    </div>
    <div>
        <b>SEXTA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, autoriza 
        expresamente a la MUSERPOL practicar los descuentos respectivos de los haberes que percibe en forma mensual a través del Comando General de la Policía Boliviana.
        <br>
        Si por cualquier motivo la MUSERPOL estuviera imposibilitada de realizar el descuento por el medio señalado, el PRESTATARIO se obliga a 
        cumplir con la cuota de amortización mediante pago directo en la Oficina Central de la MUSERPOL de la ciudad de La Paz o efectuar el depósito en la cuenta 
        fiscal de la MUSERPOL. Caso contrario el PRESTATARIO se hará pasible al recargo 
        correspondiente a los intereses que se generen al día de pago por la deuda contraída.
        <br>
        Asimismo, el PRESTATARIO se compromete hacer conocer oportunamente a la MUSERPOL sobre la omisión del descuento mensual que se hubiera dado, sin perjuicio 
        que realice el depósito directo del mes omitido, de acuerdo a lo estipulado en el párrafo precedente.
    </div>
    <div>
        <b>SÉPTIMA.- (DERECHOS DEL PRESTATARIO):</b> Conforme al Artículo 10 del Reglamento de Préstamos las partes reconocen expresamente como derechos del 
        PRESTATARIO, lo siguiente:
    </div>
    <div>
        <ol type="a" style="margin:0;">
            <li>
                Recibir buena atención, trato equitativo y digno por parte de los funcionarios de la MUSERPOL sin discriminación de ninguna naturaleza, asimismo 
                recibir información y orientación precisa, comprensible, oportuna y accesible con relación a requisitos, características y condiciones del préstamo;
            </li>
            <li>
                A la confidencialidad, información detallada y precisa concerniente a los préstamos bajo su titularidad en el marco estricto de la normativa legal vigente;
            </li>
            <li>
                A presentar queja formal por el servicio recibido si no se ajusta al presente reglamento.
            </li>
        </ol>
    </div>
    <div>
        <b>OCTAVA.- (OBLIGACIONES DEL PRESTATARIO):</b> Conforme al Artículo 11 del Reglamento de Préstamos, las partes reconocen expresamente como obligaciones del PRESTATARIO, lo siguiente:
    </div>
    <div>
        <ol type="a" style="margin:0;">
            <li>Proporcionar información y documentación veraz y legítima para la correcta tramitación del préstamo;</li>
            <li>Cumplir con los requisitos, condiciones y lineamientos del préstamo;</li>
            <li>Cumplir con el Contrato de Préstamo suscrito entre la MUSERPOL y el prestatario;</li>
            <li>Amortizar mensualmente y/o semestralmente la deuda contraída con la MUSERPOL, hasta cubrir el capital adeudado además de 
                los intereses correspondientes según contrato de préstamo suscrito;</li>
        </ol>
    </div>
    <div>
        <b>NOVENA.- (DE LA GARANTÍA):</b>El PRESTATARIO, en pleno uso de sus facultades de forma libre y espontánea, sin que medie presión, dolo o culpa en manifestación de su voluntad, 
        garantiza el préstamo con su Beneficio del Fondo de Retiro Policial Solidario que le corresponda. <br>
        De la misma forma, él PRESTATARIO, garantiza el pago de lo adeudado con la generalidad de sus bienes, derechos y acciones habidos y por haber, 
        presentes y futuros conforme determina el art. 1335 del Código Civil.
    </div>
    <div>
        <b>DÉCIMA.- (MODIFICACIÓN DE LA SITUACIÓN DEL PRESTATARIO):</b> El PRESTATARIO, en caso de fallecimiento, jubilación, retiro voluntario o retiro forzoso garantiza el 
        cumplimiento efectivo de la presente obligación con la totalidad del Beneﬁcio de Fondo de Retiro Policial Solidario otorgado por la MUSERPOL; por cuanto la liquidación
        de dicho beneﬁcio pasará a cubrir el monto total de la obligación que resulte adeudada, más los intereses devengados a la fecha, toda vez que el mismo se constituye 
        en la primera fuente de repago, sin perjuicio que el titular asuma de manera voluntaria y directa la liquidación total del préstamo.
    </div>
    <div>
        Para el fin del pago de la obligación, el PRESTATARIO de manera expresa autoriza a la MUSERPOL para que inicie el trámite del Beneficio de Fondo de Retiro Policial 
        Solidario, por cuanto se encuentra plenamente facultada a presentar la solicitud formal y realizar las gestiones necesarias para la obtención de la documentación 
        requerida para la otorgación de este Beneficio.
    </div>
    <div>
        <b>DÉCIMA PRIMERA.- (DE LA MORA):</b> El PRESTATARIO se constituirá en mora automática sin intimación o requerimiento alguno, de acuerdo a lo establecido por el 
        artículo 341, Núm. 1) del Código Civil, al incumplimiento del pago de cualquier amortización de capital o intereses, sin necesidad de intimación o requerimiento 
        alguno, o acto equivalente por parte de la MUSERPOL.
        <br>
        Además del interés acordado contractualmente, el préstamo generará en caso de mora un interés moratorio anual del {{ round($loan->interest->penal_interest) }}% sobre saldos de capital de las cuotas impagas, 
        aún cuando fuere exigible todo el capital del préstamo.  
    </div>
    <div>
        <b>DÉCIMA SEGUNDA.- (DE LOS EFECTOS DEL INCUMPLIMIENTO Y DE LA ACCIÓN EJECUTIVA):</b> El incumplimiento de pago mensual por parte del PRESTATARIO 
        dará lugar a que la totalidad de la obligación, incluidos los intereses moratorios, se determinen líquidos, exigibles y de plazo vencido quedando la MUSERPOL facultada 
        de iniciar las acciones legales correspondientes al amparo de los artículos 519 y 1465 del Código Civil así como de los artículos 378 y 379, Núm. 2) del Código Procesal 
        Civil, que otorgan a este documento la calidad de Título Ejecutivo, demandando el pago de la totalidad de la obligación contraída, más la cancelación de intereses 
        convencionales y penales, incluyendo los relacionados y emergentes de la cobranza judicial, honorarios, derechos, costas y otros, sin excepción por efecto de la acción 
        legal que la MUSERPOL instaure para lograr el cumplimiento total de la obligación.
        <br>
        En caso de incumplimiento de los pagos mensuales estipulados en el presente contrato que generen mora de la obligación, el PRESTATARIO no tendrá derecho a acceder a otro 
        crédito, hasta la cancelación total de la deuda.
    </div>
    <div>
        <b>DÉCIMA TERCERA.- (DOMICILIO ESPECIAL):</b> Para efectos legales, incluida la acción judicial u otra, se tendrá como domicilio especial del <b>PRESTATARIO</b> el señalado en 
        la cláusula primera de conformidad al artículo 29 parágrafo II del Código Civil, donde se efectuarán las citaciones y notificaciones judiciales o cualquier otra 
        comunicación, con plena validez legal y sin lugar a posterior observación o recurso alguno.
    </div>
    <div>
        <b>DÉCIMA CUARTA.- (DE LA CONFORMIDAD Y ACEPTACIÓN):</b> Por una parte en calidad de ACREEDOR la Mutual de Servicios al Policía (MUSERPOL), representada por su 
        {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} y por otra parte en calidad de
        @if (count($lenders) == 1)
        <span>
            PRESTATARI{{ $lender->gender == 'M' ? 'O' : 'A' }} {{ $lender->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $lender->full_name }} de generales ya señaladas;
            damos nuestra plena conformidad con todas y cada una de las cláusulas precedentes, obligándonos a su fiel y estricto cumplimiento. 
            En señal de lo cual suscribimos el presente contrato de préstamo de dinero en manifestación de nuestra libre y espontánea voluntad y sin que medie vicio de 
            consentimiento alguno.
        </span>
        @endif
    </div>
    <div class="text-center">
        <p class="center">
        La Paz, {{ Carbon::now()->isoFormat('LL') }}
        </p>
    </div>
</div>
<div class="block m-t-100">
    <table>
        <tr class="align-top">
            <td width="100%">
            @include('partials.signature_box', [
            'full_name' => $lender->full_name,
            'identity_card' => $lender->identity_card,
            'position' => 'PRESTATARIO'
            ])
            </td>
        </tr>
    </table>

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
</body>
</html>
