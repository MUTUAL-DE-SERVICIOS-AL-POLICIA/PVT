<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>PLATAFORMA VIRTUAL ADMINISTRATIVA - MUSERPOL </title>
    <link rel="stylesheet" href="{{ public_path("/css/report-print.min.css") }}" media="all"/>
</head>
<body>
    @include('partials.header', $header)
<div class="block">
        <div class="font-semibold leading-tight text-center m-b-10 text-lg">FORMULARIO DE CALIFICACIÓN Y APROBACIÓN DE PRÉSTAMO</div>
        @php ($lender = $lenders[0]->disbursable)
</div>
@php ($n = 1)

<div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-base">{{ $n++ }}. DATOS DEL TRÁMITE {{$Loan_type_title}}</div>
    </div>

<div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-sm-1 text-white">
                <td class="w-25">Código Tŕamite</td>
                @if ($loan->parent_loan || $loan->parent_reason)
                <td class="w-25">Trámite origen</td>
                @endif
                <td class="{{ $loan->parent_loan ? 'w-50' : 'w-75' }}" colspan="{{ $loan->parent_loan ? 1 : 2 }}">Modalidad de trámite</td>
            </tr>
            <tr>
                <td class="data-row py-5">{{ $loan->code }}</td>
                @if ($loan->parent_loan)
                <td class="data-row py-5">{{ $loan->parent_loan->code }}</td>
                @endif
                @if ($loan->parent_reason && !$loan->parent_loan_id)
                <td class="data-row py-5">{{ $loan->data_loan->code }}</td>
                @endif
                <td class="data-row py-5" colspan="{{ $loan->parent_loan ? 1 : 2 }}">@if($loan->parent_reason == "REPROGRAMACIÓN") {{$loan->parent_reason}} @endif {{ $loan->modality->name }}</td>
            </tr>
            <tr class="bg-grey-darker text-sm-1 text-white">
                <td>Monto solicitado</td>
                <td>Plazo</td>
                <td>Tipo de Desembolso</td>
            </tr>
            <tr>
                <td class="data-row py-5">{{ Util::money_format($loan->amount_requested) }} <span class="capitalize">Bs.</span></td>
                <td class="data-row py-5">{{ $loan->loan_term }} <span class="capitalize">Meses</span></td>
                <td class="data-row py-5">
                    @if($loan->payment_type->name=='Deposito Bancario')
                        <div class="font-bold">Cuenta Banco Union</div>
                        <div>{{ $loan->number_payment_type }}</div>
                    @else
                        {{ $loan->payment_type->name}}
                    @endif
                </td>
            </tr>
        </table>
    </div>
    @if($Loan_type_title == "REFINANCIAMIENTO" || $Loan_type_title == "SISMU REFINANCIAMIENTO")
    <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-sm-1 text-white">
                <td colspan="2" >REFINANCIAMIENTO DE PRÉSTAMO</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">FECHA DE SALDO DEUDOR </td>
            <td class="w-50 text-left px-10">{{Carbon::parse($loan->date_cut_refinancing())->format('d/m/y')}}
            </td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">SALDO DEUDOR EN Bs. </td>
            <td class="w-50 text-left px-10">{{Util::money_format($loan->balance_parent_refi())}}</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">MONTO REFINANCIAMIENTO (CHEQUE) EN Bs.</td>
            <td class="w-50 text-left px-10">{{Util::money_format($loan->refinancing_balance)}}</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">TOTAL NUEVO MONTO DE PRÉSTAMO</td>
            <td class="w-50 text-left px-10">{{Util::money_format($loan->amount_approved)}}</td>
            </tr> 
        </table>
        @endif
    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-base">{{ $n++ }}. DATOS DE BOLETA</div>
    </div>
