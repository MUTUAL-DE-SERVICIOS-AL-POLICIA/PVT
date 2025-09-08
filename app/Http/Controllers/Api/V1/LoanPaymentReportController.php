<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Util;

use DB;
use App\LoanPayment;
use App\Loan;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;
use App\Exports\SheetExportPayment;
use App\Exports\MultipleSheetExportPayment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
//use App\Exports\SheetExportPayment;
use Carbon;

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
    ini_set('max_execution_time', 900000);
    // aumentar el tamaño de memoria permitido de este script:
    ini_set('memory_limit', '96000M');

    $order_loan = 'Desc';
    
    $conditions = [];
    //filtros
    $initial_date = request('initial_date') ?? '';
    $final_date = request('final_date') ?? '';
    $state_pagado = 'Pagado';
    $procedure_loan_payment = "Amortización Automática";

    if ($initial_date != '') {
      array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '>=', "%{$initial_date}%"));
    }
    if ($final_date != '') {
      array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '<=', "%{$final_date}%"));
    }
    array_push($conditions, array('view_loan_amortizations.states_loan_payment', 'like', "%{$state_pagado}%"));
    array_push($conditions, array('view_loan_amortizations.procedure_loan_payment', 'like', "%{$procedure_loan_payment}%"));
    $list_loan = DB::table('view_loan_amortizations')
      ->where($conditions)
      ->select('*')
      ->orderBy('code_loan', $order_loan)
      ->get();
                      
          $File="ListadoAmortizacionesPorTipoDePago";
          $data_head=array(
              "CI AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","COD PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE CALCULO","FECHA DE TRANSACCIÓN",
              "MODALIDAD PRÉSTAMO","SUB MODALIDAD PRÉSTAMO",
              "MATRICULA", "CI", "PRIMER NOMBRE","SEGUNDO NOMBRE","APELLIDO PATERNO","APELLIDO MATERNO","APELLIDO CASADA",
              "CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE",
              "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR","SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","NRO DE COBRO","ESTADO AMORTIZACIÓN"
              );   
    $data = collect();
    //DES-COMANDO
    $data->discount_comand = array($data_head);
    //DES-SENASIR
    $data->discount_senasir= array($data_head);
    
               foreach ($list_loan as $row){    
                  
                  $padron = Loan::find($row->id_loan);
                  $row->modality=$padron->modality->procedure_type->second_name;
                  $row->sub_modality=$padron->modality->shortened;
                  $row->separation='***';
                  
                  $data_body = array(
                       $row->identity_card_affiliate,
                       $row->registration_affiliate,
                       $row->full_name_affiliate,
                       $row->separation,
                       $row->code_loan,//nro de prestamo
                       Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'),//fecha de desembolso
                       $row->state_affiliate_loan_payment,//tipo (pasivo o activo)
            
                       Carbon::parse($row->estimated_date_loan_payment)->format('d/m/Y'),//fecha de pago
                       Carbon::parse($row->date_loan_payment)->format('d/m/Y H:i:s'),//fecha de transacción
                       $row->modality, //MOdalidad
                       $row->sub_modality, //Sub modalidad

                       $row->registration_borrower,//matricula del prestatario
                       $row->identity_card_borrower,//CI del prestatario
                       $row->first_name_borrower,//primer nombre del prestatario
                       $row->second_name_borrower,//segundo nombre del prestatario
                       $row->last_name_borrower,// aprellido paterno del prestatario 
                       $row->mothers_last_name_borrower,//apellido matyerno del prestatario
                       $row->surname_husband_borrower,//apellido de casado del prestatario

                       Util::money_format($row->capital_payment),//capital .. pagado
                       Util::money_format($row->interest_payment), //interes corriente 
                       Util::money_format($row->penal_payment),// interes penal 
                       Util::money_format($row->interest_accumulated),//interes corriente pendiente
                       Util::money_format($row->penal_accumulated),//interes penal pendiente 
                       Util::money_format($row->quota_loan_payment),//total pagado 
                       Util::money_format($row->previous_balance),// saldo anterior 
                       Util::money_format($row->previous_balance - $row->capital_payment),//saldo actual 

                       $row->paid_by_loan_payment,//pagado por 
                       $row->modality_shortened_loan_payment,// tipo de descuento 

                       $row->code_loan_payment, //Nro de cobro
                       $row->states_loan_payment, //estado del cobro
                   );

                   switch($row->modality_shortened_loan_payment){
                    case 'DES-COMANDO': array_push($data->discount_comand, $data_body);
                        break; 
                    case 'DES-SENASIR': array_push($data->discount_senasir, $data_body);
                        break;
                  };
               }
               $export = new MultipleSheetExportPayment($data->discount_comand,$data->discount_senasir,'DES-COMANDO','DES-SENASIR');
               return Excel::download($export, $File.'.xlsx');
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
    $procedure_type='Efectivo';

    if ($initial_date != '') {
      array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '>=', "%{$initial_date}%"));
    }
    if ($final_date != '') {
      array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '<=', "%{$final_date}%"));
    }
    //array_push($conditions, array('view_loan_amortizations.states_loan_payment', 'ilike', "%{$state_pagado}%"));
    
    $list_loan = DB::table('view_loan_amortizations')
                ->where($conditions)
                ->select('*')
                ->orderBy('code_loan', $order_loan)
                ->get();

      foreach ($list_loan as $loan) {
      $padron = Loan::where('id', $loan->id_loan)->first();
      $loan->modality=$padron->modality->procedure_type->second_name;
      $loan->sub_modality=$padron->modality->shortened;
      $loan->separation='***';
      }

      $File="ListadoAmortizaciones";
      $data=array(
      array("CI AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","COD PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE CALCULO","FECHA DE TRANSACCIÓN",
      "MODALIDAD PRÉSTAMO","SUB MODALIDAD PRÉSTAMO",
      "MATRICULA", "CI", "PRIMER NOMBRE","SEGUNDO NOMBRE","APELLIDO PATERNO","APELLIDO MATERNO","APELLIDO CASADA",
      "CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE",
      "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","TIPO DE PAGO","CBTE","NRO DE COBRO","ESTADO AMORTIZACIÓN")
      );
               foreach ($list_loan as $row){
                   array_push($data, array(

                    $row->identity_card_affiliate,
                      $row->registration_affiliate,
                      $row->full_name_affiliate,
                      $row->separation,
                      $row->code_loan,
                      Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'),//fecha de desembolso
                      $row->state_affiliate_loan_payment,

                      Carbon::parse($row->estimated_date_loan_payment)->format('d/m/Y'),//fecha de pago/calculo
                      Carbon::parse($row->date_loan_payment)->format('d/m/Y H:i:s'),//fecha de transacción
                      $row->modality, //MOdalidad
                      $row->sub_modality, //Sub modalidad
                      $row->registration_borrower,
                      $row->identity_card_borrower,
                      $row->first_name_borrower,
                      $row->second_name_borrower,
                      $row->last_name_borrower,
                      $row->mothers_last_name_borrower,
                      $row->surname_husband_borrower,

                      Util::money_format($row->capital_payment),//capital .. pagado
                      Util::money_format($row->interest_payment), //interes corriente
                      Util::money_format($row->penal_payment),// interes penal

                      Util::money_format($row->interest_accumulated),//interes corriente pendiente
                      Util::money_format($row->penal_accumulated),//interes penal pendiente
                      Util::money_format($row->quota_loan_payment),//total pagado
                      Util::money_format($row->previous_balance),// saldo anterior
                      Util::money_format($row->previous_balance - $row->capital_payment),//saldo actual

                      $row->paid_by_loan_payment,//pagado por
                      $row->modality_shortened_loan_payment,// tipo de descuento
                      $row->voucher_type_loan_payment, //TIPO DE DESCUENTO EFECTIVO O DEP EN CUENTA
                      $row->voucher_loan_payment, //comprobante

                      $row->code_loan_payment, //Nro de cobro*/
                      $row->states_loan_payment,//estado del cobro
                   ));
               }
    $export = new ArchivoPrimarioExport($data);
    return Excel::download($export, $File.'.xlsx');
}

  /** @group Reportes de amortizaciones 
   * Reporte de amortizaciones en efectivo y deposito en cuenta por tipo de descuento
   * Reporte de amortizaciones en efectivo y deposito en cuenta modalidad amortizacion directa por tipo de descuento
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01 
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/reports_amortizations/dincuent_cash_deposit.200.json
   * Código Base por Noemi hace 3 años modificado por Fercho en 26 de mar 2024
   */

  public function report_amortization_cash_deposit_discount_type(Request $request)
  {
    // aumenta el tiempo máximo de ejecución de este script a 150 min:
    ini_set('max_execution_time', 9000);
    // aumentar el tamaño de memoria permitido de este script:
    ini_set('memory_limit', '960M');

    $order_loan = 'Desc';

    $conditions = [];
    //filtros
    $initial_date = request('initial_date') ?? '';
    $final_date = request('final_date') ?? '';
    $state_pagado = 'Pagado';
    $procedure_type_cash = 'Amortización en Efectivo';
    $procedure_type_deposit = 'Amortización con Deposito en Cuenta';

    if ($initial_date != '') {
      array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '>=', "%{$initial_date}%"));
    }
    if ($final_date != '') {
      array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '<=', "%{$final_date}%"));
    }
    array_push($conditions, array('view_loan_amortizations.states_loan_payment', 'like', "%{$state_pagado}%"));
    $list_loan = DB::table('view_loan_amortizations')
    ->where($conditions)
    ->where(function($query) use ($procedure_type_cash, $procedure_type_deposit) {
        $query->orWhere('view_loan_amortizations.procedure_loan_payment', 'ilike', "%{$procedure_type_cash}%")
              ->orWhere('view_loan_amortizations.procedure_loan_payment', 'ilike', "%{$procedure_type_deposit}%");
    })
    ->orderBy('code_loan', $order_loan)
    ->get();

    $File = "ListadoAmortizacionesPorTipoDePago";
    $data_head = array(
        "CI AFILIADO", "MATRICULA AFILIADO", "NOMBRE COMPLETO AFILIADO", "***", "COD PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO", "FECHA DE CALCULO", "FECHA DE TRANSACCIÓN",
        "MODALIDAD PRÉSTAMO", "SUB MODALIDAD PRÉSTAMO",
        "MATRICULA", "CI", "PRIMER NOMBRE", "SEGUNDO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO", "APELLIDO CASADA",
        "CAPITAL", "INTERÉS CORRIENTE", "INTERÉS PENAL", "INTERÉS CORRIENTE PENDIENTE",
        "INTERÉS PENAL PENDIENTE", "TOTAL PAGADO", "SALDO ANTERIOR", "SALDO ACTUAL", "PAGADO POR", "TIPO DESCUENTO", "TIPO DE PAGO", "CBTE", "NRO DE COBRO", "ESTADO AMORTIZACIÓN"
    );

    $data = collect();
    // EFECTIVO
    $data->cash = array($data_head);
    // DEP EN CUENTA
    $data->dep_banc = array($data_head);

    foreach ($list_loan as $row) {
      
      $padron = Loan::find($row->id_loan);
      $row->modality = $padron->modality->procedure_type->second_name;
      $row->sub_modality = $padron->modality->shortened;
      $row->separation = '***';
      
      $data_body = array(

        $row->identity_card_affiliate,
        $row->registration_affiliate,
        $row->full_name_affiliate,
        $row->separation,
        $row->code_loan,
        Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'), //fecha de desembolso
        $row->state_affiliate_loan_payment,

        Carbon::parse($row->estimated_date_loan_payment)->format('d/m/Y'), //fecha de pago/calculo
        Carbon::parse($row->date_loan_payment)->format('d/m/Y H:i:s'), //fecha de transacción
        $row->modality, //MOdalidad
        $row->sub_modality, //Sub modalidad
        $row->registration_borrower,
        $row->identity_card_borrower,
        $row->first_name_borrower,
        $row->second_name_borrower,
        $row->last_name_borrower,
        $row->mothers_last_name_borrower,
        $row->surname_husband_borrower,

        Util::money_format($row->capital_payment), //capital .. pagado
        Util::money_format($row->interest_payment), //interes corriente
        Util::money_format($row->penal_payment), // interes penal

        Util::money_format($row->interest_accumulated), //interes corriente pendiente
        Util::money_format($row->penal_accumulated), //interes penal pendiente
        Util::money_format($row->quota_loan_payment), //total pagado
        Util::money_format($row->previous_balance), // saldo anterior
        Util::money_format($row->previous_balance - $row->capital_payment), //saldo actual

        $row->paid_by_loan_payment, //pagado por
        $row->modality_shortened_loan_payment, // tipo de descuento
        $row->voucher_type_loan_payment, //TIPO DE DESCUENTO EFECTIVO O DEP EN CUENTA
        $row->voucher_loan_payment, //comprobante

        $row->code_loan_payment, //Nro de cobro*/
        $row->states_loan_payment, //estado del cobro
      );

      switch($row->voucher_type_loan_payment){
        case 'Efectivo': array_push($data->cash, $data_body);
            break; 
        default: array_push($data->dep_banc, $data_body);
            break;
      };

    }

    $export = new MultipleSheetExportPayment($data->cash,$data->dep_banc,'EFECTIVO','DEP EN CTA');
    return Excel::download($export, $File . '.xlsx');
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
    array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '>=', "%{$initial_date}%"));
  }
  if ($final_date != '') {
    array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '<=', "%{$final_date}%"));
  }
  array_push($conditions, array('view_loan_amortizations.states_loan_payment', 'ilike', "%{$state_pagado}%"));
  array_push($conditions, array('view_loan_amortizations.procedure_loan_payment', 'ilike', "%{$procedure_type}%"));
  
  $list_loan = DB::table('view_loan_amortizations')
              ->where($conditions)
              ->select('*')
              ->orderBy('code_loan', $order_loan)
              ->get();


  $File="ListadoAmortizacionesPorTipoDePago";
  $data_head=
        array("NRO DE PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE PAGO","FECHA DE TRANSACCIÓN","MODALIDAD","SUB MODALIDAD",
              "MATRICULA AFILIADO", "CI AFILIADO", "NOMBRE COMPLETO AFILIADO", "***","MATRICULA TITULAR", "CI TITULAR","APELLIDO CASADA", "APELLIDO PATERNO","APELLIDO MATERNO",
              "PRIMER NOMBRE","SEGUNDO NOMBRE","CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE", 
              "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","CBTE","NRO DE COBRO"
             );
  
  $data = collect();
  // AJUSTE-CONT
  $data->adjust = array($data_head);
  // REFINANCIAMIENTO
  $data->refinancing = array($data_head);

  foreach ($list_loan as $row){
    
    $padron = Loan::find($row->id_loan);
    $row->modality = $padron->modality->procedure_type->second_name;
    $row->sub_modality = $padron->modality->shortened;
    $row->separation = '***';

    $data_body = array(
                $row->code_loan,//nro de prestamo
                Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'),//fecha de desembolso
                $row->state_type_affiliate,//tipo (pasivo o activo)
                           
                Carbon::parse($row->estimated_date_loan_payment)->format('d/m/Y'),//fecha de pago
                Carbon::parse($row->date_loan_payment)->format('d/m/Y H:i:s'),//fecha de transacción
                $row->modality, //MOdalidad
                $row->sub_modality, //Sub modalidad

                $row->registration_affiliate,
                $row->identity_card_affiliate,
                $row->full_name_affiliate,
                "***",
                $row->registration_borrower,//matricula afiliado
                $row->identity_card_borrower,//matricula esposa

                $row->surname_husband_borrower,//ap casada
                $row->last_name_borrower,//ap paterno
                $row->mothers_last_name_borrower,//ap materno
                
                $row->first_name_borrower,//primer nombre
                $row->second_name_borrower,//segundo nombre
                Util::money_format($row->capital_payment),//capital .. pagado
                Util::money_format($row->interest_payment), //interes corriente 
                Util::money_format($row->penal_payment),// interes penal 
                Util::money_format($row->interest_remaining),//interes corriente pendiente
                Util::money_format($row->penal_remaining),//interes penal pendiente 
                Util::money_format($row->quota_loan_payment),//total pagado 
                Util::money_format($row->previous_balance),// saldo anterior 
                Util::money_format($row->current_balance),//saldo actual 

                $row->paid_by_loan_payment,//pagado por 
                $row->modality_shortened_loan_payment,// tipo de descuento 

                $row->voucher_type_loan_payment, //comprobante

                $row->code_loan_payment, //Nro de cobro
             );
             switch($row->modality_shortened_loan_payment){
              case 'AJUSTE-CONT': array_push($data->adjust, $data_body);
                  break; 
              case 'REFINANCIAMIENTO': array_push($data->refinancing, $data_body);
                  break;
            };
    }
    $export = new MultipleSheetExportPayment($data->adjust,$data->refinancing,'AJUSTE-CONT','REFINANCIAMIENTO');
    return Excel::download($export, $File.'.xlsx');
  }
 /** @group Reportes de amortizaciones 
   * Reporte de amortizaciones por complemento y fondo de retiro  
   * Reporte de amortizaciones amortizaciones por complemento y fondo de retiro 
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/reports_amortizations/report_complement_fondo.200.json
   */
  public function report_amortization_fondo_complement(Request $request)
  {
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
      $modality_type='Complemento Económico';

      $modality_type_fr='Fondo de Retiro';
      if ($initial_date != '') {
          array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '>=', "%{$initial_date}%"));
          array_push($conditions_fondo, array('view_loan_amortizations.estimated_date_loan_payment', '>=', "%{$initial_date}%"));
      }
      if ($final_date != '') {
          array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '<=', "%{$final_date}%"));
          array_push($conditions_fondo, array('view_loan_amortizations.estimated_date_loan_payment', '<=', "%{$final_date}%"));
      }
      array_push($conditions, array('view_loan_amortizations.modality_loan_payment', 'ilike', "%{$modality_type}%"));
      array_push($conditions_fondo, array('view_loan_amortizations.modality_loan_payment', 'ilike', "%{$modality_type_fr}%"));


      $list_loan = DB::table('view_loan_amortizations')
                    ->where($conditions)
                    ->select('*')
                    ->orderBy('code_loan', $order_loan)
                    ->get();

      foreach ($list_loan as $loan) {
          $padron = Loan::where('id', $loan->id_loan)->first();
          $loan->modality=$padron->modality->procedure_type->second_name;
          $loan->sub_modality=$padron->modality->shortened;
          $loan->separation='***';
      }
      $File="ListadoAmortizaciones";
      $data=array(
                 array("CI AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","COD PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE CALCULO","FECHA DE TRANSACCIÓN",
                 "MODALIDAD PRÉSTAMO","SUB MODALIDAD PRÉSTAMO",
                 "MATRICULA", "CI", "PRIMER NOMBRE","SEGUNDO NOMBRE","APELLIDO PATERNO","APELLIDO MATERNO","APELLIDO CASADA",
                 "CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE",
                 "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","CBTE","NRO DE COBRO","ESTADO AMORTIZACIÓN")
             );
      foreach ($list_loan as $row) {
          array_push($data, array(

                  $row->identity_card_affiliate,
                  $row->registration_affiliate,
                  $row->full_name_affiliate,
                  $row->separation,
                  $row->code_loan,
                  Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'),//fecha de desembolso
                  $row->state_affiliate_loan_payment,

                  Carbon::parse($row->estimated_date_loan_payment)->format('d/m/Y'),//fecha de pago/calculo
                  Carbon::parse($row->date_loan_payment)->format('d/m/Y H:i:s'),//fecha de transacción
                  $row->modality, //MOdalidad
                  $row->sub_modality, //Sub modalidad
                  $row->registration_borrower,
                  $row->identity_card_borrower,
                  $row->first_name_borrower,
                  $row->second_name_borrower,
                  $row->last_name_borrower,
                  $row->mothers_last_name_borrower,
                  $row->surname_husband_borrower,

                  Util::money_format($row->capital_payment),//capital .. pagado
                  Util::money_format($row->interest_payment), //interes corriente
                  Util::money_format($row->penal_payment),// interes penal

                  Util::money_format($row->interest_accumulated),//interes corriente pendiente
                  Util::money_format($row->penal_accumulated),//interes penal pendiente
                  Util::money_format($row->quota_loan_payment),//total pagado
                  Util::money_format($row->previous_balance),// saldo anterior
                  Util::money_format($row->previous_balance - $row->capital_payment),//saldo actual

                  $row->paid_by_loan_payment,//pagado por
                  $row->modality_shortened_loan_payment,// tipo de descuento
                  $row->voucher_loan_payment, //comprobante

                  $row->code_loan_payment, //Nro de cobro*/
                  $row->states_loan_payment//estado del cobro
                 ));
      }

      //fondo de retiro
      $list_loan_fondo = DB::table('view_loan_amortizations')
             ->where($conditions_fondo)
             ->select('*')
             ->orderBy('code_loan', $order_loan)
             ->get();

      foreach ($list_loan_fondo as $loan) {
          $padron = Loan::where('id', $loan->id_loan)->first();
          $loan->modality=$padron->modality->procedure_type->second_name;
          $loan->sub_modality=$padron->modality->shortened;
          $loan->separation='***';
      }

      $File="ListadoAmortizaciones";
      $data_fondo=array(
              array("CI AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","COD PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE CALCULO","FECHA DE TRANSACCIÓN",
              "MODALIDAD PRÉSTAMO","SUB MODALIDAD PRÉSTAMO",
              "MATRICULA", "CI", "PRIMER NOMBRE","SEGUNDO NOMBRE","APELLIDO PATERNO","APELLIDO MATERNO","APELLIDO CASADA",
              "CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE",
              "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","CBTE","NRO DE COBRO","ESTADO AMORTIZACIÓN")
          );
      foreach ($list_loan_fondo as $row) {
          array_push($data_fondo, array(

                  $row->identity_card_affiliate,
                  $row->registration_affiliate,
                  $row->full_name_affiliate,
                  $row->separation,
                  $row->code_loan,
                  Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'),//fecha de desembolso
                  $row->state_affiliate_loan_payment,

                  Carbon::parse($row->estimated_date_loan_payment)->format('d/m/Y'),//fecha de pago/calculo
                  Carbon::parse($row->date_loan_payment)->format('d/m/Y H:i:s'),//fecha de transacción
                  $row->modality, //MOdalidad
                  $row->sub_modality, //Sub modalidad
                  $row->registration_borrower,
                  $row->identity_card_borrower,
                  $row->first_name_borrower,
                  $row->second_name_borrower,
                  $row->last_name_borrower,
                  $row->mothers_last_name_borrower,
                  $row->surname_husband_borrower,

                  Util::money_format($row->capital_payment),//capital .. pagado
                  Util::money_format($row->interest_payment), //interes corriente
                  Util::money_format($row->penal_payment),// interes penal

                  Util::money_format($row->interest_accumulated),//interes corriente pendiente
                  Util::money_format($row->penal_accumulated),//interes penal pendiente
                  Util::money_format($row->quota_loan_payment),//total pagado
                  Util::money_format($row->previous_balance),// saldo anterior
                  Util::money_format($row->previous_balance - $row->capital_payment),//saldo actual

                  $row->paid_by_loan_payment,//pagado por
                  $row->modality_shortened_loan_payment,// tipo de descuento
                  $row->voucher_loan_payment, //comprobante

                  $row->code_loan_payment, //Nro de cobro*/
                  $row->states_loan_payment//estado del cobro
                 ));
      }

      $export = new MultipleSheetExportPayment($data, $data_fondo, 'COM-ECO', 'FRP');
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
    array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '>=', "%{$initial_date}%"));
  }
  if ($final_date != '') {
    array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', '<=', "%{$final_date}%"));
  }
  array_push($conditions, array('view_loan_amortizations.states_loan_payment', 'like', "%{$state_pagado}%"));
  //

        $list_loan = DB::table('view_loan_amortizations')
                          ->where($conditions)
                          ->select('*')
                          ->orderBy('code_loan', $order_loan)
                          ->get();

            foreach ($list_loan as $loan) {
                $padron = Loan::where('id', $loan->id_loan)->first();
                $loan->modality=$padron->modality->procedure_type->second_name;
                $loan->sub_modality=$padron->modality->shortened;
                $loan->separation='***';
            }
             $File="ListadoAmortizaciones";
             $data=array(
              array("CI AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","COD PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO","FECHA DE CALCULO","FECHA DE TRANSACCIÓN",
              "MODALIDAD PRÉSTAMO","SUB MODALIDAD PRÉSTAMO",
              "MATRICULA", "CI", "PRIMER NOMBRE","SEGUNDO NOMBRE","APELLIDO PATERNO","APELLIDO MATERNO","APELLIDO CASADA",
              "CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE",
              "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL","PAGADO POR","TIPO DESCUENTO","CBTE","NRO DE COBRO","ESTADO AMORTIZACIÓN")
          );
             foreach ($list_loan as $row){
                 array_push($data, array(
                  $row->identity_card_affiliate,
                  $row->registration_affiliate,
                  $row->full_name_affiliate,
                  $row->separation,
                  $row->code_loan,
                  Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'),//fecha de desembolso
                  $row->state_affiliate_loan_payment,

                  Carbon::parse($row->estimated_date_loan_payment)->format('d/m/Y'),//fecha de pago/calculo
                  Carbon::parse($row->date_loan_payment)->format('d/m/Y H:i:s'),//fecha de transacción
                  $row->modality, //MOdalidad
                  $row->sub_modality, //Sub modalidad
                  $row->registration_borrower,
                  $row->identity_card_borrower,
                  $row->first_name_borrower,
                  $row->second_name_borrower,
                  $row->last_name_borrower,
                  $row->mothers_last_name_borrower,
                  $row->surname_husband_borrower,

                  Util::money_format($row->capital_payment),//capital .. pagado
                  Util::money_format($row->interest_payment), //interes corriente
                  Util::money_format($row->penal_payment),// interes penal

                  Util::money_format($row->interest_accumulated),//interes corriente pendiente
                  Util::money_format($row->penal_accumulated),//interes penal pendiente
                  Util::money_format($row->quota_loan_payment),//total pagado
                  Util::money_format($row->previous_balance),// saldo anterior
                  Util::money_format($row->previous_balance - $row->capital_payment),//saldo actual

                  $row->paid_by_loan_payment,//pagado por
                  $row->modality_shortened_loan_payment,// tipo de descuento
                  $row->voucher_loan_payment, //comprobante

                  $row->code_loan_payment, //Nro de cobro*/
                  $row->states_loan_payment//estado del cobro
                 ));
             }
             $export = new ArchivoPrimarioExport($data);
             return Excel::download($export, $File.'.xlsx');
  }

