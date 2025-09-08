<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;
use Carbon;
use Illuminate\Support\Facades\Log;
use App\LoanPaymentPeriod;
use App\Loan;
use App\ProcedureModality;
use App\LoanPaymentState;
use App\Role;
use App\Affiliate;
use App\Http\Requests\LoanPaymentForm;
use App\LoanPaymentCategorie;
use App\User;
use App\Auth;
use App\LoanGlobalParameter;
use App\Http\Controllers\Api\V1\LoanController;
use Util;
use App\LoanBorrower;
use App\LoanGuarantor;
use App\LoanProcedure;
use App\WfState;

/** @group Importacion de datos C o S
* Importacion de datos Comando  o Senasir
*/

class ImportationController extends Controller
{

   /**
    * Agrupar montos de afiliados
    * Agrupar montos de afiliadoso
    * @queryParam origin required Tipo de importacion C (Comando general) o S (Senasir). Example: C
    * @queryParam period required id_del periodo . Example: 1
    * @authenticated
    * @responseFile responses/importation/importation_agroup.200.json
     */

    public function agruped_payments(Request $request){
        $request->validate([
            'origin'=>'required|string|in:C,S,E,AD',
            'period'=>'required|exists:loan_payment_periods,id'
        ]);

        DB::beginTransaction();
        try {

        $data = array();
        $count_affiliate = 0;
        $count_no_exist = 0;
        $validado = false;
        $origin = $request->origin; $period = $request->period;//entradas
        if($origin == 'C'){
            $query = "SELECT loan_payment_copy_commands.identity_card as identity_card, sum(amount) as amount
                        FROM loan_payment_copy_commands
                        where loan_payment_copy_commands.period_id = '$period'
                        group by loan_payment_copy_commands.identity_card";

            $payment_agroups = DB::select($query);

            $this->delete_agroups_payments($period, $origin);
            foreach($payment_agroups as $payment_agroup){
                $affiliate_id = $this->serch_id($payment_agroup->identity_card);
                if($affiliate_id != 0){
                    DB::table("loan_payment_group_commands")
                    ->insert([
                        "affiliate_id" => $affiliate_id,
                        "period_id" => $period,
                        "identity_card" =>$payment_agroup->identity_card,
                        "amount" => $payment_agroup->amount,
                        "amount_balance" => $payment_agroup->amount,
                        "created_at" =>Carbon::now(),
                        "updated_at" =>Carbon::now(),
                    ]);
                    Log::info('Registro agrupado de affiliado con Id: '.$affiliate_id);
                    $count_affiliate++;
                }else{

                    $data_loan =  $payment_agroup;
                    array_push($data, $data_loan);
                    $count_no_exist++;
                }
            }
        }
        elseif($origin == 'AD'){
            $query = "SELECT *
                        FROM loan_payment_copy_additionals
                        where loan_payment_copy_additionals.period_id = '$period'";

            $additional_payments = DB::select($query);
            foreach($additional_payments as $additional_payment){
                $loan_id = Loan::where('code', $additional_payment->loan_code)->first()->id;
                if($loan_id > 0){
                    $affiliate_id = Loan::find($loan_id)->affiliate_id;
                    DB::table("loan_payment_copy_additionals")
                    ->where("id", $additional_payment->id)
                    ->update([
                        "affiliate_id" => $affiliate_id,
                        "loan_id" => $loan_id,
                    ]);
                    Log::info('Registro de importaciones adicionales de affiliado con Id: '.$affiliate_id);
                    $count_affiliate++;
                }else{
                    $data_loan = $additional_payment;
                    array_push($data, $data_loan);
                    $count_no_exist++;
                }
            }
        }elseif($origin == 'S'){
            $query = "SELECT loan_payment_copy_senasirs.registration as registration, loan_payment_copy_senasirs.registration_dh as registration_dh, sum(amount) as amount
                    FROM loan_payment_copy_senasirs
                    where loan_payment_copy_senasirs.period_id = '$period'
                    group by loan_payment_copy_senasirs.registration, loan_payment_copy_senasirs.registration_dh";
            $payment_agroups = DB::select($query);
            $this->delete_agroups_payments($period, $origin);
            foreach($payment_agroups as $payment_agroup){
                $affiliate_id = $this->serch_id($payment_agroup->registration);
                if($affiliate_id != 0){
                    DB::table("loan_payment_group_senasirs")
                    ->insert([
                        "affiliate_id" => $affiliate_id,
                        "period_id" => $period,
                        "registration" =>$payment_agroup->registration,
                        "registration_dh" =>$payment_agroup->registration_dh,
                        "amount" => $payment_agroup->amount,
                        "amount_balance" => $payment_agroup->amount,
                        "created_at" =>Carbon::now(),
                        "updated_at" =>Carbon::now(),
                    ]);
                    $count_affiliate++;

                }else{
                    $data_loan =  $payment_agroup;
                    array_push($data, $data_loan);
                    $count_no_exist++;
                }
            }
        }elseif($origin == 'E'){
            $query = "SELECT loan_payment_copy_estacionales.identity_card as identity_card, loan_payment_copy_estacionales.identity_card_dh as identity_card_dh, sum(amount) as amount
                    FROM loan_payment_copy_estacionales
                    where loan_payment_copy_estacionales.period_id = '$period'
                    group by loan_payment_copy_estacionales.identity_card, loan_payment_copy_estacionales.identity_card_dh";
            $payment_agroups = DB::select($query);
            $this->delete_agroups_payments($period, $origin);
            foreach($payment_agroups as $payment_agroup){
                $affiliate_id = $this->serch_id($payment_agroup->identity_card);
                if($affiliate_id != 0){
                    DB::table("loan_payment_group_estacionales")
                    ->insert([
                        "affiliate_id" => $affiliate_id,
                        "period_id" => $period,
                        "identity_card" =>$payment_agroup->identity_card,
                        "identity_card_dh" =>$payment_agroup->identity_card_dh,
                        "amount" => $payment_agroup->amount,
                        "amount_balance" => $payment_agroup->amount,
                        "created_at" =>Carbon::now(),
                        "updated_at" =>Carbon::now(),
                    ]);
                    $count_affiliate++;
                }else{
                    $data_loan =  $payment_agroup;
                    array_push($data, $data_loan);
                    $count_no_exist++;
                }
            }
        }else{
            abort(409, 'Incorrecto! Debe enviar C(Comando General) ó S(Senasir) ó E(Estacional) ó AD(Adicional)');
        }
        //verificar exixtencia de afiliados
        if($count_no_exist > 0){
            DB::rollback();
            $delete_copy = $this->delete_copy_payments($period,$origin);
            Log::info('Cantidad de registros no existentes: '.$count_no_exist);
            $data_cabeceraC=array(array("NRO de CARNET", "MONTO TOTAL"));
            $data_cabeceraAD=array(array("CODIGO DE PRESTAMO", "MONTO TOTAL, COMPROBANTE"));
            $data_cabeceraS=array(array("MATRÍCULA", "MATRÍCULA D_H", "MONTO TOTAL"));
            $data_cabeceraE=array(array("CI", "CI_DH", "MONTO TOTAL"));
            foreach ($data as $row){
                if($origin == 'C'){
                    array_push($data_cabeceraC, array($row->identity_card, $row->amount));
                }elseif($origin == 'AD'){
                    array_push($data_cabeceraAD, array($row->loan_code, $row->amount, $row->voucher));
                }
                elseif($origin == 'S'){
                    array_push($data_cabeceraS, array($row->registration, $row->registration_dh, $row->amount));
                }elseif($origin == 'E'){
                    array_push($data_cabeceraE, array($row->identity_card, $row->identity_card_dh, $row->amount));
                }
            }
            $last_period = LoanPaymentPeriod::find($period);
            $last_date = Carbon::parse($last_period->year.'-'.$last_period->month)->toDateString();
            if($origin == 'C'){
                $export = new ArchivoPrimarioExport($data_cabeceraC);
                $file_name = $origin.'_'.$last_date.'.xls';
                $base_path = 'errorValidacion_Command/'.'Command_'.$last_date;
                Excel::store($export,$base_path.'/'.$file_name, 'ftp');
            }elseif($origin == 'AD'){
                $export = new ArchivoPrimarioExport($data_cabeceraAD);
                $file_name = $origin.'_'.$last_date.'.xls';
                $base_path = 'errorValidacion_Command_Add/'.'Command_'.$last_date;
                Excel::store($export,$base_path.'/'.$file_name, 'ftp');
            }elseif($origin == 'S'){
                $export = new ArchivoPrimarioExport($data_cabeceraS);
                $file_name = $origin.'_'.$last_date.'.xls';
                $base_path = 'errorValidacion_Senasir/'.'Senasir_'.$last_date;
                Excel::store($export,$base_path.'/'.$file_name, 'ftp');
            }elseif($origin == 'S'){
                $export = new ArchivoPrimarioExport($data_cabeceraE);
                $file_name = $origin.'_'.$last_date.'.xls';
                $base_path = 'errorValidacion_Estacional/'.'Estacional_'.$last_date;
                Excel::store($export,$base_path.'/'.$file_name, 'ftp');
            }
            $count_affiliate = 0;
            return  response()->json(['message' =>'Validación de datos incorrecto!','validated_agroup'=>$validado,'count_affilites'=>$count_affiliate]);
        }else{
            if($count_affiliate > 0){
                $validado =true;
                DB::commit();
                return  response()->json(['message' =>'Validación de datos realizado con exito!','validated_agroup'=>$validado,'count_affilites'=>$count_affiliate]);

            }else{
                //DB::commit();
                $delete_copy = $this->delete_copy_payments($period,$origin);
                return  response()->json(['message' =>'Validación de datos incorrecto! no se encontraron datos por agrupar','validated_agroup'=>$validado,'count_affilites'=>$count_affiliate]);
            }
        }
        } catch (\Exception $e) {
            DB::rollback();
        //throw $e;
        return ['message' => $e->getMessage()];
        }
    }

