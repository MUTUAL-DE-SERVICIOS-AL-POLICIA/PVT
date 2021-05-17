<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use App\LoanPayment;
use App\Loan;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;
use App\Exports\SheetExportPayment;
use App\Exports\MultipleSheetExportPayment;
use Illuminate\Support\Facades\Storage;
//use App\Exports\SheetExportPayment;

class LoanPaymentReportController extends Controller
{
   /** @group Reportes de amortizaciones 
   * Reporte de amortizaciones de descuentos Titular - Garante 
   * Reporte de amortizaciones de descuentos Titular - Garante 
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/reports_amortizations/dincuent_months_t_g.200.json
   */
    public function report_amortization_discount_months(Request $request){
        // aumenta el tiempo máximo de ejecución de este script a 150 min:
        ini_set('max_execution_time', 9000);
        // aumentar el tamaño de memoria permitido de este script:
        ini_set('memory_limit', '960M');

        $order_loan = 'Desc';
        
        $conditions = [];
        //filtros
        $initial_date = request('initial_date') ?? '';
        $final_date = request('final_date') ?? '';
        $state_pagado='Pagado';

        if ($initial_date != '') {
          array_push($conditions, array('loan_payments.estimated_date', '>=', "%{$initial_date}%"));
        }
        if ($final_date != '') {
          array_push($conditions, array('loan_payments.estimated_date', '<=', "%{$final_date}%"));
        }
        array_push($conditions, array('loan_payment_states.name', 'ilike', "%{$state_pagado}%"));
            
                    $list_loan = DB::table('loan_payments')
                    ->join('procedure_modalities','loan_payments.procedure_modality_id', '=', 'procedure_modalities.id')
                    ->join('procedure_types','procedure_modalities.procedure_type_id', '=', 'procedure_types.id')
                    ->join('loan_payment_states','loan_payments.state_id', '=', 'loan_payment_states.id')
                    ->join('affiliates','loan_payments.affiliate_id', '=', 'affiliates.id')
                    ->leftjoin('spouses','affiliates.id', '=', 'spouses.affiliate_id')
                    ->join('affiliate_states','affiliates.affiliate_state_id', '=', 'affiliate_states.id')
                    ->join('affiliate_state_types','affiliate_states.affiliate_state_type_id', '=', 'affiliate_state_types.id')
                    ->leftjoin('pension_entities','affiliates.pension_entity_id', '=', 'pension_entities.id')
                    ->join('loans','loan_payments.loan_id', '=', 'loans.id')
                    ->leftJoin('vouchers','loan_payments.id', '=', 'vouchers.payable_id')
                    ->leftJoin('voucher_types','vouchers.voucher_type_id', '=', 'voucher_types.id')
                    //->orWhere('vouchers.payable_type','=',$loan_payments)
                    ->whereNull('loan_payments.deleted_at')
                    ->where($conditions)
                    ->select('loans.id as id_loan','loans.code as code_loan','loans.disbursement_date as disbursement_date_loan','affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate',
                    'affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate','affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
                    'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate','pension_entities.name as pension_entity_affiliate','loan_payments.code as code_payment','loan_payments.estimated_date as estimated_date_payment','loan_payments.loan_payment_date as loan_payment_date',
                    'loan_payments.estimated_quota as estimated_quota_payment','loan_payments.voucher as voucher_payment',
                    'procedure_modalities.name as sub_modality_payment','procedure_modalities.shortened as sub_modality_shortened_payment','procedure_types.name as modality_payment','loan_payment_states.name as state_payment','voucher_types.name as name_voucher_type','spouses.registration as registration_spouse',
                    'loan_payments.paid_by as payment_by','loan_payments.capital_payment as capital_payment','loan_payments.interest_payment as interest_payment','loan_payments.penal_payment as penal_payment','loan_payments.interest_remaining as interest_current_pending','loan_payments.penal_remaining as interest_penal_pending','loan_payments.estimated_quota as estimated_quota_payment',
                    'loan_payments.previous_balance as previous_balance',DB::raw("(loan_payments.previous_balance - loan_payments.capital_payment) as current_balance"),'loan_payments.id as id_payment')
                    ->orderBy('loan_payments.code', $order_loan)
                    ->get();

                    foreach ($list_loan as $loan) {
                        $padron = Loan::where('id', $loan->id_loan)->first();
                        $loan->modality=$padron->modality->procedure_type->second_name;
                      }
          
                   $File="ListadoAmortizaciones";
                   $data=array(
                       array("NRO DE PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE PAGO","FECHA DE TRANSACCIÓN","PRODUCTO",
                       "MATRICULA AFILIADO","MATRICULA CÓNYUGUE", "CI", "APELLIDO PATERNO","APELLIDO CASADA","APELLIDO MATERNO",
                       "PRIMER NOMBRE","SEGUNDO NOMBRE","CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE", 
                       "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","CBTE","NRO DE COBRO")
                   );
                   foreach ($list_loan as $row){
                       array_push($data, array(
                           $row->code_loan,//nro de prestamo
                           $row->disbursement_date_loan,//fecha de desembolso
                           $row->state_type_affiliate,//tipo (pasivo o activo)
                           
                           $row->estimated_date_payment,//fecha de pago
                           $row->loan_payment_date,//fecha de transacción
                           $row->modality, //producto

                           $row->registration_affiliate,//matricula afiliado
                           $row->registration_spouse,//matricula esposa
                           $row->identity_card_affiliate,//CI

                           $row->last_name_affiliate,//ap paterno
                           $row->surname_husband_affiliate,//ap casada
                           $row->mothers_last_name_affiliate,//ap materno
                      
                           $row->first_name_affiliate,//primer nombre
                           $row->second_name_affiliate,//segundo nombre
                           $row->capital_payment,//capital .. pagado
                           $row->interest_payment, //interes corriente 
                           $row->penal_payment,// interes penal 
                           $row->interest_current_pending,//interes corriente pendiente
                           $row->interest_penal_pending,//interes penal pendiente 
                           $row->estimated_quota_payment,//total pagado 
                           $row->previous_balance,// saldo anterior 
                           $row->current_balance,//saldo actual 

                           $row->payment_by,//pagado por 
                           $row->sub_modality_shortened_payment,// tipo de descuento 

                           $row->voucher_payment, //comprobante
                           $row->code_payment, //Nro de cobro
                       ));
                   }
                   $export = new ArchivoPrimarioExport($data);
                   return Excel::download($export, $File.'.csv');
      }

