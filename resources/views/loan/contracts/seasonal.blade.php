<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{$file_title}}</title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>
@include('partials.header', $header)
<body>

@php 
    $modality = $loan->modality;
    $modality_name = $loan->modality->name;
@endphp

@if($modality_name == 'Estacional para el Sector Pasivo de la Policía Boliviana con Cónyuge')
<div class="block">
    <div class="font-semibold leading-tight text-center m-b-10 text-base">
        CONTRATO DE PRÉSTAMO <font style="text-transform: uppercase;">{{ $title }}</font>
        <div>Nº {{ $loan->code }}</div>
    </div>
</div>
<div class="block text-justify">
    <div>
        Conste en el presente contrato de préstamo {{ $title }}, que al solo reconocimiento de firmas y rúbricas será elevado a Instrumento Público, 
        por lo que las partes que intervienen lo suscriben al tenor y contenido de las siguientes cláusulas y condiciones:
    </div>
    <div>
        <b>PRIMERA.- (DE LAS PARTES):</b><br>
        <ol type="1" style="margin:0;">
            <li>
                LA ENTIDAD: La Mutual de Servicios al Policía "MUSERPOL", representada legalmente por su {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} 
                con C.I. {{ $employees[0]['identity_card'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} con C.I. {{ $employees[1]['identity_card'] }} 
                que para fines de este contrato en  adelante se denominará MUSERPOL o ACREEDOR con domicilio en la Z. Sopocachi, Av. 6 de agosto N° 2354.
            </li>
            <li>
                @if (count($lenders) == 1)
                @php
                    $lender = $lenders[0];
                    $male_female = Util::male_female($lender->gender);
                @endphp
                <span>
                    {{ $lender->gender == 'M' ? 'Sr.' : 'Sra.' }} {{ $lender->full_name }}, con C.I. {{ $lender->identity_card }}, {{ $lender->civil_status_gender }}, mayor de edad, 
                    hábil por derecho, natural de {{ $lender->city_birth->name }}, vecin{{ $male_female }} de {{ $lender->address->cityName() }} y con domicilio 
                    en {{ $lender->address->full_address }}, en adelante denominad{{ $male_female }} PRESTATARIO.
                </span>
                @endif
            </li>
            @if(str_contains($title,'con Cónyuge'))
            <li>
                @if (count($spouses) == 1)
                    @php 
                        $spouse = $spouses[0];
                        if($lender->gender == 'M'){
                            $spouse_gender='F';
                            $male_female_spouse = Util::male_female($spouse_gender);
                        }else{
                            $spouse_gender='M';
                            $male_female_spouse = Util::male_female($spouse_gender);
                        }
                    @endphp
                    <span>
                        {{ $spouse_gender == 'F' ? 'Sra.' : 'Sr.' }} {{ $spouse->full_name}}, 
                        con C.I. {{ $spouse->identity_card }}, {{ Util::get_civil_status($spouse->civil_status,$spouse_gender) }}, 
                        mayor de edad, hábil por derecho, natural de {{ $spouse->city_birth->name }}, vecin{{ $male_female_spouse }} 
                        de {{ $spouse->address->cityName() }} y con domicilio en {{ $spouse->address->full_address }}, en adelante 
                        denominad{{ $male_female_spouse }} CONYUGE ANUENTE.
                    </span>
                @endif
            </li>
            @endif
        </ol>
    </div>
    <div>
        <b>SEGUNDA.- (DEL OBJETO):</b>  El objeto del presente contrato es el préstamo de dinero que la Mutual de Servicios al Policía (MUSERPOL) 
        otorga al PRESTATARIO conforme a niveles de aprobación respectivos, en la suma de Bs.{{ Util::money_format($loan->amount_approved) }} 
        (<span class="uppercase">{{Util::money_format($loan->amount_approved, true)}} Bolivianos)</span>, monto que alcanza el total de su Complemento Económico.
    </div>
    <div>
        <b>TERCERA.- (DEL INTERÉS):</b> El préstamo objeto del presente contrato, devengará un interés ordinario del {{ round($loan->interest->annual_interest) }}% 
        anual sobre saldo deudor, el mismo que se recargará con el interés penal en caso de mora de una o más amortizaciones.
    </div>
    <div>
        <b>CUARTA.- (DEL PLAZO Y LA CUOTA DE AMORTIZACIÓN):</b> El plazo fijo e improrrogable para el cumplimiento de la obligación contraída por el PRESTATARIO en 
        virtud al préstamo otorgado es de {{ $loan->loan_term}} {{ $loan->loan_term == 1 ? 'semestre' : 'semestres' }} computables a partir de la fecha de desembolso. La cuota de amortización semestral es de 
        Bs.{{ Util::money_format($loan->estimated_quota) }} (<span class="uppercase">{{ Util::money_format($loan->estimated_quota, true) }} Bolivianos).</span>
        <br>
        Los intereses generados entre la fecha del desembolso del préstamo y la fecha del primer pago (seis meses) serán cobrados con el pago del Beneficio del 
        Complemento Económico conforme establece el Reglamento de Préstamo.
    </div>
    <div>
        <b>QUINTA.- (DEL DESEMBOLSO):</b> El desembolso del préstamo de dinero en la moneda pactada se acredita mediante comprobante escrito en el que conste el 
        abono efectuado a favor del PRESTATARIO, a través de una cuenta bancaria señalada por el mismo, reconociendo ambas partes que al amparo de este procedimiento 
        se cumple satisfactoriamente la exigencia contenida en el artículo 1331 del Código de Comercio.</span>
    </div>
    <div>
        <b>SEXTA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, autoriza 
        expresamente a la MUSERPOL practicar el descuento del Beneficio de Complemento Económico a través de la Dirección de Beneficios Económicos, por lo que el 
        PRESTATARIO, se compromete de forma plena y expresa a realizar su trámite del Benefició del Complemento Económico en tiempo oportuno según las formalidades 
        establecidas; por cuanto la liquidación de dicho beneficio pasará a cubrir el monto de la cuota semestral de la obligación.
        <br>
        Si por cualquier motivo la MUSERPOL estuviera imposibilitada de realizar el descuento por el medio precedentemente señalado, el PRESTATARIO se obliga a 
        cumplir con la cuota de amortización mediante pago directo en la Oficina Central de la MUSERPOL de la ciudad de La Paz o efectuar el depósito en la cuenta 
        fiscal de la MUSERPOL. Caso contrario el PRESTATARIO se hará pasible al recargo 
        correspondiente a los intereses que se generen al día de pago por la deuda contraída.
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
        <b>OCTAVA.- (OBLIGACIONES DEL PRESTATARIO):</b> Conforme al Artículo 11 del Reglamento de Préstamos, las partes reconocen expresamente como obligaciones del PRESTATARIO, 
        lo siguiente:
    </div>
    <div>
        <ol type="a" style="margin:0;">
            <li>Proporcionar información y documentación veraz y legítima para la correcta tramitación del préstamo;</li>
            <li>Cumplir con los requisitos, condiciones y lineamientos del préstamo;</li>
            <li>Cumplir con el Contrato de Préstamo suscrito entre la MUSERPOL y el Prestatario;</li>
            <li>
                Amortizar mensualmente y/o semestralmente la deuda contraída con la MUSERPOL, hasta cubrir el capital adeudado además de 
                los intereses correspondientes según contrato de préstamo suscrito;
            </li>
        </ol>
    </div>
    <div>
        <b>NOVENA.- (DE LA GARANTÍA):</b>El PRESTATARIO, en pleno uso de sus facultades de forma libre y espontánea, sin que medie presión, dolo o culpa en manifestación de su voluntad, 
        garantiza el préstamo con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme determina el art. 1335 del Código Civil.
    </div>
    <div>
        <b>DÉCIMA.- (CONTINGENCIAS POR FALLECIMIENTO):</b> En caso de incumplimiento en el pago efectivo de las obligaciones por causa de fallecimiento del PRESTATARIO,
        @if(str_contains($title,'con Cónyuge'))
            la CONYUGE ANUENTE se obliga
        @else
            los herederos se obligan
        @endif
        a cumplir con el pago de las amortizaciones de capital e intereses pendientes de pago asumidas en el presente contrato, consiguientemente, 
        la MUSERPOL se reserva el derecho de realizar la compensación de la suma adeudada con el Beneficio del Complemento Económico, que por derecho le corresponde 
        @if(str_contains($title,'con Cónyuge'))
            a la Viuda 
        @else
            a los herederos
        @endif
        por vía sucesión hereditaria, que serán pagados de acuerdo a lo establecido en la cláusula cuarta del presente contrato.
    </div>
    <div>
        <b>DÉCIMA PRIMERA.- (DE LA MORA):</b> El PRESTATARIO se constituirá en mora automática sin intimación o requerimiento alguno, de acuerdo a lo establecido por el 
        artículo 341, Núm. 1) del Código Civil, al incumplimiento del pago de cualquier amortización de capital o intereses, sin necesidad de intimación o requerimiento 
        alguno, o acto equivalente por parte de la MUSERPOL.
        <br>
        Además del interés acordado contractualmente, el préstamo generará en caso de mora un interés moratorio anual del 
        {{ round($loan->interest->penal_interest) }}% sobre saldos de capital de las cuotas impagas, 
        aún cuando fuere exigible todo el capital del préstamo.  
    </div>
    <div>
        <b>DÉCIMA SEGUNDA.- (DE LOS EFECTOS DEL INCUMPLIMIENTO Y DE LA ACCIÓN EJECUTIVA):</b> El incumplimiento de pago semestral por parte del PRESTATARIO 
        @if(str_contains($title,'con Cónyuge'))
            o la CÓNYUGE ANUENTE 
        @endif
        dará lugar a que la totalidad de la obligación, incluidos los intereses moratorios, se determinen líquidos, exigibles y de plazo vencido quedando la MUSERPOL facultada 
        de iniciar las acciones legales correspondientes al amparo de los artículos 519 y 1465 del Código Civil así como de los artículos 378 y 379, Núm. 2) del Código Procesal 
        Civil, que otorgan a este documento la calidad de Título Ejecutivo, demandando el pago de la totalidad de la obligación contraída, más la cancelación de intereses 
        convencionales y penales, incluyendo los relacionados y emergentes de la cobranza judicial, honorarios, derechos, costas y otros, sin excepción por efecto de la acción 
        legal que la MUSERPOL instaure para lograr el cumplimiento total de la obligación.
        <br>
        En caso de incumplimiento del pago semestral estipulados en el presente contrato que generen mora de la obligación, el PRESTATARIO no tendrá derecho a acceder a otro 
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
            @if(str_contains($title,'con Cónyuge'))
                y por otra en calidad de CONYUGE ANUENTE
                @if (count($spouses) == 1)
                    @php $male_female_spouse = Util::male_female($spouse_gender); @endphp
                    <span>
                    {{ $spouse_gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $spouse->full_name }},
                    </span>
                @endif
                de generales ya señaladas, damos nuestra
            @else
                damos
            @endif
            plena conformidad con todas y cada una de las cláusulas precedentes, obligándonos a su fiel y estricto cumplimiento. 
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
            <td width="50%">
            @include('partials.signature_box', [
                'full_name' => $lender->full_name,
                'identity_card' => $lender->identity_card,
                'position' => 'PRESTATARIO'
            ])
            </td>
            @if(str_contains($title,'con Cónyuge'))
                <td width="50%">
                @include('partials.signature_box', [
                    'full_name' => $spouse->full_name,
                    'identity_card' => $spouse->identity_card,
                    'position' => 'CÓNYUGE ANUENTE'
                ])
                </td>
            @endif
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
@else

