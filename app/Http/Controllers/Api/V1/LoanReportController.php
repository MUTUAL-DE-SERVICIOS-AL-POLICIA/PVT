<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Module;
use Util;
use DB;
use Log;
use Illuminate\Support\Str;
use App\Affiliate;
use App\City;
use App\User;
use App\Loan;
use App\Role;
use App\LoanState;
use App\Observation;
use App\ObservationType;
use Carbon;
use App\ProcedureModality;
use App\LoanGlobalParameter;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;
use App\Exports\SheetExportPayment;
use App\Exports\MultipleSheetExportPayment;
use App\Exports\MultipleSheetExportPaymentMora;
use Illuminate\Support\Facades\Storage;
use App\Exports\FileWithMultipleSheetsReport;
use App\Exports\FileWithMultipleSheetsDefaulted;
use App\Record;
use App\Exports\LoansByStateExport;


class LoanReportController extends Controller
{
  /** @group Reportes de Prestamos
   * Reporte de prestamos desembolsados 
   * Reporte de prestamos desembolsados o vigentes 
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/report_loans/loan_desembolsado.200.json
   */

   public function report_loan_vigent(Request $request){
    // aumenta el tiempo máximo de ejecución de este script a 150 min:
    ini_set('max_execution_time', 9000);
    // aumentar el tamaño de memoria permitido de este script:
    ini_set('memory_limit', '960M');

    $order_loan = 'Desc';
    $initial_date = request('initial_date') ?? '';
    $final_date = request('final_date') ?? '';
    $state_vigente='Vigente';
    $conditions = [];
    if ($initial_date != '') {
      array_push($conditions, array('loans.disbursement_date', '>=', "%{$initial_date}%"));
    }
    if ($final_date != '') {
        $date = $request->final_date.' 23:59:59';
      array_push($conditions, array('loans.disbursement_date', '<=', "%{$date}%"));
    }
    
    array_push($conditions, array('loan_states.name', 'ilike', "%{$state_vigente}%"));
    //desde aqui
    if ($initial_date != '' && $final_date != '') {
        $date_ini = $request->initial_date.' 00:00:00';
        $date_fin = $request->final_date.' 23:59:59';

        $list_loan = Loan::where('state_id', LoanState::where('name', 'Vigente')->first()->id)->whereBetween('disbursement_date', [$date_ini, $date_fin])->orWhere('state_id', LoanState::where('name', 'Liquidado')->first()->id)->whereBetween('disbursement_date', [$date_ini, $date_fin])->get();
    }else{
        if ($final_date != '') {
            $date_fin = $request->final_date.' 23:59:59';
            $list_loan = Loan::where('state_id', LoanState::where('name', 'Vigente')->first()->id)->where('disbursement_date', '<=', $date_fin)->orWhere('state_id', LoanState::where('name', 'Liquidado')->first()->id)->where('disbursement_date', '<=', $date_fin)->get();

        }else{
            if ($initial_date != '') {
                $date_ini = $request->initial_date.' 00:00:00';
                $list_loan = Loan::where('state_id', LoanState::where('name', 'Vigente')->first()->id)->where('disbursement_date', '>=', $date_ini)->orWhere('state_id', LoanState::where('name', 'Liquidado')->first()->id)->where('disbursement_date', '>=', $date_ini)->get();
            }else{
                $list_loan = Loan::where('state_id', LoanState::where('name', 'Vigente')->first()->id)->orWhere('state_id', LoanState::where('name', 'Liquidado')->first()->id)->get();
            }
        } 
    }
               $File="ListadoPrestamosDesembolsados";
               $data=array(
                   array( "NUP", "CI AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","COD PRESTAMO", "FECHA DE SOLICITUD", "FECHA DE DESEMBOLSO",
                   "DPTO","TIPO ESTADO","ESTADO AFILIADO","MODALIDAD","SUB MODALIDAD",
                   "CEDULA DE IDENTIDAD","EXP","MATRICULA",
                   "PRIMER NOMBRE","SEGUNDO NOMBRE","PATERNO","MATERNO","APELLIDO CASADA","CATEGORÍA","GRADO","CELULAR","***",
                   "NRO CBTE CONTABLE","SALDO ACTUAL","AMPLIACIÓN","MONTO DESEMBOLSADO","MONTO REFINANCIADO","LIQUIDO DESEMBOLSADO",
                   "PLAZO","ESTÁDO PRÉSTAMO","DESTINO CREDITO", "SIGEP")
               );
               foreach ($list_loan as $loan){
               foreach($loan->getBorrowers() as $lender){
                if(isset($loan->affiliate->cell_phone_number)){
                    $cel = str_replace(array("(", ")", "-"), '', $loan->affiliate->cell_phone_number);
                    $cel = explode(",",$cel);
                }
                   array_push($data, array(
                    $lender->id_affiliate,
                    $lender->identity_card_affiliate,
                    $lender->registration_affiliate,
                    $lender->full_name_affiliate,
                    $loan->guarantor_amortizing? '***' : '***',
                    $loan->code,
                    Carbon::parse($loan->request_date)->format('d/m/Y'),
                    Carbon::parse($loan->disbursement_date)->format('d/m/Y H:i:s'),
                    $loan->city->name,
                    $lender->state_type_affiliate,
                    $lender->state_affiliate,
                    $loan->modality->procedure_type->name,
                    $loan->modality->shortened,
                    $lender->identity_card_borrower,
                    $lender->city_exp_first_shortened_borrower,
                    $lender->registration_borrower,
                    $lender->first_name_borrower,
                    $lender->second_name_borrower,
                    $lender->last_name_borrower,
                    $lender->mothers_last_name_borrower,
                    $lender->surname_husband_borrower,
                    $lender->category_name,
                    $lender->shortened_degree,
                    $cel[0],'***',
                    $loan->num_accounting_voucher,
                    Util::money_format($loan->balance),//SALDO ACTUAL
                    $loan->parent_reason,
                    Util::money_format($loan->amount_approved),
                    //$loan->parent_reason? Util::money_format($loan->amount_approved - $loan->refinancing_balance) : '0,00',//MONTO REFINANCIADO//MONTO REFINANCIADO
                    //$loan->parent_reason? Util::money_format($loan->refinancing_balance):Util::money_format($loan->amount_approved),// LIQUIDO DESEMBOLSADO
                    Loan::whereId($loan->id)->first()->balance_parent_refi(),
                    $loan->amount_approved - (Loan::whereId($loan->id)->first()->balance_parent_refi()),
                    $loan->loan_term,//plazo
                    $loan->state->name,//estado del prestamo
                    $loan->destiny->name,
                    $loan->number_payment_type
                   ));
               }
            }
               $export = new ArchivoPrimarioExport($data);
               return Excel::download($export, $File.'.xlsx');
   }