  /** @group Reportes de amortizaciones 
   * Reporte de amortizaciones en efectivo y deposito en cuenta  
   * Reporte de amortizaciones en efectivo y deposito en cuenta modalidad amortizacion directa  
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/reports_amortizations/dincuent_cash_deposit.200.json
   */
 public function report_amortization_cash_deposit(Request $request){
    // aumenta el tiempo máximo de ejecución de este script a 150 min:
    ini_set('max_execution_time', 9000);
    // aumentar el tamaño de memoria permitido de este script:
    ini_set('memory_limit', '960M');

    $order_loan = 'Desc';
    
    $conditions = [];
    //filtros
    $initial_date = request('initial_date') ?? '';
    $final_date = request('final_date') ?? '';
    $state_pagado='Pagado';
    $procedure_type='Amortización Directa';

    if ($initial_date != '') {
      array_push($conditions, array('loan_payments.estimated_date', '>=', "%{$initial_date}%"));
    }
    if ($final_date != '') {
      array_push($conditions, array('loan_payments.estimated_date', '<=', "%{$final_date}%"));
    }
    array_push($conditions, array('loan_payment_states.name', 'ilike', "%{$state_pagado}%"));
    array_push($conditions, array('procedure_types.name', 'ilike', "%{$procedure_type}%"));
    

                $list_loan = DB::table('loan_payments')
                ->join('procedure_modalities','loan_payments.procedure_modality_id', '=', 'procedure_modalities.id')
                ->join('procedure_types','procedure_modalities.procedure_type_id', '=', 'procedure_types.id')
                ->join('loan_payment_states','loan_payments.state_id', '=', 'loan_payment_states.id')
                ->join('affiliates','loan_payments.affiliate_id', '=', 'affiliates.id')
                ->leftjoin('spouses','affiliates.id', '=', 'spouses.affiliate_id')
                ->join('affiliate_states','affiliates.affiliate_state_id', '=', 'affiliate_states.id')
                ->join('affiliate_state_types','affiliate_states.affiliate_state_type_id', '=', 'affiliate_state_types.id')
                ->leftjoin('pension_entities','affiliates.pension_entity_id', '=', 'pension_entities.id')
                ->join('loans','loan_payments.loan_id', '=', 'loans.id')
                ->leftJoin('vouchers','loan_payments.id', '=', 'vouchers.payable_id')
                ->leftJoin('voucher_types','vouchers.voucher_type_id', '=', 'voucher_types.id')
                //->orWhere('vouchers.payable_type','=',$loan_payments)
                ->whereNull('loan_payments.deleted_at')
                ->where($conditions)
                ->select('loans.id as id_loan','loans.code as code_loan','loans.disbursement_date as disbursement_date_loan','affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate',
                'affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate','affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
                'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate','pension_entities.name as pension_entity_affiliate','loan_payments.code as code_payment','loan_payments.estimated_date as estimated_date_payment','loan_payments.loan_payment_date as loan_payment_date',
                'loan_payments.estimated_quota as estimated_quota_payment','loan_payments.voucher as voucher_payment',
                'procedure_modalities.name as sub_modality_payment','procedure_modalities.shortened as sub_modality_shortened_payment','procedure_types.name as modality_payment','loan_payment_states.name as state_payment','voucher_types.name as name_voucher_type','spouses.registration as registration_spouse',
                'loan_payments.paid_by as payment_by','loan_payments.capital_payment as capital_payment','loan_payments.interest_payment as interest_payment','loan_payments.penal_payment as penal_payment','loan_payments.interest_remaining as interest_current_pending','loan_payments.penal_remaining as interest_penal_pending','loan_payments.estimated_quota as estimated_quota_payment',
                'loan_payments.previous_balance as previous_balance',DB::raw("(loan_payments.previous_balance - loan_payments.capital_payment) as current_balance"),'loan_payments.id as id_payment')
                ->orderBy('loan_payments.code', $order_loan)
                ->get();

                foreach ($list_loan as $loan) {
                    $padron = Loan::where('id', $loan->id_loan)->first();
                    $loan->modality=$padron->modality->procedure_type->second_name;
                  }
      
               $File="ListadoAmortizaciones";
               $data=array(
                   array("NRO DE PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE PAGO","FECHA DE TRANSACCIÓN","PRODUCTO",
                   "MATRICULA AFILIADO","MATRICULA CÓNYUGUE", "CI", "APELLIDO PATERNO","APELLIDO CASADA","APELLIDO MATERNO",
                   "PRIMER NOMBRE","SEGUNDO NOMBRE","CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE", 
                   "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","TIPO DE PAGO","CBTE","CBTE","NRO DE COBRO")
               );
               foreach ($list_loan as $row){
                   array_push($data, array(
                       $row->code_loan,//nro de prestamo
                       $row->disbursement_date_loan,//fecha de desembolso
                       $row->state_type_affiliate,//tipo (pasivo o activo)
                       
                       $row->estimated_date_payment,//fecha de pago
                       $row->loan_payment_date,//fecha de transacción
                       $row->modality, //producto

                       $row->registration_affiliate,//matricula afiliado
                       $row->registration_spouse,//matricula esposa
                       $row->identity_card_affiliate,//CI

                       $row->last_name_affiliate,//ap paterno
                       $row->surname_husband_affiliate,//ap casada
                       $row->mothers_last_name_affiliate,//ap materno
                  
                       $row->first_name_affiliate,//primer nombre
                       $row->second_name_affiliate,//segundo nombre
                       $row->capital_payment,//capital .. pagado
                       $row->interest_payment, //interes corriente 
                       $row->penal_payment,// interes penal 
                       $row->interest_current_pending,//interes corriente pendiente
                       $row->interest_penal_pending,//interes penal pendiente 
                       $row->estimated_quota_payment,//total pagado 
                       $row->previous_balance,// saldo anterior 
                       $row->current_balance,//saldo actual 

                       $row->payment_by,//pagado por 
                       $row->sub_modality_shortened_payment,// tipo de descuento 
                       $row->name_voucher_type,//tipo de pago

                       $row->voucher_payment, //comprobante
                       $row->code_payment, //Nro de cobro
                   ));
               }
               $export = new ArchivoPrimarioExport($data);
               return Excel::download($export, $File.'.csv');
  }