<div class="block">
    <div class="font-semibold leading-tight text-center m-b-10 text-base">
        CONTRATO DE PRÉSTAMO <font style="text-transform: uppercase;">{{ $title }}</font>
        <div>Nº {{ $loan->code }}</div>
    </div>
</div>
<div class="block text-justify">
    <div>
        Conste en el presente contrato de préstamo {{ $title }}, que al solo reconocimiento de firmas y rúbricas será elevado a Instrumento Público, 
        por lo que las partes que intervienen lo suscriben al tenor y contenido de las siguientes cláusulas y condiciones:
    </div>
    <div>
        <b>PRIMERA.- (DE LAS PARTES):</b><br>
        <ol type="1" style="margin:0;">
            <li>
                LA ENTIDAD: La Mutual de Servicios al Policía "MUSERPOL", representada legalmente por su {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} 
                con C.I. {{ $employees[0]['identity_card'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} con C.I. {{ $employees[1]['identity_card'] }} 
                que para fines de este contrato en  adelante se denominará MUSERPOL o ACREEDOR con domicilio en la Z. Sopocachi, Av. 6 de agosto N° 2354.
            </li>
            <li>
                @if (count($lenders) == 1)
                @php
                    $lender = $lenders[0];
                    $male_female = Util::male_female($lender->gender);
                @endphp
                <span>
                    {{ $lender->gender == 'M' ? 'Sr.' : 'Sra.' }} {{ $lender->full_name }}, con C.I. {{ $lender->identity_card }}, {{ $lender->civil_status_gender }}, mayor de edad, 
                    hábil por derecho, natural de {{ $lender->city_birth->name }}, vecin{{ $male_female }} de {{ $lender->address->cityName() }} y con domicilio 
                    en {{ $lender->address->full_address }}, en adelante denominad{{ $male_female }} PRESTATARIO.
                </span>
                @endif
            </li>
            @if(str_contains($title,'con Cónyuge'))
            <li>
                @if (count($spouses) == 1)
                    @php 
                        $spouse = $spouses[0];
                        if($lender->gender == 'M'){
                            $spouse_gender='F';
                            $male_female_spouse = Util::male_female($spouse_gender);
                        }else{
                            $spouse_gender='M';
                            $male_female_spouse = Util::male_female($spouse_gender);
                        }
                    @endphp
                    <span>
                        {{ $spouse_gender == 'F' ? 'Sra.' : 'Sr.' }} {{ $spouse->full_name}}, 
                        con C.I. {{ $spouse->identity_card }}, {{ Util::get_civil_status($spouse->civil_status,$spouse_gender) }}, 
                        mayor de edad, hábil por derecho, natural de {{ $spouse->city_birth->name }}, vecin{{ $male_female_spouse }} 
                        de {{ $spouse->address->cityName() }} y con domicilio en {{ $spouse->address->full_address }}, en adelante 
                        denominad{{ $male_female_spouse }} CONYUGE ANUENTE.
                    </span>
                @endif
            </li>
            @endif
        </ol>
    </div>
    <div>
        <b>SEGUNDA.- (DEL ANTECEDENTE):</b>  Mediante contrato de préstamo de dinero N° {{ $loan->code }} de fecha {{ Carbon::parse($loan->disbursement_date)->isoFormat('LL') }}, se ha suscrito entre la MUSERPOL y el PRESTATARIO un Préstamo Estacional por la suma de Bs.{{ Util::money_format($parent_loan->amount_approved) }} 
        (<span class="uppercase">{{Util::money_format($loan->amount_approved, true)}} Bolivianos)</span>.
    </div>
    <div>
        <b>TERCERA.- (DEL OBJETO):</b>  El objeto del presente contrato es el refinanciamiento del préstamo de dinero que la MUSERPOL
        otorga al PRESTATARIO conforme a calificación, previa evaluación y autorización, de conformidad a los niveles de aprobación respectivos en la suma de Bs.{{ Util::money_format($loan->refinancing_balance) }} 
        para lo cual el PRESTATARIO reconoce de manera expresa el saldo anterior de la deuda correspondiente al préstamo correspondiente al préstamo contraido con anterioridad, que asciende a la suma de 
        Bs.{{ Util::money_format($loan->balance_parent_refi())}}, montos que hacen un total efectivo de Bs.{{ Util::money_format($loan->amount_approved ) }},
        que representa la nueva obligación contraida sujeta a cumplimiento, en función a la operación de refinanciamiento.
        (<span class="uppercase">{{Util::money_format($loan->amount_approved, true)}} Bolivianos)</span>, monto que alcanza el total de su Complemento Económico.
    </div>
    <div>
        <b>CUARTA.- (DEL INTERÉS):</b> El préstamo objeto del presente contrato, devengará un interés ordinario del {{ round($loan->interest->annual_interest) }}% 
        anual sobre saldo deudor, el mismo que se recargará con el interés penal en caso de mora de una o más amortizaciones.
    </div>
    <div>
        <b>QUINTA.- (DEL PLAZO Y LA CUOTA DE AMORTIZACIÓN):</b> El plazo fijo e improrrogable para el cumplimiento de la obligación contraída por el PRESTATARIO en 
        virtud al préstamo otorgado es de {{ $loan->loan_term }} {{ $loan->loan_term == 1 ? 'semestre' : 'semestres' }} computables a partir de la fecha de desembolso. La cuota de amortización semestral es de 
        Bs.{{ Util::money_format($loan->estimated_quota) }} (<span class="uppercase">{{ Util::money_format($loan->estimated_quota, true) }} Bolivianos).</span>
        <br>
        Los intereses generados entre la fecha del desembolso del préstamo y la fecha del primer pago (seis meses) serán cobrados con el pago del Beneficio del 
        Complemento Económico conforme establece el Reglamento de Préstamo.
    </div>
    <div>
        <b>SEXTA.- (DEL DESEMBOLSO):</b> El desembolso del préstamo de dinero en la moneda pactada se acredita mediante comprobante escrito en el que conste el 
        abono efectuado a favor del PRESTATARIO, a través de una cuenta bancaria señalada por el mismo, reconociendo ambas partes que al amparo de este procedimiento 
        se cumple satisfactoriamente la exigencia contenida en el artículo 1331 del Código de Comercio.</span>
    </div>
    <div>
        <b>SEPTIMA.- (DE LA FORMA DE PAGO Y OTRAS CONTINGENCIAS):</b> Para el cumplimiento estricto de la obligación (capital e intereses) el PRESTATARIO, autoriza 
        expresamente a la MUSERPOL practicar el descuento del Beneficio de Complemento Económico a través de la Dirección de Beneficios Económicos, por lo que el 
        PRESTATARIO, se compromete de forma plena y expresa a realizar su trámite del Benefició del Complemento Económico en tiempo oportuno según las formalidades 
        establecidas; por cuanto la liquidación de dicho beneficio pasará a cubrir el monto de la cuota semestral de la obligación.
        <br>
        Si por cualquier motivo la MUSERPOL estuviera imposibilitada de realizar el descuento por el medio precedentemente señalado, el PRESTATARIO se obliga a 
        cumplir con la cuota de amortización mediante pago directo en la Oficina Central de la MUSERPOL de la ciudad de La Paz o efectuar el depósito en la cuenta 
        fiscal de la MUSERPOL. Caso contrario el PRESTATARIO se hará pasible al recargo 
        correspondiente a los intereses que se generen al día de pago por la deuda contraída.
    </div>
    <div>
        <b>OCTAVA.- (DERECHOS DEL PRESTATARIO):</b> Conforme al Artículo 10 del Reglamento de Préstamos las partes reconocen expresamente como derechos del 
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
        <b>NOVENA.- (OBLIGACIONES DEL PRESTATARIO):</b> Conforme al Artículo 11 del Reglamento de Préstamos, las partes reconocen expresamente como obligaciones del PRESTATARIO, 
        lo siguiente:
    </div>
    <div>
        <ol type="a" style="margin:0;">
            <li>Proporcionar información y documentación veraz y legítima para la correcta tramitación del préstamo;</li>
            <li>Cumplir con los requisitos, condiciones y lineamientos del préstamo;</li>
            <li>Cumplir con el Contrato de Préstamo suscrito entre la MUSERPOL y el Prestatario;</li>
            <li>
                Amortizar mensualmente y/o semestralmente la deuda contraída con la MUSERPOL, hasta cubrir el capital adeudado además de 
                los intereses correspondientes según contrato de préstamo suscrito;
            </li>
        </ol>
    </div>
    <div>
        <b>DÉCIMA.- (DE LA GARANTÍA):</b>El PRESTATARIO, en pleno uso de sus facultades de forma libre y espontánea, sin que medie presión, dolo o culpa en manifestación de su voluntad, garantiza el préstamo con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y futuros conforme determina el art. 1335 del Código Civil.
    </div>
    <div>
        <b>DÉCIMA PRIMERA.- (CONTINGENCIAS POR FALLECIMIENTO):</b> En caso de incumplimiento en el pago efectivo de las obligaciones por causa de fallecimiento del PRESTATARIO,
        @if(str_contains($title,'con Cónyuge'))
         la CÓNYUGE ANUENTE se obliga
        @else
            los herederos se obligan
        @endif
        a cumplir con el pago de las amortizaciones de capital e intereses pendientes de pago asumidas en el presente contrato, consiguientemente, 
        la MUSERPOL se reserva el derecho de realizar la compensación de la suma adeudada con el Beneficio del Complemento Económico, que por derecho le corresponde 
        @if(str_contains($title,'con Cónyuge'))
            a la Viuda 
        @else
            a los herederos
        @endif
        por vía sucesión hereditaria, que serán pagados de acuerdo a lo establecido en la cláusula cuarta del presente contrato.
    </div>
    <div>
        <b>DÉCIMA SEGUNDA.- (DE LA MORA):</b> El PRESTATARIO se constituirá en mora automática sin intimación o requerimiento alguno, de acuerdo a lo establecido por el 
        artículo 341, Núm. 1) del Código Civil, al incumplimiento del pago de cualquier amortización de capital o intereses, sin necesidad de intimación o requerimiento 
        alguno, o acto equivalente por parte de la MUSERPOL.
        <br>
        Además del interés acordado contractualmente, el préstamo generará en caso de mora un interés moratorio anual del 
        {{ round($loan->interest->penal_interest) }}% sobre saldos de capital de las cuotas impagas, 
        aún cuando fuere exigible todo el capital del préstamo.  
    </div>
    <div>
        <b>DÉCIMA TERCERA.- (DE LOS EFECTOS DEL INCUMPLIMIENTO Y DE LA ACCIÓN EJECUTIVA):</b> El incumplimiento de pago semestral por parte del PRESTATARIO
        @if(str_contains($title,'con Cónyuge'))
            o la CÓNYUGE ANUENTE
        @endif
        dará lugar a que la totalidad de la obligación, incluidos los intereses moratorios, se determinen líquidos, exigibles y de plazo vencido quedando la MUSERPOL 
        facultada de iniciar las acciones legales correspondientes al amparo de los artículos 519 y 1465 del Código Civil así como de los artículos 378 y 379, Núm. 2) del Código Procesal Civil, 
        que otorgan a este documento la calidad de Título Ejecutivo, demandando el pago de la totalidad de la obligación contraída, más la cancelación de intereses convencionales y penales, 
        incluyendo los relacionados y emergentes de la cobranza judicial, honorarios, derechos, costas y otros, sin excepción por efecto de la acción legal que la MUSERPOL instaure para lograr el cumplimiento total de la obligación.
        <br>
        En caso de incumplimiento del pago semestral estipulados en el presente contrato que generen mora de la obligación, el PRESTATARIO no tendrá derecho a acceder a otro crédito, 
        hasta la cancelación total de la deuda.
    </div>
    <div>
        <b>DÉCIMA CUARTA.- (DOMICILIO ESPECIAL):</b> Para efectos legales, incluida la acción judicial u otra, se tendrá como domicilio especial del PRESTATARIO 
        el señalado en la cláusula primera de conformidad al artículo 29 parágrafo II del Código Civil, donde se efectuarán las citaciones y notificaciones judiciales o cualquier otra comunicación, 
        con plena validez legal y sin lugar a posterior observación o recurso alguno.
    </div>
    <div>
        <b>DÉCIMA QUINTA.- (DE LA CONFORMIDAD Y ACEPTACIÓN):</b> Por una parte en calidad de ACREEDOR la Mutual de Servicios al Policía (MUSERPOL), representada por su 
        {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} y por otra parte en calidad de
        @if (count($lenders) == 1)
        <span>
            PRESTATARI{{ $lender->gender == 'M' ? 'O' : 'A' }} {{ $lender->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $lender->full_name }} de generales ya señaladas;
            @if(str_contains($title,'con Cónyuge'))
                y por otra en calidad de CONYUGE ANUENTE
                @if (count($spouses) == 1)
                    @php $male_female_spouse = Util::male_female($spouse_gender); @endphp
                    <span>
                    {{ $spouse_gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $spouse->full_name }},
                    </span>
                @endif
                de generales ya señaladas, damos nuestra
            @else
                damos
            @endif
            plena conformidad con todas y cada una de las cláusulas precedentes, obligándonos a su fiel y estricto cumplimiento. 
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
            <td width="50%">
            @include('partials.signature_box', [
                'full_name' => $lender->full_name,
                'identity_card' => $lender->identity_card,
                'position' => 'PRESTATARIO'
            ])
            </td>
            @if(str_contains($title,'con Cónyuge'))
                <td width="50%">
                @include('partials.signature_box', [
                    'full_name' => $spouse->full_name,
                    'identity_card' => $spouse->identity_card,
                    'position' => 'CÓNYUGE ANUENTE'
                ])
                </td>
            @endif
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
@endif

</body>
</html>
