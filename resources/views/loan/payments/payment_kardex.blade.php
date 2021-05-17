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
    </div>


    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DE{{ $plural ? ' LOS' : 'L' }} TITULAR{{ $plural ? 'ES' : ''}}</div>
    </div>

    <div class="block">
        @foreach ($lenders as $lender)
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-xxs text-white">
                <td class="w-50">Solicitante</td>
                <td class="w-15">CI</td>
                <td class="w-15">Matricula</td>
                <td class="w-20">Sector</td>
            </tr>
            <tr>
                <td class="data-row py-5">{{ $lender->title }} {{ $lender->full_name }}</td>
                <td class="data-row py-5">{{ $lender->identity_card_ext }}</td>
                <td class="data-row py-5">{{ $lender->registration }}</td>
                @if($loan->affiliate_state != null)
                    <td class="data-row py-5">{{ $lender->affiliate_state->affiliate_state_type->name }}</td>
                @else
                    <td class="data-row py-5">{{ $lender->affiliate_state->affiliate_state_type->name }}</td>
                @endif
            </tr>
        </table>
        @endforeach
    </div>

    @if ($loan->guarantors()->count())
    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DE{{ $plural ? ' LOS' : 'L' }} GARANTE{{ $plural ? 'S' : ''}}</div>
    </div>

    <div class="block">
        @foreach ($loan->guarantors as $guarantor)
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-xxs text-white">
                <td class="w-70">Garante</td>
                <td class="w-15">CI</td>
                <td class="w-15">Estado</td>
            </tr>
            <tr>
                <td class="data-row py-5">{{ $guarantor->title }} {{ $guarantor->full_name }}</td>
                <td class="data-row py-5">{{ $guarantor->identity_card_ext }}</td>
                <td class="data-row py-5">{{ $guarantor->affiliate_state->affiliate_state_type->name }}</td>
            </tr>
        </table>
        @endforeach
    </div>
    @endif


    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DEL TRÁMITE</div>
    </div>

    <div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-xxs text-white">
                <td class="w-15">Código Tŕamite</td>
                @if ($loan->parent_loan)
                <td class="w-35">Trámite origen</td>
                @endif
                <td class="{{ $loan->parent_loan ? 'w-50' : 'w-50' }}" colspan="{{ $loan->parent_loan ? 1 : 2 }}">Modalidad de trámite</td>
                <td class="w-15">Tasa Anual</td>
                <td class="w-25">Cuota Fija Mensual</td>
            </tr>
            <tr>
                <td class="data-row py-5 m-b-10 text-xs">{{ $loan->code }}</td>
                @if ($loan->parent_loan)
                <td class="data-row py-5 m-b-10 text-xs">{{ $loan->parent_loan->code }}</td>
                @endif
                <td class="data-row py-5 m-b-10 text-xs" colspan="{{ $loan->parent_loan ? 1 : 2 }}">{{ $loan->modality->name }}</td>
                <td class="m-b-10 text-xs">{{ $loan->interest->annual_interest}}</td>
                <td class="m-b-10 text-xs" colspan="2">{{$loan->estimated_quota}}</td>
            </tr>
            <tr class="bg-grey-darker text-xxs text-white">
                <td class="w-25">Plazo</td>
                <td class="w-25">Tipo de Desembolso</td>
                <td class="w-25">Fecha de Desembolso</td>
                <td colspan="2">Monto Desembolsado</td>
            </tr>
            <tr>          
                <td class="data-row py-5 m-b-10 text-xs">{{ $loan->loan_term }} <span class="capitalize">Meses</span></td>
                <td class="data-row py-5 m-b-10 text-xs">
                    @if($loan->payment_type->name=='Deposito Bancario')
                        <div class="font-bold">Cuenta Banco Union</div>
                        <div>{{ $loan->number_payment_type }}</div>
                    @else
                        {{ $loan->payment_type->name}}
                    @endif
                </td>
                <td class="data-row py-5 m-b-10 text-xs" >{{Carbon::parse($loan->disbursement_date)->format('d/m/Y H:m:s')}}</td>
                @if($loan->parent_loan && $loan->parent_reason == "REPROGRAMACIÓN")
                <td colspan="2">{{ Util::money_format($loan->parent_loan->amount_approved) }} <span class="capitalize">Bs.</span></td>
                @else
                <td colspan="2">{{ Util::money_format($loan->amount_approved) }} <span class="capitalize">Bs.</span></td>
                @endif
            </tr>
            <tr class="bg-grey-darker text-xxs text-white">
                <td class="w-25">Certificacion Presupuestaria Contable</td>
                <td colspan="2">Intereses Corrientes Pendientes</td> 
                <td colspan="2">Intereses Penales Pendientes</td>       
            </tr>
            <tr>
            @if($loan->paymentsKardex->first() != null)
                <td class="data-row py-5 m-b-10 text-xs">{{$loan->num_accounting_voucher}}</td>
                <td colspan="2">{{ $loan->paymentsKardex->first()->interest_accumulated}}</td>
                <td colspan="2">{{ $loan->paymentsKardex->first()->penal_accumulated}}</td>
                @else
                <td class="data-row py-5 m-b-10 text-xs">0</td>
                <td colspan="2">0</td>
                <td colspan="2">0</td>
            @endif
            </tr>
        </table>
    </div>

    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. KARDEX DE PAGOS (EXPRESADO EN BOLIVIANOS)</div>
    </div>

    <div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
                @php ($sum_capital_payment = 0)
                @php ($sum_interest_payment = 0)
                @php ($sum_penal_payment = 0)
                <!--@php ($sum_interest_remaining = 0)
                @php ($sum_penal_remaining = 0)-->
                @php ($sum_estimated_quota = 0)
                @php ($res_saldo_capital = 0)
                @php ($sum_capital_payment = 0)
                
                @if($loan->parent_loan_id != null)
            <thead>
                <tr class="bg-grey-darker text-xxs text-white">
                    <th class="w-5">Nº</th>
                    <th class="w-8"><div>Fecha de</div><div>cálculo</div></td>
                    <th class="w-8"><div>Fecha de</div><div>Cobro</div></td>
                    <th class="w-8"><div>Amortización</div><div>capital</div></td>
                    <th class="w-8"><div>Interés</div><div>corriente</div></td>
                    <th class="w-8"><div>Interes</div><div>Penal</div></td>
                    <!--<th class="w-8"><div>Interes Corriente</div><div>Pendiente</div></td>
                    <th class="w-8"><div>Interés Penal</div><div>Pendiente</div></td>-->
                    <th class="w-8"><div>Total Pagado</div></th>
                    <th class="w-8"><div>Saldo</div><div>Capital</div> </th>
                    <th class="w-8"><div>Cbte</div> </th>
                    <th class="w-8"><div>Obs</div> </th>        
                </tr>
            </thead>
            <tbody>
                @php ($capital = $loan->parent_loan->amount_approved)
                @foreach ($loan->parent_loan->paymentsKardex->sortBy('quota_number') as $parent_loan_payment)
                @php ($res_saldo_capital = $capital-$parent_loan_payment->capital_payment)
                <tr>
                    <td class="w-5">{{ $parent_loan_payment->quota_number }}</td>
                    <td class="w-10">{{ Carbon::parse($parent_loan_payment->estimated_date)->format('d/m/Y') }}</td>
                    <td class="w-10">{{ Carbon::parse($parent_loan_payment->pay_date)->format('d/m/Y') }}</td>
                    <td class="w-10 text-right">{{ Util::money_format($parent_loan_payment->capital_payment) }}</td> {{-- capital --}}
                    <td class="w-10 text-right">{{ Util::money_format($parent_loan_payment->interest_payment) }}</td>{{-- interes corriente --}}
                    <td class="w-10 text-right">{{ Util::money_format($parent_loan_payment->penal_payment) }}</td>{{-- interes penal --}}
                    <!--<td class="w-10 text-right">{{ Util::money_format($parent_loan_payment->interest_remaining) }}</td>{{-- Interes acumulados --}}
                    <td class="w-10 text-right">{{ Util::money_format($parent_loan_payment->penal_remaining) }}</td>{{-- Penal verificar --}}-->
                    <td class="w-10 text-right">{{ Util::money_format($parent_loan_payment->estimated_quota) }}</td> {{-- total pagado--}}
                    <td class="w-10 text-right">{{ Util::money_format($res_saldo_capital) }}</td>
                    <td class="w-10 text-right">{{ $parent_loan_payment->voucher }}</td>
                    <td class="w-10">{{ $payment->modality->shortened }}</td>
                </tr>
                @php ($sum_estimated_quota += $parent_loan_payment->estimated_quota)
                @php ($sum_capital_payment += $parent_loan_payment->capital_payment)
                @php ($sum_interest_payment += $parent_loan_payment->interest_payment)
                @php ($sum_penal_payment += $parent_loan_payment->penal_payment)
                <!--@php ($sum_interest_remaining += $parent_loan_payment->interest_remaining )
                @php ($sum_penal_remaining += $parent_loan_payment->penal_remaining)    -->
                @endforeach
                @endif
                <thead>
                <tr class="bg-grey-darker text-xxs text-white">
                    <th class="w-5">Nº</th>
                    <th class="w-8"><div>Fecha de</div><div>cálculo</div></td>
                    <th class="w-8"><div>Fecha de</div><div>Cobro</div></td>
                    <th class="w-8"><div>Amortización</div><div>capital</div></td>
                    <th class="w-8"><div>Interés</div><div>corriente</div></td>
                    <th class="w-8"><div>Interes</div><div>Penal</div></td>
                    <!--<th class="w-8"><div>Interes Corriente</div><div>Pendiente</div></td>
                    <th class="w-8"><div>Interés Penal</div><div>Pendiente</div></td>-->
                    <th class="w-8"><div>Total Pagado</div></th>
                    <th class="w-8"><div>Saldo</div><div>Capital</div> </th>
                    <th class="w-8"><div>Cpte</div> </th>        
                    <th class="w-8"><div>Obs</div> </th>        
                </tr>
            </thead>
                @php ($res_saldo_capital = 0)
                @php ($capital = $loan->amount_approved)
                @foreach ($loan->paymentsKardex->sortBy('quota_number') as $payment)
                @php ($res_saldo_capital = $capital-$payment->capital_payment)
                <tr>
                    <td class="w-5">{{ $payment->quota_number }}</td>
                    <td class="w-10">{{ Carbon::parse($payment->estimated_date)->format('d/m/Y') }}</td>
                    <td class="w-10">{{ Carbon::parse($payment->pay_date)->format('d/m/Y') }}</td>
                    <td class="w-10 text-right">{{ Util::money_format($payment->capital_payment) }}</td> {{-- capital --}}
                    <td class="w-10 text-right">{{ Util::money_format($payment->interest_payment) }}</td>{{-- interes corriente --}}
                    <td class="w-10 text-right">{{ Util::money_format($payment->penal_payment) }}</td>{{-- interes penal --}}
                    <!--<td class="w-10 text-right">{{ Util::money_format($payment->interest_remaining) }}</td>{{-- Dias acumulados --}}
                    <td class="w-10 text-right">{{ Util::money_format($payment->penal_remaining) }}</td>{{-- dias verificar --}}-->
                    <td class="w-10 text-right">{{ Util::money_format($payment->estimated_quota) }}</td> {{-- total pagado--}}
                    <td class="w-10 text-right">{{ Util::money_format($res_saldo_capital) }}</td>
                    <td class="w-10 text-right">{{ $payment->voucher }}</td>
                    <td class="w-10">{{ $payment->modality->shortened }}</td>
                </tr>
                @php ($sum_capital_payment += $payment->capital_payment)
                @php ($sum_interest_payment += $payment->interest_payment)
                @php ($sum_penal_payment += $payment->penal_payment)
                <!--@php ($sum_interest_remaining += $payment->interest_remaining )
                @php ($sum_penal_remaining += $payment->penal_remaining)-->
                @php ($sum_estimated_quota += $payment->estimated_quota)
                @php ($capital = $res_saldo_capital)
                @endforeach
                <tr>
                    <td colspan="3" class="data-row py-2 font-semibold leading-tight text-xs">TOTALES</td>
                    <td class="w-10 text-right">{{ Util::money_format($sum_capital_payment) }}</td>
                    <td class="w-10 text-right">{{ Util::money_format($sum_interest_payment) }}</td>
                    <td class="w-10 text-right">{{ Util::money_format($sum_penal_payment) }}</td>
                    <!--<td class="w-10 text-right">{{ Util::money_format($sum_interest_remaining) }}</td>
                    <td class="w-10 text-right">{{ Util::money_format($sum_penal_remaining) }}</td>-->
                    <td class="w-10 text-right">{{ Util::money_format($sum_estimated_quota) }}</td>
                    <td class="w-10 text-right">{{ Util::money_format($res_saldo_capital) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
