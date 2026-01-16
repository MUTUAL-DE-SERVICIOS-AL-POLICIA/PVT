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
        <div class="font-semibold leading-tight text-center m-b-10 text-lg">FICHA DE REGISTRO DE GARANTIAS</div>
</div>
@php $num=1; @endphp
<div>
    @foreach($data_loan_guarantors as $guarantor)
    <div class="font-semibold leading-tight m-b-10 text-lg">{{$num}}. DATOS DEL GARANTE {{ $num }}</div>
    @php $num++; @endphp
    <br>
        <div>
            <table class="table-info w-100 text-center uppercase my-10">
                <tr class="bg-grey-darker text-white">
                    <td class="w-50 text-left px-10">GARANTE</td>
                    <td class="w-50 text-left px-10">C.I.</td>
                    <td class="w-50 text-left px-10">CATEGORÍA</td>
                    <td class="w-50 text-left px-10">ESTADO</td>
                </tr>
                <tr>
                    <td class="w-50 text-left px-10">{{ $guarantor->full_name }}</td>
                    <td class="w-50 text-left px-10">{{ $guarantor->identity_card }}</td>
                    <td class="w-50 text-left px-10">{{ $guarantor->category }}</td>
                    <td class="w-50 text-left px-10">{{ $guarantor->state }}</td>
                </tr>
            </table>
        </div>
        <br>
        <div class="font-semibold leading-tight m-b-10 text-lg" style="padding-left: 40px;">PRESTAMOS GARANTIZADOS</div>
        
        @if(count($guarantor->guarantor_loans) == 0)
            <div>Sin Garantias</div>
        @else
            <div>
                <table style="font-size:12px;" class="table-info w-100 text-center uppercase my-10">
                    <tr class="bg-grey-darker text-white">
                        <td class="w-50 text-left px-10">CODIGO</td>
                        <td class="w-50 text-left px-10">TITULAR</td>
                        <td class="w-50 text-left px-10">CI</td>
                        <td class="w-50 text-left px-10">MATRICULA</td>
                        <td class="w-50 text-left px-10">MODALIDAD</td>
                        <td class="w-50 text-left px-10">FECHA DE SOLICITUD</td>
                        <td class="w-50 text-left px-10">FECHA DE DESEMBOLSO</td>
                        <td class="w-50 text-left px-10">MONTO SOLICITADO</td>
                        <td class="w-50 text-left px-10">PLAZO</td>
                        <td class="w-50 text-left px-10">CUOTA</td>
                        <td class="w-50 text-left px-10">SALDO</td>
                        <td class="w-50 text-left px-10">TIPO TRAMITE</td>
                        <td class="w-50 text-left px-10">ESTADO</td>
                    </tr>
                    @foreach($guarantor->guarantor_loans as $loan)
                        <tr>
                            <td class="w-50 text-left px-10">{{ $loan->code }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->lender_full_name }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->lender_identity_card }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->lender_registration }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->modality }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->request_date }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->disbursement_date }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->amount_approved }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->term }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->estimated_quota }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->balance }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->type }}</td>
                            <td class="w-50 text-left px-10">{{ $loan->state }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
        <br>
        <br>
    @endforeach
</div>
</body>
</html>