   //buscar id y ci
   public function serch_id($ci){
       //$ci= '1700723';
       $ci=$ci;

        $query = "  SELECT affiliates.id as id
                    FROM affiliates
                    where affiliates.identity_card = '$ci'
                    or affiliates.registration = '$ci'";

        $query_spouse = "  SELECT spouses.affiliate_id as affiliate_id
                    FROM spouses
                    where spouses.identity_card = '$ci'
                    or spouses.registration = '$ci'";
        $affiliate =DB::select($query);
        if($affiliate){
            return $affiliate[0]->id;
        }
        else{
            $spouse = DB::select($query_spouse);
            if($spouse){
                return $spouse[0]->affiliate_id;
            }else
                return 0; //no existe el ci
        }
    }

    /**
    * Importar/registrar cobros de SENASIR
    * Importar/registrar cobros de SENASIR
    * @queryParam period required id_del periodo . Example: 1
    * @authenticated
    * @responseFile responses/importation/importation_senasir.200.json
     */
    public function importation_payment_senasir(Request $request){
        $request->validate([
            'origin'=>'string|in:C,S',
            'period'=>'required|exists:loan_payment_periods,id'
        ]);
      DB::beginTransaction();
      try{
        // aumenta el tiempo máximo de ejecución de este script a 150 min:
        ini_set('max_execution_time', 9000000);
        // aumentar el tamaño de memoria permitido de este script:
        ini_set('memory_limit', '96000M');
        $period =LoanPaymentPeriod::whereId($request->period)->first();
        $procedure_modality_id = ProcedureModality::whereShortened('DES-SENASIR')->first()->id;
        $categorie_id = LoanPaymentCategorie::whereTypeRegister('SISTEMA')->first()->id;
        $senasir_lender = 0; $senasir_guarantor = 0;
        if(!$period->importation){
            $estimated_date = Carbon::create($period->year, $period->month, 1);
            $estimated_date = Carbon::parse($estimated_date)->endOfMonth()->format('Y-m-d');

            $query = "SELECT * FROM loan_payment_group_senasirs where loan_payment_group_senasirs.period_id = '$period->id'";

            $payment_agroups = DB::select($query);

            foreach($payment_agroups as $payment_agroup){
                $sw = false;
                $amount_group = $payment_agroup->amount_balance;
                if($amount_group > 0){
                    if($this->loan_lenders($payment_agroup->affiliate_id,$period)){
                        $loans_lender = $this->loan_lenders($payment_agroup->affiliate_id,$period);
                        foreach($loans_lender as $loan_lender){
                            $loan = Loan::whereId($loan_lender->id)->first();
                            if($amount_group > 0 && $loan->balance > 0 ){
                            //$loan = Loan::whereId($loan_lender->id)->first();
                            $payment = $loan->get_amount_payment($estimated_date,false,'T');
                            if($amount_group >= $payment)
                            $sw =true;
                                if($amount_group > 0){
                                    $form = (object)[
                                        'procedure_modality_id' => $procedure_modality_id,
                                        'affiliate_id' => $payment_agroup->affiliate_id,
                                        'paid_by' => "T",
                                        'categorie_id' => $categorie_id,
                                        'estimated_date' => $estimated_date,
                                        'voucher' => 'AUTOMÁTICO',
                                        'estimated_quota' => $sw == true ? $payment:$amount_group,
                                        'loan_payment_date' => Carbon::now(),
                                        'liquidate' => false,
                                        'description'=> 'Pago registrado',
                                    ];
                                    $registry_patment = $this->set_payment($form, $loan);
                                    $amount_group = $amount_group - $registry_patment->estimated_quota;
                                    $update = "UPDATE loan_payment_group_senasirs set amount_balance = $amount_group where id = $payment_agroup->id";
                                    $update = DB::select($update);
                                    $senasir_lender++;
                                    $sw =false;
                                    Log::info('Cantidad: '.$senasir_lender.' *Titularidad : Se registro el cobro en el loanPayments con id: '.$registry_patment->id. 'y codigo: '.$registry_patment->code);
                                }
                            }
                        }
                    }
                    if($this->loan_guarantors($payment_agroup->affiliate_id,$period) && $amount_group > 0){//garantias del afiliado
                        $loans_lender = $this->loan_guarantors($payment_agroup->affiliate_id,$period);
                        foreach($loans_lender as $loan_lender){
                           $loan = Loan::find($loan_lender->loan_id);
                           if($amount_group > 0 && $loan->balance > 0 ){
                            //$loan = Loan::whereId($loan_lender->id)->first();
                            $payment = $loan->get_amount_payment($estimated_date,false,'G');
                            if($amount_group >= $payment)
                            $sw =true;
                            if($amount_group >0){
                                $form = (object)[
                                    'procedure_modality_id' => $procedure_modality_id,
                                    'affiliate_id' => $payment_agroup->affiliate_id,
                                    'paid_by' => "G",
                                    'categorie_id' => $categorie_id,
                                    'estimated_date' => $estimated_date,
                                    'voucher' => 'AUTOMÁTICO',
                                    'estimated_quota' => $sw == true ? $payment:$amount_group,
                                    'loan_payment_date' => Carbon::now(),
                                    'liquidate' => false,
                                    'description'=> 'Pago registrado',
                                ];
                                $registry_patment = $this->set_payment($form, $loan);
                                $amount_group = $amount_group - $registry_patment->estimated_quota;
                                $update = "UPDATE loan_payment_group_senasirs set amount_balance = $amount_group where id = $payment_agroup->id";
                                $update = DB::select($update);
                                $senasir_guarantor++;
                                $sw =false;
                                Log::info('Cantidad: '.$senasir_guarantor.' *Garantia: Se registro el cobro en el loanPayments con id: '.$registry_patment->id. 'y codigo: '.$registry_patment->code);
                            }
                          }
                        }

                    }
                }
            }

            $update_period = "UPDATE loan_payment_periods set importation = true where id = $period->id";
            $update_period = DB::select($update_period);
            Log::info('Se actualizo el estado del periodo en la columna importation  del id_periodo: '.$period->id);

            $paids = [
                'period'=>$period,
                'paid_by_lenders' => $senasir_lender,
                'paid_by_guarantors' => $senasir_guarantor,
                'message'=>"SENASIR Importación realizada con exito! ".$period->month.'/'.$period->year,
                'importation_validated'=> true
            ];
            LoanController::verify_loans();
            $importationQuantity = DB::table("loan_payment_copy_senasirs")
                                            ->where("period_id", $period->id)
                                            ->count();

            DB::table("loan_payment_periods")
                ->where("id", $period->id)
                ->update([
                    "importation_quantity" => $importationQuantity,
                    "importation_date" => now(),
                ]);
            DB::commit();
            return $paids;

        }else{
            Log::info('No se puede volver a realizar la importación de cobros del periodo con id : '.$period->id.' por que ya se lo realizó anteriormente :)');
            $paids = [
                'period'=>$period,
                'paid_by_lenders' => $senasir_lender,
                'paid_by_guarantors' => $senasir_guarantor,
                'message'=>"SENASIR Error! Anteriormente ya realizó la importación del periodo: ".$period->month.'/'.$period->year,
                'importation_validated'=> false
            ];
            return $paids;
        }
        }catch(Exception $e){
            DB::rollback();
            Log::info('ocurrio un error se realizó un rollback...');
            return $e;
        }
    }

