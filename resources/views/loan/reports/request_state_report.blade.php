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
</div>
<div class="block">
    @php ($wf_states = "")
    @php ($c = 0)
    @php ($cantidad = 0)
    @php ($amount = 0)
    @php ($amount_dirbursement = 0)
    @php ($total = 0)
    @php ($total_dirbursement = 0)
    <table style="font-size:15px;" class="table-info w-100 text-center uppercase my-10">
        <tr class="bg-grey-darker text-s text-white">
            <td style="font-size:80%;font-weight: bold;">Nro Prestamo</td>
            <td style="font-size:80%;font-weight: bold;">Procedencia</td>
            <td style="font-size:80%;font-weight: bold;">Fecha Sol.</td>
            <td style="font-size:80%;font-weight: bold;">Solicitante</td>
            <td style="font-size:80%;font-weight: bold;">C.I.</td>
            <td style="font-size:80%;font-weight: bold;">Estado Flujo</td>
            <td style="font-size:80%;font-weight: bold;">Fecha derivacion</td>
            <td style="font-size:80%;font-weight: bold;">Usuario</td>
            <td style="font-size:80%;font-weight: bold;">Monto Solicitado</td>
            <td style="font-size:80%;font-weight: bold;">Monto desembolsado</td>
        </tr>
        @foreach ( $loans as $loan )
            @php ($cantidad += 1)
            @if($wf_states == $loan['wf_states'] || $wf_states == "")
                <tr>
                    <td style="font-size:60%;">{{ $loan['code'] }}</td>
                    <td style="font-size:60%;">{{ $loan['procedence'] }}</td>
                    <td style="font-size:60%;">{{ $loan['request_date'] }}</td>
                    @foreach($loan['lenders'] as $lender)
                        <td style="font-size:60%;">{{ $lender->full_name }}</td>
                        <td style="font-size:60%;">{{ $lender->identity_card }}</td>
                    @endforeach
                    <td style="font-size:60%;">{{ $loan['wf_states'] }}</td>
                    <td style="font-size:60%;">{{ $loan['update_date'] }}</td>
                    <td style="font-size:60%;">{{ $loan['user'] }}</td>
                    <td style="font-size:60%;">{{ Util::money_format($loan['amount']) }}</td>
                    <td style="font-size:60%;">{{ Util::money_format($loan['amount_dirbursement']) }}</td>
                </tr>
                @php ($wf_states = $loan['wf_states'])
                @php ($c += 1)
                @php ($amount += $loan['amount'])
                @php ($amount_dirbursement += $loan['amount_dirbursement'])
            @else
                <tr class="bg-grey-darker text-s text-white">
                    <td style="font-size:60%;font-weight: bold;" colspan="3">Cantidad Por Estado de Flujo</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2" align="right">{{ $c }}</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2">Sub Total por Estado de Flujo</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2" align="right">{{ Util::money_format($amount) }}</td>
                    <td style="font-size:60%;font-weight: bold;">{{ Util::money_format($amount_dirbursement) }}</td>
                </tr>
                <tr>
                    <td style="font-size:60%;">{{ $loan['code'] }}</td>
                    <td style="font-size:60%;">{{ $loan['procedence'] }}</td>
                    <td style="font-size:60%;">{{ $loan['request_date'] }}</td>
                    @foreach($loan['lenders'] as $lender)
                        <td style="font-size:60%;">{{$lender->full_name}}</td>
                        <td style="font-size:60%;">{{$lender->identity_card}}</td>
                    @endforeach
                    <td style="font-size:60%;">{{ $loan['wf_states'] }}</td>
                    <td style="font-size:60%;">{{ $loan['update_date'] }}</td>
                    <td style="font-size:60%;">{{ $loan['user'] }}</td>
                    <td style="font-size:60%;">{{ Util::money_format($loan['amount']) }}</td>
                    <td style="font-size:60%;">{{ Util::money_format($loan['amount_dirbursement']) }}</td>
                </tr>
                @php ($total = $total+$amount)
                @php ($total_dirbursement = $total_dirbursement + $amount_dirbursement)
                @php ($wf_states = $loan['wf_states'])
                @php ($c = 1)
                @php ($amount = $loan['amount'])
                @php ($amount_dirbursement = $loan['amount_dirbursement'])

            @endif
        @endforeach
        <tr class="bg-grey-darker text-s text-white"">
                    <td style="font-size:60%;font-weight: bold;" colspan="3">Cantidad Por Estado de Flujo</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2" align="right">{{ $c }}</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2">Sub Total por Estado de Flujo</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2" align="right">{{ Util::money_format($amount) }}</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="1" align="right">{{ Util::money_format($amount_dirbursement) }}</td>
        </tr>
        @php ($total = $total+$amount)
        @php ($total_dirbursement = $total_dirbursement+$amount_dirbursement)
        <tr class="bg-grey-darker text-s text-white">
                    <td style="font-size:60%;font-weight: bold;" colspan="5">*** TOTAL GENERAL ***</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2" align="right">{{ $cantidad }}</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2" align="right">{{ Util::money_format($total) }}</td>
                    <td style="font-size:60%;font-weight: bold;" colspan="2" align="right">{{ Util::money_format($total_dirbursement) }}</td>
        </tr>
    </table>
</div>
</body>
</html>