/**
   * Listar amortizaciones generando reportes
   * Lista todos los amortizaciones con opcion a busquedas
   * @queryParam sortDesc Vector de orden descendente(0) o ascendente(1). Example: 0
   * @queryParam per_page Número de datos por página. Example: 8
   * @queryParam page Número de página. Example: 1
   * @queryParam excel Valor booleano para descargar  el docExcel. Example: true
   * @queryParam id_loan Buscar ID del Préstamo. Example: 1
   * @queryParam code_loan  Buscar código del Préstamo. Example: PTMO000012-2021
   * @queryParam code_payment  Buscar código del Pago. Example: PAY000001-2021
   * @queryParam date_loan_payment  Buscar fecha del Pago. Example: 2021-01-01
   * @queryParam disbursement_date_loan  Fecha de desembolso. Example: 2021-01-01
   * @queryParam state_type_affiliate  Estado del afiliado. Example: Activo
   * @queryParam identity_card_affiliate ID del afiliado. Example: 667895
   * @queryParam registration_affiliate ID del afiliado. Example: 667895MP
   * @queryParam full_name_affiliate Buscar por el nombre completo del afiliado. Example: RIVERA
   * @queryParam last_name_affiliate Buscar por primer apellido del afiliado. Example: RIVERA
   * @queryParam mothers_last_name_affiliate Buscar por segundo apellido del afiliado. Example: ARTEAG
   * @queryParam first_name_affiliate Buscar por primer Nombre del afiliado. Example: ABAD
   * @queryParam second_name_affiliate Buscar por segundo Nombre del afiliado. Example: FAUST
   * @queryParam surname_husband_affiliate Buscar por Apellido de casada Nombre del afiliado. Example: De LA CRUZ
   * @queryParam identity_card_borrower ID del afiliado. Example: 667895
   * @queryParam full_name_borrower Buscar por el nombre completo del afiliado. Example: RIVERA
   * @queryParam last_name_borrower Buscar por primer apellido del afiliado. Example: RIVERA
   * @queryParam mothers_last_name_borrower Buscar por segundo apellido del afiliado. Example: ARTEAG
   * @queryParam first_name_borrower Buscar por primer Nombre del afiliado. Example: ABAD
   * @queryParam second_name_borrower Buscar por segundo Nombre del afiliado. Example: FAUST
   * @queryParam surname_husband_borrower Buscar por Apellido de casada Nombre del afiliado. Example: De LA CRUZ
   * @queryParam pension_entity_affiliate Buscar por la La pension entidad del afiliado. Example: SENASIR
   * @queryParam sub_modality_loan Buscar por sub modalidad del préstamo. Example: Corto plazo sector activo
   * @queryParam modality_loan_payment Buscar por Modalidad del prestamo. Example: Préstamo a corto plazo
   * @queryParam state_type_affiliate Buscar por tipo de estado del afiliado. Example: Activo
   * @queryParam state_affiliate Buscar por estado del affiliado. Example: Servicio
   * @queryParam quota_loan Buscar por la quota del prestamo. Example: 1500
   * @queryParam states_loan_payments Buscar por el estado del pago. Example: Pagado
   * @queryParam modality_shortened_loan_payment Buscar por la modalidad de pago. Example: DES-SENASIR
   * @queryParam voucher_type_loan_payment Buscar por el tipo de pago. Example: Depósito Bancario
   * @authenticated
   * @responseFile responses/loan_payment/list_loan_payment_generate.200.json
   */

  public function list_loan_payments_generate(request $request)
  {
      // aumenta el tiempo máximo de ejecución de este script a 150 min:
  ini_set('max_execution_time', 9000);
  // aumentar el tamaño de memoria permitido de este script:
  ini_set('memory_limit', '960M');

  if($request->has('excel'))
       $excel = $request->boolean('excel');
  else 
       $excel =false;

  $order = request('sortDesc') ?? '';
  if($order != ''){
      if($order) $order_loan = 'Asc';
      if(!$order) $order_loan = 'Desc';

  }else{
   $order_loan = 'Desc';
  }
  $pagination_rows = request('per_page') ?? 10;
  $conditions = [];
  $conditions_or = [];
  //filtros
  $id_loan = request('id_loan') ?? '';
  $code_loan = request('code_loan') ?? '';
  $disbursement_date_loan = request('disbursement_date_loan') ?? '';

  $state_type_affiliate = request('state_type_affiliate') ?? '';
  $state_affiliate = request('state_affiliate') ?? '';

  $id_affiliate = request('id_affiliate') ?? '';
  $identity_card_affiliate = request('identity_card_affiliate') ?? '';
  $registration_affiliate = request('registration_affiliate') ?? '';

  $last_name_affiliate = request('last_name_affiliate') ?? '';
  $mothers_last_name_affiliate = request('mothers_last_name_affiliate') ?? '';
  $first_name_affiliate = request('first_name_affiliate') ?? '';
  $second_name_affiliate = request('second_name_affiliate') ?? '';
  $surname_husband_affiliate = request('surname_husband_affiliate') ?? '';
  $full_name_affiliate = request('full_name_affiliate') ?? '';

  $identity_card_borrower = request('identity_card_borrower') ?? '';
  $registration_borrower = request('registration_borrower') ?? '';
  $last_name_borrower = request('last_name_borrower') ?? '';
  $mothers_last_name_borrower = request('mothers_last_name_borrower') ?? '';
  $first_name_borrower = request('first_name_borrower') ?? '';
  $second_name_borrower = request('second_name_borrower') ?? '';
  $surname_husband_borrower = request('surname_husband_borrower') ?? '';
  $full_name_borrower = request('full_name_borrower') ?? '';

  $pension_entity_affiliate = request('pension_entity_affiliate') ?? '';
  
  $code_loan_payment = request('code_loan_payment') ?? '';
  $estimated_date_loan_payment = request('estimated_date_loan_payment') ?? '';
  $quota_loan_payment = request('quota_loan_payment') ?? '';
  $voucher_loan_payment = request('voucher_loan_payment') ?? '';

  $modality_loan_payment = request('modality_loan_payment') ?? '';
  $modality_shortened_loan_payment = request('modality_shortened_loan_payment') ?? '';
  $procedure_loan_payment = request('procedure_loan_payment') ?? '';

  $states_loan_payment = request('states_loan_payment') ?? '';

  $paid_by_loan_payment = request('paid_by_loan_payment') ?? '';

  $date_loan_payment = request('loan_payment_date') ?? '';
  $voucher_type_loan_payment = request('voucher_type_loan_payment') ?? '';

    if ($id_loan != '') {//1
      array_push($conditions, array('view_loan_amortizations.id_loan', 'ilike', "%{$id_loan}%"));
    }

    if ($code_loan != '') {//2
      array_push($conditions, array('view_loan_amortizations.code_loan', 'ilike', "%{$code_loan}%"));
    }

    if ($disbursement_date_loan != '') {//3
      array_push($conditions, array('view_loan_amortizations.disbursement_date_loan', 'ilike', "%{$disbursement_date_loan}%"));
    }

    if ($state_type_affiliate != '') {//4
      array_push($conditions, array('view_loan_amortizations.state_type_affiliate', 'ilike', "%{$state_type_affiliate}%"));
    }
    if ($state_affiliate != '') {//5
      array_push($conditions, array('view_loan_amortizations.state_affiliate', 'ilike', "%{$state_affiliate}%"));
    }

    if ($identity_card_affiliate != '') {//7
      array_push($conditions, array('view_loan_amortizations.identity_card_affiliate', 'ilike', "%{$identity_card_affiliate}%"));
    }
    if ($registration_affiliate != '') {//8
      array_push($conditions, array('view_loan_amortizations.registration_affiliate', 'ilike', "%{$registration_affiliate}%"));
    }


    if ($last_name_affiliate != '') {//9
      array_push($conditions, array('view_loan_amortizations.last_name_affiliate', 'ilike', "%{$last_name_affiliate}%"));
    }
    if ($mothers_last_name_affiliate != '') {//10
      array_push($conditions, array('view_loan_amortizations.mothers_last_name_affiliate', 'ilike', "%{$mothers_last_name_affiliate}%"));
    }

    if ($first_name_affiliate != '') {//11
      array_push($conditions, array('view_loan_amortizations.first_name_affiliate', 'ilike', "%{$first_name_affiliate}%"));//
    }
    if ($second_name_affiliate != '') {//12
      array_push($conditions, array('view_loan_amortizations.second_name_affiliate', 'ilike', "%{$second_name_affiliate}%"));
    }
    if ($surname_husband_affiliate != '') {//13
      array_push($conditions, array('view_loan_amortizations.surname_husband_affiliate', 'ilike', "%{$surname_husband_affiliate}%"));
    }
    if ($full_name_affiliate != '') {//13
        array_push($conditions, array('view_loan_amortizations.full_name_affiliate', 'ilike', "%{$full_name_affiliate}%"));
    }

    if ($identity_card_borrower != '') {//7
        array_push($conditions, array('view_loan_amortizations.identity_card_borrower', 'ilike', "%{$identity_card_borrower}%"));
      }
      if ($registration_borrower != '') {//8
        array_push($conditions, array('view_loan_amortizations.registration_borrower', 'ilike', "%{$registration_borrower}%"));
      }  
      if ($last_name_borrower != '') {//9
        array_push($conditions, array('view_loan_amortizations.last_name_borrower', 'ilike', "%{$last_name_borrower}%"));
      }
      if ($mothers_last_name_borrower != '') {//10
        array_push($conditions, array('view_loan_amortizations.mothers_last_name_borrower', 'ilike', "%{$mothers_last_name_borrower}%"));
      }
  
      if ($first_name_borrower != '') {//11
        array_push($conditions, array('view_loan_amortizations.first_name_borrower', 'ilike', "%{$first_name_borrower}%"));//
      }
      if ($second_name_borrower != '') {//12
        array_push($conditions, array('view_loan_amortizations.second_name_borrower', 'ilike', "%{$second_name_borrower}%"));
      }
      if ($surname_husband_borrower != '') {//13
        array_push($conditions, array('view_loan_amortizations.surname_husband_borrower', 'ilike', "%{$surname_husband_borrower}%"));
      }
      if ($full_name_borrower != '') {//13
          array_push($conditions, array('view_loan_amortizations.full_name_borrower', 'ilike', "%{$full_name_borrower}%"));
      }
    if ($pension_entity_affiliate != '') {//14
      array_push($conditions, array('view_loan_amortizations.pension_entity_affiliate', 'ilike', "%{$pension_entity_affiliate}%"));
    }

    if ($code_loan_payment != '') {//14
      array_push($conditions, array('view_loan_amortizations.code_loan_payment', 'ilike', "%{$code_loan_payment}%"));
    }

    if ($estimated_date_loan_payment != '') {//14
      array_push($conditions, array('view_loan_amortizations.estimated_date_loan_payment', 'ilike', "%{$estimated_date_loan_payment}%"));
    }

    if ($quota_loan_payment != '') {//14
      array_push($conditions, array('view_loan_amortizations.quota_loan_payment', 'ilike', "%{$quota_loan_payment}%"));
    }
    if ($voucher_loan_payment != '') {//14
      array_push($conditions, array('view_loan_amortizations.voucher_loan_payment', 'ilike', "%{$voucher_loan_payment}%"));
    }

    if ($modality_loan_payment != '') {
      array_push($conditions, array('view_loan_amortizations.modality_loan_payment', 'ilike', "%{$modality_loan_payment}%"));
    }
    if ($modality_shortened_loan_payment != '') {
      array_push($conditions, array('view_loan_amortizations.modality_shortened_loan_payment', 'ilike', "%{$modality_shortened_loan_payment}%"));
    }
    if ($procedure_loan_payment != '') {
      array_push($conditions, array('view_loan_amortizations.procedure_loan_payment', 'ilike', "%{$procedure_loan_payment}%"));
    }
    if ($states_loan_payment != '') {
      array_push($conditions, array('view_loan_amortizations.states_loan_payment', 'ilike', "%{$states_loan_payment}%"));
    }
    if ($paid_by_loan_payment != '') {
      array_push($conditions, array('view_loan_amortizations.paid_by_loan_payment', 'ilike', "%{$paid_by_loan_payment}%"));
    }
    if ($date_loan_payment != '') {
      array_push($conditions, array('view_loan_amortizations.date_loan_payment', 'ilike', "%{$date_loan_payment}%"));
    }
    if ($voucher_type_loan_payment != '') {
        array_push($conditions, array('view_loan_amortizations.voucher_type_loan_payment', 'ilike', "%{$voucher_type_loan_payment}%"));
    }

    if($excel==true){
        $list_loan = DB::table('view_loan_amortizations')
                      ->where($conditions)
                      ->select('*')
                      ->orderBy('code_loan', $order_loan)
                      ->get();

        $File="ListadoPrestamos";
        $data=array(
          array("CI AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","COD PRÉSTAMO", "FECHA DE DESEMBOLSO", "TIPO ESTADO","ESTADO AFILIADO",
          "MODALIDAD PRÉSTAMO",
          "MATRICULA", "CI", "PRIMER NOMBRE","SEGUNDO NOMBRE","APELLIDO PATERNO","APELLIDO MATERNO","APELLIDO CASADA","NOMBRE COMPLETO","ENTIDAD DE PENSIÓN",
          "**",
          "COD AMORTIZACIÓN","FECHA DE CALCULO","FECHA DE TRANSACCIÓN",

          "CAPITAL","INTERÉS CORRIENTE","INTERÉS PENAL","INTERÉS CORRIENTE PENDIENTE",
          "INTERÉS PENAL PENDIENTE","TOTAL PAGADO","SALDO ANTERIOR", "SALDO ACTUAL",
          "PAGADO POR","TIPO DESCUENTO","TIPO DE PAGO","CBTE","ESTADO AMORTIZACIÓN","COD TRANSACCIÓN")
      );
             foreach ($list_loan as $row){
                 array_push($data, array(

                  $row->identity_card_affiliate,
                  $row->registration_affiliate,
                  $row->full_name_affiliate,
                  "***",
                  $row->code_loan,
                  Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'),
                  $row->state_affiliate_loan_payment,
                  $row->state_affiliate,

                  $row->procedure_type_loan, //MOdalidad preetsamo

                  $row->registration_borrower,
                  $row->identity_card_borrower,
                  $row->first_name_borrower,
                  $row->second_name_borrower,
                  $row->last_name_borrower,
                  $row->mothers_last_name_borrower,
                  $row->surname_husband_borrower,
                  $row->full_name_borrower,
                  $row->pension_entity_affiliate,
                  "**",
                  $row->code_loan_payment,
                  Carbon::parse($row->estimated_date_loan_payment)->format('d/m/Y'),//fecha de pago/calculo
                  Carbon::parse($row->date_loan_payment)->format('d/m/Y H:i:s'),//fecha de transacción


                  Util::money_format($row->capital_payment),//capital .. pagado
                  Util::money_format($row->interest_payment), //interes corriente
                  Util::money_format($row->penal_payment),// interes penal

                  Util::money_format($row->interest_accumulated),//interes corriente pendiente
                  Util::money_format($row->penal_accumulated),//interes penal pendiente
                  Util::money_format($row->quota_loan_payment),//total pagado
                  Util::money_format($row->previous_balance),// saldo anterior
                  Util::money_format($row->current_balance),//saldo actual

                  $row->paid_by_loan_payment,//pagado por
                  $row->modality_shortened_loan_payment,// tipo de descuento
                  $row->voucher_type_loan_payment, //TIPO DE DESCUENTO EFECTIVO O DEP EN CUENTA
                  $row->voucher_loan_payment, //comprobante
                  $row->states_loan_payment,//estado del cobro
                  $row->code_voucher
                 ));
             }
             $export = new ArchivoPrimarioExport($data);
             return Excel::download($export, $File.'.xls');
    }else{
        $list_loan = DB::table('view_loan_amortizations')
        ->where($conditions)
        ->select('*')
        ->orderBy('code_loan', $order_loan)
        ->paginate($pagination_rows);
    return $list_loan;
    }
  }

  /** @group Reporte de amortizaciones en caja
   * Reporte de amortizaciones realizados en caja
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/reports_request_payments/payments_in_treasury.200.json
   */
  public function treasury_report(request $request, $standalone = true)
  {
    try{
      if($request->initial_date == null)
        $initial_date = Carbon::parse('1900-01-01');
      else
        $initial_date = $request->initial_date;
      if($request->final_date == null)
        $final_date = Carbon::now();
      else
        $final_date = $request->final_date;
      $payments = DB::table('view_loan_amortizations')
      ->where('date_loan_payment', '>=', Carbon::parse($initial_date)->startOfDay())
      ->where('date_loan_payment', '<=', Carbon::parse($final_date)->endOfDay())
      ->where('voucher_type_loan_payment', 'Efectivo')
      ->orWhere('date_loan_payment', '>=', Carbon::parse($initial_date)->startOfDay())
      ->where('date_loan_payment', '<=', Carbon::parse($final_date)->endOfDay())
      ->where('voucher_type_loan_payment', 'Depósito Bancario')
      ->orderBy('procedure_type_loan')
      ->get();
      $data = [
        'header' => [
            'direction' => 'DIRECCIÓN DE ASUNTOS ADMINISTRATIVOS',
            'unity' => 'UNIDAD DE TESORERIA',
            'table' => [
                ['Fecha', Carbon::now()->format('d-m-Y')],
                ['Hora', Carbon::now()->format('H:m:s')],
                ['Usuario', Auth::user()->username]
            ]
        ],
        'title' => 'AMORTIZACION PRESTAMO',
        'initial_date' => $request->initial_date,
        'final_date' => $request->final_date,
        'payments' => $payments,
        'file_title' => 'Ingresos Depositados en Tesoreria',
    ];
    $file_name = 'Ingresos Depositados en Tesoreria.pdf';
    $view = view()->make('loan.reports.payments_in_treasury_report')->with($data)->render();
    if ($standalone) return Util::pdf_to_base64([$view], $file_name, 'Depositos en Tesoreria' ,'letter', $request->copies ?? 1, false);
    return $view;
    }catch(\Exception $e){
      return $e;
    }
  }
}