    //prestamos por  afiliado
    public function loan_lenders($id_affiliate,LoanPaymentPeriod $period){
        $date_day = LoanProcedure::where('is_enable', true)->first()->loan_global_parameter->offset_interest_day;

        $estimated_date = Carbon::create($period->year, $period->month, $date_day);
        $estimated_date = Carbon::parse($estimated_date)->format('Y-m-d');

        $query = "SELECT l.id
                from loans l
                join loan_states ls on ls.id = l.state_id
                where l.affiliate_id = '$id_affiliate'
                and ls.name = 'Vigente'
                and l.disbursement_date <= '$estimated_date'
                order by l.disbursement_date ";
        $loan_lenders = DB::select($query);
        return $loan_lenders;
    }
    //prestamos por  guarantor
    public function loan_guarantors($id_affiliate, LoanPaymentPeriod $period){
        $date_day = LoanProcedure::where('is_enable', true)->first()->loan_global_parameter->offset_interest_day;
        $estimated_date = Carbon::create($period->year, $period->month, $date_day);
        $estimated_date = Carbon::parse($estimated_date)->format('Y-m-d');
        $query = "SELECT *
                    from loans l 
                    join loan_guarantors lg on lg.loan_id = l.id
                    join loan_states ls on ls.id = l.state_id
                    where lg.affiliate_id = '$id_affiliate'
                    and ls.name = 'Vigente'
                    and l.guarantor_amortizing = true
                    and l.disbursement_date <= '$estimated_date'
                    order by l.disbursement_date";

       $loan_guarantors= DB::select($query);
        return $loan_guarantors;
    }

