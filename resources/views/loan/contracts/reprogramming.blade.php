<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>PLATAFORMA VIRTUAL ADMINISTRATIVA - MUSERPOL </title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>
@include('partials.header', $header)
@php
    if($loan->parent_loan_id != null){
        $oficial_loan = 'parent_loan';
        $comodin = '';
    }else{
        $oficial_loan = 'data_loan';
        $comodin = ' SISMU';
    }
@endphp
<body>
<div class="block">
    <div class="font-semibold leading-tight text-center m-b-10 text-base">
        ADENDA AL CONTRATO DE PRÉSTAMO POR REPROGRAMACIÓN AL CONTRATO 
        {{-- <font style="text-transform: uppercase;">{{$loan->code.$comodin}}</font> --}}
        <font style="text-transform: uppercase;"> {{ $parent_loan->modality->name ?? null }} </font>
        <div>Nº {{ $loan->code }}</div>
    </div>
</div>
<div class="block text-justify">

    <div>
        Conste por la presente Adenda al contrato de préstamo de {{ $title }} que al solo reconocimiento 
        de firmas y rúbricas será elevado a Instrumento Público, por lo que las partes que intervienen lo suscriben al tenor 
        y contenido de las siguientes cláusulas y condiciones:
    </div>
    <div>
        <b>PRIMERA.- (DE LAS PARTES):</b> Intervienen en el presente contrato, por una parte como acreedor la Mutual de 
        Servicios al Policía (MUSERPOL), representada legalmente por el {{ $employees[0]['position'] }}
        {{ $employees[0]['name'] }} con C.I. {{ $employees[0]['identity_card'] }} y su {{ $employees[1]['position'] }} 
        {{ $employees[1]['name'] }} con C.I. {{ $employees[1]['identity_card'] }}, que para fines de este contrato 
        en adelante se denominará MUSERPOL o ACREEDOR con domicilio en la Z. Sopocachi, Av. 6 de Agosto Nº 2354 y por 
        otra parte como PRESTATARIO
        @if (count($lenders) == 1)
            @php 
                $lender = $lenders[0];
                $male_female = Util::male_female($lender->gender);
            @endphp
            <span>
                {{ $lender->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $lender->full_name }}, con C.I. {{ $lender->identity_card }}, 
                {{ $lender->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $lender->city_birth->name }}, 
                vecin{{ $male_female }} de {{ $lender->address->cityName() }} y con domicilio especial en {{ $lender->address->full_address }}, 
                en adelante denominad{{ $male_female }} PRESTATARIO.
            </span>
        @endif
    </div>
    <div>
        <b>SEGUNDA.- (DEL ANTECEDENTE):</b> Mediante contrato de préstamo N° {{ $loan->parent_loan->code }} de fecha 
        {{ Carbon::parse($loan->parent_loan->request_date)->isoFormat('LL') }}
        suscrito entre MUSERPOL y el PRESTATARIO, se otorgó un préstamo por la suma de 
        {{ Util::money_format($loan->parent_loan->amount_approved) }} <span class="uppercase">
        ({{ Util::money_format($loan->parent_loan->amount_approved, true) }}</span> Bolivianos),
        con la generalidad de sus bienes, derechos y acciones habidos y por haber, presentes y @if(count($guarantors)>0)futuros,@else futuros; @endif
        @if(count($guarantors)>0) así como la garantía personal,
        @php $c = 0; @endphp
        @foreach($parent_loan_guarantors as $key => $guarantor)
            <span>
                @if($c > 0) y @endif
                @php $c++; @endphp
            {{ $guarantor->gender == 'M' ? 'el Sr.' : 'la Sra' }} {{ $guarantor->full_name }}, con C.I. {{ $guarantor->identity_card }}, 
            {{ $guarantor->civil_status_gender }}, mayor de edad, hábil por derecho, natural de {{ $guarantor->city_birth->name }}, 
            vecin{{ Util::male_female($guarantor->gender) }} de {{ $guarantor->address->cityName() }} y con domicilio especial en 
            {{ $guarantor->address->full_address }}{{ count($guarantors) > 1 ? " (garante Nº ".($key+1).")" : '' }},
            </span>
        @endforeach
        @endif
        programados a un plazo de {{ $loan->loan_term}} meses de pago para el cumplimiento de obligación, con una 
        amortización mensual de Bs. {{ Util::money_format($loan->parent_loan->estimated_quota) }} (<span class="uppercase">
        {{ Util::money_format($loan->parent_loan->estimated_quota, true) }}</span> Bolivianos). 
        <br>
        El mencionado préstamo mantiene un saldo deudor a reprogramar de Bs. {{ Util::money_format($loan->amount_approved) }} (<span class="uppercase">
        {{ Util::money_format($loan->amount_approved, true) }}</span> Bolivianos) el cual se encuentra en el Kardex entregado por la MUSERPOL al 
        PRESTATARIO en la presente fecha y la cual forma parte de este contrato.
    </div>
    <div>
        <b>TERCERA.- (DEL OBJETO):</b>  El objeto del presente es la suscripción de la adenda modificatoria del contrato señalado en los antecedentes 
        de la cláusula segunda, para lo cual de acuerdo a la solicitud escrita del PRESTATARIO de fecha
        {{ Carbon::parse($loan->request_date)->isoFormat('LL') }}, misma que
        se encuentra respaldada por los documentos adjuntados, la Mutual de Servicios al Policía (MUSERPOL) en estricta sujeción con lo previsto 
        en el art. 67 y 68 del Reglamento de Préstamos, conforme a calificación, previa evaluación y autorización se procede a reprogramar el préstamo 
        descrito en la cláusula precedente bajo los siguientes términos y condiciones que se establecen en la presente Adenda.
        <br>
        Consiguientemente el ACREEDOR y la MUSERPOL acuerdan reprogramar y modificar la obligación original de la CLÁUSULA (PLAZO Y CUOTA DE AMORTIZACIÓN), de acuerdo a lo siguiente:
        <br>
        <b>3.1.- (Plazo)</b>.- Se reprograma el plazo de vigencia del préstamo señalado en la cláusula segunda 
        por el plazo de {{ $loan->loan_term}} meses, computables a partir de la presente Adenda.
        <br>
        <b>3.2.- (Cuota de Amortización)</b>.-  La amortización del pago a capital e intereses mensual y constantes que 
        el prestatario efectuara a partir de la fecha de la suscripción de la presente adenda es de
        Bs. {{ Util::money_format($loan->estimated_quota) }} (<span class="uppercase">{{ Util::money_format($loan->estimated_quota, true) }}</span> Bolivianos). 
    </div>
    <div>
        <b>CUARTA.- (DE LAS CONDICIONES Y CLAUSULAS ACORDADAS):</b> En cuanto a las demás clausulas y condiciones establecidas en el 
        contrato de préstamos señalado en la cláusula segunda de la presente adenda, se mantienen plenamente vigentes y con pleno valor 
        legal siendo de cumplimiento obligatorio para el PRESTATARIO y la MUSERPOL, no admitiendo por tanto ningún tipo de sobre entendimiento, 
        conclusiones e interpretaciones contrarias, constituyéndose la presente de única y exclusiva modiﬁcación de los puntos 3.1 y 3.2 
        señalados en la cláusula precedente, por lo que la presente adenda forma parte integrante e indivisible del contrato antes mencionado.
    </div>
    <div>
        <b>QUINTA.- (DE LA CONFORMIDAD Y ACEPTACIÓN):</b> Por una parte en calidad de ACREEDOR la Mutual de Servicios al Policia MUSERPOL, representada por su 
        {{ $employees[0]['position'] }} {{ $employees[0]['name'] }} y su {{ $employees[1]['position'] }} {{ $employees[1]['name'] }} 
        y por otra parte en calidad de PRESTATARIO
        <span>
            @if (count($lenders) == 1)
                {{ $lender->gender == 'M' ? 'el Sr.' : 'la Sra.' }} {{ $lender->full_name }} de generales ya @if(count($guarantors)>0)señaladas; @else señaladas,  @endif
            @endif
            @if(count($guarantors)>0)
                @if(count($guarantors) > 1)
                    <span>asimismo en calidad de garantes personales</span>
                @else
                    <span>asimismo en calidad de garante personal</span>
                @endif
            @endif
            @foreach($guarantors as $key => $guarantor)
                <span>
                    {{ $guarantor->gender == 'M' ? 'el Sr.' : 'la Sra.' }} {{ $guarantor->full_name }}, con C.I. 
                    {{ $guarantor->identity_card }}, {{ $guarantor->civil_status_gender }}, mayor de edad, hábil por derecho, natural de 
                    {{ $guarantor->city_birth->name }}, vecin{{ Util::male_female($guarantor->gender) }} de {{ $guarantor->address->cityName() }} y con 
                    domicilio especial en {{ $guarantor->address->full_address }}{{ count($guarantors) > 1 ? " (garante Nº ".($key+1).")" : '' }},
                </span>
            @endforeach
            damos plena conformidad con todas y cada una de las cláusulas precedentes, obligándolos a su ﬁel y estricto cumplimiento. 
            En señal de lo cual suscribimos el presente contrato de préstamo de dinero en manifestación de nuestra libre y espontánea voluntad 
            y sin que medie vicio de consentimiento alguno.
        </span>
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
        @php $guarantor = $guarantors[0]; @endphp
        <table>
            <tr>
                <td  with = "50%">
                @include('partials.signature_box', [
                    'full_name' => $lender->full_name,
                    'identity_card' => $lender->identity_card,
                    'position' => 'PRESTATARIO'
                ])
                </td>
                <td  with = "50%">
                @include('partials.signature_box', [
                    'full_name' => $guarantor->full_name,
                    'identity_card' => $guarantor->identity_card,
                    'position' => 'GARANTE'
                ])
                </td>
            </tr>
        </table>
        @else
    </div>
    @if (count($guarantors) == 2)
    <div>
        @include('partials.signature_box', [
            'full_name' => $lender->full_name,
            'identity_card' => $lender->identity_card,
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
                            'identity_card' => $guarantor->identity_card,
                            'position' => 'GARANTE'
                        ])
                    @endforeach
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @else
    <div>
        @include('partials.signature_box', [
            'full_name' => $lender->full_name,
            'identity_card' => $lender->identity_card,
            'position' => 'PRESTATARIO'
        ])
        </div>
    @endif
    @endif
    <div>
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
</body>
</html>