  /** @group Reportes de amortizaciones 
   * Reporte de amortizaciones por ajustes
   * Reporte de amortizaciones por ajustes modalidad amortizacion Ajuste
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/reports_amortizations/report_amortization_ajust.200.json
   */
 public function report_amortization_ajust(Request $request){
  // aumenta el tiempo máximo de ejecución de este script a 150 min:
  ini_set('max_execution_time', 9000);
  // aumentar el tamaño de memoria permitido de este script:
  ini_set('memory_limit', '960M');

  $order_loan = 'Desc';
  
  $conditions = [];
  //filtros
  $initial_date = request('initial_date') ?? '';
  $final_date = request('final_date') ?? '';
  $state_pagado='Pagado';
  $procedure_type='Amortización por Ajuste';

  if ($initial_date != '') {
    array_push($conditions, array('loan_payments.estimated_date', '>=', "%{$initial_date}%"));
  }
  if ($final_date != '') {
    array_push($conditions, array('loan_payments.estimated_date', '<=', "%{$final_date}%"));
  }
  array_push($conditions, array('loan_payment_states.name', 'ilike', "%{$state_pagado}%"));
  array_push($conditions, array('procedure_types.name', 'ilike', "%{$procedure_type}%"));
  

              $list_loan = DB::table('loan_payments')
              ->join('procedure_modalities','loan_payments.procedure_modality_id', '=', 'procedure_modalities.id')
              ->join('procedure_types','procedure_modalities.procedure_type_id', '=', 'procedure_types.id')
              ->join('loan_payment_states','loan_payments.state_id', '=', 'loan_payment_states.id')
              ->join('affiliates','loan_payments.affiliate_id', '=', 'affiliates.id')
              ->leftjoin('spouses','affiliates.id', '=', 'spouses.affiliate_id')
              ->join('affiliate_states','affiliates.affiliate_state_id', '=', 'affiliate_states.id')
              ->join('affiliate_state_types','affiliate_states.affiliate_state_type_id', '=', 'affiliate_state_types.id')
              ->leftjoin('pension_entities','affiliates.pension_entity_id', '=', 'pension_entities.id')
              ->join('loans','loan_payments.loan_id', '=', 'loans.id')
              ->leftJoin('vouchers','loan_payments.id', '=', 'vouchers.payable_id')
              ->leftJoin('voucher_types','vouchers.voucher_type_id', '=', 'voucher_types.id')
              //->orWhere('vouchers.payable_type','=',$loan_payments)
              ->whereNull('loan_payments.deleted_at')
              ->where($conditions)
              ->select('loans.id as id_loan','loans.code as code_loan','loans.disbursement_date as disbursement_date_loan','affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate',
              'affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate','affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
              'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate','pension_entities.name as pension_entity_affiliate','loan_payments.code as code_payment','loan_payments.estimated_date as estimated_date_payment','loan_payments.loan_payment_date as loan_payment_date',
              'loan_payments.estimated_quota as estimated_quota_payment','loan_payments.voucher as voucher_payment',
              'procedure_modalities.name as sub_modality_payment','procedure_modalities.shortened as sub_modality_shortened_payment','procedure_types.name as modality_payment','loan_payment_states.name as state_payment','voucher_types.name as name_voucher_type','spouses.registration as registration_spouse',
              'loan_payments.paid_by as payment_by','loan_payments.capital_payment as capital_payment','loan_payments.interest_payment as interest_payment','loan_payments.penal_payment as penal_payment','loan_payments.interest_remaining as interest_current_pending','loan_payments.penal_remaining as interest_penal_pending','loan_payments.estimated_quota as estimated_quota_payment',
              'loan_payments.previous_balance as previous_balance',DB::raw("(loan_payments.previous_balance - loan_payments.capital_payment) as current_balance"),'loan_payments.id as id_payment')
              ->orderBy('loan_payments.code', $order_loan)
              ->get();

              foreach ($list_loan as $loan) {
                  $padron = Loan::where('id', $loan->id_loan)->first();
                  $loan->modality=$padron->modality->procedure_type->second_name;
                }
    
             $File="ListadoAmortizaciones";
             $data=array(
                 array("NRO DE PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE PAGO","FECHA DE TRANSACCIÓN","PRODUCTO",
                 "MATRICULA AFILIADO","MATRICULA CÓNYUGUE", "CI", "APELLIDO PATERNO","APELLIDO CASADA","APELLIDO MATERNO",
                 "PRIMER NOMBRE","SEGUNDO NOMBRE","CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE", 
                 "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","TIPO DE PAGO","CBTE","CBTE","NRO DE COBRO")
             );
             foreach ($list_loan as $row){
                 array_push($data, array(
                     $row->code_loan,//nro de prestamo
                     $row->disbursement_date_loan,//fecha de desembolso
                     $row->state_type_affiliate,//tipo (pasivo o activo)
                     
                     $row->estimated_date_payment,//fecha de pago
                     $row->loan_payment_date,//fecha de transacción
                     $row->modality, //producto

                     $row->registration_affiliate,//matricula afiliado
                     $row->registration_spouse,//matricula esposa
                     $row->identity_card_affiliate,//CI

                     $row->last_name_affiliate,//ap paterno
                     $row->surname_husband_affiliate,//ap casada
                     $row->mothers_last_name_affiliate,//ap materno
                
                     $row->first_name_affiliate,//primer nombre
                     $row->second_name_affiliate,//segundo nombre
                     $row->capital_payment,//capital .. pagado
                     $row->interest_payment, //interes corriente 
                     $row->penal_payment,// interes penal 
                     $row->interest_current_pending,//interes corriente pendiente
                     $row->interest_penal_pending,//interes penal pendiente 
                     $row->estimated_quota_payment,//total pagado 
                     $row->previous_balance,// saldo anterior 
                     $row->current_balance,//saldo actual 

                     $row->payment_by,//pagado por 
                     $row->sub_modality_shortened_payment,// tipo de descuento 

                     $row->voucher_payment, //comprobante

                     $row->code_payment, //Nro de cobro
                 ));
             }
             $export = new ArchivoPrimarioExport($data);
             return Excel::download($export, $File.'.csv');
  }
 /** @group Reportes de amortizaciones 
   * Reporte de amortizaciones por complemento y fondo de retiro  
   * Reporte de amortizaciones amortizaciones por complemento y fondo de retiro 
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/reports_amortizations/report_complement_fondo.200.json
   */
  public function report_amortization_fondo_complement(Request $request){
    // aumenta el tiempo máximo de ejecución de este script a 150 min:
    ini_set('max_execution_time', 9000);
    // aumentar el tamaño de memoria permitido de este script:
    ini_set('memory_limit', '960M');
  
    $order_loan = 'Desc';
    
    $conditions = [];
    $conditions_fondo = [];
    //filtros
    $initial_date = request('initial_date') ?? '';
    $final_date = request('final_date') ?? '';
    $state_pagado='Pagado';
    $procedure_type='Complemento Económico';
    
    $procedure_type_fr='Amortización Fondo de Retiro';
  
    if ($initial_date != '') {
      array_push($conditions, array('loan_payments.estimated_date', '>=', "%{$initial_date}%"));
      array_push($conditions_fondo, array('loan_payments.estimated_date', '>=', "%{$initial_date}%"));
    }
    if ($final_date != '') {
      array_push($conditions, array('loan_payments.estimated_date', '<=', "%{$final_date}%"));
      array_push($conditions_fondo, array('loan_payments.estimated_date', '<=', "%{$final_date}%"));
    }
    array_push($conditions, array('loan_payment_states.name', 'ilike', "%{$state_pagado}%"));
    array_push($conditions_fondo, array('loan_payment_states.name', 'ilike', "%{$state_pagado}%"));
    array_push($conditions, array('procedure_modalities.name', 'ilike', "%{$procedure_type}%"));
    array_push($conditions_fondo, array('procedure_types.name', 'ilike', "%{$procedure_type_fr}%"));
  
      $list_loan = DB::table('loan_payments')
                ->join('procedure_modalities','loan_payments.procedure_modality_id', '=', 'procedure_modalities.id')
                ->join('procedure_types','procedure_modalities.procedure_type_id', '=', 'procedure_types.id')
                ->join('loan_payment_states','loan_payments.state_id', '=', 'loan_payment_states.id')
                ->join('affiliates','loan_payments.affiliate_id', '=', 'affiliates.id')
                ->leftjoin('spouses','affiliates.id', '=', 'spouses.affiliate_id')
                ->join('affiliate_states','affiliates.affiliate_state_id', '=', 'affiliate_states.id')
                ->join('affiliate_state_types','affiliate_states.affiliate_state_type_id', '=', 'affiliate_state_types.id')
                ->leftjoin('pension_entities','affiliates.pension_entity_id', '=', 'pension_entities.id')
                ->join('loans','loan_payments.loan_id', '=', 'loans.id')
                ->leftJoin('vouchers','loan_payments.id', '=', 'vouchers.payable_id')
                ->leftJoin('voucher_types','vouchers.voucher_type_id', '=', 'voucher_types.id')
                //->orWhere('vouchers.payable_type','=',$loan_payments)
                ->whereNull('loan_payments.deleted_at')
                ->where($conditions)
                ->select('loans.id as id_loan','loans.code as code_loan','loans.disbursement_date as disbursement_date_loan','affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate',
                'affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate','affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
                'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate','pension_entities.name as pension_entity_affiliate','loan_payments.code as code_payment','loan_payments.estimated_date as estimated_date_payment','loan_payments.loan_payment_date as loan_payment_date',
                'loan_payments.estimated_quota as estimated_quota_payment','loan_payments.voucher as voucher_payment',
                'procedure_modalities.name as sub_modality_payment','procedure_modalities.shortened as sub_modality_shortened_payment','procedure_types.name as modality_payment','loan_payment_states.name as state_payment','voucher_types.name as name_voucher_type','spouses.registration as registration_spouse',
                'loan_payments.paid_by as payment_by','loan_payments.capital_payment as capital_payment','loan_payments.interest_payment as interest_payment','loan_payments.penal_payment as penal_payment','loan_payments.interest_remaining as interest_current_pending','loan_payments.penal_remaining as interest_penal_pending','loan_payments.estimated_quota as estimated_quota_payment',
                'loan_payments.previous_balance as previous_balance',DB::raw("(loan_payments.previous_balance - loan_payments.capital_payment) as current_balance"),'loan_payments.id as id_payment')
                ->orderBy('loan_payments.code', $order_loan)
                ->get();
  
                foreach ($list_loan as $loan) {
                    $padron = Loan::where('id', $loan->id_loan)->first();
                    $loan->modality=$padron->modality->procedure_type->second_name;
                  }
      
               $File="ListadoAmortizaciones";
               $data=array(
                   array("NRO DE PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE PAGO","FECHA DE TRANSACCIÓN","PRODUCTO",
                   "MATRICULA AFILIADO","MATRICULA CÓNYUGUE", "CI", "APELLIDO PATERNO","APELLIDO CASADA","APELLIDO MATERNO",
                   "PRIMER NOMBRE","SEGUNDO NOMBRE","CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE", 
                   "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","TIPO DE PAGO","CBTE","NRO DE COBRO")
               );
               foreach ($list_loan as $row){
                   array_push($data, array(
                       $row->code_loan,//nro de prestamo
                       $row->disbursement_date_loan,//fecha de desembolso
                       $row->state_type_affiliate,//tipo (pasivo o activo)
                       
                       $row->estimated_date_payment,//fecha de pago
                       $row->loan_payment_date,//fecha de transacción
                       $row->modality, //producto
  
                       $row->registration_affiliate,//matricula afiliado
                       $row->registration_spouse,//matricula esposa
                       $row->identity_card_affiliate,//CI
  
                       $row->last_name_affiliate,//ap paterno
                       $row->surname_husband_affiliate,//ap casada
                       $row->mothers_last_name_affiliate,//ap materno
                  
                       $row->first_name_affiliate,//primer nombre
                       $row->second_name_affiliate,//segundo nombre
                       $row->capital_payment,//capital .. pagado
                       $row->interest_payment, //interes corriente 
                       $row->penal_payment,// interes penal 
                       $row->interest_current_pending,//interes corriente pendiente
                       $row->interest_penal_pending,//interes penal pendiente 
                       $row->estimated_quota_payment,//total pagado 
                       $row->previous_balance,// saldo anterior 
                       $row->current_balance,//saldo actual 
  
                       $row->payment_by,//pagado por 
                       $row->sub_modality_shortened_payment,// tipo de descuento 
  
                       $row->voucher_payment, //comprobante
  
                       $row->code_payment, //Nro de cobro
                   ));
               }
              
               //fondo de retiro
               $list_loan_fondo = DB::table('loan_payments')
                ->join('procedure_modalities','loan_payments.procedure_modality_id', '=', 'procedure_modalities.id')
                ->join('procedure_types','procedure_modalities.procedure_type_id', '=', 'procedure_types.id')
                ->join('loan_payment_states','loan_payments.state_id', '=', 'loan_payment_states.id')
                ->join('affiliates','loan_payments.affiliate_id', '=', 'affiliates.id')
                ->leftjoin('spouses','affiliates.id', '=', 'spouses.affiliate_id')
                ->join('affiliate_states','affiliates.affiliate_state_id', '=', 'affiliate_states.id')
                ->join('affiliate_state_types','affiliate_states.affiliate_state_type_id', '=', 'affiliate_state_types.id')
                ->leftjoin('pension_entities','affiliates.pension_entity_id', '=', 'pension_entities.id')
                ->join('loans','loan_payments.loan_id', '=', 'loans.id')
                ->leftJoin('vouchers','loan_payments.id', '=', 'vouchers.payable_id')
                ->leftJoin('voucher_types','vouchers.voucher_type_id', '=', 'voucher_types.id')
                //->orWhere('vouchers.payable_type','=',$loan_payments)
                ->whereNull('loan_payments.deleted_at')
                ->where($conditions_fondo)
                ->select('loans.id as id_loan','loans.code as code_loan','loans.disbursement_date as disbursement_date_loan','affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate',
                'affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate','affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
                'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate','pension_entities.name as pension_entity_affiliate','loan_payments.code as code_payment','loan_payments.estimated_date as estimated_date_payment','loan_payments.loan_payment_date as loan_payment_date',
                'loan_payments.estimated_quota as estimated_quota_payment','loan_payments.voucher as voucher_payment',
                'procedure_modalities.name as sub_modality_payment','procedure_modalities.shortened as sub_modality_shortened_payment','procedure_types.name as modality_payment','loan_payment_states.name as state_payment','voucher_types.name as name_voucher_type','spouses.registration as registration_spouse',
                'loan_payments.paid_by as payment_by','loan_payments.capital_payment as capital_payment','loan_payments.interest_payment as interest_payment','loan_payments.penal_payment as penal_payment','loan_payments.interest_remaining as interest_current_pending','loan_payments.penal_remaining as interest_penal_pending','loan_payments.estimated_quota as estimated_quota_payment',
                'loan_payments.previous_balance as previous_balance',DB::raw("(loan_payments.previous_balance - loan_payments.capital_payment) as current_balance"),'loan_payments.id as id_payment')
                ->orderBy('loan_payments.code', $order_loan)
                ->get();
  
                foreach ($list_loan_fondo as $loan) {
                    $padron = Loan::where('id', $loan->id_loan)->first();
                    $loan->modality=$padron->modality->procedure_type->second_name;
                  }
      
               $File="ListadoAmortizaciones";
               $data_fondo=array(
                   array("NRO DE PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE PAGO","FECHA DE TRANSACCIÓN","PRODUCTO",
                   "MATRICULA AFILIADO","MATRICULA CÓNYUGUE", "CI", "APELLIDO PATERNO","APELLIDO CASADA","APELLIDO MATERNO",
                   "PRIMER NOMBRE","SEGUNDO NOMBRE","CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE", 
                   "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","TIPO DE PAGO","CBTE","NRO DE COBRO")
               );
               foreach ($list_loan_fondo as $row){
                   array_push($data_fondo, array(
                       $row->code_loan,//nro de prestamo
                       $row->disbursement_date_loan,//fecha de desembolso
                       $row->state_type_affiliate,//tipo (pasivo o activo)
                       
                       $row->estimated_date_payment,//fecha de pago
                       $row->loan_payment_date,//fecha de transacción
                       $row->modality, //producto
  
                       $row->registration_affiliate,//matricula afiliado
                       $row->registration_spouse,//matricula esposa
                       $row->identity_card_affiliate,//CI
  
                       $row->last_name_affiliate,//ap paterno
                       $row->surname_husband_affiliate,//ap casada
                       $row->mothers_last_name_affiliate,//ap materno
                  
                       $row->first_name_affiliate,//primer nombre
                       $row->second_name_affiliate,//segundo nombre
                       $row->capital_payment,//capital .. pagado
                       $row->interest_payment, //interes corriente 
                       $row->penal_payment,// interes penal 
                       $row->interest_current_pending,//interes corriente pendiente
                       $row->interest_penal_pending,//interes penal pendiente 
                       $row->estimated_quota_payment,//total pagado 
                       $row->previous_balance,// saldo anterior 
                       $row->current_balance,//saldo actual 
  
                       $row->payment_by,//pagado por 
                       $row->sub_modality_shortened_payment,// tipo de descuento 
  
                       $row->voucher_payment, //comprobante
  
                       $row->code_payment, //Nro de cobro
                   ));
               }

               $export = new MultipleSheetExportPayment($data, $data_fondo,'COM-ECO','FRP');
               return Excel::download($export, $File.'.xlsx');
    }