    public function copy_payments(request $request)
    {
        DB::beginTransaction();
        try{
            $file_validate = Storage::disk('ftp')->get($request->location."/".$request->file_name);
            $file_validate = explode("\n",$file_validate);
            $file_validate = $file_validate[0];
            $base_path = $request->location."/".$request->file_name;
            $base_path ='ftp://'.env('FTP_HOST').env('FTP_ROOT').$base_path;
            $username =env('FTP_USERNAME');
            $password =env('FTP_PASSWORD');
            if (!Storage::disk('ftp')->exists($request->location . '/' . $request->file_name)) {
                return response()->json(['error' => 'El archivo no existe en el servidor FTP.'], 404);
            }
            $this->delete_copy_payments($request->period_id, $request->type);
            if(LoanPaymentPeriod::whereId($request->period_id)->first()){
                $drop = "drop table if exists payments_aux";
                $drop = DB::select($drop);
                if($request->type == 'C'){
                    $temporary = "create temporary table payments_aux(period_id integer, identity_card varchar, amount float)";
                    $temporary = DB::select($temporary);
                    $copy = "copy payments_aux(identity_card, amount)
                            FROM PROGRAM 'wget -O - $@  --user=$username --password=$password $base_path'
                            WITH DELIMITER ':' CSV header;";
                    $copy = DB::select($copy);
                    $update = "update payments_aux
                                set period_id = $request->period_id";
                    $update = DB::select($update);
                    $update2 = "update payments_aux
                                set identity_card = REPLACE(LTRIM(REPLACE(identity_card,'0',' ')),' ','0')";
                    $update2 = DB::select($update2);
                    $insert = "INSERT INTO loan_payment_copy_commands(period_id, identity_card, amount)
                                SELECT period_id, identity_card, amount FROM payments_aux;";
                    $insert = DB::select($insert);
                    DB::commit();
                    $drop = "drop table if exists payments_aux";
                    $drop = DB::select($drop);
                    $consult = "select count(*) from loan_payment_copy_commands where period_id = $request->period_id";
                    $consult = DB::select($consult);
                    return $consult;
                }
                if($request->type == 'AD'){
                    // Crear tabla temporal
                    DB::statement("CREATE TEMPORARY TABLE payments_aux (
                        period_id INTEGER,
                        loan_code VARCHAR,
                        amount FLOAT,
                        voucher VARCHAR
                    )");
                    $copyCommand = "
                        COPY payments_aux(loan_code, amount, voucher)
                        FROM PROGRAM 'wget -O - $@ --user=$username --password=$password $base_path'
                        WITH DELIMITER ':' CSV HEADER;
                    ";
                    DB::statement($copyCommand);
                    DB::table('payments_aux')->update(['period_id' => $request->period_id]);
                    DB::statement("
                        UPDATE payments_aux
                        SET voucher = REPLACE(LTRIM(REPLACE(voucher,'0',' ')),' ','0')
                    ");
                    DB::statement("
                        UPDATE payments_aux
                        SET loan_code = REPLACE(LTRIM(REPLACE(loan_code,'0',' ')),' ','0')
                    ");
                    DB::statement("
                        INSERT INTO loan_payment_copy_additionals(period_id, loan_code, amount, voucher)
                        SELECT period_id, loan_code, amount, voucher FROM payments_aux
                    ");
                    DB::statement("DROP TABLE IF EXISTS payments_aux");
                    DB::commit();
                    $count = "select count(*) from loan_payment_copy_additionals where period_id = $request->period_id";
                    $count = DB::select($count);
                    return $count;
                }
                elseif($request->type == 'S'){
                    $temporary = "CREATE TEMPORARY TABLE payments_aux (
                        period_id INTEGER, 
                        registration VARCHAR(255), 
                        registration_dh VARCHAR(255), 
                        amount FLOAT)";
                    DB::statement($temporary);
                    $copy = "copy payments_aux(registration, registration_dh, amount)
                            FROM PROGRAM 'wget -q -O - $@ --user=$username --password=$password $base_path'
                            WITH DELIMITER ':' CSV header;";
                    $copy = DB::select($copy);
                    $update = "update payments_aux
                                set period_id = $request->period_id";
                    $update = DB::statement($update);
                    $insert = "INSERT INTO loan_payment_copy_senasirs(period_id, registration, registration_dh, amount)
                                SELECT period_id, registration, registration_dh, amount FROM payments_aux;";
                    $insert = DB::select($insert);
                    DB::commit();
                    $drop = "drop table if exists payments_aux";
                    $drop = DB::statement($drop);
                    $consult = "select count(*) from loan_payment_copy_senasirs where period_id = $request->period_id";
                    $consult = DB::select($consult);
                    return $consult;
                }elseif($request->type == 'E'){
                    $temporary = "CREATE TEMPORARY TABLE payments_aux (
                        period_id INTEGER, 
                        identity_card VARCHAR(255), 
                        identity_card_dh VARCHAR(255), 
                        amount FLOAT)";
                    DB::statement($temporary);
                    $copy = "copy payments_aux(identity_card, identity_card_dh, amount)
                            FROM PROGRAM 'wget -q -O - $@ --user=$username --password=$password $base_path'
                            WITH DELIMITER ':' CSV header;";
                    $copy = DB::select($copy);
                    $update = "update payments_aux
                                set period_id = $request->period_id";
                    $update = DB::statement($update);
                    $insert = "INSERT INTO loan_payment_copy_estacionales(period_id, identity_card, identity_card_dh, amount)
                                SELECT period_id, identity_card, identity_card_dh, amount FROM payments_aux;";
                    $insert = DB::select($insert);
                    DB::commit();
                    $drop = "drop table if exists payments_aux";
                    $drop = DB::statement($drop);
                    $consult = "select count(*) from loan_payment_copy_estacionales where period_id = $request->period_id";
                    $consult = DB::select($consult);
                    return $consult;
                }else{
                    return "tipo inexistente";
                }
            }else{
                return "periodo inexistente";
            }
        }catch(\Illuminate\Database\QueryException $e){
            DB::rollback();
            return false;
        }
    }

    public function delete_copy_payments($period, $origin)
    {
        DB::beginTransaction();
        try {
            $loanPaymentPeriod = LoanPaymentPeriod::find($period);
            
            if ($loanPaymentPeriod && in_array($origin, ['C', 'S', 'E', 'AD'])) {
                $tables = [
                    'C' => 'loan_payment_copy_commands',
                    'S' => 'loan_payment_copy_senasirs',
                    'E' => 'loan_payment_copy_estacionales',
                    'AD' => 'loan_payment_copy_additionals',
                ];
                $query = "DELETE FROM {$tables[$origin]} WHERE period_id = $period";
                DB::delete($query);
                DB::commit();
                return true;
            }
            return false;
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }


    /**
    * Cargado del archivo csv de Pagos 
    * Realiza el copiado del archivo por ftp.
	* @bodyParam file file required Archivo de importación. Example: file.csv
    * @bodyParam state enum required Tipo importacion Comando(C) o Senasir(S). Example: 1
    * @authenticated
    * @responseFile responses/loan_payment/upload_file_payment.200.json
    */
    public function upload_file_payment(Request $request){
        $request->validate([
            'file' => 'required',
            'state'=> 'string|in:C,S,E,AD',
         ]);
         $result['validate'] = false;
        try {
            $extencion= strtolower($request->file->getClientOriginalExtension()); 
            $last_period_senasir = LoanPaymentPeriod::orderBy('id')->where('importation_type','SENASIR')->get()->last();
            $last_period_comand = LoanPaymentPeriod::orderBy('id')->where('importation_type','COMANDO')->get()->last();
            $last_period_season = LoanPaymentPeriod::orderBy('id')->where('importation_type','ESTACIONAL')->get()->last();
            $last_period_comand_additional = LoanPaymentPeriod::orderBy('id')->where('importation_type','DESC-NOR-COMANDO')->where('month', $last_period_comand->month)->where('year', $last_period_comand->year)->get()->last();
        if($extencion == "csv"){
            $file_name_entry = $request->file->getClientOriginalName(); 
            $file_name_entry = explode(".csv",$file_name_entry);
            $file_name_entry = $file_name_entry[0];
            $result=[];
            $period_state = false;
            if($request->state == "C"){
                $last_date = Carbon::parse($last_period_comand->year.'-'.$last_period_comand->month)->format('Y-m'); 
                $origin = "comando-".$last_period_comand->year;
                $period_state = $last_period_comand->importation;
                $new_file_name ="comando-".$last_date;
                $period_id = $last_period_comand->id;
            }elseif($request->state == "S"){
                $last_date = Carbon::parse($last_period_senasir->year.'-'.$last_period_senasir->month)->format('Y-m'); 
                $origin = "senasir-".$last_period_senasir->year;
                $period_state = $last_period_senasir->importation;
                $new_file_name ="senasir-".$last_date;
                $period_id = $last_period_senasir->id;
            }elseif($request->state == "E")
            {
                $last_date = Carbon::parse($last_period_season->year.'-'.$last_period_season->month)->format('Y-m'); 
                $origin = "estacional-".$last_period_season->year;
                $period_state = $last_period_season->importation;
                $new_file_name ="estacional-".$last_date;
                $period_id = $last_period_season->id;
            }
            elseif($request->state == "AD"){
                $last_date = Carbon::parse($last_period_comand_additional->year.'-'.$last_period_comand_additional->month)->format('Y-m'); 
                $origin = "comando-ad-".$last_period_comand_additional->year;
                $period_state = $last_period_comand_additional->importation;
                $new_file_name ="comando-ad-".$last_date;
                $period_id = $last_period_comand_additional->id;
            }
            if($file_name_entry == $new_file_name){
                if($period_state == false || $request->state == 'AD'){
                    $file_name = $new_file_name.'.csv';
                    $base_path = 'cobranzas-importacion/'.$origin;
                    $file_path = Storage::disk('ftp')->putFileAs($base_path,$request->file,$file_name);
                    $request['period_id'] = $period_id;
                    $request['location'] = $base_path;
                    $request['type'] = $request->state;
                    $request['file_name'] = $file_name;
                    $result['message'] = $this->copy_payments($request);
                    if($result['message'] == false){
                        $result['validate'] = false;
                        $result['message'] = "La informacion requerida no coincide con el contenido";  
                    }else{
                        $result['validate'] = true;
                        return $result;
                    }               
                }else{
                    $result['message'] = "No se puede ralizar el cargado del archivo ya que se realizo el registro de pago";  
                }
            }else {
                $result['message'] = "El nombre del archivo debe ser igual al requerido";
            }
        }else {
            $result['message'] = "El tipo de archivo requerido es .csv";
        }
            return $result;
        }
        catch (\Exception $e) {
            return $e;
        }
    }

    /**
    * Descargar archivo de error validacion de C o S
    * Descargar error validacion de C o S
    * @queryParam origin required Tipo de importacion C (Comando general) o S (Senasir). Example: C
    * @queryParam period required id_del periodo . Example: 1
    * @authenticated
    * @responseFile responses/importation/validation_group.200.json
     */

    public function upload_fail_validated_group(Request $request){

        $request->validate([
            'origin'=>'required|string|in:C,S,E',
            'period'=>'required|exists:loan_payment_periods,id'
        ]);

        $origin=$request->origin;
        $last_period = LoanPaymentPeriod::find($request->period);
        $last_date = Carbon::parse($last_period->year.'-'.$last_period->month)->toDateString();

        if($origin == 'C'){
            $file_name = $origin.'_'.$last_date.'.xls';
            $base_path = 'errorValidacion_Command/'.'Command_'.$last_date;
        }elseif($origin == 'S'){
            $file_name = $origin.'_'.$last_date.'.xls';
            $base_path = 'errorValidacion_Senasir/'.'Senasir_'.$last_date;
        }elseif($origin == 'E'){
            $file_name = $origin.'_'.$last_date.'.xls';
            $base_path = 'errorValidacion_Estacional/'.'Estacional_'.$last_date;
        }

        if(Storage::disk('ftp')->has($base_path.'/'.$file_name)){
            return $file = Storage::disk('ftp')->download($base_path.'/'.$file_name);
        }else{
            return abort(403, 'No existe archivo de falla para mostrar');
        }
    }


    /**
    * Importar/registrar cobros de COMANDO
    * @queryParam period required id_del periodo . Example: 1
    * @authenticated
    * @responseFile responses/importation/importation_command.200.json
    */
    public function create_payments_command(request $request)
    {
        $request->validate([
            'period'=>'required|exists:loan_payment_periods,id'
        ]);
        DB::beginTransaction();
        try{
            $period = LoanPaymentPeriod::whereId($request->period)->first();
            $c = 0;$sw = false;$c2 = 0;
            if(!$period->importation)
            {
                $query = "select * from loan_payment_group_commands where period_id = $period->id order by id";
                $payments = DB::select($query);
                $estimated_date = Carbon::create($period->year, $period->month, 1);
                $estimated_date = Carbon::parse($estimated_date)->endOfMonth()->format('Y-m-d');
                $estimated_date_disbursement = Carbon::create($period->year, $period->month, LoanProcedure::where('is_enable',true)->first()->loan_global_parameter->offset_interest_day);
                $estimated_date_disbursement = Carbon::parse($estimated_date_disbursement)->endOfMonth()->format('Y-m-d');
                $c = 0;$sw = false;$c2 = 0;
                foreach ($payments as $payment){
                    $amount = $payment->amount_balance;
                    $affiliate = Affiliate::find($payment->affiliate_id);
                    $loans = "SELECT l.id
                            from loans l
                            join loan_states ls on ls.id = l.state_id
                            where l.affiliate_id = $affiliate->id
                            and ls.name = 'Vigente'
                            and l.guarantor_amortizing = false
                            and l.disbursement_date <= '$estimated_date_disbursement'
                            order by disbursement_date ";
                    $loans = DB::select($loans);
                    foreach($loans as $loan){
                        $loan_calculate = Loan::whereId($loan->id)->first();
                        if($loan_calculate->balance > 0)
                        {
                            $estimated_quota = $loan_calculate->get_amount_payment($estimated_date,false,'T');
                            $sw = false;
                            if( $estimated_quota <= $amount )
                                $sw = true;
                            if($amount > 0){
                                $form = (object)[
                                    'procedure_modality_id' => ProcedureModality::whereShortened('DES-COMANDO')->first()->id,
                                    'affiliate_id' => $affiliate->id,
                                    'paid_by' => "T",
                                    'categorie_id' => LoanPaymentCategorie::whereTypeRegister('SISTEMA')->first()->id,
                                    'estimated_date' => $estimated_date,
                                    'voucher' => 'AUTOMATICO',
                                    'estimated_quota' => $sw == true ? $estimated_quota : $amount,
                                    'user_id' => null,
                                    'loan_payment_date' => Carbon::now(),
                                    'liquidate' => false,
                                    'state_affiliate'=> "ACTIVO",
                                    'description' => 'Pago registrado',
                                ];
                                $loan_payment = $this->set_payment($form, $loan_calculate);
                                $amount = $amount - $loan_payment->estimated_quota;
                                $c++;
                            }
                        }
                    }
                    $guarantees = "SELECT l.id
                                    from loans l 
                                    join loan_guarantors lg on lg.loan_id = l.id
                                    join loan_states ls on ls.id = l.state_id
                                    where lg.affiliate_id = $affiliate->id
                                    and ls.name = 'Vigente'
                                    and l.guarantor_amortizing = true
                                    and disbursement_date <= '$estimated_date_disbursement'
                                    order by disbursement_date";
                    $guarantees = DB::select($guarantees);
                    $c2 = 0;
                    foreach($guarantees as $guarantee)
                    {
                        $loan_calculate = Loan::whereId($guarantee->id)->first();
                        if($loan_calculate->balance > 0)
                        {
                            $quota = "SELECT lg.quota_treat 
                                        from loan_guarantors lg
                                        where lg.loan_id = $guarantee->id
                                        and affiliate_id = $affiliate->id";
                            $quota = DB::select($quota)[0]->quota_treat;
                            $sw = false;
                            if( $quota <= $amount )
                                    $sw = true;
                            if($amount > 0){
                                $form = (object)[
                                    'procedure_modality_id' => ProcedureModality::whereShortened('DES-COMANDO')->first()->id,
                                    'affiliate_id' => $affiliate->id,
                                    'paid_by' => "G",
                                    'categorie_id' => LoanPaymentCategorie::whereTypeRegister('SISTEMA')->first()->id,
                                    'estimated_date' => $estimated_date,
                                    'voucher' => 'AUTOMATICO',
                                    'estimated_quota' => $sw == true ? $quota : $amount,
                                    'user_id' => null,
                                    'loan_payment_date' => Carbon::now(),
                                    'liquidate' => false,
                                    'state_affiliate'=> "ACTIVO",
                                    'description'=> 'Pago registrado',
                                ];
                                $loan_payment = $this->set_payment($form, $loan_calculate);
                                $amount = $amount - $loan_payment->estimated_quota;
                                $c2++;
                            }
                        }
                    }
                    $update_command_grupded = "UPDATE loan_payment_group_commands set amount_balance = $amount where id = $payment->id";
                    $update_command_grupded = DB::select($update_command_grupded);
                }
                $update_period = "UPDATE loan_payment_periods set importation = true where id = $period->id";
                $update_period = DB::select($update_period);
                $update_period_additional = "UPDATE loan_payment_periods set importation = false where year = $period->year and month = $period->month and importation_type = 'DESC-NOR-COMANDO'";
                $update_period_additional = DB::select($update_period_additional);
                LoanController::verify_loans();
                $importationQuantity = DB::table("loan_payment_copy_commands")
                                            ->where("period_id", $period->id)
                                            ->count();

                DB::table("loan_payment_periods")
                    ->where("id", $period->id)
                    ->update([
                        "importation_quantity" => $importationQuantity,
                        "importation_date" => now(),
                    ]);
                DB::commit();
                $paids = [
                    'period'=> LoanPaymentPeriod::whereId($request->period)->first(),
                    'paid_by_lenders' => $c,
                    'paid_by_guarantors' => $c2,
                    'message'=>"Comando General Importación realizada con exito! ".$period->month.'/'.$period->year,
                    'importation_validated'=> true
                ];
            }else{
            $paids = [
                'period'=>$period,
                'paid_by_lenders' => $c,
                'paid_by_guarantors' => $c2,
                'message'=>"Comando General Error! Anteriormente ya realizó la importación del periodo: ".$period->month.'/'.$period->year,
                'importation_validated'=> false
            ];
            }
            return $paids;
        }
        catch(Exception $e){
            DB::rollback();
            return $e;
        }
    }

    /**
    * Importar/registrar cobros de COMANDO ADICIONAL
    * @queryParam period required id_del periodo . Example: 1
    * @authenticated
    * @responseFile responses/importation/importation_command.200.json
    */
    public function create_payments_command_additional(request $request)
    {
        $request->validate([
            'period'=>'required|exists:loan_payment_periods,id'
        ]);
        DB::beginTransaction();
        try{
            $period = LoanPaymentPeriod::whereId($request->period)->first();
            $c = 0;$sw = false;$c2 = 0;
            if(!$period->importation)
            {
                $query = "select * from loan_payment_copy_additionals where period_id = $period->id order by id";
                $payments = DB::select($query);
                $estimated_date = Carbon::create($period->year, $period->month, 1);
                $estimated_date = Carbon::parse($estimated_date)->endOfMonth()->format('Y-m-d');
                $estimated_date_disbursement = Carbon::create($period->year, $period->month, LoanProcedure::where('is_enable',true)->first()->loan_global_parameter->offset_interest_day);
                $estimated_date_disbursement = Carbon::parse($estimated_date_disbursement)->endOfMonth()->format('Y-m-d');
                $c = 0;$sw = false;$c2 = 0;
                foreach ($payments as $payment){
                    $amount = $payment->amount;
                    $affiliate = Affiliate::find($payment->affiliate_id);
                    $loan = Loan::find($payment->loan_id);
                    if($loan->balance > 0)
                    {

                        if($amount > 0){
                            $form = (object)[
                                'procedure_modality_id' => ProcedureModality::whereShortened('DESC-NOR-COMANDO')->first()->id,
                                'affiliate_id' => $affiliate->id,
                                'paid_by' => "T",
                                'categorie_id' => LoanPaymentCategorie::whereTypeRegister('SISTEMA')->first()->id,
                                'estimated_date' => $estimated_date,
                                'voucher' => 'AUTOMATICO',
                                'estimated_quota' => $amount,
                                'user_id' => null,
                                'loan_payment_date' => Carbon::now(),
                                'liquidate' => false,
                                'state_affiliate'=> "ACTIVO",
                                'description'=> $payment->voucher,
                            ];
                            $loan_payment = $this->set_payment($form, $loan);
                            $amount = $amount - $loan_payment->estimated_quota;
                            DB::table('loan_payment_copy_additionals')
                                ->where('id', $payment->id)
                                ->update(['amount_balance' => $amount]);
                            $c++;
                        }
                    }
                }
                $update_period = "UPDATE loan_payment_periods set importation = true where id = $period->id";
                $update_period = DB::select($update_period);
                LoanController::verify_loans();
                $importationQuantity = DB::table("loan_payment_copy_additionals")
                                            ->where("period_id", $period->id)
                                            ->count();

                DB::table("loan_payment_periods")
                    ->where("id", $period->id)
                    ->update([
                        "importation_quantity" => $importationQuantity,
                        "importation_date" => now(),
                    ]);
                DB::commit();
                $paids = [
                    'period'=> LoanPaymentPeriod::whereId($request->period)->first(),
                    'paid_by_lenders' => $c,
                    'paid_by_guarantors' => $c2,
                    'message'=>"Comando General Importación Adicional realizada con exito! ".$period->month.'/'.$period->year,
                    'importation_validated'=> true
                ];
            }else{
            $paids = [
                'period'=>$period,
                'paid_by_lenders' => $c,
                'paid_by_guarantors' => $c2,
                'message'=>"Comando General Adicional Error! Anteriormente ya realizó la importación del periodo: ".$period->month.'/'.$period->year,
                'importation_validated'=> false
            ];
            }
            return $paids;
        }
        catch(Exception $e){
            DB::rollback();
            return $e;
        }
    }

    public function set_payment($request, $loan)
    {
        if($loan->balance!=0){
            $payment = $loan->next_payment2($request->affiliate_id, $request->estimated_date, $request->paid_by, $request->procedure_modality_id, $request->estimated_quota, $request->liquidate);
            $payment->description = $request->description;
            $payment->state_id = LoanPaymentState::whereName('Pagado')->first()->id;
            $payment->wf_states_id = WfState::whereName('Archivo')->whereModuleId(6)->first()->id;
            $payment->procedure_modality_id = $request->procedure_modality_id;
            $payment->voucher = $request->voucher;
            $payment->affiliate_id = $request->affiliate_id;

            $affiliate_id=$request->affiliate_id;
            $affiliate=Affiliate::find($affiliate_id);
            $affiliate_state=$affiliate->affiliate_state->affiliate_state_type->name;
            $payment->state_affiliate = strtoupper($affiliate_state);

            if($request->paid_by == 'T')
                $payment->initial_affiliate = LoanBorrower::where('loan_id',$loan->id)->first()->initials;
            elseif($request->paid_by == 'G')
                $payment->initial_affiliate = LoanGuarantor::where('loan_id',$loan->id)->first()->initials;
            $payment->categorie_id = $request->categorie_id;

            $payment->paid_by = $request->paid_by;
            $payment->validated = false;
            $payment->user_id = auth()->id();
            $payment->loan_payment_date = $request->loan_payment_date;

            //obtencion de codigo de pago
            $correlative = 0;
            $correlative = Util::correlative('payment');
            $payment->code = implode(['PAY', str_pad($correlative, 6, '0', STR_PAD_LEFT), '-', Carbon::now()->year]);
            //fin obtencion de codigo;

            $loan_payment = $loan->payments()->create($payment->toArray());
            return $payment;
        }else{
            abort(403, 'El préstamo ya fue liquidado');
        }
    }

    public function set_payment_estacional($request, $loan)
    {
        if($loan->balance!=0){
            $payment = $loan->next_payment_season($request->affiliate_id, $request->estimated_date, $request->paid_by, $request->procedure_modality_id, $request->estimated_quota, $request->liquidate);
            $payment->description = $request->description;
            $payment->state_id = LoanPaymentState::whereName('Pagado')->first()->id;
            $payment->wf_states_id = WfState::whereName('Archivo')->whereModuleId(6)->first()->id;
            $payment->procedure_modality_id = $request->procedure_modality_id;
            $payment->voucher = $request->voucher;
            $payment->affiliate_id = $request->affiliate_id;

            $affiliate_id=$request->affiliate_id;
            $affiliate=Affiliate::find($affiliate_id);
            $affiliate_state=$affiliate->affiliate_state->affiliate_state_type->name;
            $payment->state_affiliate = strtoupper($affiliate_state);
            $payment->initial_affiliate = LoanBorrower::where('loan_id',$loan->id)->first()->initials;
            $payment->categorie_id = $request->categorie_id;

            $payment->paid_by = $request->paid_by;
            $payment->validated = false;
            $payment->user_id = auth()->id();
            $payment->loan_payment_date = $request->loan_payment_date;

            //obtencion de codigo de pago
            $correlative = 0;
            $correlative = Util::correlative('payment');
            $payment->code = implode(['PAY', str_pad($correlative, 6, '0', STR_PAD_LEFT), '-', Carbon::now()->year]);
            //fin obtencion de codigo;

            $loan_payment = $loan->payments()->create($payment->toArray());
            return $payment;
        }else{
            abort(403, 'El préstamo ya fue liquidado');
        }
    }

    //borrado de pagos agrupados
    public function delete_agroups_payments($period, $origin)
    {
        DB::beginTransaction();
        try {
            $loanPaymentPeriod = LoanPaymentPeriod::find($period);
            if ($loanPaymentPeriod && in_array($origin, ['C', 'S', 'E'])) {
                $tables = [
                    'C' => 'loan_payment_group_commands',
                    'S' => 'loan_payment_group_senasirs',
                    'E' => 'loan_payment_group_estacionales',
                ];
                $query = "DELETE FROM {$tables[$origin]} WHERE period_id = ?";
                DB::delete($query, [$period]);
    
                DB::commit();
                return true;
            }
    
            return false;
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }
    

    /**
    * Rollback datos de command o SENASIR
    * Rollback datos de command o SENASIR siempre y cuando los estados en el periodo esten en estado FALSE
    * @queryParam origin required Tipo de importacion C (Comando general) o S (Senasir). Example: C
    * @queryParam period required id_del periodo . Example: 1
    * @authenticated
    * @responseFile responses/importation/rollback_copy_groups.200.json
    */
    public function rollback_copy_groups_payments(Request $request)
    {
        $request->validate([
            'origin' => 'required|string|in:C,S,E,AD',
            'period' => 'required|exists:loan_payment_periods,id'
        ]);

        DB::beginTransaction();
        try {
            $period = LoanPaymentPeriod::find($request->period);
            $origin = $request->origin;

            // Validar si se puede realizar el rollback
            if (!$period->importation) {
                if($origin != 'AD')
                    $this->delete_agroups_payments($period->id, $origin);
                $this->delete_copy_payments($period->id, $origin);
                $message = "Rollback realizado con éxito!";
                $validated_rollback = true;
            } else {
                $message = "No se puede realizar el rollback";
                $validated_rollback = false;
            }

            $rollback = [
                'validated_rollback' => $validated_rollback,
                'message' => $message
            ];

            DB::commit();
            return $rollback;
        } catch (Exception $e) {
            DB::rollback();
            return $e;
        }
    }

     /**
    * Pasos y porcentage del proceso de importación 
    * Reporte para visualizar pasos de importacion.
	* @bodyParam period_id required id_del periodo . Example: 1
    * @bodyParam origin enum required Tipo importacion Comando(C) o Senasir(S). Example: C
    * @authenticated
    * @responseFile responses/loan_payment/import_progress_bar.200.json
    */
    public function  import_progress_bar(Request $request){
        $request->validate([
            'origin'=>'required|string|in:C,S,E,AD',
            'period_id'=>'required|exists:loan_payment_periods,id'
        ]);
        $origin = $request->origin;
        $period = LoanPaymentPeriod::whereId($request->period_id)->first();
        $result['percentage'] = 0;
        $result['query_step_1'] = false;
        $result['query_step_2'] = false;
        $result['query_step_3'] = false;
        $result['file_name'] = false;
        $result['reg_copy'] = 0;
        $result['reg_group'] = 0;
        $last_date = Carbon::parse($period->year.'-'.$period->month)->format('Y-m');  
        $result['period_importation'] = "$last_date";    
        $base_path = 'cobranzas-importacion/';   
        if($origin == 'C'){
        $origin_name ='comando-';
        $new_file_name = $origin_name.$last_date.'.csv';
        $base_path = $base_path.$origin_name.$period->year.'/'.$new_file_name;
            if(Storage::disk('ftp')->has($base_path)){
                $result['file_name'] = $new_file_name;
            }else{
                $result['file_name'] = false;
            }
            $query = " select (count(*)>0) as num_reg, count(*) as num_tot_reg
            from loan_payment_copy_commands 
            where period_id = $request->period_id" ;
            $query_step_1 = DB::select($query)[0]->num_reg;
            $result['reg_copy'] = DB::select($query)[0]->num_tot_reg;
            $query_grouped = " select (count(*)>0) as num_reg, count(*) as num_reg_group
            from loan_payment_group_commands 
            where period_id = $request->period_id" ;
            $query_step_2 = DB::select($query_grouped)[0]->num_reg;
            $result['reg_group'] = DB::select($query_grouped)[0]->num_reg_group;
            $query_step_3 = $period->importation;
            if($query_step_1 == true && $query_step_2 == true && $query_step_3 == true ){
                $result['percentage'] = 100;
                $result['query_step_3'] = true;
            }else{ 
                if($query_step_1 == true && $query_step_2 == true && $query_step_3 == false ){
                    $result['percentage'] = 60;
                    $result['query_step_2'] = true;
                }elseif($query_step_1 == true && $query_step_2 == false && $query_step_3 == false ){
                $result['query_step_1'] = true;
                $result['percentage'] = 30;
                }
            }
        }
        elseif($origin == 'S'){
            $origin_name = 'senasir-';
            $new_file_name = $origin_name.$last_date.'.csv';
            $base_path = $base_path.$origin_name.$period->year.'/'.$new_file_name;
            if(Storage::disk('ftp')->has($base_path)){
                $result['file_name'] = $new_file_name;
            }else{
                $result['file_name'] = false;
            }
            $query = " select (count(*)>0) as num_reg, count(*) as num_tot_reg 
            from loan_payment_copy_senasirs 
            where period_id = $request->period_id" ;
            $query_step_1 = DB::select($query)[0]->num_reg;
            $result['reg_copy'] = DB::select($query)[0]->num_tot_reg;
            $query_grouped = " select (count(*)>0) as num_reg, count(*) as num_reg_group 
            from loan_payment_group_senasirs 
            where period_id = $request->period_id" ;
            $query_step_2 = DB::select($query_grouped)[0]->num_reg;
            $result['reg_group'] = DB::select($query_grouped)[0]->num_reg_group;
            $query_step_3 = $period->importation;
            if($query_step_1 == true && $query_step_2 == true && $query_step_3 == true ){
                $result['percentage'] = 100;
                $result['query_step_3'] = true;
            }else{ 
                if($query_step_1 == true && $query_step_2 == true && $query_step_3 == false ){
                    $result['percentage'] = 60;
                    $result['query_step_2'] = true;
                }elseif($query_step_1 == true && $query_step_2 == false && $query_step_3 == false ){
                $result['query_step_1'] = true;
                $result['percentage'] = 30;
                }
            }
        }
        elseif($origin == 'E'){
            $origin_name = 'estacional-';
            $new_file_name = $origin_name.$last_date.'.csv';
            $base_path = $base_path.$origin_name.$period->year.'/'.$new_file_name;
            if(Storage::disk('ftp')->has($base_path)){
                $result['file_name'] = $new_file_name;
            }else{
                $result['file_name'] = false;
            }
            $query = " select (count(*)>0) as num_reg, count(*) as num_tot_reg 
            from loan_payment_copy_estacionales
            where period_id = $request->period_id" ;
            $query_step_1 = DB::select($query)[0]->num_reg;
            $result['reg_copy'] = DB::select($query)[0]->num_tot_reg;
            $query_grouped = " select (count(*)>0) as num_reg, count(*) as num_reg_group 
            from loan_payment_group_estacionales
            where period_id = $request->period_id" ;
            $query_step_2 = DB::select($query_grouped)[0]->num_reg;
            $result['reg_group'] = DB::select($query_grouped)[0]->num_reg_group;
            $query_step_3 = $period->importation;
            if($query_step_1 == true && $query_step_2 == true && $query_step_3 == true ){
                $result['percentage'] = 100;
                $result['query_step_3'] = true;
            }else{ 
                if($query_step_1 == true && $query_step_2 == true && $query_step_3 == false ){
                    $result['percentage'] = 60;
                    $result['query_step_2'] = true;
                }elseif($query_step_1 == true && $query_step_2 == false && $query_step_3 == false ){
                $result['query_step_1'] = true;
                $result['percentage'] = 30;
                }
            }
        }
        elseif ($origin == 'AD') {
            $origin_name = 'comando-ad-';
            $new_file_name = $origin_name . $last_date . '.csv';
            $base_path = $base_path . $origin_name . $period->year . '/' . $new_file_name;
            $result['file_name'] = Storage::disk('ftp')->has($base_path) ? $new_file_name : false;
            $query = "SELECT (COUNT(*) > 0) AS num_reg, COUNT(*) AS num_tot_reg
                      FROM loan_payment_copy_additionals
                      WHERE period_id = $request->period_id";
            $query_result = DB::select($query)[0];
            $query_step_1 = $query_result->num_reg;
            $result['reg_copy'] = $query_result->num_tot_reg;
            $query_grouped = "SELECT (COUNT(*) > 0) AS num_reg, COUNT(*) AS num_reg_group
                      FROM loan_payment_copy_additionals
                      WHERE period_id = $request->period_id
                      AND loan_id is not null 
                      AND affiliate_id is not null";
            $query_grouped_result = DB::select($query_grouped)[0];
            $query_step_2 = $query_grouped_result->num_reg;
            $result['reg_group'] = $query_grouped_result->num_reg_group;
            $query_step_3 = $period->importation;
            if ($query_step_1 && $query_step_2 && $query_step_3) {
                $result['percentage'] = 100;
                $result['query_step_3'] = true;
            } else {
                if ($query_step_1 && $query_step_2 && !$query_step_3) {
                    $result['percentage'] = 60;
                    $result['query_step_2'] = true;
                } elseif ($query_step_1 && !$query_step_2 && !$query_step_3) {
                    $result['percentage'] = 30;
                    $result['query_step_1'] = true;
                }
            }
        }          
        return $result; 
    }


    /**
    * Importar/registrar cobros de prestamos por complemento economico
    * Importar/registrar cobros de prestamos por complemento economico
    * @queryParam period required id_del periodo . Example: 1
    * @authenticated
    * @responseFile responses/importation/importation_estacional.200.json
     */
    public function importation_payment_estacional(Request $request){
        $request->validate([
            'origin'=>'string|in:E',
            'period'=>'required|exists:loan_payment_periods,id'
        ]);
      DB::beginTransaction();
      try{
        // aumenta el tiempo máximo de ejecución de este script a 150 min:
        ini_set('max_execution_time', 9000000);
        // aumentar el tamaño de memoria permitido de este script:
        ini_set('memory_limit', '96000M');
        $period =LoanPaymentPeriod::find($request->period);
        $procedure_modality_id = ProcedureModality::whereShortened('APE')->first()->id;
        $categorie_id = LoanPaymentCategorie::whereTypeRegister('SISTEMA')->first()->id;
        $estacional_lender = 0;
        if(!$period->importation){
            $estimated_date = Carbon::create($period->year, $period->month, 1);
            $estimated_date = Carbon::parse($estimated_date)->endOfMonth()->format('Y-m-d');
            $query = "SELECT * FROM loan_payment_group_estacionales where period_id = '$period->id'";
            $payment_agroups = DB::select($query);
            $payment_agroups = collect($payment_agroups);
            foreach($payment_agroups as $payment_agroup){
                $sw = false;
                $amount_group = $payment_agroup->amount_balance;
                if($amount_group > 0){
                    $affiliate = Affiliate::find($payment_agroup->affiliate_id);
                    if($affiliate->active_loans_estacional->count() > 0){
                        $loans_lender = $affiliate->active_loans_estacional;
                        //$loans_lender = $this->loan_lenders($payment_agroup->affiliate_id,$period);
                        foreach($loans_lender as $loan_lender){
                            $loan = Loan::find($loan_lender->id);
                            if($amount_group > 0 && $loan->balance > 0 ){
                            $payment = $loan->get_amount_payment($estimated_date,false,'T');
                            if($amount_group >= $payment)
                            $sw =true;
                                if($amount_group > 0){
                                    $form = (object)[
                                        'procedure_modality_id' => $procedure_modality_id,
                                        'affiliate_id' => $payment_agroup->affiliate_id,
                                        'paid_by' => "T",
                                        'categorie_id' => $categorie_id,
                                        'estimated_date' => $estimated_date,
                                        'voucher' => 'AUTOMÁTICO',
                                        'estimated_quota' => $sw == true ? $payment:$amount_group,
                                        'loan_payment_date' => Carbon::now(),
                                        'liquidate' => false,
                                        'description'=> 'Pago registrado',
                                    ];
                                    $registry_patment = $this->set_payment_estacional($form, $loan);
                                    $amount_group = $amount_group - $registry_patment->estimated_quota;
                                    $update = "UPDATE loan_payment_group_estacionales set amount_balance = $amount_group where id = $payment_agroup->id";
                                    $update = DB::select($update);
                                    $estacional_lender++;
                                    $sw =false;
                                    Log::info('Cantidad: '.$estacional_lender.' *Titularidad : Se registro el cobro en el loanPayments con id: '.$registry_patment->id. 'y codigo: '.$registry_patment->code);
                                }
                            }
                        }
                    }
                }
            }

            $update_period = "UPDATE loan_payment_periods set importation = true where id = $period->id";
            $update_period = DB::select($update_period);
            Log::info('Se actualizo el estado del periodo en la columna importation  del id_periodo: '.$period->id);

            $paids = [
                'period'=>$period,
                'paid_by_lenders' => $estacional_lender,
                'message'=>"ESTACIONAL Importación realizada con exito! ".$period->month.'/'.$period->year,
                'importation_validated'=> true
            ];
            LoanController::verify_loans();
            DB::commit();
            return $paids;

        }else{
            Log::info('No se puede volver a realizar la importación de cobros del periodo con id : '.$period->id.' por que ya se lo realizó anteriormente :)');
            $paids = [
                'period'=>$period,
                'paid_by_lenders' => $estacional_lender,
                'message'=>"SENASIR Error! Anteriormente ya realizó la importación del periodo: ".$period->month.'/'.$period->year,
                'importation_validated'=> false
            ];
            return $paids;
        }
        }catch(Exception $e){
            DB::rollback();
            Log::info('ocurrio un error se realizó un rollback...');
            return $e;
        }
    }
}
