<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{$file_title}}</title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>
<body>
    @include('partials.header', $header)
<div class="block">
        <div class="font-semibold leading-tight text-center m-b-10 text-lg">{{ $title }}</div>
        <div class="font-semibold leading-tight text-center m-b-10 text-lg">De {{ Carbon::parse($initial_date)->format('d/m/Y') }} a {{ Carbon::parse($final_date)->format('d/m/Y') }}</div>
</div>
<div class="block">
    @php ( $count_loans = $loans->count() )
    @php ( $c = 0 )
    @php ( $total_amount = 0 )
    @php ( $total_amount2 = 0 )
    @for ($i = 0 ; $i < sizeof($wf_states) ; $i++)
    @php ($count = json_encode($wf_states[$i]->count))
    <div class="darker text-s">{{ $loans[$c]['wf_states'] }}</div>
        <table style="font-size:12px;" class="table-info w-100 text-center uppercase my-10">
            <tr class="bg-grey-darker text-s text-white">
                <td style="font-size:80%;font-weight: bold;">Nro</td>
                <td style="font-size:80%;font-weight: bold;">Nro de Tramite</td>
                <td style="font-size:80%;font-weight: bold;">Fecha de Solicitud</td>
                <td style="font-size:80%;font-weight: bold;">Modalidad</td>
                <td style="font-size:80%;font-weight: bold;">Sub Modalidad</td>
                <td style="font-size:80%;font-weight: bold;">Cat.</td>
                <td style="font-size:80%;font-weight: bold;">Grado</td>
                <td style="font-size:80%;font-weight: bold;">Nombre Completo</td>
                <td style="font-size:80%;font-weight: bold;">C.I.</td>
                <td style="font-size:80%;font-weight: bold;">Usuario</td>
                <td style="font-size:80%;font-weight: bold;">Regional</td>
                <td style="font-size:80%;font-weight: bold;">Fecha de Derivación</td>
                <td style="font-size:80%;font-weight: bold;">Monto Solicitado</td>
                <td style="font-size:80%;font-weight: bold;">Ref.</td>
                <td style="font-size:80%;font-weight: bold;">Liquido Desembolsado</td>
            </tr>
            @for ($j = 0 ; $j < $count ; $j++)
                <tr>
                    <td>{{ $j+1 }}</td>
                    <td>{{ $loans[$c]['code'] }}</td>
                    <td>{{ Carbon::parse($loans[$c]['request_date'])->format('d-m-Y') }}</td>
                    <td>{{ $loans[$c]['modality'] }}</td>
                    <td>{{ $loans[$c]['sub_modality'] }}</td>
                    <td>{{ $loans[$c]['category_name'] }}</td>
                    <td>{{ $loans[$c]['shortened_degree'] }}</td>
                    <td>{{ $loans[$c]['borrower'] }}</td>
                    <td>{{ $loans[$c]['ci_borrower'] }}</td>
                    <td>{{ $loans[$c]['user'] }}</td>
                    <td>{{ $loans[$c]['city'] }}</td>
                    <td>{{ $loans[$c]['derivation_date'] }}</td>
                    <td>{{ Util::money_format($loans[$c]['request_amount']) }}</td>
                    <td>{{ $loans[$c]['ref'] }}</td>
                    <td>{{ Util::money_format($loans[$c]['disbursed_amount']) }}</td>
                    @php ( $total_amount = $total_amount + $loans[$c]['request_amount'] )
                    @php ( $total_amount2 = $total_amount2 + $loans[$c]['disbursed_amount'] )
                    @php ( $c++ )
                </tr>
            @endfor
                <tr class="bg-grey-darker text-s text-white">
                    <td colspan="10"></td>
                    <td style="font-weight: bold;">{{ Util::money_format($total_amount) }}</td>
                    <td></td>
                    <td style="font-weight: bold;">{{ Util::money_format($total_amount2) }}</td>
                </tr>
                @php ( $total_amount = 0 )
                @php ( $total_amount2 = 0 )
        </table>
    @endfor
</div>
</body>
</html>
