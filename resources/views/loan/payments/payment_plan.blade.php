<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{$file_title}}</title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>

<body>
    @php ($plural = false)
    @php ($n = 1)
    @include('partials.header', $header)

    <div class="block">
        <div class="font-semibold leading-tight text-center m-b-10 text-xs">{{ $title }}</div>
    </div>

    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DEL TRÁMITE</div>
    </div>

    <div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-xxs text-white">
                <td class="w-25">Código Tŕamite</td>
                @if ($loan->parent_loan)
                <td class="w-25">Trámite origen</td>
                @endif
                <td class="{{ $loan->parent_loan ? 'w-50' : 'w-75' }}" colspan="{{ $loan->parent_loan ? 1 : 3 }}">Modalidad de trámite</td>
            </tr>
            <tr>
                <td class="data-row py-5">{{ $loan->code }}</td>
                @if ($loan->parent_loan)
                <td class="data-row py-5">{{ $loan->parent_loan->code }}</td>
                @endif
                <td class="data-row py-5" colspan="{{ $loan->parent_loan ? 1 : 3 }}">{{ $loan->modality->name }}</td>
            </tr>
            <tr class="bg-grey-darker text-xxs text-white">
                <td>Monto Desembolsado</td>
                <td>Plazo</td>
                <td>Tipo de Desembolso</td>
                <!--<td>Fecha de Desembolso</td>-->

            </tr>
            <tr>
                @if ($loan->parent_reason == 'REPROGRAMACIÓN')
                    <td class="data-row py-5"> {{ Util::money_format($loan->parent_loan->amount_approved)}} <span class="capitalize">Bs.</span></td>
                @else
                    <td class="data-row py-5">{{ Util::money_format($loan->amount_approved) }} <span class="capitalize">Bs.</span></td>
                @endif
                @if ($loan->modality->procedure_type_id != 29)
                    @php ($term_text = $loan->loan_term == 1 ? 'mes' : 'meses')
                @else
                    @php ($term_text = $loan->loan_term == 1 ? 'semestre' : 'semestres')
                @endif
                <td class="data-row py-5">{{ $loan->loan_term }} <span class="capitalize">{{ $term_text }}</span></td>
                <td class="data-row py-5">
                    @if($loan->payment_type->name=='Deposito Bancario')
                        <div class="font-bold">Cuenta Entidad financiera</div>
                        <div>{{ $loan->number_payment_type }}</div>
                    @else
                        @if($loan->parent_reason == 'REPROGRAMACIÓN')
                            {{ $loan->parent_loan->payment_type->name }}
                        @else
                            {{ $loan->payment_type->name }}
                        @endif
                    @endif
                </td>
                <!--<td class="data-row py-5">{{ Carbon::parse($loan->disbursement_date)->format('d/m/y') }}</td>-->
            </tr>
            <tr class="bg-grey-darker text-xxs text-white">
                <td>Tasa Anual (%)</td>
                <td>Cuota Fija</td>
                <td>Fecha de Desembolso</td>
            </tr>
            <tr>
                <td>{{ $loan->interest->annual_interest}}</td>
                <td>{{ Util::money_format($loan->estimated_quota) }}</td>
                @if ($loan->parent_reason == 'REPROGRAMACIÓN')
                    <td>{{ Carbon::parse($loan->parent_loan->disbursement_date)->format('d/m/Y H:i:s') }}</td>
                @else
                    <td>{{ Carbon::parse($loan->disbursement_date)->format('d/m/Y H:i:s') }}</td>
                @endif
            </tr>
            <tr class="bg-grey-darker text-xxs text-white">
                <td>Certificación Presupuestaria contable</td>
                @if($loan->parent_reason == 'REPROGRAMACIÓN')
                    <td>Monto Reprogramado</td>
                    <td>Fecha de Reprogramación</td>
                @endif
            </tr>
            <tr>
                @if ($loan->parent_reason == 'REPROGRAMACIÓN')
                    <td>{{$loan->parent_loan->num_accounting_voucher ? $loan->parent_loan->num_accounting_voucher: 0}}</td>
                    <td>{{ Util::money_format($loan->amount_approved) }} <span class="capitalize">Bs.</span></td>
                    <td>{{ Carbon::parse($loan->disbursement_date)->format('d/m/Y H:i:s') }}</td>
                @else
                    <td>{{$loan->num_accounting_voucher ? $loan->num_accounting_voucher: 0}}</td>
                @endif
            </tr>
        </table>
    </div>

    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. DATOS DE{{ $plural ? ' LOS' : 'L' }} TITULAR{{ $plural ? 'ES' : ''}}</div>
    </div>

    <div class="block">
        
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-xxs text-white">
                <td class="w-60">Solicitante</td>
                <td class="w-15">CI</td>
                <td class="w-10">Estado</td>
            </tr>
            <tr>
                <td class="data-row py-5">{{ $lender->title }} {{ $lender->full_name }}</td>
                <td class="data-row py-5">{{ $lender->identity_card_ext }}</td>
                @if(!$is_dead)
                <td class="data-row py-5">{{ $lender->affiliate_state->affiliate_state_type->name }}</td>
                @else
                <td class="data-row py-5">PASIVO</td>
                @endif
            </tr>
        </table>
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
        <div class="font-semibold leading-tight text-left m-b-10 text-xs">{{ $n++ }}. PLAN DE PAGOS (EXPRESADO EN BOLIVIANOS)</div>
    </div>
    <div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
            <thead>
                <tr class="bg-grey-darker text-xxs text-white">
                    <th class="w-10">Nº</th>
                    <th class="w-15">Fecha</td>
                    <th class="w-15"><div>Días</div><div>Amr</div></td>
                    <th class="w-15"><div>Capital</div></td>
                    <th class="w-15">Amortización </div><div>Interés</td>
                    <th class="w-15">Penal</td>
                    <th class="w-15"><div>Total a</div><div>Pagar</div></td>
                    <th class="w-15"><div>Saldo</div><div>capital</div></th>
                    <!--<th class="w-15"><div>Interes</div><div>Acumulado</div></th>-->
                </tr>
            </thead>
            <tbody>
                @php ($sum_capital = 0)
                @php ($sum_interest = 0)
                @php ($sum_estimated_quota = 0)
                @php ($sum_days_amr = 0)
                @php ($sw = 0)
                @php ($aux = 0)
                @foreach ($loan->loan_plan as $quota)
                <tr>
                    <td class="data-row py-2">{{ $quota->quota_number }}</td>
                    <td class="data-row py-2">{{ Carbon::parse($quota->estimated_date)->format('d/m/Y') }}</td>
                    <td class="data-row py-2">{{ $quota->days }}</td>
                    <td class="data-row py-2">{{ Util::money_format($quota->capital) }}</td>
                    <td class="data-row py-2">{{ Util::money_format($quota->interest) }}</td>
                    <td class="data-row py-2">{{ Util::money_format(0) }}</td>
                    <td class="data-row py-2">{{ Util::money_format($quota->total_amount) }}</td>
                    <td class="data-row py-2">{{ Util::money_format($quota->balance) }}</td>
                </tr>
                @php ($sum_estimated_quota += $quota->total_amount)
                @php ($sum_days_amr += $quota->days)
                @php ($sum_capital += $quota->capital)
                @php ($sum_interest += $quota->interest)
                @endforeach
                <tr>
                    <td colspan="2" class="data-row py-2 font-semibold leading-tight text-xs">TOTALES</td>
                    
                    <td class="data-row py-2">{{$sum_days_amr}}</td>
                    <td class="data-row py-2">{{ Util::money_format($sum_capital) }}</td>
                    <td class="data-row py-2">{{ Util::money_format($sum_interest) }}</td>
                    <td class="data-row py-2">{{ Util::money_format(0) }}</td>
                    <td class="data-row py-2">{{ Util::money_format($sum_estimated_quota) }}</td>
                    <td class="data-row py-2">{{ Util::money_format(0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="block">
        <div class="font leading-tight text-left m-b-10 text-xs">
            LA PRESENTE TABLA DE AMORTIZACIÓN ES REFERENCIAL YA QUE LA MISMA PODRÍA SUFRIR ALTERACIONES EN FUNCIÓN A LA VARIACIÓN DEL PAGO DE CUOTAS Y/O EN LAS FECHAS DE PAGOS ESTABLECIDAS; POR TANTO, CUALQUIER ALTERACIÓN DEJA SIN EFECTO ESTE DOCUMENTO.
            <p>
            EN CASO DE TENER ALGUNA CONSULTA, FAVOR APERSONARSE POR EL ÁREA DE COBRANZAS
        </div>
    </div>
<?php ?>
</body>
</html>
