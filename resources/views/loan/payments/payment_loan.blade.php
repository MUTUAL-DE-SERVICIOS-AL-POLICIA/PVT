<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>PLATAFORMA VIRTUAL ADMINISTRATIVA - MUSERPOL </title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>
<body>
    @php ($plural = count($lenders) > 1)
    @php ($n = 1)
    @include('partials.header', $header)

    <div class="block">
        <div class="font-semibold leading-tight text-center m-b-10 text-xs">{{ $title }}</div>
        <div class="font-semibold text-center m-b-3 text-xs">Nro. {{ $loan_payment->code }}</div>
    </div>
    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DEL TRÁMITE</div>
    </div>
    <div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
            <tr  class="bg-grey-darker text-xxs text-white">
                <td class="w-25">Código Tŕamite</td>
                @if ($loan->parent_loan)
                <td class="w-25">Trámite origen</td>
                @endif
                <td class="{{ $loan->parent_loan ? 'w-50' : 'w-75' }}" colspan="{{ $loan->parent_loan ? 2 : 3 }}">Modalidad de trámite</td>
            </tr>
            <tr>
                <td class="data-row py-5">{{ $loan->code }}</td>
                @if ($loan->parent_loan)
                <td class="data-row py-5">{{ $loan->parent_loan->code }}</td>
                @endif
                <td class="data-row py-5" colspan="{{ $loan->parent_loan ? 2 : 3 }}">{{ $loan->modality->name }}</td>
            </tr>
            @foreach ($lenders as $lender)
            <tr class="bg-grey-darker text-xxs text-white">
                <td>Fecha de Desembolso</td>
                <td>DATOS DE{{ $plural ? ' LOS' : 'L' }} TITULAR{{ $plural ? 'ES' : ''}}</td>
                <td>CI</td>
                <td>Matricula</td>
            </tr>
            <tr>
                <td class="data-row py-5">{{ Carbon::parse($loan->disbursement_date)->format('d/m/Y H:i:s' )}}</td>
                <td class="data-row py-5">{{ $lender->title }} {{ $lender->full_name }}</td>
                <td class="data-row py-5">{{ $lender->identity_card_ext }}</td>
                <td class="data-row py-5">{{ $lender->registration }}</td>      
            </tr>
            @endforeach
        </table>
    </div>
        <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DEL PAGO</div>
    </div>
    <div class="block">
        <table class="table-info w-100 my-20 uppercase">
            <tr class="bg-grey-darker text-center text-xxs text-white">   
               <td colspan="4">Pago</td>
           </tr>
            <tr>
                <td class="w-40 font-semibold  text-left">Amortizacion a Capital</td>
                <td class="leading-tight w-10 text-right">{{ Util::money_format($loan_payment->capital_payment) }}</td>
                <td class="w-40 font-semibold text-left">Número de cuota</td>
                <td class="w-10 text-right">{{ $loan_payment->quota_number }}</td>
            </tr>
            <tr class="">
                <td class="w-10 font-semibold ">Intereses corriente por {{ $estimated_days['current']}} dias a ({{ $loan->interest->annual_interest}} % anual)</td>
                <td class="w-10 text-right">{{ Util::money_format($loan_payment->interest_payment) }}</td>
                <td class="w-40 font-semibold ">Fecha de Cálculo</td> 
                <td class="text-right w-10">{{ Carbon::parse($loan_payment->estimated_date)->format('d/m/Y') }}</td>  
                
            </tr>
            <tr class="">
                <td class="w-40 font-semibold ">Intereses Penales a ({{ $loan->interest->penal_interest}} % anual)</td>
                <td class="leading-tight w-10 text-right">{{ Util::money_format($loan_payment->penal_payment) }}</td>
                <td class="w-40 font-semibold ">Fecha de Trans.</td>
                <td class="text-right w-10">{{$loan_payment->loan_payment_date? Carbon::parse($loan_payment->loan_payment_date)->format('d/m/Y'):Carbon::parse($loan_payment->created_at)->format('d/m/Y')}}</td> 
            </tr>
            <tr class="">
                <td class="w-40 font-semibold ">Interes Corriente Pendiente</td>
                <td class="leading-tight w-10 text-right">{{ Util::money_format($loan_payment->interest_remaining) }}</td>
                <td class="w-40 font-semibold" >Intereses Corrientes Pendientes</td>
                <td class="text-right">{{ Util::money_format($loan_payment->interest_accumulated) }}</td>           
            </tr>
            <tr class="">
                <td class="w-40 font-semibold">Interes Penal Pendiente</td>
                <td class="leading-tight w-10 text-right">{{ Util::money_format($loan_payment->penal_remaining) }}</td> 
                <td class="w-40 font-semibold">Intereses Penales Pendientes</td>
                <td class="text-right">{{ Util::money_format($loan_payment->penal_accumulated) }}</td>                      
                </tr>
            <tr class="">
                <td class="font-semibold leading-tight w-40 text-left p-10"><div>Total a Pagar:</div> </td>
                <td class="leading-tight w-10 text-right">{{ Util::money_format($loan_payment->estimated_quota) }}</td>
                <td class="w-40 font-semibold ">Saldo Anterior</td>
                <td class="text-right data-row py-5">{{ Util::money_format($loan_payment->previous_balance) }}</td>
            </tr>
            <tr class="">
                <td colspan="2" class="font-semibold  leading-tight text-right">Son:(<span class="uppercase font-semibold leading-tight  m-b-10 text-xs">{{Util::money_format($loan_payment->estimated_quota, true)}} Bolivianos</span> )</td>
                <td class="w-40 font-semibold ">Saldo Actual</td> 
                <td class="data-row w-10 text-right">{{ Util::money_format($loan_payment->previous_balance-$loan_payment->capital_payment) }}</td>               
            </tr>       
            <tr>
                <td colspan="4" class="text-left p-10"><span class="font-semibold">Glosa:</span> <br>
                    {{$loan_payment->description}}
                </td>
            </tr>
        </table>
    </div>
    <div class="m-t-25 no-page-break">
        <table>
            <tbody>
            <tr height="300px" class="">                  
                <td class="font-semibold leading-tight text-center w-50">                  
                ELABORADO POR
                </td>
                <td class="font-semibold leading-tight text-center w-50">
                PAGADO POR 
                </td>
            </tr>     
            </tbody>
        </table>
        <div class="m-b-10">
            ***Esta liquidación no es válida sin el Refrendo y Sello de Tesorería***</div>
    </div>
</body>

</html>