   /** @group Reportes de Prestamos
   * Reporte de prestamos del estado de cartera 
   * Reporte de prestamos del estado de cartera (vigente - cancelado)
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/report_loans/loan_desembolsado.200.json
   */
    public function report_loan_state_cartera(Request $request)
    {
        ini_set('max_execution_time', 9000);
        ini_set('memory_limit', '2048M');
        DB::connection()->disableQueryLog();
        $final_date = $request->has('final_date')
            ? Carbon::parse($request->input('final_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        app()->instance('suppress-db-log', true);
        
        $filename = 'ListadoPrestamosVigenteLiquidado';
        $export = new LoansByStateExport($final_date);
        return Excel::download($export, $filename.'.xlsx');
        app()->forgetInstance('suppress-db-log');
    }

    private function cleanPhone($number)
    {
        return str_replace(['(', ')', '-'], '', $number);
    }

    private function guarantorData($guarantor, $status)
    {
        if (!$status) {
            return array_fill(0, 9, '');
        }
        return $guarantor ? [
            $guarantor->identity_card,
            $guarantor->registration,
            $guarantor->last_name,
            $guarantor->mothers_last_name,
            $guarantor->surname_husband,
            $guarantor->first_name,
            $guarantor->second_name,
            $guarantor->degree->shortened ?? '',
            $this->cleanPhone($guarantor->cell_phone_number)
        ] : array_fill(0, 9, '');
    }

   /** @group Reportes de Prestamos
   * Reporte de prestamos en mora 
   * Reporte de prestamos prestamos en mora PARCIAL TOTAL MORA
   * @authenticated
   * @responseFile responses/report_loans/loan_desembolsado.200.json
   */

  public function report_loans_mora(Request $request){
    // aumenta el tiempo máximo de ejecución de este script a 150 min:
    ini_set('max_execution_time', 9000);
    // aumentar el tamaño de memoria permitido de este script:
    ini_set('memory_limit', '960M');
    
    $final_date = request('date') ? Carbon::parse(request('date'))->endOfDay() : Carbon::now()->endOfDay();
    $state = LoanState::whereName('Vigente')->first();

    $loans=Loan::where('state_id',$state->id)->orderBy('code')->where('disbursement_date', '<', Carbon::parse($final_date))->get();
    $loans_mora_total = collect();
    $loans_mora_parcial = collect();
    $loans_mora = collect();
    //mora
    foreach($loans as $loan){
        if(count($loan->payments) > 0 && Carbon::parse($loan->last_payment_validated->estimated_date) < $final_date && Carbon::parse($loan->last_payment_validated->estimated_date)->endOfDay()->diffInDays($final_date) > $loan->loan_procedure->loan_global_parameter->days_current_interest){
            if(count($loan->borrowerGuarantors)>0){
                $loan->guarantor = $loan->borrowerGuarantors;
            }
            $loan->lenders = $loan->borrower[0];
            if(count($loan->personal_references)>0){
                $loan->personal_ref = $loan->personal_references;
            }
            $loan->separation="*";
            $loans_mora->push($loan);
            continue;
          }

        if(count($loan->payments)== 0 && Carbon::parse($loan->disbursement_date)->diffInDays($final_date) > $loan->loan_procedure->loan_global_parameter->days_current_interest){
            if(count($loan->borrowerGuarantors)>0){
                $loan->guarantor = $loan->borrowerGuarantors;
            }
            $loan->lenders = $loan->borrower[0];
            if(count($loan->personal_references)>0){
                $loan->personal_ref = $loan->personal_references;
            }
            $loan->separation="*"; 
    
            $loans_mora_total->push($loan);
            continue;
          }

        if($loan->last_payment_validated && !$loan->regular_payments_date($final_date)){
            if(count($loan->borrowerGuarantors)>0){
                $loan->guarantor = $loan->borrowerGuarantors;
            }
            $loan->lenders = $loan->borrower[0];
            if(count($loan->personal_references)>0){
                $loan->personal_ref = $loan->personal_references;
            }
            $loan->separation="*";
            $loans_mora_parcial->push($loan);
            continue;
          }
    }

    //prestamomora total
    $File="PrestamosMoraTotal";
        $data_mora_total=array(
            array("MATRICULA AFILIADO","CI AFILIADO", "EXP","NOMBRE COMPLETO AFILIADO", "***","MATRICULA PRESTATARIO","CI PRESTATARIO","EXP","NOMBRE COMPLETO PRESTATARIO","NRO DE CEL.1","NRO DE CEL.2","NRO FIJO","CIUDAD","DIRECCIÓN","PTMO","FECHA DESEMBOLSO",
            "NRO DE CUOTAS","TASA ANUAL","FECHA DEL ÚLTIMO PAGO","TIPO DE PAGO","CUOTA MENSUAL","SALDO ACTUAL","ÉSTADO DEL AFILIADO","MODALIDAD","SUB MODALIDAD","DÍAS MORA",
            "*","NOMBRE COMPLETO (Ref. Personal)","NRO DE TEL. FIJO (Ref. Personal)","NRO DE CEL (Ref. Personal)","DIRECCIÓN(Ref. Personal)",
            "**","MATRICULA AFILIADO (GARANTE 1)", "CI AFILIADO (GARANTE 1)", "EXP (GARANTE 1)", "NOMBRE COMPLETO AFILIADO (GARANTE 1)", "*-->*","MATRICULA (GARANTE TITULAR 1)","CI (GARANTE TITULAR 1)","EXP (GARANTE TITULAR 1)","NOMBRE COMPLETO (GARANTE TITULAR 1)","NRO DE TEL. FIJO","NRO DE CEL. 1","NRO DE CEL. 2","ESTADO DEL AFILIADO",
            "***", "MATRICULA AFILIADO (GARANTE 2)", " CI AFILIADO (GARANTE 2)", "EXP (GARANTE 2)", "NOMBRE COMPLETO AFILIADO (GARANTE 2)", "*-->*","MATRICULA (GARANTE TITULAR 2)","CI (GARANTE TITULAR 2)","EXP (GARANTE TITULAR 2)","NOMBRE COMPLETO (GARANTE TITULAR 2)","NRO DE TEL. FIJO","NRO DE CEL. 1","NRO DE CEL. 2","ESTADO DEL AFILIADO")
        );
        foreach ($loans_mora_total as $row){
            $phone_number = str_replace(array("(", ")", "-"), '', $row->lenders->cell_phone_number);
            $phone_number = explode(",",$phone_number);
            $sw = false;
            $sw_g = false;
            $sw_g2 = false;
            if(count($phone_number)>1)
                $sw = true;
            if(isset($row->guarantor[0])){
                $phone_number_g1 = str_replace(array("(", ")", "-"), '', $row->guarantor[0]->cell_phone_number);
                $phone_number_g1 = explode(",",$phone_number_g1);
                if(count($phone_number_g1)>1)
                    $sw_g = true;
            }
            if(isset($row->guarantor[1])){
                $phone_number_g2 = str_replace(array("(", ")", "-"), '', $row->guarantor[1]->cell_phone_number);
                $phone_number_g2 = explode(",",$phone_number_g2);
                if(count($phone_number_g2)>1)
                    $sw_g2 = true;
            }
            array_push($data_mora_total, array(
                $row->getBorrowers()->first()->registration_affiliate,
                $row->getBorrowers()->first()->identity_card_affiliate,
                $row->getBorrowers()->first()->city_exp_first_shortened_affiliate,
                $row->getBorrowers()->first()->full_name_affiliate,
                "***",
                $row->getBorrowers()->first()->registration_borrower,
                $row->getBorrowers()->first()->identity_card_borrower,
                $row->getBorrowers()->first()->city_exp_first_shortened_borrower,
                $row->getBorrowers()->first()->full_name_borrower,
                $phone_number[0],
                $sw ? $phone_number[1] : "",
                $row->lenders->phone_number,
                $row->lenders->address->cityName(),
                $row->lenders->address->full_address,
                $row->code,
                Carbon::parse($row->disbursement_date)->format('d/m/Y H:i:s'),
                $row->loan_term,
                $row->interest->annual_interest,
                $row->lastPaymentValidated? Carbon::parse($row->lastPaymentValidated->estimated_date)->format('d-m-Y'):'sin registro',
                $row->lastPaymentValidated? $row->lastPaymentValidated->modality->shortened:'sin registro',
                Util::money_format($row->estimated_quota),
                Util::money_format($row->balance),
                $row->lenders->affiliate_state->affiliate_state_type->name,
                $row->modality->procedure_type->second_name,
                $row->modality->shortened,
                Carbon::parse($row->disbursement_date)->startOfDay()->diffInDays(Carbon::parse($final_date)->endOfDay()) - $row->loan_procedure->loan_global_parameter->days_current_interest,
                $row->separation,
                $row->personal_ref ? $row->personal_ref[0]->first_name.' '.$row->personal_ref[0]->second_name.' '.$row->personal_ref[0]->last_name.' '.$row->personal_ref[0]->mothers_last_name.' '.$row->personal_ref[0]->surname_husband:'no tiene registro',
                $row->personal_ref ? $row->personal_ref[0]->phone_number:'S/R',
                $row->personal_ref ? str_replace(array("(", ")", "-"), '', $row->personal_ref[0]->cell_phone_number) :'S/R',
                $row->personal_ref ? $row->personal_ref[0]->address:'S/R',
                $row->separation,
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->registration_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->identity_card_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->city_exp_first_shortened_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->full_name_affiliate : '',
                "*Titular-->*",
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->registration_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->identity_card_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->city_exp_first_shortened_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->full_name_guarantor : '',
                $row->guarantor ? $row->guarantor[0]->phone_number:'S/R',
                isset($row->guarantor[0]) ? $phone_number_g1[0] : '',
                $sw_g ? $phone_number_g1[1] : "",
                $row->guarantor ? $row->guarantor[0]->affiliate_state->affiliate_state_type->name:'',
                $row->separation,
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->registration_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->identity_card_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->city_exp_first_shortened_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->full_name_affiliate : '',
                "*Titular-->*",
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->registration_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->identity_card_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->city_exp_first_shortened_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->full_name_guarantor : '',
                isset($row->guarantor[1]) ? $row->guarantor[1]->phone_number:'S/R',
                isset($row->guarantor[1]) ? $phone_number_g2[0] : '',
                $sw_g2 ? $phone_number_g2[1] : "",
                isset($row->guarantor[1]) ? $row->guarantor[1]->affiliate_state->affiliate_state_type->name:'',
         
            ));
        }
        //prestamomora parcial
        $File="PrestamosMoraParcial";
        $data_mora_parcial=array(
            array("MATRICULA AFILIADO","CI AFILIADO", "EXP","NOMBRE COMPLETO AFILIADO", "***","MATRICULA","CI","EXP","NOMBRE COMPLETO","NRO DE CEL. 1","NRO DE CEL. 2","NRO FIJO","CIUDAD","DIRECCIÓN","PTMO","FECHA DESEMBOLSO",
            "NRO DE CUOTAS","TASA ANUAL","FECHA DEL ÚLTIMO PAGO","TIPO DE PAGO","CUOTA MENSUAL","SALDO ACTUAL","ÉSTADO DEL AFILIADO","MODALIDAD","SUB MODALIDAD","DÍAS",
            "*","NOMBRE COMPLETO (Ref. Personal)","NRO DE TEL. FIJO (Ref. Personal)","NRO DE CEL (Ref. Personal)","DIRECCIÓN(Ref. Personal)",
            "**","MATRICULA AFILIADO (GARANTE 1)", "CI AFILIADO (GARANTE 1)", "EXP (GARANTE 1)", "NOMBRE COMPLETO AFILIADO (GARANTE 1)", "*-->*","MATRICULA (GARANTE TITULAR 1)","CI (GARANTE TITULAR 1)","EXP (GARANTE TITULAR 1)","NOMBRE COMPLETO (GARANTE TITULAR 1)","NRO DE TEL. FIJO","NRO DE CEL. 1","NRO DE CEL. 2","ESTADO DEL AFILIADO",
            "***","MATRICULA AFILIADO (GARANTE 2)", "CI AFILIADO (GARANTE 2)", "EXP (GARANTE 2)", "NOMBRE COMPLETO AFILIADO (GARANTE 2)", "*-->*","MATRICULA (GARANTE TITULAR 2)","CI (GARANTE TITULAR 2)","EXP (GARANTE TITULAR 2)","NOMBRE COMPLETO (GARANTE TITULAR 2)","NRO DE TEL. FIJO","NRO DE CEL. 1","NRO DE CEL. 2","ESTADO DEL AFILIADO")
        );
        foreach ($loans_mora_parcial as $row){
            $phone_number = str_replace(array("(", ")", "-"), '', $row->lenders->cell_phone_number);
            $phone_number = explode(",",$phone_number);
            $sw = false;
            $sw_g = false;
            $sw_g2 = false;
            if(count($phone_number)>1)
                $sw = true;
            if(isset($row->guarantor[0])){
                $phone_number_g1 = str_replace(array("(", ")", "-"), '', $row->guarantor[0]->cell_phone_number);
                $phone_number_g1 = explode(",",$phone_number_g1);
                if(count($phone_number_g1)>1)
                    $sw_g = true;
            }
            if(isset($row->guarantor[1])){
                $phone_number_g2 = str_replace(array("(", ")", "-"), '', $row->guarantor[1]->cell_phone_number);
                $phone_number_g2 = explode(",",$phone_number_g2);
                if(count($phone_number_g2)>1)
                    $sw_g2 = true;
            }
            array_push($data_mora_parcial, array(
                $row->getBorrowers()->first()->registration_affiliate,
                $row->getBorrowers()->first()->identity_card_affiliate,
                $row->getBorrowers()->first()->city_exp_first_shortened_affiliate,
                $row->getBorrowers()->first()->full_name_affiliate,
                "***",
                $row->getBorrowers()->first()->registration_borrower,
                $row->getBorrowers()->first()->identity_card_borrower,
                $row->getBorrowers()->first()->city_exp_first_shortened_borrower,
                $row->getBorrowers()->first()->full_name_borrower,
                $phone_number[0],
                $sw ? $phone_number[1] : "",
                $row->lenders->phone_number,
                $row->lenders->address->cityName(),
                $row->lenders->address->full_address,
                $row->code,
                Carbon::parse($row->disbursement_date)->format('d/m/Y H:i:s'),
                $row->loan_term,
                $row->interest->annual_interest,
                $row->lastPaymentValidated? Carbon::parse($row->lastPaymentValidated->estimated_date)->format('d-m-Y'):'sin registro',
                $row->lastPaymentValidated? $row->lastPaymentValidated->modality->shortened:'sin registro',
                Util::money_format($row->estimated_quota),
                Util::money_format($row->balance),
                $row->lenders->affiliate_state->affiliate_state_type->name,
                $row->modality->procedure_type->second_name,
                $row->modality->shortened,
                $row->last_payment_validated ? Carbon::parse($row->last_payment_validated->estimated_date)->diffInDays(Carbon::parse($final_date)) : Carbon::parse($row->disbursement_date)->diffInDays(Carbon::parse($final_date)),
                $row->separation,
                $row->personal_ref ? $row->personal_ref[0]->first_name.' '.$row->personal_ref[0]->second_name.' '.$row->personal_ref[0]->last_name.' '.$row->personal_ref[0]->mothers_last_name.' '.$row->personal_ref[0]->surname_husband:'no tiene registro',
                $row->personal_ref ? $row->personal_ref[0]->phone_number:'S/R',
                $row->personal_ref ? str_replace(array("(", ")", "-"), '', $row->personal_ref[0]->cell_phone_number) :'S/R',
                $row->personal_ref ? $row->personal_ref[0]->address:'S/R',
                $row->separation.$row->separation,
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->registration_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->identity_card_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->city_exp_first_shortened_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->full_name_affiliate : '',
                "*Titular-->*",
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->registration_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->identity_card_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->city_exp_first_shortened_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->full_name_guarantor : '',
                $row->guarantor ? $row->guarantor[0]->phone_number:'S/R',
                isset($row->guarantor[0]) ? $phone_number_g1[0] : '',
                $sw_g ? $phone_number_g1[1] : "",
                $row->guarantor ? $row->guarantor[0]->affiliate_state->affiliate_state_type->name:'',
                $row->separation.$row->separation.$row->separation,
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->registration_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->identity_card_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->city_exp_first_shortened_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->full_name_affiliate : '',
                "*Titular-->*",
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->registration_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->identity_card_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->city_exp_first_shortened_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->full_name_guarantor : '',
                isset($row->guarantor[1]) ? $row->guarantor[1]->phone_number:'S/R',
                isset($row->guarantor[1]) ? $phone_number_g2[0] : '',
                $sw_g2 ? $phone_number_g2[1] : "",
                isset($row->guarantor[1]) ? $row->guarantor[1]->affiliate_state->affiliate_state_type->name:'',
            ));
        }
        //prestamomora 
        $File="PrestamosMora";
        $data_mora=array(
            array("MATRICULA AFILIADO","CI AFILIADO", "EXP","NOMBRE COMPLETO AFILIADO", "***","MATRICULA","CI","EXP","NOMBRE COMPLETO","NRO DE CEL.1","NRO DE CEL.2","NRO FIJO","CIUDAD","DIRECCIÓN","PTMO","FECHA DESEMBOLSO",
            "NRO DE CUOTAS","TASA ANUAL","FECHA DEL ÚLTIMO PAGO","TIPO DE PAGO","CUOTA MENSUAL","SALDO ACTUAL","ÉSTADO DEL AFILIADO","MODALIDAD","SUB MODALIDAD","DÍAS MORA",
            "*","NOMBRE COMPLETO (Ref. Personal)","NRO DE TEL. FIJO (Ref. Personal)","NRO DE CEL (Ref. Personal)","DIRECCIÓN(Ref. Personal)",
            "**","MATRICULA AFILIADO (GARANTE 1)", "CI AFILIADO (GARANTE 1)", "EXP (GARANTE 1)", "NOMBRE COMPLETO AFILIADO (GARANTE 1)", "*-->*","MATRICULA (GARANTE TITULAR 1)","CI (GARANTE TITULAR 1)","EXP (GARANTE TITULAR 1)","NOMBRE COMPLETO (GARANTE TITULAR 1)","NRO DE TEL. FIJO","NRO DE CEL1","NRO DE CEL2","ESTADO DEL AFILIADO",
            "***","MATRICULA AFILIADO (GARANTE 2)", "CI AFILIADO (GARANTE 2)", "EXP (GARANTE 2)", "NOMBRE COMPLETO AFILIADO (GARANTE 2)", "*-->*","MATRICULA (GARANTE TITULAR 2)","CI (GARANTE TITULAR 2)","EXP (GARANTE TITULAR 2)","NOMBRE COMPLETO (GARANTE TITULAR 2)","NRO DE TEL. FIJO","NRO DE CEL1","NRO DE CEL.2","ESTADO DEL AFILIADO")
        );
        foreach ($loans_mora as $row){
            $phone_number = str_replace(array("(", ")", "-"), '', $row->lenders->cell_phone_number);
            $phone_number = explode(",",$phone_number);
            $sw = false;
            $sw_g = false;
            $sw_g2 = false;
            if(count($phone_number)>1)
                $sw = true;
            if(isset($row->guarantor[0])){
                $phone_number_g1 = str_replace(array("(", ")", "-"), '', $row->guarantor[0]->cell_phone_number);
                $phone_number_g1 = explode(",",$phone_number_g1);
                if(count($phone_number_g1)>1)
                    $sw_g = true;
            }
            if(isset($row->guarantor[1])){
                $phone_number_g2 = str_replace(array("(", ")", "-"), '', $row->guarantor[1]->cell_phone_number);
                $phone_number_g2 = explode(",",$phone_number_g2);
                if(count($phone_number_g2)>1)
                    $sw_g2 = true;
            }
            array_push($data_mora, array(
                $row->getBorrowers()->first()->registration_affiliate,
                $row->getBorrowers()->first()->identity_card_affiliate,
                $row->getBorrowers()->first()->city_exp_first_shortened_affiliate,
                $row->getBorrowers()->first()->full_name_affiliate,
                "***",
                $row->getBorrowers()->first()->registration_borrower,
                $row->getBorrowers()->first()->identity_card_borrower,
                $row->getBorrowers()->first()->city_exp_first_shortened_borrower,
                $row->getBorrowers()->first()->full_name_borrower,
                $phone_number[0],
                $sw ? $phone_number[1] : "",
                $row->lenders->phone_number,
                $row->lenders->address->cityName(),
                $row->lenders->address->full_address,
                $row->code,
                Carbon::parse($row->disbursement_date)->format('d/m/Y H:i:s'),
                $row->loan_term,
                $row->interest->annual_interest,
                $row->lastPaymentValidated? Carbon::parse($row->lastPaymentValidated->estimated_date)->format('d-m-Y'):'sin registro',
                $row->lastPaymentValidated? $row->lastPaymentValidated->modality->shortened:'sin registro',
                Util::money_format($row->estimated_quota),
                Util::money_format($row->balance),
                $row->lenders->affiliate_state->affiliate_state_type->name,
                $row->modality->procedure_type->second_name,
                $row->modality->shortened,
                Carbon::parse($row->last_payment_validated->estimated_date)->diffInDays(Carbon::parse($final_date)) - $row->loan_procedure->loan_global_parameter->days_current_interest,
                $row->separation,
                $row->personal_ref ? $row->personal_ref[0]->first_name.' '.$row->personal_ref[0]->second_name.' '.$row->personal_ref[0]->last_name.' '.$row->personal_ref[0]->mothers_last_name.' '.$row->personal_ref[0]->surname_husband:'no tiene registro',
                $row->personal_ref ? $row->personal_ref[0]->phone_number:'S/R',
                $row->personal_ref ? str_replace(array("(", ")", "-"), '', $row->personal_ref[0]->cell_phone_number) :'S/R',
                $row->personal_ref ? $row->personal_ref[0]->address:'S/R',
                $row->separation.$row->separation,
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->registration_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->identity_card_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->city_exp_first_shortened_affiliate : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->full_name_affiliate : '',
                "*Titular-->*",
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->registration_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->identity_card_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->city_exp_first_shortened_guarantor : '',
                $row->guarantor ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[0]->affiliate_id)->first()->full_name_guarantor : '',
                $row->guarantor ? $row->guarantor[0]->phone_number:'S/R',
                isset($row->guarantor[0]) ? $phone_number_g1[0] : '',
                $sw_g ? $phone_number_g1[1] : "",
                $row->guarantor ? $row->guarantor[0]->affiliate_state->affiliate_state_type->name:'',
                $row->separation.$row->separation.$row->separation,
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->registration_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->identity_card_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->city_exp_first_shortened_affiliate : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->full_name_affiliate : '',
                "*Titular-->*",
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->registration_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->identity_card_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->city_exp_first_shortened_guarantor : '',
                isset($row->guarantor[1]) ? $row->getGuarantors()->where('id_affiliate',$row->guarantor[1]->affiliate_id)->first()->full_name_guarantor : '',
                isset($row->guarantor[1]) ? $row->guarantor[1]->phone_number:'S/R',
                isset($row->guarantor[1]) ? $phone_number_g2[0] : '',
                $sw_g2 ? $phone_number_g2[1] : "",
                isset($row->guarantor[1]) ? $row->guarantor[1]->affiliate_state->affiliate_state_type->name:'',
            ));
        }
        $export = new MultipleSheetExportPaymentMora($data_mora_total,$data_mora_parcial,$data_mora,'MORA TOTAL','MORA PARCIAL','MORA');
        return Excel::download($export, $File.'.xlsx');
  }

  /** @group Reportes de Prestamos
   * Reporte de prestamos en mora 
   * Reporte de prestamos prestamos en mora PARCIAL TOTAL MORA con llamada a procedimientos almacenados en la base de datos
   * @authenticated
   * @responseFile responses/report_loans/loan_desembolsado.200.json
   */


  public function report_loans_mora_v2(Request $request){
    // aumenta el tiempo máximo de ejecución de este script a 150 min:
    ini_set('max_execution_time', 900000);
    // aumentar el tamaño de memoria permitido de este script:
    ini_set('memory_limit', '2048M');
    
    $final_date = request('date') ? Carbon::parse(request('date'))->endOfDay() : Carbon::now()->endOfDay();

    $datos_mora = DB::select("select loans_mora_data(?)",[$final_date]);
    $loans_mora = json_decode($datos_mora[0]->loans_mora_data);

    $File = "PrestamosMora";
    $data_head = array("NUP","MATRICULA AFILIADO","CI AFILIADO", "EXP","NOMBRE COMPLETO AFILIADO", "***","MATRICULA","CI","EXP","NOMBRE COMPLETO","CATEGORÍA","GRADO","NRO DE CEL.1","NRO DE CEL.2","NRO FIJO","CIUDAD","DIRECCIÓN","PTMO","FECHA DESEMBOLSO",
    "NRO DE CUOTAS","TASA ANUAL","FECHA DEL ÚLTIMO PAGO","TIPO DE PAGO","CUOTA MENSUAL","SALDO ACTUAL","ÉSTADO DEL AFILIADO","MODALIDAD","SUB MODALIDAD","DÍAS MORA",
    "*","NOMBRE COMPLETO (Ref. Personal)","NRO DE TEL. FIJO (Ref. Personal)","NRO DE CEL (Ref. Personal)","DIRECCIÓN(Ref. Personal)",
    "**","MATRICULA AFILIADO (GARANTE 1)", "CI AFILIADO (GARANTE 1)", "EXP (GARANTE 1)", "NOMBRE COMPLETO AFILIADO (GARANTE 1)", "*-->*","MATRICULA (GARANTE TITULAR 1)","CI (GARANTE TITULAR 1)","EXP (GARANTE TITULAR 1)","NOMBRE COMPLETO (GARANTE TITULAR 1)","NRO DE TEL. FIJO","NRO DE CEL1","NRO DE CEL2","ESTADO DEL AFILIADO",
    "***","MATRICULA AFILIADO (GARANTE 2)", "CI AFILIADO (GARANTE 2)", "EXP (GARANTE 2)", "NOMBRE COMPLETO AFILIADO (GARANTE 2)", "*-->*","MATRICULA (GARANTE TITULAR 2)","CI (GARANTE TITULAR 2)","EXP (GARANTE TITULAR 2)","NOMBRE COMPLETO (GARANTE TITULAR 2)","NRO DE TEL. FIJO","NRO DE CEL1","NRO DE CEL.2","ESTADO DEL AFILIADO");
    $data = collect();
    // PRESTAMOS EN MORA
    $data->mora = array($data_head);
    // PRESTAMOS EN MORA TOTAL
    $data->mora_total = array($data_head);
    // PRESTAMOS EN MORA PARCIAL
    $data->mora_parcial = array($data_head);
    $row = 0;
    for( $row ; $row < sizeof($loans_mora) ; $row++ ){
        $data_body = array(
            $loans_mora[$row]->id_affiliate ? $loans_mora[$row]->id_affiliate : '',
            $loans_mora[$row]->registration_affiliate ? $loans_mora[$row]->registration_affiliate : '',
            $loans_mora[$row]->identity_card_affiliate ? $loans_mora[$row]->identity_card_affiliate : '',
            $loans_mora[$row]->city_exp_first_shortened_affiliate ? $loans_mora[$row]->city_exp_first_shortened_affiliate : '',
            $loans_mora[$row]->full_name_affiliate,
            "***",
            $loans_mora[$row]->registration_borrower ? $loans_mora[$row]->registration_borrower : '',
            $loans_mora[$row]->identity_card_borrower ? $loans_mora[$row]->identity_card_borrower : '',
            $loans_mora[$row]->city_exp_first_shortened_borrower ? $loans_mora[$row]->city_exp_first_shortened_borrower : '',
            $loans_mora[$row]->full_name_borrower,
            $loans_mora[$row]->category_name ? $loans_mora[$row]->category_name : '',
            $loans_mora[$row]->shortened_degree ? $loans_mora[$row]->shortened_degree : '',
            isset($loans_mora[$row]->cell_phone->number[0]) ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->cell_phone->number[0]) : 'S/R',
            isset($loans_mora[$row]->cell_phone->number[1]) ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->cell_phone->number[1]) : 'S/R',
            isset($loans_mora[$row]->phone->number[0]) ? str_replace(array("(", ")", "-"), '',$loans_mora[$row]->phone->number[0]) : 'S/R',
            $loans_mora[$row]->address[0]->name,
            $loans_mora[$row]->address[0]->description,
            $loans_mora[$row]->code,
            Carbon::parse($loans_mora[$row]->disbursement_date)->format('d/m/Y H:i:s'),
            $loans_mora[$row]->loan_term,
            $loans_mora[$row]->annual_interest,
            $loans_mora[$row]->estimated_date ? Carbon::parse($loans_mora[$row]->estimated_date)->format('d-m-Y'):'sin registro',
            $loans_mora[$row]->shortened ? $loans_mora[$row]->shortened :'sin registro',
            Util::money_format($loans_mora[$row]->estimated_quota),
            Util::money_format($loans_mora[$row]->balance),
            $loans_mora[$row]->name,
            $loans_mora[$row]->second_name,
            $loans_mora[$row]->sub_modality,
            $loans_mora[$row]->days_mora,
            "*",
            isset($loans_mora[$row]->reference[0]->full_name) ? $loans_mora[$row]->reference[0]->full_name : 'no tiene registro',
            isset($loans_mora[$row]->reference[0]->phone_number) ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->reference[0]->phone_number) : 'S/R',
            isset($loans_mora[$row]->reference[0]->cell_phone_number) ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->reference[0]->cell_phone_number) : 'S/R',
            isset($loans_mora[$row]->reference[0]->address) ? $loans_mora[$row]->reference[0]->address : 'S/R',
            "**",
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->registration_affiliate : '',
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->identity_card_affiliate : '',
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->city_exp_first_shortened_affiliate : '',
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->full_name_affiliate : '',
            "*Titular-->*",
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->registration_guarantor : '',
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->identity_card_guarantor : '',
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->city_exp_first_shortened_guarantor : '',
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->full_name_guarantor : '',
            isset($loans_mora[$row]->guarantors[0]) ? ($loans_mora[$row]->guarantors[0]->phone_number ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->guarantors[0]->phone_number) : 'S/R') : '',
            isset($loans_mora[$row]->guarantors[0]) ? (isset($loans_mora[$row]->guarantors[0]->cell_phone->number[0]) ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->guarantors[0]->cell_phone->number[0]) : 'S/R') : '',
            isset($loans_mora[$row]->guarantors[0]) ? (isset($loans_mora[$row]->guarantors[0]->cell_phone->number[1]) ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->guarantors[0]->cell_phone->number[1]) : 'S/R') : '',
            isset($loans_mora[$row]->guarantors[0]) ? $loans_mora[$row]->guarantors[0]->name : '',
            "**",
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->registration_affiliate : '',
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->identity_card_affiliate : '',
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->city_exp_first_shortened_affiliate : '',
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->full_name_affiliate : '',
            "*Titular-->*",
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->registration_guarantor : '',
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->identity_card_guarantor : '',
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->city_exp_first_shortened_guarantor : '',
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->full_name_guarantor : '',
            isset($loans_mora[$row]->guarantors[1]) ? ($loans_mora[$row]->guarantors[1]->phone_number ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->guarantors[1]->phone_number) : 'S/R') : '',
            isset($loans_mora[$row]->guarantors[1]) ? (isset($loans_mora[$row]->guarantors[1]->cell_phone->number[0]) ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->guarantors[1]->cell_phone->number[0]) : 'S/R') : '',
            isset($loans_mora[$row]->guarantors[1]) ? (isset($loans_mora[$row]->guarantors[1]->cell_phone->number[1]) ? str_replace(array("(", ")", "-"), '', $loans_mora[$row]->guarantors[1]->cell_phone->number[1]) : 'S/R') : '',
            isset($loans_mora[$row]->guarantors[1]) ? $loans_mora[$row]->guarantors[1]->name : '',
        );
        
        switch($loans_mora[$row]->type){
            case 'mora': array_push($data->mora, $data_body);
                break; 
            case 'mora_total': array_push($data->mora_total, $data_body);
                break;
            case 'mora_parcial': array_push($data->mora_parcial, $data_body);
                break;
        };
    }
    $export = new MultipleSheetExportPaymentMora($data->mora_total,$data->mora_parcial,$data->mora,'MORA TOTAL','MORA PARCIAL','MORA');
    return Excel::download($export, $File.'.xlsx');

  }

  /** @group Reportes de Prestamos
    * generar reporte de nuevos prestamos desembolsados
    * reporte de los nuevos desembolsados por periodo
	* @bodyParam date date Fecha para el periodo a consultar. Example: 16-06-2021
    * @responseFile responses/report_loans/loan_desembolsado.200.json
    * @authenticated
    */
    public function loan_information(Request $request)
    {
        $month = Carbon::parse($request->date)->format('m');
        $year = Carbon::parse($request->date)->format('Y');
        $loans = Loan::whereMonth('disbursement_date', $month)->whereYear('disbursement_date', $year)->orderBy('disbursement_date')->get();
        $date_previous = Carbon::parse($request->date)->startOfMonth()->subMonth()->endOfMonth()->format('Y-m-d');

        $date_calculate = Carbon::parse($request->date)->endOfMonth()->format('Y-m-d');

        $date_limit = Carbon::create(Carbon::parse($date_previous)->format('Y'), Carbon::parse($date_previous)->format('m'), 15);
        $date_limit = Carbon::parse($date_limit)->format('Y-m-d');
        $date_limit = Carbon::parse($date_limit)->endOfDay();

        $loans_request = Loan::where('state_id', LoanState::where('name', 'Vigente')->first()->id)->where('disbursement_date','<=', $date_limit)->orderBy('disbursement_date')->get();

        $id_senasir = array();
        foreach(ProcedureModality::where('name', 'like', '%SENASIR%')->get() as $procedure)
             array_push($id_senasir, $procedure->id);
        $id_comando = array();
        foreach(ProcedureModality::where('name', 'like', '%Activo%')->orWhere('name', 'like', '%Disponibilidad%')->get() as $procedure)
             array_push($id_comando, $procedure->id);

             $command_sheet_before=array(
                array("CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
                "COD PRESTAMO","MODALIDAD","SUB MODALIDAD", "FECHA DE DESEMBOLSO", "DPTO", "TIPO ESTADO","ESTADO AFILIADO",
                "MATRICULA PRESTATARIO", "CI PRESTATARIO","EXP PRESTATARIO", "1ER NOMBRE", "2DO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO","APELLIDO DE CASADA", "SALDO ACTUAL", "CUOTA FIJA MENSUAL", "DESCUENTO PROGRAMADO", "INTERES","Amort. TIT o GAR?","***",
                "GAR Estado","GAR Tipo de estado","Matricula garante","GAR CI", "GAR Exp","GAR Primer Nombre","GAR Segundo Nombre",
                "GAR 1er Apellido","GAR 2do Apellido","GAR Apellido de Casada","GAR Cuota fija","GAR descuento","***","GAR2 Estado","GAR2 Tipo de estado","Matricula garante","GAR2 CI", "GAR2 Exp","GAR2 Primer Nombre","GAR2 Segundo Nombre",
                "GAR2 1er Apellido","GAR2 2do Apellido","GAR2 Apellido de Casada","GAR2 Cuota fija","GAR2 descuento")
            );
         $command_sheet_later=array(
            array("CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
            "COD PRESTAMO","MODALIDAD","SUB MODALIDAD", "FECHA DE DESEMBOLSO", "DPTO", "TIPO ESTADO","ESTADO AFILIADO",
            "MATRICULA PRESTATARIO", "CI PRESTATARIO","EXP PRESTATARIO", "1ER NOMBRE", "2DO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO","APELLIDO DE CASADA", "SALDO ACTUAL", "CUOTA FIJA MENSUAL", "DESCUENTO PROGRAMADO", "INTERES","Amort. TIT o GAR?","***",
            "GAR Estado","GAR Tipo de estado","Matricula garante","GAR CI", "GAR Exp","GAR Primer Nombre","GAR Segundo Nombre",
            "GAR 1er Apellido","GAR 2do Apellido","GAR Apellido de Casada","GAR Cuota fija","GAR descuento","***","GAR2 Estado","GAR2 Tipo de estado","Matricula garante","GAR2 CI", "GAR2 Exp","GAR2 Primer Nombre","GAR2 Segundo Nombre",
            "GAR2 1er Apellido","GAR2 2do Apellido","GAR2 Apellido de Casada","GAR2 Cuota fija","GAR2 descuento")
        );
         $senasir_sheet_before=array(
            array("CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
            "COD PRESTAMO","MODALIDAD","SUB MODALIDAD", "FECHA DE DESEMBOLSO", "DPTO", "TIPO ESTADO","ESTADO AFILIADO",
            "MATRICULA PRESTATARIO", "CI PRESTATARIO","EXP PRESTATARIO", "1ER NOMBRE", "2DO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO","APELLIDO DE CASADA", "SALDO ACTUAL", "CUOTA FIJA MENSUAL", "DESCUENTO PROGRAMADO", "INTERES","Amort. TIT o GAR?","***",
            "GAR Estado","GAR Tipo de estado","Matricula garante","GAR CI", "GAR Exp","GAR Primer Nombre","GAR Segundo Nombre",
            "GAR 1er Apellido","GAR 2do Apellido","GAR Apellido de Casada","GAR Cuota fija","GAR descuento","***","GAR2 Estado","GAR2 Tipo de estado","Matricula garante","GAR2 CI", "GAR2 Exp","GAR2 Primer Nombre","GAR2 Segundo Nombre",
            "GAR2 1er Apellido","GAR2 2do Apellido","GAR2 Apellido de Casada","GAR2 Cuota fija","GAR2 descuento")
        );
         $senasir_sheet_later=array(
            array("CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
            "COD PRESTAMO","MODALIDAD","SUB MODALIDAD", "FECHA DE DESEMBOLSO", "DPTO", "TIPO ESTADO","ESTADO AFILIADO",
            "MATRICULA PRESTATARIO", "CI PRESTATARIO","EXP PRESTATARIO", "1ER NOMBRE", "2DO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO","APELLIDO DE CASADA", "SALDO ACTUAL", "CUOTA FIJA MENSUAL", "DESCUENTO PROGRAMADO", "INTERES","Amort. TIT o GAR?","***",
            "GAR Estado","GAR Tipo de estado","Matricula garante","GAR CI", "GAR Exp","GAR Primer Nombre","GAR Segundo Nombre",
            "GAR 1er Apellido","GAR 2do Apellido","GAR Apellido de Casada","GAR Cuota fija","GAR descuento","***","GAR2 Estado","GAR2 Tipo de estado","Matricula garante","GAR2 CI", "GAR2 Exp","GAR2 Primer Nombre","GAR2 Segundo Nombre",
            "GAR2 1er Apellido","GAR2 2do Apellido","GAR2 Apellido de Casada","GAR2 Cuota fija","GAR2 descuento")
        );

         $command_ancient=array(
            array("CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
            "COD PRESTAMO","MODALIDAD","SUB MODALIDAD", "FECHA DE DESEMBOLSO", "DPTO", "TIPO ESTADO","ESTADO AFILIADO",
            "MATRICULA PRESTATARIO", "CI PRESTATARIO","EXP PRESTATARIO", "1ER NOMBRE", "2DO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO","APELLIDO DE CASADA", "SALDO ACTUAL", "CUOTA FIJA MENSUAL", "DESCUENTO PROGRAMADO", "INTERES","Amort. TIT o GAR?","***",
            "GAR Estado","GAR Tipo de estado","Matricula garante","GAR CI", "GAR Exp","GAR Primer Nombre","GAR Segundo Nombre",
            "GAR 1er Apellido","GAR 2do Apellido","GAR Apellido de Casada","GAR Cuota fija","GAR descuento","***","GAR2 Estado","GAR2 Tipo de estado","Matricula garante","GAR2 CI", "GAR2 Exp","GAR2 Primer Nombre","GAR2 Segundo Nombre",
            "GAR2 1er Apellido","GAR2 2do Apellido","GAR2 Apellido de Casada","GAR2 Cuota fija","GAR2 descuento")
        );
          $senasir_ancient=array(
            array("CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
            "COD PRESTAMO","MODALIDAD","SUB MODALIDAD", "FECHA DE DESEMBOLSO", "DPTO", "TIPO ESTADO","ESTADO AFILIADO",
            "MATRICULA PRESTATARIO", "CI PRESTATARIO","EXP PRESTATARIO", "1ER NOMBRE", "2DO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO","APELLIDO DE CASADA", "SALDO ACTUAL", "CUOTA FIJA MENSUAL", "DESCUENTO PROGRAMADO", "INTERES","Amort. TIT o GAR?","***",
            "GAR Estado","GAR Tipo de estado","Matricula garante","GAR CI", "GAR Exp","GAR Primer Nombre","GAR Segundo Nombre",
            "GAR 1er Apellido","GAR 2do Apellido","GAR Apellido de Casada","GAR Cuota fija","GAR descuento","***","GAR2 Estado","GAR2 Tipo de estado","Matricula garante","GAR2 CI", "GAR2 Exp","GAR2 Primer Nombre","GAR2 Segundo Nombre",
            "GAR2 1er Apellido","GAR2 2do Apellido","GAR2 Apellido de Casada","GAR2 Cuota fija","GAR2 descuento")
        );

         foreach($loans as $loan){
            if(Carbon::parse($loan->disbursement_date)->day <= $loan->loan_procedure->loan_global_parameter->offset_interest_day){
                 if(in_array($loan->procedure_modality_id, $id_comando))
                 {
                     foreach($loan->getBorrowers() as $lender)
                     {
                         array_push($command_sheet_before, array(
                            $lender->identity_card_affiliate,
                            $lender->city_exp_first_shortened_affiliate,
                            $lender->registration_affiliate,
                            $lender->full_name_affiliate,
                            $loan->guarantor_amortizing? '***' : '***',
                             $lender->code_loan,
                             $lender->sub_modality_loan,
                             $lender->shortened_sub_modality_loan,
                             Carbon::parse($lender->disbursement_date_loan)->format('d/m/Y H:i:s'),
                             $lender->city_loan,
                             $lender->state_type_affiliate,
                             $lender->state_affiliate,
                             $lender->registration_borrower,
                             $lender->identity_card_borrower,
                             $lender->city_exp_first_shortened_borrower,
                             $lender->first_name_borrower,
                             $lender->second_name_borrower,
                             $lender->last_name_borrower,
                             $lender->mothers_last_name_borrower,
                             $lender->surname_husband_borrower,
                             Util::money_format($loan->balance),
                             Util::money_format($loan->estimated_quota),
                             Util::money_format($loan->get_amount_payment($date_calculate,false,'T')),
                             $loan->interest->annual_interest,
                             $loan->guarantor_amortizing? 'Amort. Garante':'Amort. Titular',
                             $loan->guarantor_amortizing? '***' : '***',
                        ));
                     }
                 }
                 else{
                     if(in_array($loan->procedure_modality_id, $id_senasir))
                     {
                         foreach($loan->getBorrowers() as $lender)
                         {
                            array_push($senasir_sheet_before, array(
                                $lender->identity_card_affiliate,
                                $lender->city_exp_first_shortened_affiliate,
                                $lender->registration_affiliate,
                                $lender->full_name_affiliate,
                                $loan->guarantor_amortizing? '***' : '***',
                                 $lender->code_loan,
                                 $lender->sub_modality_loan,
                                 $lender->shortened_sub_modality_loan,
                                 Carbon::parse($lender->disbursement_date_loan)->format('d/m/Y H:i:s'),
                                 $lender->city_loan,
                                 $lender->state_type_affiliate,
                                 $lender->state_affiliate,
                                 $lender->registration_borrower,
                                 $lender->identity_card_borrower,
                                 $lender->city_exp_first_shortened_borrower,
                                 $lender->first_name_borrower,
                                 $lender->second_name_borrower,
                                 $lender->last_name_borrower,
                                 $lender->mothers_last_name_borrower,
                                 $lender->surname_husband_borrower,
                                 Util::money_format($loan->balance),
                                 Util::money_format($loan->estimated_quota),
                                 Util::money_format($loan->get_amount_payment($date_calculate,false,'T')),
                                 $loan->interest->annual_interest,
                                 $loan->guarantor_amortizing? 'Amort. Garante':'Amort. Titular',
                                 $loan->guarantor_amortizing? '***' : '***',
                            ));
                         }
                     }
                 }
             }
         }
         $sub_month = Carbon::parse($request->date)->subMonth()->format('m');
         $loans_before = Loan::whereMonth('disbursement_date', $sub_month)->whereYear('disbursement_date', $year)->orderBy('disbursement_date')->get();//considerar caso fin de año
         foreach($loans_before as $loan){
            if(Carbon::parse($loan->disbursement_date)->day > $loan->loan_procedure->loan_global_parameter->offset_interest_day){
                 if(in_array($loan->procedure_modality_id, $id_comando))
                 {
                     foreach($loan->getBorrowers() as $lender)
                     {
                        array_push($command_sheet_later, array(
                            $lender->identity_card_affiliate,
                            $lender->city_exp_first_shortened_affiliate,
                            $lender->registration_affiliate,
                            $lender->full_name_affiliate,
                            $loan->guarantor_amortizing? '***' : '***',
                             $lender->code_loan,
                             $lender->sub_modality_loan,
                             $lender->shortened_sub_modality_loan,
                             Carbon::parse($lender->disbursement_date_loan)->format('d/m/Y H:i:s'),
                             $lender->city_loan,
                             $lender->state_type_affiliate,
                             $lender->state_affiliate,
                             $lender->registration_borrower,
                             $lender->identity_card_borrower,
                             $lender->city_exp_first_shortened_borrower,
                             $lender->first_name_borrower,
                             $lender->second_name_borrower,
                             $lender->last_name_borrower,
                             $lender->mothers_last_name_borrower,
                             $lender->surname_husband_borrower,
                             Util::money_format($loan->balance),
                             Util::money_format($loan->estimated_quota),
                             Util::money_format($loan->get_amount_payment($date_calculate,false,'T')),
                             $loan->interest->annual_interest,
                             $loan->guarantor_amortizing? 'Amort. Garante':'Amort. Titular',
                             $loan->guarantor_amortizing? '***' : '***',
                        ));
                     }
                 }
                 else{
                     if(in_array($loan->procedure_modality_id, $id_senasir))
                     {
                         foreach($loan->getBorrowers() as $lender)
                         {
                            array_push($senasir_sheet_later, array(
                                $lender->identity_card_affiliate,
                                $lender->city_exp_first_shortened_affiliate,
                                $lender->registration_affiliate,
                                $lender->full_name_affiliate,
                                $loan->guarantor_amortizing? '***' : '***',
                                 $lender->code_loan,
                                 $lender->sub_modality_loan,
                                 $lender->shortened_sub_modality_loan,
                                 Carbon::parse($lender->disbursement_date_loan)->format('d/m/Y H:i:s'),
                                 $lender->city_loan,
                                 $lender->state_type_affiliate,
                                 $lender->state_affiliate,
                                 $lender->registration_borrower,
                                 $lender->identity_card_borrower,
                                 $lender->city_exp_first_shortened_borrower,
                                 $lender->first_name_borrower,
                                 $lender->second_name_borrower,
                                 $lender->last_name_borrower,
                                 $lender->mothers_last_name_borrower,
                                 $lender->surname_husband_borrower,
                                 Util::money_format($loan->balance),
                                 Util::money_format($loan->estimated_quota),
                                 Util::money_format($loan->get_amount_payment($date_calculate,false,'T')),
                                 $loan->interest->annual_interest,
                                 $loan->guarantor_amortizing? 'Amort. Garante':'Amort. Titular',
                                 $loan->guarantor_amortizing? '***' : '***',
                            ));
                         }
                     }
                 }
             }
         }
         foreach($loans_request as $loan){
              if(in_array($loan->procedure_modality_id, $id_comando))
              {
                  foreach($loan->getBorrowers() as $lender)
                  {
                    array_push($command_ancient, array(
                        $lender->identity_card_affiliate,
                        $lender->city_exp_first_shortened_affiliate,
                        $lender->registration_affiliate,
                        $lender->full_name_affiliate,
                        $loan->guarantor_amortizing? '***' : '***',
                         $lender->code_loan,
                         $lender->sub_modality_loan,
                         $lender->shortened_sub_modality_loan,
                         Carbon::parse($lender->disbursement_date_loan)->format('d/m/Y H:i:s'),
                         $lender->city_loan,
                         $lender->state_type_affiliate,
                         $lender->state_affiliate,
                         $lender->registration_borrower,
                         $lender->identity_card_borrower,
                         $lender->city_exp_first_shortened_borrower,
                         $lender->first_name_borrower,
                         $lender->second_name_borrower,
                         $lender->last_name_borrower,
                         $lender->mothers_last_name_borrower,
                         $lender->surname_husband_borrower,
                         Util::money_format($loan->balance),
                         Util::money_format($loan->estimated_quota),
                         Util::money_format($loan->get_amount_payment($date_calculate,false,'T')),
                         $loan->interest->annual_interest,
                         $loan->guarantor_amortizing? 'Amort. Garante':'Amort. Titular',
                         $loan->guarantor_amortizing? '***' : '***',
                    ));
                  }
              }
              else{
                  if(in_array($loan->procedure_modality_id, $id_senasir))
                  {
                      foreach($loan->getBorrowers() as $lender)
                      {
                        array_push($senasir_ancient, array(
                            $lender->identity_card_affiliate,
                            $lender->city_exp_first_shortened_affiliate,
                            $lender->registration_affiliate,
                            $lender->full_name_affiliate,
                            $loan->guarantor_amortizing? '***' : '***',
                             $lender->code_loan,
                             $lender->sub_modality_loan,
                             $lender->shortened_sub_modality_loan,
                             Carbon::parse($lender->disbursement_date_loan)->format('d/m/Y H:i:s'),
                             $lender->city_loan,
                             $lender->state_type_affiliate,
                             $lender->state_affiliate,
                             $lender->registration_borrower,
                             $lender->identity_card_borrower,
                             $lender->city_exp_first_shortened_borrower,
                             $lender->first_name_borrower,
                             $lender->second_name_borrower,
                             $lender->last_name_borrower,
                             $lender->mothers_last_name_borrower,
                             $lender->surname_husband_borrower,
                             Util::money_format($loan->balance),
                             Util::money_format($loan->estimated_quota),
                             Util::money_format($loan->get_amount_payment($date_calculate,false,'T')),
                             $loan->interest->annual_interest,
                             $loan->guarantor_amortizing? 'Amort. Garante':'Amort. Titular',
                             $loan->guarantor_amortizing? '***' : '***',
                        ));
                      }
                  }
              }
      }

         $file_name = $month.'-'.$year;
         $extension = '.xlsx';
         $export = new FileWithMultipleSheetsReport($command_sheet_later, $command_sheet_before, $senasir_sheet_later, $senasir_sheet_before,$command_ancient,$senasir_ancient);
         return Excel::download($export, $file_name.$extension);
    }
 
    
    /** @group Reportes de Prestamos
     * Reporte descuentos por garantia
     * reporte de prestamos amortizados por los garantes
     * @responseFile responses/report_loans/loan_desembolsado.200.json
     * @authenticated
     */
    public function loan_defaulted_guarantor()
    {
         $month = Carbon::now()->format('m');
         $year = Carbon::now()->format('Y');
         $loans = Loan::where('state_id', LoanState::where('name', 'Vigente')->first()->id)->where('guarantor_amortizing', true)->get();
         $date_calculate = Carbon::now()->endOfMonth()->format('Y-m-d');
         $command_sheet_dafaulted=array(
             array("NUP","CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
             "COD PRESTAMO", "FECHA DE DESEMBOLSO", "DPTO", "TIPO ESTADO","ESTADO AFILIADO",
             "MATRICULA PRESTATARIO", "CI PRESTATARIO","EXP PRESTATARIO", "1ER NOMBRE", "2DO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO","APELLIDO DE CASADA", "SALDO ACTUAL", "CUOTA FIJA MENSUAL", "DESCUENTO PROGRAMADO", "INTERES","Amort. TIT o GAR?","***",
             "CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
             "GAR Estado","GAR Tipo de estado","Matricula garante","GAR CI", "GAR Exp","GAR Primer Nombre","GAR Segundo Nombre",
             "GAR 1er Apellido","GAR 2do Apellido","GAR Apellido de Casada","GAR Cuota fija","GAR descuento","***",
             "CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","GAR2 Estado","GAR2 Tipo de estado","Matricula garante","GAR2 CI", "GAR2 Exp","GAR2 Primer Nombre","GAR2 Segundo Nombre",
             "GAR2 1er Apellido","GAR2 2do Apellido","GAR2 Apellido de Casada","GAR2 Cuota fija","GAR2 descuento")
         );
         $senasir_sheet_defaulted=array(
             array("NUP","CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
             "COD PRESTAMO", "FECHA DE DESEMBOLSO", "DPTO", "TIPO ESTADO","ESTADO AFILIADO",
             "MATRICULA PRESTATARIO", "CI PRESTATARIO","EXP PRESTATARIO", "1ER NOMBRE", "2DO NOMBRE", "APELLIDO PATERNO", "APELLIDO MATERNO","APELLIDO DE CASADA", "SALDO ACTUAL", "CUOTA FIJA MENSUAL", "DESCUENTO PROGRAMADO", "INTERES","Amort. TIT o GAR?","***",
             "CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***",
             "GAR Estado","GAR Tipo de estado","Matricula garante","GAR CI", "GAR Exp","GAR Primer Nombre","GAR Segundo Nombre",
             "GAR 1er Apellido","GAR 2do Apellido","GAR Apellido de Casada","GAR Cuota fija","GAR descuento","***",
             "CI AFILIADO","EXP AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","***","GAR2 Estado","GAR2 Tipo de estado","Matricula garante","GAR2 CI", "GAR2 Exp","GAR2 Primer Nombre","GAR2 Segundo Nombre",
             "GAR2 1er Apellido","GAR2 2do Apellido","GAR2 Apellido de Casada","GAR2 Cuota fija","GAR2 descuento")
         );
         $id_senasir = array();
        foreach(ProcedureModality::where('name', 'like', '%SENASIR')->get() as $procedure)
             array_push($id_senasir, $procedure->id);
        $id_comando = array();
        foreach(ProcedureModality::where('name', 'like', '%Activo')->orWhere('name', 'like', '%Activo%')->get() as $procedure)
             array_push($id_comando, $procedure->id);
 
         foreach($loans as $loan){
             if(in_array($loan->procedure_modality_id, $id_comando))
             {
                 foreach($loan->borrower as $lender)
                 {
                      $loan->guarantor = $loan->guarantors;
                     array_push($command_sheet_dafaulted, array(
                        $lender->affiliate()->id,
                        $lender->affiliate()->identity_card,
                        $lender->affiliate()->city_identity_card->first_shortened,
                        $lender->affiliate()->registration,
                        $lender->affiliate()->full_name,
                        "*Prestatario-->",
                        $loan->code,
                        Carbon::parse($loan->disbursement_date)->format('d/m/Y H:i:s'),
                        $loan->city->name,
                        $lender->affiliate_state->name,
                        $lender->affiliate_state->affiliate_state_type->name,
                        $lender->registration,
                        $lender->identity_card,
                        $lender->city_identity_card->first_shortened,
                        $lender->first_name,
                        $lender->second_name,
                        $lender->last_name,
                        $lender->mothers_last_name,
                        $lender->surname_husband,
                        Util::money_format($loan->balance),
                        Util::money_format($lender->quota_treat),
                        Util::money_format($loan->get_amount_payment($date_calculate,false,'T')),
                        $loan->interest->annual_interest,
                        $loan->guarantor_amortizing ? 'Amort. Garante':'Amort. Titular',                        
                        "*Titular-->*",
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate->identity_card : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate->city_identity_card->first_shortened : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate->registration : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate->full_name : '',
                        "*Garante-->*",
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate_state->name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate_state->affiliate_state_type->name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->registration : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->identity_card: '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->city_identity_card->first_shortened : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->first_name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->second_name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->last_name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->mothers_last_name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->surname_husband : '',
                        $loan->guarantor_amortizing ? Util::money_format($loan->borrowerGuarantors[0]->quota_treat) : '',
                        $loan->guarantor_amortizing ? Util::money_format($loan->get_amount_payment($date_calculate,false,'G')) : '',

                        "*Titular-->",
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate->identity_card : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate->city_identity_card->first_shortened : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate->registration : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate->full_name : '',
                        "*garante-->*",
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate_state: '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate_state_type: '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->registration : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->identity_card : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->city_identity_card->first_shortened : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->first_name : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->second_name : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->last_name : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->mothers_last_name : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->surname_husband : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->quota_treat : '',
                        isset($loan->borrowerGuarantors[1]) ? Util::money_format($loan->get_amount_payment($date_calculate,false,'G')): '',
                     ));
                 }              
             }
             if(in_array($loan->procedure_modality_id, $id_senasir))
             {
                foreach($loan->borrower as $lender)
                { 
                     $loan->guarantor = $loan->guarantors;
                    array_push($senasir_sheet_defaulted, array(  
                        $lender->affiliate()->id,
                        $lender->affiliate()->identity_card,
                        $lender->affiliate()->city_identity_card->first_shortened,
                        $lender->affiliate()->registration,
                        $lender->affiliate()->full_name,
                        "*Prestatario-->",
                        $loan->code,
                        Carbon::parse($loan->disbursement_date)->format('d/m/Y H:i:s'),
                        $loan->city->name,
                        $lender->affiliate_state->name,
                        $lender->affiliate_state->affiliate_state_type->name,
                        $lender->registration,
                        $lender->identity_card,
                        $lender->city_identity_card->first_shortened,
                        $lender->first_name,
                        $lender->second_name,
                        $lender->last_name,
                        $lender->mothers_last_name,
                        $lender->surname_husband,
                        Util::money_format($loan->balance),
                        Util::money_format($lender->quota_treat),
                        Util::money_format($loan->get_amount_payment($date_calculate,false,'T')),
                        $loan->interest->annual_interest,
                        $loan->guarantor_amortizing ? 'Amort. Garante':'Amort. Titular',                        
                        "*Titular-->*",
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate->identity_card : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate->city_identity_card->first_shortened : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate->registration : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate->full_name : '',
                        "*Garante-->*",
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate_state->name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->affiliate_state->affiliate_state_type->name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->registration : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->identity_card: '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->city_identity_card->first_shortened : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->first_name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->second_name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->last_name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->mothers_last_name : '',
                        $loan->guarantor_amortizing ? $loan->borrowerGuarantors[0]->surname_husband : '',
                        $loan->guarantor_amortizing ? Util::money_format($loan->borrowerGuarantors[0]->quota_treat) : '',
                        $loan->guarantor_amortizing ? Util::money_format($loan->get_amount_payment($date_calculate,false,'G')) : '',

                        "*Titular-->",
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate->identity_card : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate->city_identity_card->first_shortened : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate->registration : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate->full_name : '',
                        "*garante-->*",
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate_state: '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->affiliate_state_type: '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->registration : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->identity_card : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->city_identity_card->first_shortened : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->first_name : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->second_name : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->last_name : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->mothers_last_name : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->surname_husband : '',
                        isset($loan->borrowerGuarantors[1]) ? $loan->borrowerGuarantors[1]->quota_treat : '',
                        isset($loan->borrowerGuarantors[1]) ? Util::money_format($loan->get_amount_payment($date_calculate,false,'G')): '',
                    ));
                }                 
        }
        }
         $file_name = $month.'-'.$year;
         $extension = '.xlsx';
         $export = new FileWithMultipleSheetsDefaulted($command_sheet_dafaulted, $senasir_sheet_defaulted);
         return Excel::download($export, $file_name.$extension);
    }
   //seguimiento de prestamos
    /** @group Reportes de Prestamos
   * Seguimiento de prestamos
   * Lista todos los prestamos con opcion a busquedas
   * @queryParam sortDesc Vector de orden descendente(0) o ascendente(1). Example: 0
   * @queryParam trashed_loan Para filtrar ANULADOS(true) o estados Vigente,Liq,En Proceso(false). Example: true
   * @queryParam per_page Número de datos por página. Example: 8
   * @queryParam page Número de página. Example: 1
   * @queryParam excel Valor booleano para descargar  el docExcel. Example: true
   * @queryParam code_loan  Buscar código del Préstamo. Example: PTMO000012-2021
   * @queryParam identity_card_borrower  Buscar por nro de CI del Prestatario. Example: 10069775
   * @queryParam registration_borrower  Buscar por Matricula del Prestatario. Example: 100697MDF
   * @queryParam last_name_borrower Buscar por primer apellido del Prestatario. Example: RIVERA
   * @queryParam mothers_last_name_borrower Buscar por segundo apellido del Prestatario. Example: ARTEAG
   * @queryParam first_name_borrower Buscar por primer Nombre del Prestatario. Example: ABAD
   * @queryParam second_name_borrower Buscar por segundo Nombre del Prestatario. Example: FAUST
   * @queryParam surname_husband_borrower Buscar por Apellido de casada Nombre del Prestatario. Example: De LA CRUZ
   * @queryParam full_name_borrower Buscar por nombre completo del Prestatario. Example: ANA CRUZ PEREZ
   * @queryParam full_name_affiliate Buscar por nombre completo del Afiliado. Example: ANA CRUZ PEREZ
   * @queryParam sub_modality_loan Buscar por sub modalidad del préstamo. Example: Corto plazo sector activo
   * @queryParam shortened_sub_modality_loan Buscar por nombre corto sub modalidad del préstamo. Example: COR-AFP
   * @queryParam modality_loan Buscar por Modalidad del prestamo. Example: Préstamo a corto plazo
   * @queryParam amount_approved_loan Buscar monto aprobado del afiliado. Example: 25000
   * @queryParam state_type_affiliate Buscar por tipo de estado del afiliado. Example: Activo
   * @queryParam state_affiliate Buscar por estado del affiliado. Example: Servicio
   * @queryParam quota_loan Buscar por la quota del prestamo. Example: 1500
   * @queryParam state_loan Buscar por el estado del prestamo. Example: En proceso
   * @queryParam guarantor_loan_affiliate Buscar los garantes del préstamo. Example: false
   * @queryParam pension_entity_affiliate Buscar por la La pension entidad del afiliado. Example: SENASIR
   * @queryParam disbursement_date_loan Buscar por fecha de desembolso. Example: 2021
   * @queryParam delivery_contract_date Buscar por fecha de Entrega de Contrato. Example: 2021-06-16
   * @queryParam return_contract_date Buscar por fecha de Devolucion de Contrato. Example: 2022-01-28
   * @queryParam regional_delivery_contract_date Buscar por fecha de Entrega de Contrato Regional. Example: 2021-11-09
   * @queryParam regional_return_contract_date Buscar por fecha de Devolución de Contrato Regional. Example: 2022-02-07
   * @authenticated
   * @responseFile responses/loan/list_tracing.200.json
   */

  public function loan_tracking(Request $request){
        // aumenta el tiempo máximo de ejecución de este script a 150 min:
        ini_set('max_execution_time', 9000);
        // aumentar el tamaño de memoria permitido de este script:
        ini_set('memory_limit', '960M');

        if ($request->has('excel')) {
            $excel = $request->boolean('excel');
        } else {
            $excel =false;
        }

        $order = request('sortDesc') ?? '';
        if ($order != '') {
            if ($order) {
                $order_loan = 'asc';
            }
            if (!$order) {
                $order_loan = 'desc';
            }
        } else {
            $order_loan = 'desc';
        }

        if ($request->has('trashed_loan')) {
            $trashed_loan = $request->boolean('trashed_loan');
            if (!$trashed_loan) {
                $trashed_loan = false;
            }
            if ($trashed_loan) {
                $trashed_loan = true;
            }
        } else {
            $trashed_loan = false;
        }
        $pagination_rows = request('per_page') ?? 10;
        $conditions = [];
        $conditions_or = [];
        //filtros
        $id_loan = request('id_loan') ?? '';
        $id_affiliate = request('id_affiliate') ?? '';
        // filtros borrower
        $identity_card_affiliate = request('identity_card_affiliate') ?? '';
        $registration_affiliate = request('registration_affiliate') ?? '';
        $last_name_affiliate = request('last_name_affiliate') ?? '';
        $mothers_last_name_affiliate = request('mothers_last_name_affiliate') ?? '';
        $first_name_affiliate = request('first_name_affiliate') ?? '';
        $second_name_affiliate = request('second_name_affiliate') ?? '';
        $surname_husband_affiliate = request('surname_husband_affiliate') ?? '';
        $pension_entity_affiliate = request('pension_entity_affiliate') ?? '';
        $registration_borrower = request('registration_borrower') ?? '';
        $last_name_borrower = request('last_name_borrower') ?? '';
        $mothers_last_name_borrower = request('mothers_last_name_borrower') ?? '';
        $first_name_borrower = request('first_name_borrower') ?? '';
        $second_name_borrower = request('second_name_borrower') ?? '';
        $surname_husband_borrower = request('surname_husband_borrower') ?? '';
        $identity_card_borrower = request('identity_card_borrower') ?? '';//CI
        $full_name_borrower = request('full_name_borrower') ?? '';//FULL NAME
        //fin filtros borrower
        //loan
        $city_loan = request('city_loan') ?? '';//DTO
        $name_state_loan = request('name_wf_state_loan') ?? '';//AREA
        $user_loan = request('user_loan') ?? '';//USUARIO
        $code_loan = request('code_loan') ?? '';//CODE LOAN
        $sub_modality_loan = request('sub_modality_loan') ?? '';
        $shortened_sub_modality_loan = request('shortened_sub_modality_loan') ?? '';
        $modality_loan = request('modality_loan') ?? '';
        $amount_approved_loan = request('amount_approved_loan') ?? '';
        $state_type_affiliate = request('state_type_affiliate') ?? '';
        $state_affiliate = request('state_affiliate') ?? '';
        $state_loan = request('state_loan') ?? '';
        $quota_loan = request('quota_loan') ?? '';
        $guarantor_loan = request('guarantor_loan') ?? '';
        $disbursement_date_loan = request('disbursement_date_loan') ?? '';
        $amount_approved_loan = request('amount_approved_loan') ?? '';
        $validated_loan = request('validated_loan') ?? '';
        // Filtros por Fecha de Contrato
        $delivery_contract_date = request('delivery_contract_date') ?? '';
        $return_contract_date = request('return_contract_date') ?? '';
        $regional_delivery_contract_date = request('regional_delivery_contract_date') ?? '';
        $regional_return_contract_date = request('regional_return_contract_date') ?? '';

        if ($id_loan != '') {
            array_push($conditions, array('view_loan_borrower.id_loan', 'ilike', "%{$id_loan}%"));
        }
        if ($code_loan != '') {
            array_push($conditions, array('view_loan_borrower.code_loan', 'ilike', "%{$code_loan}%"));
        }
        if ($id_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.id_affiliate', 'ilike', "%{$id_affiliate}%"));
        }
        if ($identity_card_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.identity_card_affiliate', 'ilike', "%{$identity_card_affiliate}%"));
        }
        if ($registration_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.registration_affiliate', 'ilike', "%{$registration_affiliate}%"));
        }
        if ($last_name_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.last_name_affiliate', 'ilike', "%{$last_name_affiliate}%"));
        }
        if ($mothers_last_name_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.mothers_last_name_affiliate', 'ilike', "%{$mothers_last_name_affiliate}%"));
        }
        if ($first_name_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.first_name_affiliate', 'ilike', "%{$first_name_affiliate}%"));//
        }
        if ($second_name_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.second_name_affiliate', 'ilike', "%{$second_name_affiliate}%"));
        }
        if ($surname_husband_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.surname_husband_affiliate', 'ilike', "%{$surname_husband_affiliate}%"));
        }
        if ($identity_card_borrower != '') {
            array_push($conditions, array('view_loan_borrower.identity_card_borrower', 'ilike', "%{$identity_card_borrower}%"));
        }
        if ($registration_borrower != '') {
            array_push($conditions, array('view_loan_borrower.registration_borrower', 'ilike', "%{$registration_borrower}%"));
        }
        if ($last_name_borrower != '') {
            array_push($conditions, array('view_loan_borrower.last_name_borrower', 'ilike', "%{$last_name_borrower}%"));
        }
        if ($mothers_last_name_borrower != '') {
            array_push($conditions, array('view_loan_borrower.mothers_last_name_borrower', 'ilike', "%{$mothers_last_name_borrower}%"));
        }
        if ($first_name_borrower != '') {
            array_push($conditions, array('view_loan_borrower.first_name_borrower', 'ilike', "%{$first_name_borrower}%"));//
        }
        if ($second_name_borrower != '') {
            array_push($conditions, array('view_loan_borrower.second_name_borrower', 'ilike', "%{$second_name_borrower}%"));
        }
        if ($surname_husband_borrower != '') {
            array_push($conditions, array('view_loan_borrower.surname_husband_borrower', 'ilike', "%{$surname_husband_borrower}%"));
        }
        if ($full_name_borrower != '') {
            array_push($conditions, array('view_loan_borrower.full_name_borrower', 'ilike', "%{$full_name_borrower}%"));
        }
        if ($sub_modality_loan != '') {
            array_push($conditions, array('view_loan_borrower.sub_modality_loan', 'ilike', "%{$sub_modality_loan}%"));
        }
        if ($shortened_sub_modality_loan != '') {
            array_push($conditions, array('view_loan_borrower.shortened_sub_modality_loan', 'ilike', "%{$shortened_sub_modality_loan}%"));
        }
        if ($modality_loan != '') {
            array_push($conditions, array('view_loan_borrower.modality_loan', 'ilike', "%{$modality_loan}%"));
        }
        if ($amount_approved_loan != '') {
            array_push($conditions, array('view_loan_borrower.amount_approved_loan', 'ilike', "%{$amount_approved_loan}%"));
        }
        if ($state_type_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.state_type_affiliate', 'ilike', "%{$state_type_affiliate}%"));
        }
        if ($state_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.state_affiliate', 'ilike', "%{$state_affiliate}%"));
        }
        if ($quota_loan != '') {
            array_push($conditions, array('view_loan_borrower.quota_loan', 'ilike', "%{$quota_loan}%"));
        }
        if ($state_loan != '') {
            array_push($conditions, array('view_loan_borrower.state_loan', 'ilike', "%{$state_loan}%"));
        }
        if ($guarantor_loan != '') {
            array_push($conditions, array('view_loan_borrower.guarantor_loan', 'ilike', "%{$guarantor_loan}%"));
        }
        if ($pension_entity_affiliate != '') {
            array_push($conditions, array('view_loan_borrower.pension_entity_affiliate', 'ilike', "%{$pension_entity_affiliate}%"));
        }
        if ($disbursement_date_loan != '') {
            array_push($conditions, array('view_loan_borrower.disbursement_date_loan', 'ilike', "%{$disbursement_date_loan}%"));
        }
        if ($city_loan != '') {
            array_push($conditions, array('view_loan_borrower.city_loan', 'ilike', "%{$city_loan}%"));
        }
        if ($user_loan != '') {
            array_push($conditions, array('view_loan_borrower.user_loan', 'ilike', "%{$user_loan}%"));
        }
        if ($name_state_loan != '') {
            array_push($conditions, array('view_loan_borrower.name_wf_state_loan', 'ilike', "%{$name_state_loan}%"));
        }
        if ($validated_loan != '') {
            array_push($conditions, array('view_loan_borrower.validated_loan', 'ilike', "%{$validated_loan}%"));
        }
        if ($delivery_contract_date != '') {
            array_push($conditions, array('view_loan_borrower.delivery_contract_date', 'ilike', "%{$delivery_contract_date}%"));
        }
        if ($return_contract_date != '') {
            array_push($conditions, array('view_loan_borrower.return_contract_date', 'ilike', "%{$return_contract_date}%"));
        }
        if ($regional_delivery_contract_date != '') {
            array_push($conditions, array('view_loan_borrower.regional_delivery_contract_date', 'ilike', "%{$regional_delivery_contract_date}%"));
        }
        if ($regional_return_contract_date != '') {
            array_push($conditions, array('view_loan_borrower.regional_return_contract_date', 'ilike', "%{$regional_return_contract_date}%"));
        }
        if ($trashed_loan) {
            array_push($conditions, array('view_loan_borrower.state_loan', 'like', "Anulado"));
        }else{
            array_push($conditions, array('view_loan_borrower.state_loan', '<>', "Anulado"));
        }

        if ($excel==true) {
            if($trashed_loan){
                $list_loan = DB::table('view_loan_borrower')
                ->join('observables', 'view_loan_borrower.id_loan', '=', 'observables.observable_id')
                ->where('observables.date', '=', DB::raw("(select max(date) from observables where observable_id=view_loan_borrower.id_loan and observable_type='loans')"))
                ->where('observables.observable_type', '=', 'loans')
                ->where($conditions)
                ->select('*')
                ->orderBy('id_loan', $order_loan)->distinct()
                ->get();
            }else{
            $list_loan = DB::table('view_loan_borrower')
                ->where($conditions)
                ->select('*')
                ->orderBy('id_loan', $order_loan)
                ->get();
            }

            $File="ListadoPrestamos";

            $headFile=array("DPTO","ÁREA","USUARIO","ID PRESTAMO", "COD. PRESTAMO", "ID AFILIADO","CI AFILIADO","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","CI PRESTATARIO", "MATRÍCULA PRESTATARIO", "NOMBRE COMPLETO PRESTATARIO","SUB MODALIDAD",
            "MODALIDAD","MONTO","PLAZO","TIPO ESTADO","ESTADO AFILIADO","CUOTA","ESTADO PRÉSTAMO","ENTE GESTOR AFILIADO","FECHA DE SOLICITUD","FECHA DE DESEMBOLSO","FECHA CORTE PRESTAMO REFINANCIADO","TIPO SOLICITUD AFILIADO/ESPOSA", "FECHA DE ENTREGA DEL CONTRATO",
            "FECHA DE DEVOLUCION DEL CONTRATO", "FECHA DE ENTREGA DEL CONTRATO REGIONAL", "FECHA DE DEVOLUCION DEL CONTRATO REGIONAL");
            if($trashed_loan){array_push($headFile,"FECHA DE ANULACIÓN","OBSERVACIÓN DE ANULADOS");}
            $data=array($headFile);

            foreach ($list_loan as $row){
                $bodyFile = array(
                    $row->city_loan,
                    $row->name_wf_state_loan,
                    $row->user_loan,
                    $row->id_loan,
                    $row->code_loan,
                    $row->id_affiliate,
                    $row->identity_card_affiliate,
                    $row->registration_affiliate,
                    $row->full_name_affiliate,
                    $row->identity_card_borrower,
                    $row->registration_borrower,
                    $row->full_name_borrower,
                    $row->sub_modality_loan,
                    $row->modality_loan,
                    Util::money_format($row->amount_approved_loan),
                    $row->loan_term,
                    $row->state_type_affiliate,
                    $row->state_affiliate,
                    Util::money_format($row->quota_loan),
                    $row->state_loan,
                    $row->pension_entity_affiliate,
                    Carbon::parse($row->request_date_loan)->format('d/m/Y'),
                    $row->disbursement_date_loan? Carbon::parse($row->disbursement_date_loan)->format('d/m/Y'):'',
                    $row->date_cut_refinancing ? Carbon::parse($row->date_cut_refinancing)->format('d/m/Y') : '',
                    $row->type_affiliate_spouse_loan,
                    $row->delivery_contract_date? Carbon::parse($row->delivery_contract_date)->format('d/m/Y'):'',
                    $row->return_contract_date? Carbon::parse($row->return_contract_date)->format('d/m/Y'):'',
                    $row->regional_delivery_contract_date? Carbon::parse($row->regional_delivery_contract_date)->format('d/m/Y'):'',
                    $row->regional_return_contract_date? Carbon::parse($row->regional_return_contract_date)->format('d/m/Y'):''
                );
                if($trashed_loan){ array_push($bodyFile, 
                    $trashed_loan ? Carbon::parse($row->date)->format('d/m/Y'):'',
                    $trashed_loan ? $row->message : ''
                );}
                array_push($data, $bodyFile);
            }
            $export = new ArchivoPrimarioExport($data);
            return Excel::download($export, $File.'.xls');
        }else{
            if($trashed_loan){
                $list_loan = DB::table('view_loan_borrower')
                ->join('observables', 'view_loan_borrower.id_loan', '=', 'observables.observable_id')
                ->where('observables.date', '=', DB::raw("(select max(date) from observables where observable_id=view_loan_borrower.id_loan and observable_type='loans')"))
                ->where('observables.observable_type', '=', 'loans')
                ->where($conditions)
                ->select('*')
                ->orderBy('id_loan', $order_loan)->distinct()
                ->paginate($pagination_rows);
            }else{
                $list_loan = DB::table('view_loan_borrower')
                ->where($conditions)
                ->select('*')
                ->orderBy('id_loan', $order_loan)
                ->paginate($pagination_rows);
            }
            return $list_loan;
        }
    }

   /** @group Reportes de Prestamos
     * PVT y SISMU descuentos simultaneos
     * @bodyParam date date required Fecha para el periodo a consultar. Example: 16-06-2021
     * @responseFile responses/report_loans/loan_desembolsado.200.json
     * @authenticated
     */ 
   public function loan_pvt_sismu_report(request $request){
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        $first_month = Carbon::parse($request->date);
        $first_month->startOfMonth()->subMonth()->endOfMonth()->format('d-m-Y');
        $second_month = $first_month->startOfMonth()->subMonth()->endOfMonth()->format('d-m-Y');
        $loans_lenders = Loan::where('disbursement_date', '<=', Carbon::parse($request->date))->where('guarantor_amortizing', false)->where('state_id', LoanState::whereName('Vigente')->first()->id)->get();
        //$loans_guarantors = Loan::where('disbursement_date', '!=', null)->where('guarantor_amortizing', true)->where('state_id', LoanState::whereName('Vigente')->first()->id)->get();
        $loan_sheets = array(
            array("Nombres y Apellidos", "Cedula de Identidad", "Matricula", "Matricula DH", "Nro Prestamo", "Fecha de Solicitud", "Fecha de desembolso", "Monto solicitado", "Saldo", "cuota fija mensual", "origen", "Amortizado Por")
        );
        foreach($loans_lenders as $loan)
        {
            foreach($loan->borrower as $lender)
            {
                $loans_sismu = $lender->affiliate()->active_loans_sismu();
                $guarantees_sismu = $lender->affiliate()->active_guarantees_sismu();
                if($loans_sismu != null)
                {
                    array_push($loan_sheets, array(
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                    ));
                    array_push($loan_sheets, array(
                        $lender->full_name,
                        $lender->identity_card,
                        $lender->registration,
                        $lender->spouse ? $lender->spouse->registration : "",
                        $loan->code,
                        Carbon::parse($loan->request_date)->format('d/m/Y'),
                        Carbon::parse($loan->disbursement_date)->format('d/m/Y H:i:s'),
                        Util::money_format($loan->amount_approved),
                        Util::money_format($loan->balance),
                        $lender->quota_treat,
                        "PVT",
                        "TITULAR",
                    ));
                    foreach($loans_sismu as $loan_sismu)
                    {
                        array_push($loan_sheets, array(
                            $lender->full_name,
                            $lender->identity_card,
                            $lender->registration,
                            $lender->spouse ? $lender->spouse->registration : "",
                            $loan_sismu->PresNumero,
                            Carbon::parse($loan_sismu->PresFechaPrestamo)->format('d/m/Y'),
                            Carbon::parse($loan_sismu->PresFechaDesembolso)->format('d/m/Y H:i:s'),
                            $loan_sismu->PresMntDesembolso,
                            $loan_sismu->PresSaldoAct,
                            $loan_sismu->PresCuotaMensual,
                            "SISMU",
                            "TITULAR",
                        ));
                    }
                }
                if($guarantees_sismu != null)
                {
                    $state = false;
                    foreach($guarantees_sismu as $guarantee_sismu)
                    {
                        $query = "SELECT top 4 *
                                    from Amortizacion
                                    where Amortizacion.IdPrestamo = '$guarantee_sismu->IdPrestamo'
                                    and Amortizacion.AmrTipPago = 'GARANTE'
                                    order by Amortizacion.AmrFecPag DESC";
                        $payments = DB::connection('sqlsrv')->select($query);
                        foreach($payments as $payment)
                        {
                            if(Carbon::parse($payment->AmrFecPag)->format('d-m-Y') == $first_month || Carbon::parse($payment->AmrFecPag)->format('d-m-Y') == $second_month){
                                $state = true;
                            }
                        }
                    }
                    if($loans_sismu == null && $state == true)
                    {
                        array_push($loan_sheets, array(
                            "",
                            "",
                            "",
                            "",
                            "",
                            "",
                            "",
                            "",
                            "",
                        ));
                        array_push($loan_sheets, array(
                            $lender->full_name,
                            $lender->identity_card,
                            $lender->registration,
                            $lender->spouse ? $lender->spouse->registration : "",
                            $loan->code,
                            Carbon::parse($loan->request_date)->format('d/m/Y'),
                            Carbon::parse($loan->disbursement_date)->format('d/m/Y H:i:s'),
                            Util::money_format($loan->amount_approved),
                            Util::money_format($loan->balance),
                            $lender->quota_treat,
                            "PVT",
                            "TITULAR",
                        ));
                    }
                    foreach($guarantees_sismu as $guarantee_sismu)
                    {
                        $state = false;
                        $query = "SELECT top 4 *
                                    from Amortizacion
                                    where Amortizacion.IdPrestamo = '$guarantee_sismu->IdPrestamo'
                                    and Amortizacion.AmrTipPago = 'GARANTE'
                                    order by Amortizacion.AmrFecPag DESC";
                        $payments = DB::connection('sqlsrv')->select($query);
                        foreach($payments as $payment)
                        {
                            if(Carbon::parse($payment->AmrFecPag)->format('d-m-Y') == $first_month || Carbon::parse($payment->AmrFecPag)->format('d-m-Y') == $second_month)
                                $state = true;
                        }
                        if($state == true){
                            array_push($loan_sheets, array(
                                $lender->full_name,
                                $lender->identity_card,
                                $lender->registration,
                                $lender->spouse ? $lender->spouse->registration : "",
                                $guarantee_sismu->PresNumero,
                                Carbon::parse($loan_sismu->PresFechaPrestamo)->format('d/m/Y'),
                                Carbon::parse($guarantee_sismu->PresFechaDesembolso)->format('d/m/Y H:i:s'),
                                $guarantee_sismu->PresMntDesembolso,
                                $guarantee_sismu->PresSaldoAct,
                                $guarantee_sismu->PresCuotaMensual/$guarantee_sismu->quantity_guarantors,
                                "SISMU",
                                "GARANTE"
                            ));
                        }
                    }
                }
            }
        }
        $file_name = $month.'-'.$year;
         $extension = '.xlsx';
         $export = new SheetExportPayment($loan_sheets, "Prestamos PVT y SISMU");
         return Excel::download($export, $file_name.$extension);
    }


    /** @group Reportes de Prestamos
     * Estado de Solicitudes de Prestamo
     * @bodyParam date date required Fecha para el periodo a consultar. Example: 16-06-2021
     * @responseFile responses/loan/print_request_loans.200.json
     * @authenticated
     */ 
    public function request_state_report(request $request, $standalone = true)
    {
        if(!$request->date)
            $date = Carbon::now()->format('Y-m-d');
        else
            $date = $request->date;
        $loans = Loan::whereStateId(LoanState::whereName('En Proceso')->first()->id)->where('request_date', '<=', Carbon::parse($request->date)->endOfDay())->orderBy('wf_states_id')->get();
        $loans_array = collect([]);
        $date = "";
        if($request->type == "xlsx")
            $loan_sheets = array(
                array("Nro de Prestamo", "Procedencia", "Fecha de Solicitud", "Solicitante", "C.I.", "Estado de Flujo", "Fecha de Derivacion", "Usuario", "Monto Solicitado", "Monto Desembolsado")
            );
        foreach($loans as $loan)
        {
            foreach($loan->records as $record){
                if(strpos($record->action, "derivó") != false){
                    $date = "";
                    $date = $record->created_at;
                    break;
                }
            }
            $loans_array->push([
                "code" => $loan->code,
                "request_date" => Carbon::parse($loan->request_date)->format('d/m/Y'),
                "lenders" => $loan->borrower,
                "wf_states" => $loan->currentState->name,
                "update_date" => Carbon::parse($date)->format('d/m/Y H:i:s'),
                "user" => $loan->user ? $loan->user->username : "",
                "amount" => $loan->amount_approved,
                "amount_dirbursement" => $loan->refinancing_balance == 0? $loan->amount_approved:$loan->refinancing_balance,
                "procedence" => $loan->city->name,
            ]);
        }
        if($request->type == "xlsx")
        {
            foreach($loans_array as $loan)
            {
                array_push($loan_sheets, array(
                    $loan['code'],
                    $loan['procedence'],
                    $loan['request_date'],
                    $loan['lenders'][0]->fullname,
                    $loan['lenders'][0]->identity_card,
                    $loan['wf_states'],
                    $loan['update_date'],
                    $loan['user'],
                    $loan['amount'],
                    $loan['amount_dirbursement'],
                ));
            }
            $file_name = $request->date;
            $extension = '.xlsx';
            $export = new SheetExportPayment($loan_sheets, "Estado de Solicitudes de Prestamo");
            return Excel::download($export, $file_name.$extension);
        }
        elseif($request->type == "pdf")
        {
            $data = [
                'header' => [
                    'direction' => 'DIRECCIÓN DE ASUNTOS ADMINISTRATIVOS',
                    'unity' => 'UNIDAD DE SISTEMAS',
                    'table' => [
                        ['Fecha', Carbon::now()->format('d-m-Y')],
                        ['Hora', Carbon::now()->format('H:m:s')],
                        ['Usuario', Auth::user()->username]
                    ]
                ],
                'title' => 'Estado de Solicitudes de Prestamos',
                'loans' => $loans_array,
                'file_title' => 'Estado de Solicitudes de Prestamos',
            ];
            $file_name = 'Solicitudes de Prestamos.pdf';
            $view = view()->make('loan.reports.request_state_report')->with($data)->render();
            if ($standalone) return Util::pdf_to_base64([$view], $file_name,'Reporte Estado de Solicitudes de Prestamos','letter', $request->copies ?? 1, false);
            return $view;
        }
    }
  /** @group Reportes de Prestamos
   * Listar prestamos generando reportes
   * Lista todos los prestamos con opcion a busquedas
   * @queryParam sortDesc Vector de orden descendente(0) o ascendente(1). Example: 0
   * @queryParam per_page Número de datos por página. Example: 8
   * @queryParam page Número de página. Example: 1
   * @queryParam excel Valor booleano para descargar  el docExcel. Example: true
   * @queryParam id_loan Buscar ID del Préstamo. Example: 1
   * @queryParam code_loan  Buscar código del Préstamo. Example: PTMO000012-2021
   * @queryParam id_affiliate  Buscar por ID del affiliado. Example: 33121
   * @queryParam identity_card_borrower  Buscar por nro de CI del Prestatario. Example: 10069775
   * @queryParam registration_borrower  Buscar por Matricula del Prestatario. Example: 100697MDF
   * @queryParam last_name_borrower Buscar por primer apellido del Prestatario. Example: RIVERA
   * @queryParam mothers_last_name_borrower Buscar por segundo apellido del Prestatario. Example: ARTEAG
   * @queryParam first_name_borrower Buscar por primer Nombre del Prestatario. Example: ABAD
   * @queryParam second_name_borrower Buscar por segundo Nombre del Prestatario. Example: FAUST
   * @queryParam surname_husband_borrower Buscar por Apellido de casada Nombre del Prestatario. Example: De LA CRUZ
   * @queryParam full_name_borrower Buscar por Apellido de casada Nombre del Prestatario. Example: RIVERA ARTEAG ABAD FAUST De LA CRUZ
   * @queryParam sub_modality_loan Buscar por sub modalidad del préstamo. Example: Corto plazo sector activo
   * @queryParam modality_loan Buscar por Modalidad del prestamo. Example: Préstamo a corto plazo
   * @queryParam shortened_sub_modality_loan Buscar por nombre corto de la sub modalidad del prestamo. Example:COR-ACT
   * @queryParam amount_approved_loan Buscar monto aprobado del afiliado. Example: 25000
   * @queryParam state_type_affiliate Buscar por tipo de estado del afiliado. Example: Activo
   * @queryParam state_affiliate Buscar por estado del affiliado. Example: Servicio
   * @queryParam quota_loan Buscar por la quota del prestamo. Example: 1500
   * @queryParam state_loan Buscar por el estado del prestamo. Example: En proceso
   * @queryParam guarantor_loan Buscar los garantes del préstamo. Example: false
   * @queryParam pension_entity_affiliate Buscar por la La pension entidad del afiliado. Example: SENASIR
   * @queryParam disbursement_date_loan Buscar por fecha de desembolso. Example: 2021
   * @queryParam guarantor_amortizing_loan Filtra los prestamos que estan amortizando garantes(TRUE) y amortizando Titular(FALSE). Example: true
   * @authenticated
   * @responseFile responses/loan/list_loans_generate.200.json
   */

  public function list_loan_generate(Request $request){
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
    //filtros
    $id_loan = request('id_loan') ?? '';
    $code_loan = request('code_loan') ?? '';
    $id_affiliate = request('id_affiliate') ?? '';
    $identity_card_borrower = request('identity_card_borrower') ?? '';
    $registration_borrower = request('registration_borrower') ?? '';
 
    $last_name_affiliate = request('last_name_affiliate') ?? '';
    $mothers_last_name_affiliate = request('mothers_last_name_affiliate') ?? '';
    $first_name_affiliate = request('first_name_affiliate') ?? '';
    $second_name_affiliate = request('second_name_affiliate') ?? '';
    $surname_husband_affiliate = request('surname_husband_affiliate') ?? '';
    $full_name_borrower = request('full_name_borrower') ?? '';
 
    $sub_modality_loan = request('sub_modality_loan') ?? '';
    $modality_loan = request('modality_loan') ?? '';
    $shortened_sub_modality_loan = request('shortened_sub_modality_loan') ?? '';

    $amount_approved_loan = request('amount_approved_loan') ?? '';
    $state_type_affiliate = request('state_type_affiliate') ?? '';
    $state_affiliate = request('state_affiliate') ?? '';
    $state_loan = request('state_loan') ?? '';
 
    $quota_loan = request('quota_loan') ?? '';
    $guarantor_loan = request('guarantor_loan') ?? '';
    $pension_entity_affiliate = request('pension_entity_affiliate') ?? '';

    $disbursement_date_loan = request('disbursement_date_loan') ?? '';
 
    $amount_approved_loan = request('amount_approved_loan') ?? '';

    $guarantor_amortizing_loan = request('guarantor_amortizing_loan') ?? '';

       if ($id_loan != '') {
        array_push($conditions, array('view_loan_borrower.id_loan', 'ilike', "%{$id_loan}%"));
      }
 
      if ($code_loan != '') {
        array_push($conditions, array('view_loan_borrower.code_loan', 'ilike', "%{$code_loan}%"));
      }
  
      if ($id_affiliate != '') {
        array_push($conditions, array('view_loan_borrower.id_affiliate', 'ilike', "%{$id_affiliate}%"));
      }

      if ($identity_card_borrower != '') {
        array_push($conditions, array('view_loan_borrower.identity_card_borrower', 'ilike', "%{$identity_card_borrower}%"));
      }

      if ($registration_borrower != '') {
        array_push($conditions, array('view_loan_borrower.registration_borrower', 'ilike', "%{$registration_borrower}%"));
      }

      if ($last_name_affiliate != '') {
        array_push($conditions, array('view_loan_borrower.last_name_affiliate', 'ilike', "%{$last_name_affiliate}%"));
      }

     if ($mothers_last_name_affiliate != '') {
        array_push($conditions, array('view_loan_borrower.mothers_last_name_affiliate', 'ilike', "%{$mothers_last_name_affiliate}%"));
      }

      if ($first_name_affiliate != '') {
        array_push($conditions, array('view_loan_borrower.first_name_affiliate', 'ilike', "%{$first_name_affiliate}%"));
      }

      if ($second_name_affiliate != '') {
        array_push($conditions, array('view_loan_borrower.second_name_affiliate', 'ilike', "%{$second_name_affiliate}%"));
      }

      if ($surname_husband_affiliate != '') {
        array_push($conditions_or, array('view_loan_borrower.surname_husband_affiliate', 'ilike', "%{$surname_husband_affiliate}%"));
      }
      if ($full_name_borrower != '') {
        array_push($conditions, array('view_loan_borrower.full_name_borrower', 'ilike', "%{$full_name_borrower}%"));
      }
      if ($sub_modality_loan != '') {
        array_push($conditions, array('view_loan_borrower.sub_modality_loan', 'ilike', "%{$sub_modality_loan}%"));
      }

      if ($modality_loan != '') {
        array_push($conditions, array('view_loan_borrower.modality_loan', 'ilike', "%{$modality_loan}%"));
      }
      if ($shortened_sub_modality_loan != '') {
        array_push($conditions, array('view_loan_borrower.shortened_sub_modality_loan', 'ilike', "%{$shortened_sub_modality_loan}%"));
      }
 
      if ($amount_approved_loan != '') {
        array_push($conditions, array('view_loan_borrower.amount_approved_loan', 'ilike', "%{$amount_approved_loan}%"));
      }
      if ($state_type_affiliate != '') {
        array_push($conditions, array('view_loan_borrower.state_type_affiliate', 'ilike', "%{$state_type_affiliate}%"));
      }
      if ($state_affiliate != '') {
        array_push($conditions, array('view_loan_borrower.state_affiliate', 'ilike', "%{$state_affiliate}%"));
      }
  
      if ($quota_loan != '') {
        array_push($conditions, array('view_loan_borrower.quota_loan', 'ilike', "%{$quota_loan}%"));
      }
      if ($state_loan != '') {
        array_push($conditions, array('view_loan_borrower.state_loan', 'ilike', "%{$state_loan}%"));
      }
      if ($guarantor_loan != '') {
        array_push($conditions, array('view_loan_borrower.guarantor_loan', 'ilike', "%{$guarantor_loan}%"));
      }
      if ($pension_entity_affiliate != '') {
        array_push($conditions, array('view_loan_borrower.pension_entity_affiliate', 'ilike', "%{$pension_entity_affiliate}%"));
      }
      if ($disbursement_date_loan != '') {
        array_push($conditions, array('view_loan_borrower.disbursement_date_loan', 'ilike', "%{$disbursement_date_loan}%"));
      }
      if ($guarantor_amortizing_loan != '') {
        array_push($conditions, array('view_loan_borrower.guarantor_amortizing_loan', '=', "{$guarantor_amortizing_loan}"));
      }
 
      if($excel==true){
                $list_loan = DB::table('view_loan_borrower')
                ->where($conditions)
                ->select('*')
                ->orderBy('code_loan', $order_loan)
                ->get();
               foreach ($list_loan as $loan) {
                 $padron = Loan::where('id', $loan->id_loan)->first();
                 $loan->balance_loan = $padron->balance;
               }
               $File="ListadoPrestamos";
               $data=array(
                   array("CI AFILIADO","EXP","MATRICULA AFILIADO","NOMBRE COMPLETO AFILIADO","ID PRESTAMO", "COD. PRESTAMO","FECHA DE SOLICITUD","FECHA DE DESEMBOLSO","DPTO","ÍNDICE DE ENDEUDAMIENTO","SUB MODALIDAD",
                   "MODALIDAD","CI PRESTATARIO","EXP",
                   "MATRÍCULA PRESTATARIO","APELLIDO PATERNO ","APELLIDO MATERNO","AP. CASADA","1ER. NOMBRE","2DO. NOMBRE","NRO CPTE CTB","MONTO","ESTADO AFILIADO","TIPO ESTADO","CUOTA","ESTADO PRÉSTAMO","ENTE GESTOR AFILIADO",'SALDO PRÉSTAMO','TIPO SOLICITUD AFILIADO/ESPOSA',
                   'AMORTIZANDO?' )
               );
               foreach ($list_loan as $row){
                   array_push($data, array(
                       $row->identity_card_affiliate,
                       $row->city_exp_first_shortened_affiliate,
                       $row->registration_affiliate,
                       $row->full_name_affiliate,
                       $row->id_loan,
                       $row->code_loan,
                       Carbon::parse($row->request_date_loan)->format('d/m/Y'),
                       Carbon::parse($row->disbursement_date_loan)->format('d/m/Y H:i:s'),
                       $row->city_loan,
                       $row->indebtedness_borrower,
                       $row->sub_modality_loan,
                       $row->modality_loan,
                       $row->identity_card_affiliate,
                       $row->city_exp_first_shortened_affiliate,
                       $row->registration_affiliate,
                       $row->last_name_affiliate,
                       $row->mothers_last_name_affiliate,
                       $row->surname_husband_affiliate,
                       $row->first_name_affiliate,
                       $row->second_name_affiliate,
                       $row->num_accounting_voucher_loan,
                       Util::money_format($row->amount_approved_loan),
                       $row->state_type_affiliate,
                       $row->state_affiliate,
                       Util::money_format($row->quota_loan),
                       $row->state_loan,
                       $row->pension_entity_affiliate,
                       Util::money_format($row->balance_loan),
                       $row->type_affiliate_spouse_loan,
                       $row->guarantor_amortizing_loan? 'PRES. AMORTIZANDO GARANTE':'PRES. AMORTIZANDO TITULAR'
                   ));
               }
               $export = new ArchivoPrimarioExport($data);
               return Excel::download($export, $File.'.xls');
      }else{    
        $list_loan = DB::table('view_loan_borrower')
       ->where($conditions)
       ->select('*')
       ->orderBy('code_loan', $order_loan)   
       ->paginate($pagination_rows);
            $list_loan->getCollection()->transform(function ($list_loan) {
            $padron = Loan::findOrFail($list_loan->id_loan);
            $list_loan->balance_loan=$padron->balance;
            return $list_loan;
               });
           return $list_loan;
      }
   }

   /** @group Reportes de Prestamos
   * Reporte de solicitudes de prestamos
   * @queryParam initial_date Fecha inicial. Example: 2021-01-01
   * @queryParam final_date Fecha Final. Example: 2021-05-01
   * @authenticated
   * @responseFile responses/loan/list_tracing.200.json
   */
  public function loan_application_status(request $request, $standalone = true)
  {
    if($request->initial_date == null)
        $initial_date = Carbon::parse('1900-01-01');
    else
        $initial_date = $request->initial_date;
    if($request->final_date == null)
        $final_date = Carbon::now();
    else
        $final_date = $request->final_date;
    $initial_date = Carbon::parse($initial_date)->startOfDay();
    $final_date = Carbon::parse($final_date)->endOfDay();

    $loans = Loan::where('request_date', '>=', $initial_date)
            ->where('request_date', '<=', $final_date)
            ->where('deleted_at', null)
            ->orderBy('wf_states_id')->get();
    $loans_collect = collect([]);
    $query = "SELECT wf_states_id, count(*)
            from loans l
            where l.request_date >= '$initial_date'
            and l.request_date <= '$final_date'
            and l.deleted_at is null 
            group by wf_states_id
            order by wf_states_id";
    $wf_states = DB::select($query);
    foreach($loans as $loan)
    {
        $ubication = $loan->currentState->name;
        $query_derivation = "SELECT *
                            from records r 
                            where r.recordable_type = 'loans'
                            and r.record_type_id = 3
                            and r.recordable_id = $loan->id
                            and r.action like '%$ubication'
                            order by r.created_at";
        $derivation = DB::select($query_derivation);
        $borrower_view = $loan->getBorrowers()->first();
        $loans_collect->push([
               'code' => $loan->code,
               'request_date' => $loan->request_date,
               'modality' => $loan->modality->procedure_type->name,
               'sub_modality' => $loan->modality->name,
               'type' => $loan->borrower->first()->state->affiliate_state_type->name,
               'category_name' => $borrower_view->category_name ? $borrower_view->category_name : '',
               'shortened_degree' => $borrower_view->shortened_degree ? $borrower_view->shortened_degree : '',
               'borrower' => $loan->borrower->first()->full_name,
               'ci_borrower' => $loan->borrower->first()->identity_card,
               'user' => $loan->user ? $loan->user->username : '',
               'wf_states' => $loan->currentState->name,
               'city' => $loan->city->name,
               'derivation_date' => sizeof($derivation) == 0 ? '' : Carbon::parse($derivation[0]->created_at)->format('d-m-Y H:m:s'),
               'request_amount' => $loan->amount_approved,
               'ref' => $loan->parent_reason == "REFINANCIAMIENTO" ? "S" : "N",
               'disbursed_amount' => $loan->refinancing_balance == 0 ? $loan->amount_approved : $loan->refinancing_balance
        ]);
    }
      
    if($request->type == "pdf")
    {
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE JEFATURA DE PRESTAMOS',
                'table' => [
                    ['Fecha', Carbon::now()->format('d-m-Y')],
                    ['Hora', Carbon::now()->format('H:m:s')],
                    ['Usuario', Auth::user()->username]
                ]
            ],
            'title' => 'ESTADOS DE SOLICITUDES DE PRESTAMOS',
            'initial_date' => $request->initial_date,
            'final_date' => $request->final_date,
            'loans' => $loans_collect,
            'wf_states' => $wf_states,
            'file_title' => 'Estado de Solicitud de Prestamos',
        ];
        $file_name = 'Ingresos Depositados en Tesoreria.pdf';
        $view = view()->make('loan.reports.loan_state_request_report')->with($data)->render();
        if ($standalone) return Util::pdf_to_base64([$view], $file_name, 'Depositos en Tesoreria' ,'letter', $request->copies ?? 1, false);
        return $view;
    }
    elseif($request->type == "xlsx")
    {
        $loan_sheets = array(
                array("Nro","Nro de Tramite", "Modalidad", "Sub Modalidad", "Sector","Categoría","Grado", "Nombre Completo", "C. I.", "Usuario", "Area", "Procedencia", "Fecha de Derivación", "Monto Solicitado", "Refinanciado", "Monto Desembolsado")
        );
        foreach($loans as $key => $loan)
        {
            $borrower_view = $loan->getBorrowers()->first();
            array_push($loan_sheets, array(
                $key+1,
                $loan->code,
                $loan->modality->procedure_type->name,
                $loan->modality->name,
                $loan->borrower->first()->state->affiliate_state_type->name,
                $borrower_view->category_name ? $borrower_view->category_name : '',
                $borrower_view->shortened_degree ? $borrower_view->shortened_degree : '',
                $loan->borrower->first()->full_name,
                $loan->borrower->first()->identity_card,
                $loan->user ? $loan->user->username : '',
                $loan->currentState->name,
                $loan->city->name,
                sizeof($derivation) == 0 ? '' : Carbon::parse($derivation[0]->created_at)->format('d-m-Y H:m:s'),
                $loan->amount_approved,
                $loan->parent_reason == "REFINANCIAMIENTO" ? "S" : "N",
                $loan->refinancing_balance == 0 ? $loan->amount_approved : $loan->refinancing_balance
            ));
        }
        $file_name = $request->date;
        $extension = '.xlsx';
        $export = new SheetExportPayment($loan_sheets, "Estado de Solicitudes de Prestamo");
        return Excel::download($export, $file_name.$extension);
    }
  }

  public function loans_days_amortization(Request $request)
  {
   //Variables para enviar a la consulta
   $loan_state_id=3;                       // Estado del prestamo --valido
   $loan_payment_earring_id=3;             // Estado de los pagos del prestamo --pendiente por confirmar
   $loan_payment_paid_id=4;                // Estado de los pagos del prestamo --pagado
   $final_date=request('final_date');      // Fecha de entrada fronted

   $loans = Loan::whereDoesntHave('payments', function ($query) use ($final_date, $loan_payment_earring_id, $loan_payment_paid_id) {
       $query->where('estimated_date', '>=', $final_date)
           ->where(function ($subquery) use ($loan_payment_earring_id, $loan_payment_paid_id) {
               $subquery->where('state_id', $loan_payment_earring_id)
                       ->orWhere('state_id', $loan_payment_paid_id);
           });
   })
   ->with(['payments' => function ($query) use ($loan_payment_earring_id, $loan_payment_paid_id) {
       $query->where('state_id', $loan_payment_earring_id)
           ->orWhere('state_id', $loan_payment_paid_id);
   }])
   ->where('state_id', $loan_state_id)
   ->where('disbursement_date','<', $final_date)
   ->get();

   $File="PrestamosMoraPorPeriodos";

   $data_mora=array(
    array("C.I.","NOMBRE COMPLETO","CATEGORIA","GRADO","NRO. DE CEL.", "PTMO"
    ,"FECHA DESEMBOLSO", "TASA ANUAL", "FECHA DEL ULTIMO PAGO","CUOTA MENSUAL","SALDO ACTUAL", "MODALIDAD", "SUB-MODALIDAD","DIAS TRANSCURRIDOS")
    );
   
   foreach($loans as $loan)
   {   
       array_push($data_mora, array(
        $loan->loanBorrowers->first()->identity_card,
        $loan->loanBorrowers->first()->full_name,
        //colocar validación si es que el dato existe
        $loan->loanBorrowers->first()->category ? $loan->loanBorrowers->first()->category->name : '',
        $loan->loanBorrowers->first()->degree ? $loan->loanBorrowers->first()->degree->shortened : '',
        $loan->loanBorrowers->first()->cell_phone_number,
        $loan->code,
        Carbon::parse($loan->disbursement_date)->format('Y-m-d'),
        $loan->interest->annual_interest,
        $loan->payments->isNotEmpty() ? Carbon::parse($loan->payments->first()->estimated_date)->format('Y-m-d') : '',
        $loan->estimated_quota,
        $loan->balance,
        $loan->modality->procedure_type->second_name,
        $loan->modality->shortened,
        $loan->payments->isNotEmpty() ? (Carbon::parse($final_date)->diffInDays(Carbon::parse($loan->payments->first()->estimated_date))) : (Carbon::parse($final_date)->diffInDays(Carbon::parse($loan->disbursement_date)->endOfDay()->format('Y-m-d')))
           )
       );
   }

   $export = new ArchivoPrimarioExport($data_mora);
   return Excel::download($export, $File.'.xlsx');

  }

  public function processed_loan_report(Request $request)
  {//return Auth::user()->id;
    $file = "reporte de tramites procesados";
    $report_process_loan=array(
        array("N°","DEPTO.", "CODIGO PRESTAMO","CI PRESTATARIO", "NOMBRE PRESTATARIO","MODALIDAD", "MONTO", "ROL","ACCION", "FECHA DE ACCION","USUARIO")
    );
    $records = Record::where('recordable_type', 'loans')
    ->whereBetween('created_at', [$request->initial_date, $request->final_date])
    ->where('record_type_id', 3)
    ->where('user_id', Auth::user()->id)
    ->orWhere('recordable_type', 'loans')
    ->whereBetween('created_at', [$request->initial_date, $request->final_date])
    ->where('action', 'ilike', 'editó [Rol]%')
    ->where('user_id', Auth::user()->id)->get();
    $c = 1;
    $rol = 0;
    foreach($records  as $record)
    {
        $loan = Loan::find($record->recordable_id);
        if($loan){
            if(preg_match('/\b\d+\b/', $record->action, $matches) && $record->record_type_id <> 3)
                $rol = (int)$matches[0];
            array_push($report_process_loan, array(
                $c,
                $loan->city->name,
                $loan->code,
                $loan->borrower->first()->identity_card,
                $loan->borrower->first()->full_name,
                $loan->modality->name,
                $loan->amount_approved,
                $record->record_type_id == 3 ? $record->role->display_name : Role::find($rol)->display_name,
                $record->record_type_id == 3 ? 'Derivó Tramite' : 'Devolvió Tramite',
                Carbon::parse($record->created_at)->format('d-m-Y'),
                User::find($record->user_id)->full_name
            ));
            $c++;
        }
    }
    $export = new ArchivoPrimarioExport($report_process_loan);
    return Excel::download($export, $file.'.xlsx');
  }

  public function report_loans_income(Request $request)
  { 
    $initial_date=request('initial_date');
    $final_date=request('final_date');
    $loan_state_id=3;                       //Estado del prestamo --vigente
    
    $loans = Loan::whereHas('loan_plan', function ($query) use ($initial_date, $final_date) {
        $query->whereBetween('estimated_date', [$initial_date, $final_date]);
    })
    ->with(['loan_plan' => function ($query) use ($initial_date, $final_date) {
        $query->whereBetween('estimated_date', [$initial_date, $final_date]);
    }])
    ->where('state_id',$loan_state_id)
    ->get();

    $File="IngresosSegúnPlanDePagos";
    $data_income=array(
        array("Número","Modalidad de Tramite", "Código de préstamo","Carnet de identidad","Nombre del prestatario","Importe capital","Importe interés","Total Cuota")
    );

    foreach($loans as $key => $loan)
    {   
        array_push($data_income, array(
            $key+1,
            $loan->modality->name,
            $loan->code,
            $loan->loanBorrowers->first()->identity_card,
            $loan->loanBorrowers->first()->first_name." ".$loan->loanBorrowers->first()->second_name." ".$loan->loanBorrowers->first()->last_name." ".$loan->loanBorrowers->first()->mothers_last_name." ".$loan->loanBorrowers->first()->surname_husband,
            $loan->loan_plan->sum('capital'),
            $loan->loan_plan->sum('interest'),
            $loan->loan_plan->sum('total_amount')
            )
        );
    }
    $export = new ArchivoPrimarioExport($data_income);
    return Excel::download($export, $File.'.xlsx');
    }

  public function report_loans_pay_partial() { 
    $year=request('year');
    $month=request('month');
    $date = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
    $loans = DB::select("select loans_partial_payments(?)",[$date]);
    $File="Prestatarios con Pagos Parciales";
    $data_income=array(
        array(
            "NUP",
            "MATRÍCULA AFILIADO",
            "CI AFILIADO",
            "EXP",
            "NOMBRE COMPLETO AFILIADO",
            "***",
            "MATRÍCULA",
            "CI",
            "EXP",
            "NOMBRE COMPLETO",
            "CATEGORÍA",
            "GRADO",
            "NRO DE CEL.1",
            "NRO DE CEL.2",
            "NRO FIJO",
            "CIUDAD",
            "DIRECCIÓN",
            "PTMO",
            "FECHA DESEMBOLSO",
            "NRO DE CUOTAS",
            "TASA ANUAL",
            "FECHA CORTE KARDEX PAGOS",
            "TIPO DE PAGO",
            "CUOTA MENSUAL",
            "SALDO ACTUAL KARDEX DE PAGO",
            "FECHA CORTE PLAN DE PAGOS",
            "SALDO ACTUAL DE PLAN DE PAGOS",
            "ESTADO DEL AFILIADO",    
            "MODALIDAD",
            "SUB MODALIDAD"
        )
    );

    foreach($loans as $key => $loan){
        $loan = json_decode($loans[$key]->loans_partial_payments, true);
        array_push($data_income, array(
            $loan['nup_affiliate'],
            $loan['matricula_affiliate'],
            $loan['ci_affiliate'],
            $loan['exp_ci_affiliate'],
            $loan['full_name_affiliate'],
            '***',
            $loan['matricula_borrower'],
            $loan['ci_borrower'],
            $loan['exp_ci_borrower'],
            $loan['full_name_borrower'],
            $loan['category_borrower'],
            $loan['degree_borrower'],
            $loan['cell_number_borrower_one'],
            $loan['cell_number_borrower_two'],
            $loan['phone_number_borrower'],
            $loan['city_borrower'],
            $loan['address_borrower'],
            $loan['code_loan'],
            $loan['disbursement_date_loan'],
            $loan['loan_term_loan'],
            $loan['annual_interest_loan'],
            $loan['payment_kardex_cut_date'],
            $loan['type_pay_kardex'],
            $loan['estimated_quota_loan'],
            $loan['balance_kardex_loan'],
            $loan['payment_plan_cut_date'],
            $loan['balance_plan_payment'],
            $loan['state_affiliate'],    
            $loan['modality_loan'],
            $loan['sub_modality_loan']
            )
        );
    }
    $export = new ArchivoPrimarioExport($data_income);
    return Excel::download($export, $File.'.xlsx');
  }

    public function affiliate_observation_report()
    {
        $observables = Observation::join('observation_types', 'observables.observation_type_id', '=', 'observation_types.id')
        ->where('observation_types.module_id', 6)
        ->where('observables.observable_type', 'affiliates')
        ->where('observables.enabled', false)
        ->select('observables.*', 'observation_types.name') // Seleccionar solo el campo 'name' de 'observation_types'
        ->get();
    
        $data_income=array(
            array("Nup","Carnet de Identidad","Nombre Completo", "Tipo de Observación", "Observacion")
        );
        foreach($observables as $observable)
        {
            $affiliate = Affiliate::find($observable->observable_id);
            array_push($data_income, array(
                $affiliate->id,
                $affiliate->identity_card,
                $affiliate->full_name,
                $observable->name,
                $observable->message
            ));
        }
        $export = new ArchivoPrimarioExport($data_income);
        $File="Afiliados con observaciones";
        return Excel::download($export, $File.'.xlsx');
    }

    public function loan_with_penal_payment_report()
    {
        /*$headers = [
            "C.I.","NOMBRE COMPLETO","CATEGORIA","GRADO","NRO. DE CEL.",
            "PTMO","FECHA DESEMBOLSO","TASA ANUAL","CUOTA MENSUAL",
            "SALDO ACTUAL","MODALIDAD","SUB-MODALIDAD"
        ];*/
        $headers = [
            "N° DE PTMO", "FECHA DE DESEMBOLSO", "TASA DE INTERES", "CUOTA MENSUAL", "SALDO ACTUAL",
            "CI_TIT.", "AP. PAT. TIT", "AP. MAT. TIT", "AP. ESPOSO", "1ER NOM. TIT", "2DO. NOM. TIT",
            "MODALIDAD DE PTMO", "SUB MODALIDAD DE PTMO", "SALDO PLAN DE PAGOS", "ULT. FECHA AMORTIZACIÓN",
            "CATEGORIA", "GRADO", "N° CEL. TIT."
        ];
        $loansConPenal = Loan::query()
            ->where('state_id', 3)
            ->whereHas('payments', function ($q) {
                $q->whereNull('deleted_at')->where('penal_payment', '>', 0);
            })
            ->with([
                'loanBorrowers' => function ($q) {
                    $q->orderBy('id');
                },
                'interest',
                'modality.procedure_type',
            ])
            ->get();

        $file1 = [ $headers ];

        foreach ($loansConPenal as $loan) {
            $b = $loan->loanBorrowers->first();

            $categoryName = ($b && $b->category) ? $b->category->name : '';
            $degreeShort  = ($b && $b->degree)    ? $b->degree->shortened : '';

            $file1[] = [
                $loan->code,
                $loan->disbursement_date ? Carbon::parse($loan->disbursement_date)->format('Y-m-d') : '',
                optional($loan->interest)->annual_interest,
                $loan->estimated_quota,
                $loan->verify_balance(),
                $b->identity_card ?? '',
                $b->last_name ?? '',
                $b->mothers_last_name ?? '',
                $b->surname_husband ?? '',
                $b->first_name ?? '',
                $b->second_name ?? '',
                optional(optional($loan->modality)->procedure_type)->second_name ?? '',
                optional($loan->modality)->shortened ?? '',
                $loan->loan_plan->where('estimated_date', '<=',Carbon::now())->sortByDesc('quota_number')->count() > 0 ? $loan->loan_plan->where('estimated_date', '<=',Carbon::now())->sortByDesc('quota_number')->first()->balance : $loan->balance,
                $loan->last_payment_validated ? Carbon::parse($loan->last_payment_validated->estimated_date)->format('Y-m-d') : Carbon::parse($loan->disbursement_date)->format('Y-m-d'),
                $categoryName,
                $degreeShort,
                $b->cell_phone_number ?? '',
            ];
        }
        $loansSinPenal = Loan::query()
            ->where('state_id', 3)
            ->whereDoesntHave('payments', function ($q) {
                $q->whereNull('deleted_at')->where('penal_payment', '>', 0);
            })
            ->with([
                'loanBorrowers' => function ($q) {
                    $q->orderBy('id');
                },
                'interest',
                'modality.procedure_type',
            ])
            ->get();

        $file2 = [ $headers ];

        foreach ($loansSinPenal as $loan) {
            $b = $loan->loanBorrowers->first();

            $categoryName = ($b && $b->category) ? $b->category->name : '';
            $degreeShort  = ($b && $b->degree)    ? $b->degree->shortened : '';

            $file2[] = [
                $loan->code,
                $loan->disbursement_date ? Carbon::parse($loan->disbursement_date)->format('Y-m-d') : '',
                optional($loan->interest)->annual_interest,
                $loan->estimated_quota,
                $loan->verify_balance(),
                $b->identity_card ?? '',
                $b->last_name ?? '',
                $b->mothers_last_name ?? '',
                $b->surname_husband ?? '',
                $b->first_name ?? '',
                $b->second_name ?? '',
                optional(optional($loan->modality)->procedure_type)->second_name ?? '',
                optional($loan->modality)->shortened ?? '',
                $loan->loan_plan->where('estimated_date', '<=',Carbon::now())->sortByDesc('quota_number')->first() ? $loan->loan_plan->where('estimated_date', '<=',Carbon::now())->sortByDesc('quota_number')->first()->balance : $loan->balance,
                $loan->last_payment_validated ? Carbon::parse($loan->last_payment_validated->estimated_date)->format('Y-m-d') : Carbon::parse($loan->disbursement_date)->format('Y-m-d'),
                $categoryName,
                $degreeShort,
                $b->cell_phone_number ?? '',
            ];
        }
        $export = new MultipleSheetExportPayment($file1, $file2,'Préstamos con penalidad','Péstamos sin penalidad');
        return Excel::download($export, 'PrestamosConPagosPenales.xlsx');
    }
}