    /** @group Reportes de amortizaciones 
   * Reporte de amortizaciones pendientes de confirmacion deacuerdo al comprobante de generacion
   * Reporte de amortizaciones por ajustes modalidad amortizacion Ajuste
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/reports_amortizations/report_amortization_ajust.200.json
   */
 public function report_amortization_pending_confirmation(Request $request){
  // aumenta el tiempo máximo de ejecución de este script a 150 min:
  ini_set('max_execution_time', 9000);
  // aumentar el tamaño de memoria permitido de este script:
  ini_set('memory_limit', '960M');

  $order_loan = 'Desc';
  
  $conditions = [];
  //filtros
  $initial_date = request('initial_date') ?? '';
  $final_date = request('final_date') ?? '';
  $state_pagado='Pendiente por confirmar';

  if ($initial_date != '') {
    array_push($conditions, array('loan_payments.estimated_date', '>=', "%{$initial_date}%"));
  }
  if ($final_date != '') {
    array_push($conditions, array('loan_payments.estimated_date', '<=', "%{$final_date}%"));
  }
  array_push($conditions, array('loan_payment_states.name', 'ilike', "%{$state_pagado}%"));
  
  $list_loan = DB::table('loan_payments')
              ->join('procedure_modalities','loan_payments.procedure_modality_id', '=', 'procedure_modalities.id')
              ->join('procedure_types','procedure_modalities.procedure_type_id', '=', 'procedure_types.id')
              ->join('loan_payment_states','loan_payments.state_id', '=', 'loan_payment_states.id')
              ->join('affiliates','loan_payments.affiliate_id', '=', 'affiliates.id')
              ->leftjoin('spouses','affiliates.id', '=', 'spouses.affiliate_id')
              ->join('affiliate_states','affiliates.affiliate_state_id', '=', 'affiliate_states.id')
              ->join('affiliate_state_types','affiliate_states.affiliate_state_type_id', '=', 'affiliate_state_types.id')
              ->leftjoin('pension_entities','affiliates.pension_entity_id', '=', 'pension_entities.id')
              ->join('loans','loan_payments.loan_id', '=', 'loans.id')
              ->leftJoin('vouchers','loan_payments.id', '=', 'vouchers.payable_id')
              ->leftJoin('voucher_types','vouchers.voucher_type_id', '=', 'voucher_types.id')
              //->orWhere('vouchers.payable_type','=',$loan_payments)
              ->whereNull('loan_payments.deleted_at')
              ->where($conditions)
              ->select('loans.id as id_loan','loans.code as code_loan','loans.disbursement_date as disbursement_date_loan','affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate',
              'affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate','affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
              'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate','pension_entities.name as pension_entity_affiliate','loan_payments.code as code_payment','loan_payments.estimated_date as estimated_date_payment','loan_payments.loan_payment_date as loan_payment_date',
              'loan_payments.estimated_quota as estimated_quota_payment','loan_payments.voucher as voucher_payment',
              'procedure_modalities.name as sub_modality_payment','procedure_modalities.shortened as sub_modality_shortened_payment','procedure_types.name as modality_payment','loan_payment_states.name as state_payment','voucher_types.name as name_voucher_type','spouses.registration as registration_spouse',
              'loan_payments.paid_by as payment_by','loan_payments.capital_payment as capital_payment','loan_payments.interest_payment as interest_payment','loan_payments.penal_payment as penal_payment','loan_payments.interest_remaining as interest_current_pending','loan_payments.penal_remaining as interest_penal_pending','loan_payments.estimated_quota as estimated_quota_payment',
              'loan_payments.previous_balance as previous_balance',DB::raw("(loan_payments.previous_balance - loan_payments.capital_payment) as current_balance"),'loan_payments.id as id_payment')
              ->orderBy('loan_payments.code', $order_loan)
              ->get();

              foreach ($list_loan as $loan) {
                  $padron = Loan::where('id', $loan->id_loan)->first();
                  $loan->modality=$padron->modality->procedure_type->second_name;
                }
    
             $File="ListadoAmortizaciones";
             $data=array(
                 array("NRO DE PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE PAGO","FECHA DE TRANSACCIÓN","PRODUCTO",
                 "MATRICULA AFILIADO","MATRICULA CÓNYUGUE", "CI", "APELLIDO PATERNO","APELLIDO CASADA","APELLIDO MATERNO",
                 "PRIMER NOMBRE","SEGUNDO NOMBRE","CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE", 
                 "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","ESTADO DEL COBRO","TIPO DESCUENTO","CBTE","NRO DE COBRO")
             );
             foreach ($list_loan as $row){
                 array_push($data, array(
                     $row->code_loan,//nro de prestamo
                     $row->disbursement_date_loan,//fecha de desembolso
                     $row->state_type_affiliate,//tipo (pasivo o activo)
                     
                     $row->estimated_date_payment,//fecha de pago
                     $row->loan_payment_date,//fecha de transacción
                     $row->modality, //producto

                     $row->registration_affiliate,//matricula afiliado
                     $row->registration_spouse,//matricula esposa
                     $row->identity_card_affiliate,//CI

                     $row->last_name_affiliate,//ap paterno
                     $row->surname_husband_affiliate,//ap casada
                     $row->mothers_last_name_affiliate,//ap materno
                
                     $row->first_name_affiliate,//primer nombre
                     $row->second_name_affiliate,//segundo nombre
                     $row->capital_payment,//capital .. pagado
                     $row->interest_payment, //interes corriente 
                     $row->penal_payment,// interes penal 
                     $row->interest_current_pending,//interes corriente pendiente
                     $row->interest_penal_pending,//interes penal pendiente 
                     $row->estimated_quota_payment,//total pagado 
                     $row->previous_balance,// saldo anterior 
                     $row->current_balance,//saldo actual 

                     $row->payment_by,//pagado por 
                     $row->state_payment,// Estado del cobro
                     $row->sub_modality_shortened_payment,// tipo de descuento 
                     

                     $row->voucher_payment, //comprobante

                     $row->code_payment, //Nro de cobro
                 ));
             }
             $export = new ArchivoPrimarioExport($data);
             return Excel::download($export, $File.'.csv');
  }
  


}