<div class="block">
        <table class="table-info w-100 text-center uppercase my-20">        
        @php ($sum_prom_payable_liquid_calculated = 0)
        @php ($sum_prom_bonus_calculated = 0)
        @php ($count_lender = 0)
        @foreach($loan->lenders as $lender_affiliate)
            @php ($count_lender = $count_lender + 1)
            @php ($title_lender = $count_lender == 1 ? "TITULAR":"CODEUDOR")    
        <tr class="bg-grey-darker text-sm-1 text-white">
            <td class="w-50 text-left px-10" colspan="7" > {{$title_lender}} {{$lender_affiliate->full_name}} </td>
        </tr>
            @php ($sum_prom_payable_liquid_calculated += $lender_affiliate->pivot->payable_liquid_calculated)
            @php ($sum_prom_bonus_calculated += $lender_affiliate->pivot->bonus_calculated)
        @if($lender_affiliate->pivot->contributionable_type == "contributions")
            <tr>
                <td class="bg-grey-darker text-sm-1 text-white">Periodo</td>
                <td class="bg-grey-darker text-sm-1 text-white">Liquido</td>
                <td class="bg-grey-darker text-sm-1 text-white">Monto de Ajuste</td>
                <td class="bg-grey-darker text-sm-1 text-white">Bono Frontera</td>
                <td class="bg-grey-darker text-sm-1 text-white">Bono Cargo</td>
                <td class="bg-grey-darker text-sm-1 text-white">Bono Oriente</td>
                <td class="bg-grey-darker text-sm-1 text-white">Bono Seguridad Ciudadana</td>       
           </tr>        
            @php ($sum_border_bonus = 0)
            @php ($sum_position_bonus = 0)
            @php ($sum_east_bonus = 0)
            @php ($sum_public_security_bonus = 0)
            @php ($sum_payable_liquid = 0)
            @php ($sum_mount_adjust= 0)
            @php ($num_reg = 0)
         @foreach($loan->ballot_affiliate($lender_affiliate->id)->ballot as $ballot)
            @php ($mount_adjust = 0)
         
           <tr>
                <td>{{Carbon::parse($ballot->month_year)->format('d/m/y')}}</td>
                <td> {{Util::money_format($ballot->payable_liquid)}}</td>
                    @foreach($loan->ballot_affiliate($lender_affiliate->id)->adjusts as $adjust)
                    @if($ballot->id == $adjust->adjustable_id)
                    @php($mount_adjust = $adjust->amount)
                    @endif
                    @endforeach
                <td> {{Util::money_format($mount_adjust)}}</td>  
                <td> {{Util::money_format($ballot->border_bonus)}}</td>
                <td> {{Util::money_format($ballot->position_bonus)}}</td>
                <td> {{Util::money_format($ballot->east_bonus)}}</td>
                <td> {{Util::money_format($ballot->public_security_bonus)}}</td>
                        
           </tr >
           @php ($num_reg = $num_reg + 1)
           @php ($sum_border_bonus += $ballot->border_bonus)
           @php ($sum_position_bonus += $ballot->position_bonus)
           @php ($sum_east_bonus += $ballot->east_bonus)
           @php ($sum_public_security_bonus += $ballot->public_security_bonus)
           @php ($sum_payable_liquid += $ballot->payable_liquid)
           @php ($sum_mount_adjust += $mount_adjust)         
            @endforeach
           <tr>
                <td>Total Promedio</td>
                <td> {{Util::money_format($sum_payable_liquid/$num_reg)}}</td>
                <td> {{Util::money_format($sum_mount_adjust/$num_reg)}}</td>
                <td> {{Util::money_format($sum_border_bonus/$num_reg)}}</td>
                <td> {{Util::money_format($sum_position_bonus/$num_reg)}}</td>
                <td> {{Util::money_format($sum_east_bonus/$num_reg)}}</td>
                <td> {{Util::money_format($sum_public_security_bonus/$num_reg)}}</td>
              
           </tr>
        @endif
       @if($lender_affiliate->pivot->contributionable_type =="aid_contributions")
            <tr>
                <td class="bg-grey-darker text-sm-1 text-white">Periodo</td>
                <td class="bg-grey-darker text-sm-1 text-white">Liquido</td>
                <td class="bg-grey-darker text-sm-1 text-white">Monto de Ajuste</td>
                <td class="bg-grey-darker text-sm-1 text-white">Bono Renta Dignidad</td>
              
           </tr>
            @php ($sum_dignity_rent = 0)
            @php ($sum_rent = 0)
            @php ($sum_mount_adjust_aid = 0)
            @php ($num_reg = 0)
            @foreach($loan->ballot_affiliate($lender_affiliate->id)->ballot as $ballot)
            @php ($mount_adjust_aid = 0)
            <tr>
                <td>{{Carbon::parse($ballot->month_year)->format('d/m/y')}}</td> 
                <td> {{Util::money_format($ballot->rent)}}</td> 
                @foreach($loan->ballot_affiliate($lender_affiliate->id)->adjusts as $adjust)
                    @if($ballot->id == $adjust->adjustable_id)
                    @php($mount_adjust_aid=$adjust->amount)
                    @endif
                    @endforeach
                <td>{{Util::money_format($mount_adjust_aid)}}</td>  
                <td> {{Util::money_format($ballot->dignity_rent)}}</td>                 
            </tr >  
           @php ($num_reg = $num_reg + 1)
           @php ($sum_dignity_rent += $ballot->dignity_rent)
           @php ($sum_rent += $ballot->$ballot->rent)
           @php ($sum_mount_adjust_aid += $mount_adjust_aid)      
          @endforeach
            <tr>
                <td>Total Promedio</td>
                <td> {{Util::money_format($sum_rent/$num_reg)}}</td>
                <td> {{Util::money_format($sum_mount_adjust_aid/$num_reg)}}</td>
                <td> {{Util::money_format($sum_dignity_rent/$num_reg)}}</td>             
             </tr>
        @endif
        @if($lender_affiliate->pivot->contributionable_type == "loan_contribution_adjusts")
           <tr>
                <td class="bg-grey-darker text-sm-1 text-white">Periodo</td>
                <td class="bg-grey-darker text-sm-1 text-white">Liquido</td>
                <td class="bg-grey-darker text-sm-1 text-white">Monto de Ajuste</td>
           </tr>
           @php ($sum_liquid_amount = 0)
           @php ($sum_mount_adjust = 0)
           @php ($num_reg = 0)
           @foreach($loan->ballot_affiliate($lender_affiliate->id)->ballot as $ballot)
           @php ($mount_adjust = 0)         
           <tr>
                <td>{{Carbon::parse($ballot->period_date)->format('d/m/y')}}</td>  
                <td>{{Util::money_format($ballot->amount)}}</td> 
                @foreach($loan->ballot_affiliate($lender_affiliate->id)->adjusts as $adjust)
                    @if($ballot->period_date == $adjust->period_date)
                    @php($mount_adjust = $adjust->amount)
                    @endif
                    @endforeach
                <td>{{Util::money_format($mount_adjust)}}</td>            
            </tr >
           @php ($num_reg = $num_reg + 1)
           @php ($sum_liquid_amount += $ballot->amount)
           @php ($sum_mount_adjust += $mount_adjust)    
           @endforeach
            <tr>
                <td>Total Promedio</td>
                <td> {{Util::money_format($sum_liquid_amount/$num_reg)}}</td> 
                <td> {{Util::money_format($sum_mount_adjust/$num_reg)}}</td>            
             </tr>
        @endif
        @endforeach
            </table>
            <table class="table-info w-100 text-center uppercase my-20"> 
            <tr class="bg-grey-darker text-sm-1 text-white">
            <td colspan="2" class="w-100">PROMEDIO DE LA BOLETA</td>
            </tr>
            <tr >
            <td class="w-50 text-left px-10">TOTAL PROMEDIO LÍQUIDO PAGABLE</td>
            <td class="w-50 text-left px-10">{{ $sum_prom_payable_liquid_calculated}} </td>
            </tr>
            <tr >
            <td class="w-50 text-left px-10">TOTAL PROMEDIO BONOS</td>
            <td class="w-50 text-left px-10">{{ $sum_prom_bonus_calculated}}</td>
            </tr>
            <tr >
            <td class="w-50 text-left px-10">LÍQUIDO PARA CALIFICACIÓN</td>
            <td class="w-50 text-left px-10">{{ $loan->liquid_qualification_calculated }}</td>
            </tr>
        </table>
    </div>
   </div>
   <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-base">{{ $n++ }}. DATOS DE EVALUACIÓN AL TITULAR</div>
    </div>
    <div class="block">
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-sm-1 text-white">
                <td colspan="2" >PROPUESTA Y APROBACIÓN </td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">NOMBRES</td>
            <td class="w-50 text-left px-10">{{ $lender->full_name }}
            </td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">INTERÉS CORRIENTE</td>
            <td class="w-50 text-left px-10">{{ $loan->interest->annual_interest }} % Anual</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">INTERÉS PENAL</td>
            <td class="w-50 text-left px-10">{{ $loan->interest->penal_interest }} % Anual</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">LÍQUIDO PARA CALIFICACIÓN</td>
            <td class="w-50 text-left px-10">{{ $loan->liquid_qualification_calculated }}</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">MONTO DEL PRÉSTAMO AUTORIZADO</td>
            <td class="w-50 text-left px-10">{{ $loan->amount_approved }}</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">PLAZO EN MESESES</td>
            <td class="w-50 text-left px-10">{{ $loan->loan_term }}</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">CUOTA MENSUAL</td>
            <td class="w-50 text-left px-10">{{ $loan->estimated_quota }}</td>
            </tr>
            <tr  class="w-100">
            <td class="w-50 text-left px-10">ÍNDICE DE ENDEUDAMIENTO</td>
            <td class="w-50 text-left px-10">{{ $loan->indebtedness_calculated }} </td>
            </tr>      
        </table>
    </div>
    @php($plural = $loan->guarantors()->count() > 1)
    @php ($num_gar = 1)
    @if ($loan->guarantors()->count())
    <div class="block">
        <div class="font-semibold leading-tight text-left m-b-10 text-base">{{ $n++ }}. DATOS DE EVALUACIÓN DE{{ $plural ? ' LOS' : 'L' }} GARANTE{{ $plural ? 'S' : ''}}</div>
    </div>
    <div class="block ">
        @foreach ($loan->guarantors as $guarantor)
        <table class="table-info w-100 text-center uppercase my-20">
            <tr class="bg-grey-darker text-sm-1 text-white">
                    <td class="w-100" colspan="2">Garante {{ $num_gar++ }}</td>               
            </tr>
            <tr  class="w-100">
                <td class="w-50 text-left px-10">NOMBRES</td>
                <td class="w-50 text-left px-10">{{ $guarantor->full_name }}</td> 
            </tr>
            <tr  class="w-100">
                <td class="w-50 text-left px-10">LÍQUIDO PARA CALIFICACIÓN</td>
                <td class="w-50 text-left px-10">{{ $guarantor->pivot->liquid_qualification_calculated }}</td> 
            </tr>
            <tr  class="w-100">
                <td class="w-50 text-left px-10">ÍNDICE DE ENDEUDAMIENTO</td>
                <td class="w-50 text-left px-10">{{ $guarantor->pivot->indebtedness_calculated }} </td> 
            </tr>
            <tr  class="w-100">
                <td class="w-50 text-left px-10">PORCENTAJE DE PAGO</td>
                <td class="w-50 text-left px-10">{{ $guarantor->pivot->payment_percentage }} %</td> 
            </tr>
        </table>
        @endforeach
    </div>
    @endif
    <table class="text-center">
            <tbody>
                <tr class="align-top">                  
                <td width="35%">                  
                ELABORADO POR
                </td>
                <td width="35%">
                REVISADO POR 
                </td>
                <td width="30%">
                APROBADO POR
                </td>
                </tr>             
            </tbody>
        </table>
</body>
</html>