<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MovementFundRotatory;
use App\Http\Requests\MovementFundRotatoryForm;
use DB;
use App\Affiliate;
use App\MovementConcept;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Util;
use Carbon\CarbonImmutable;
use Carbon;
use App\Loan;
use App\LoanPlanPayment;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;

/** @group Movimientos
* Datos de los movimientos de fondo rotatorio
*/
class MovementFundRotatoryController extends Controller
{
    public static function append_data(MovementFundRotatory $movement_fund_rotatory)
    {
        $movement_fund_rotatory->movement_concept = $movement_fund_rotatory->movement_concept;
        return $movement_fund_rotatory;
    }

    /**
    * Lista de movimientos de fondo rotatorio
    * Devuelve el listado de llos movimientos de fondo rotatorio
    * @authenticated
    * @responseFile responses/movements/index.200.json
    */
    public function index()
    {
        return MovementFundRotatory::orderBy('created_at')->get();
    }


    /**
    * Detalle de un movimiento de fondo rotatorio
    * Devuelve el detalle de un movimiento de fondo rotatorio mediante su ID
    * @urlParam movement required ID de movimiento. Example: 1
    * @authenticated
    * @responseFile responses/movements/show.200.json
    */
    public function show(MovementFundRotatory $movement)
    {
        return $this->append_data($movement);
    }

    /**
    * Edita el Registro del movimiento del fondo rotatorio.
    * @urlParam id required ID del registro realizado. Example: 1
	* @bodyParam date_check_delivery date fecha del cheque. Example: 28-08-2121
    * @bodyParam description string descripcion del movimiento. Example: ingreso de fondo rotatorio
    * @bodyParam entry_amount float monto de ingreso del movimiento. Example: 50000
    * @bodyParam user_id integer ID del usuario que registro. Example: 70
    * @bodyParam role_id integer role con el que el registro fue creado. Example: 90
    * @authenticated
    * @responseFile responses/movements/update.200.json
    */
    public function update(MovementFundRotatoryForm $request, MovementFundRotatory $movement)
    {
        DB::beginTransaction();
        try{
            if($movement->movement_concept->type == "INGRESO"){
                $last_mov = MovementFundRotatory::orderBy('id')->get();
                $last_mov = $last_mov->last();
                if($last_mov->id == $movement->id)
                {
                    if($request->has('entry_amount')){
                    $new_balance = $movement->balance - $movement->entry_amount;
                    $new_balance = $new_balance + $request->entry_amount;
                    $movement->balance = $new_balance;
                    }
                    $movement->fill($request->all());
                    $movement->save();
                    DB::commit();
                    $message['message'] = "movimiento editado";
                    $message['movement'] = $movement;
                }else{
                        $message['message'] = "no es el ultimo registro";
                        $message['movement'] = $movement;
                }
            }else{
                $message['message'] = "no se puede editar un egreso";
                $message['movement'] = $movement;;
            }
            return $message;
        }catch (\Exception $e){
            DB::rollback();
            return $e;
        }
    }

    /**
    * Anular Registro de moviento de fondo rotatorio
    * @urlParam movement required ID del registro. Example: 1
    * @authenticated
    * @responseFile responses/movements/destroy.200.json
    */
    public function destroy($movementfundRotatory_id)
    {
        $movementfundRotatory = MovementFundRotatory::findOrFail($movementfundRotatory_id);
        $movementfundRotatory->delete();
        return $movementfundRotatory;
    }


    /**
    * @authenticated
    * @responseFile responses/movements/store.200.json
    */
    /*public function store(FundRotatoryOutputForm $request)
    {
        return FundRotatoryOutput::create($request->all());
    }*/

       /**
    * Nuevo Ingreso del fondo rotatorio
    * Inserta nuevo Nuevo fondo rotatorio
    * @bodyParam date_check_delivery date required fecha de cheque. Example: 31-07-2021
    * @bodyParam entry_amount numeric  Monto ingresado del cheque Example: 1052.26
    * @bodyParam description string required Texto de descripción. Example: segundo ingreso del fondo rotatorio
    * @bodyParam movement_concept_id integer required Ingresar ID de la tabla concepto de movimientos. Example: 2
    * @bodyParam role_id integer required role con el que el registro fue creado. Example: 90
    * @responseFile responses/movements/store_input.200.json
    */
    public function store_input(MovementFundRotatoryForm $request)
    {  
        DB::beginTransaction();
        try {
            if($request->has('entry_amount')){
                if($request->has('date_check_delivery')){
                    $movement_concept= MovementConcept::whereIsValid(true)->whereType("INGRESO")->whereShortened("FON-ROT-IN")->first();
                    $abbreviated_supporting_document = $movement_concept->abbreviated_supporting_document;
                    //$movement_concept_code = MovementFundRotatory::where('movement_concept_id',$movement_concept->id)->withTrashed()->count()+1;
                    $movement_fund_rotatory = new MovementFundRotatory;
                    $movement_fund_rotatory->user_id = Auth::id();
                    //$movement_fund_rotatory->movement_concept_code = $movement_concept->abbreviated_supporting_document."-".$movement_concept_code.'/'.Carbon::now()->year;
                    $correlative = Util::correlative('cheque');
                    $movement_fund_rotatory->movement_concept_code = $movement_concept->abbreviated_supporting_document."-".$correlative.'/'.Carbon::now()->year;
                    $movement_fund_rotatory->date_check_delivery = $request->input('date_check_delivery');
                    $movement_fund_rotatory->entry_amount = $request->input('entry_amount');
                    $movement_fund_rotatory->description = $request->input('description');
                    $movement_fund_rotatory->movement_concept_id = $movement_concept->id;
                    $movement_fund_rotatory->role_id = $request->input('role_id');  
                    $movement_fund_rotatory_last = MovementFundRotatory::orderBy('id')->get()->last();    
                    if($movement_fund_rotatory_last == null){
                        $movement_fund_rotatory->balance = $request->input('entry_amount');
                    }else{
                        $movement_fund_rotatory->balance = $request->input('entry_amount') + $movement_fund_rotatory_last->balance;
                    }
                    $movement_fund_rotatory_return = MovementFundRotatory::create($movement_fund_rotatory->toArray());
                    DB::commit();
                    return $movement_fund_rotatory_return;
                }
                return abort(409, "El campo fecha de entrega de cheque es requerido");
            }  
        return abort(409, "El campo monto es requerido ");
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function store_output()
    {}

    /**
    * Impresión del recibo desembolso de prestamo
    * Devuelve un pdf del Pago acorde a un ID de registro de fondo rotatorio
    * @urlParam loan_id required ID del prestamo. Example: 1
    * @queryParam copies Número de copias del documento. Example: 2
    * @authenticated
    * @responseFile responses/movements/store_input.200.json
    */
    public function print_fund_rotary(Request $request,$loan_id, $standalone = true)

    {   $movement_fund_rotatorie = MovementFundRotatory::whereLoanId($loan_id)->first();
        $loan = Loan::findOrFail($loan_id);
        $affiliate = Affiliate::findOrFail($loan->affiliate_id);
        $persons = collect([]);
        foreach ($loan->borrower as $lender) {
            $persons->push([
                'full_name' => implode(' ', [$lender->full_name]),
                'identity_card' => $lender->identity_card_ext,
                'position' => 'RECIBIDO POR'
            ]);
        }
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => [
                    ['Código', $movement_fund_rotatorie->movement_concept_code],
                    ['Fecha', Carbon::now()->format('d/m/Y')],
                    ['Hora', Carbon::now()->format('h:m:i')],
                    ['Usuario', Auth::user()->username]
                ]
            ],
            'title' => 'RECIBO DE PAGO',
            'ouputs_fund_rotatorie' => $movement_fund_rotatorie,
            'loan' => $loan,
            'signers' => $persons,
            'lenders' => $loan->borrower
        ];
     
        $file_name = implode('_', ['movement_fund_rotatory', $movement_fund_rotatorie->movement_concept_code]) . '.pdf';
        $view = view()->make('loan.forms.disbursement_receipt_form')->with($data)->render();
        if ($standalone) return Util::pdf_to_treasury_receipt([$view],'letter', $request->copies ?? 1);
        return $view; 
    }

    /**
     * Reportes de salidas fondo rotatorio Prestamos concepto "DESEMBOLSO ANTICIPO"
     * @queryParam initial_date Fecha inicial. Example: 2021-01-01
     * @queryParam final_date Fecha Final. Example: 2021-05-01
     * @responseFile responses/movements/disbursement_receipt_form.200.json
     * @authenticated
     */ 
    public function disbursements_fund_rotatory_outputs_report(request $request, $standalone = true)
    {
        DB::beginTransaction();
        try {
        $movement_concept = MovementConcept::whereName('DESEMBOLSO ANTICIPO')->first();
        $initial_date = request('initial_date') ?? '';
        $final_date = request('final_date') ?? '';
        $state_vigente='Vigente';
        $conditions = [];
        //desde aqui
        if ($initial_date != '' && $final_date != '') {
            $date_ini = $request->initial_date.' 00:00:00';
            $date_fin = $request->final_date.' 23:59:59';

            $loans = DB::table('view_loan_borrower')
              ->join('movement_fund_rotatories','movement_fund_rotatories.loan_id','=','view_loan_borrower.id_loan')
              ->whereBetween('movement_fund_rotatories.created_at', [$date_ini, $date_fin])
              ->where("view_loan_borrower.state_loan", "Vigente")
              ->where("movement_fund_rotatories.movement_concept_id",'=',$movement_concept->id)
              ->whereNull("movement_fund_rotatories.deleted_at")
              ->select('movement_fund_rotatories.created_at as disbursement_date_loan','movement_fund_rotatories.*','view_loan_borrower.*')
              ->orderBy('movement_fund_rotatories.id')
              ->get();

        } else {
            if ($final_date != '') {
                $date_fin = $request->final_date.' 23:59:59';

                $loans = DB::table('view_loan_borrower')
                ->join('movement_fund_rotatories','movement_fund_rotatories.loan_id','=','view_loan_borrower.id_loan')
                ->where('movement_fund_rotatories.created_at',  "<=", $date_fin)
                ->where("view_loan_borrower.state_loan", "Vigente")
                ->where("movement_fund_rotatories.movement_concept_id",'=',$movement_concept->id)
                ->whereNull("movement_fund_rotatories.deleted_at")
                ->select('movement_fund_rotatories.created_at as disbursement_date_loan','movement_fund_rotatories.*','view_loan_borrower.*')
                ->orderBy('movement_fund_rotatories.id')
                ->get();
            } else {
                $date_fin = Carbon::now();
                if ($initial_date != '') {
                    $date_ini = $request->initial_date.' 00:00:00';

                    $loans = DB::table('view_loan_borrower')
                    ->join('movement_fund_rotatories','movement_fund_rotatories.loan_id','=','view_loan_borrower.id_loan')
                    ->where('movement_fund_rotatories.created_at', ">=", $date_ini)
                    ->where("view_loan_borrower.state_loan", "Vigente")
                    ->where("movement_fund_rotatories.movement_concept_id",'=',$movement_concept->id)
                    ->whereNull("movement_fund_rotatories.deleted_at")
                    ->select('movement_fund_rotatories.created_at as disbursement_date_loan','movement_fund_rotatories.*','view_loan_borrower.*')
                    ->orderBy('movement_fund_rotatories.id')
                    ->get();
                } else {
                    $loans = DB::table('view_loan_borrower')
                    ->join('movement_fund_rotatories','movement_fund_rotatories.loan_id','=','view_loan_borrower.id_loan')
                    ->where("view_loan_borrower.state_loan", "Vigente")
                    ->where("movement_fund_rotatories.movement_concept_id",'=',$movement_concept->id)
                    ->whereNull("movement_fund_rotatories.deleted_at")
                    ->select('movement_fund_rotatories.created_at as disbursement_date_loan','movement_fund_rotatories.*','view_loan_borrower.*')
                    ->orderBy('movement_fund_rotatories.id')
                    ->get();
                }
            }
        }
            $loans_array = collect([]);
            $data_income=array(
                array("Nro","Nro RECIBO","FECHA DESEMBOLSO", "COD. PRÉSTAMO", "CI DEL PRESTATARIO", "NOMBRE Y APELLIDOS", "IMPORTE (SALIDA)", "REGIONAL")
            );
            $c=0;
            foreach ($loans as $loan) {
                array_push($data_income, array(
                    $c++,
                    $loan->movement_concept_code,
                    Carbon::parse($loan->disbursement_date_loan)->format('d-m-Y'),
                    $loan->code_loan,
                    $loan->identity_card_borrower,
                    $loan->full_name_borrower,
                    number_format($loan->output_amount,2,'.',','),
                    $loan->city_loan
                ));
            }
            $export = new ArchivoPrimarioExport($data_income);
            $File="Salidas de Fondo Rotatorio";
            return Excel::download($export, $File.'.xlsx');
        }catch (\Exception $e){
            DB::rollback();
            return $e;
        }
    }


    /**
    * Elimina un Movimiento de fondo rotatorio
    * Elimina un movimiento rotatorio siempre que sea el ultimo
    * @urlParam movement_fund_rotatory required ID del movimiento de fondo rotatorio. Example: 1
    * @authenticated
    * @responseFile responses/movements/delete_movement.200.json
    */
    public function delete_movement(MovementFundRotatory $movement)
    {
        DB::beginTransaction();
        $message = [];
        try{
            $last_mov = MovementFundRotatory::orderBy('id')->get();
            $last_mov = $last_mov->last();
            if($last_mov->id == $movement->id)
            {
                if($movement->movement_concept->type == "INGRESO"){
                    $movement->delete();
                    DB::commit();
                    $message['message'] = "movimiento eliminado";
                    $message['movement'] = $movement;
                }
                else{
                    if($movement->loan_id != null)
                    {
                        $loan = Loan::whereId($movement->loan_id)->first();
                        $loan->disbursement_date = null;
                        $loan->validated = false;
                        $loan->user_id = null;
                        $loan->update();
                        LoanPlanPayment::where('loan_id', $loan->id)->delete();
                    }
                    $movement->delete();
                    DB::commit();
                    $message['message'] = "movimiento eliminado";
                    $message['movement'] = $movement;
                }
            }
            else{
                $message['message'] = "no es el ultimo registro";
                $message['movement'] = $movement;
            }
            return $message;
        }catch (\Exception $e){
                DB::rollback();
                return $e;
        }
    }

 /**
    * Lista de movimientos de fondo rotatorio
    * Devuelve el listado de los movimientos de fondo rotatorio con intervalo de fechas
    * @queryParam initial_date Fecha inicial. Example: 2021-01-01
    * @queryParam final_date Fecha Final. Example: 2021-05-01
    * @queryParam page Nro página. Example:2
    * @queryParam per_page Cantidad de datos por pagina. Example:10
    * @queryParam pdf Descargar PDF si(true) o no(false) . Example:true
    * @authenticated
    * @responseFile responses/movements/list_movements_fund_rotatory.200.json
    */
    public function list_movements_fund_rotatory(Request $request)
    {
        $initial_date = request('initial_date') ?? '';
        $final_date = request('final_date') ?? '';
        $total_entry_amount = 0; $total_output_amount = 0;
        $per_page = $request->per_page ?? 10;
        $print_pdf = request('pdf') ?? false;
        $movement_concepts= collect();
        if($request->has('pdf') && $request->pdf || $request->has('pdf') && !$request->pdf)
            $print_pdf= $request->boolean('pdf');
        else
            $print_pdf = false;

        if(!$print_pdf){
            if ($initial_date != '' && $final_date != '') {
                $date_ini = $request->initial_date.' 00:00:00';
                $date_fin = $request->final_date.' 23:59:59';
                $total_entry_amount = MovementFundRotatory::whereBetween('created_at', [$date_ini, $date_fin])->orderBy('id')->get()->sum('entry_amount');
                $total_output_amount = MovementFundRotatory::whereBetween('created_at', [$date_ini, $date_fin])->orderBy('id')->get()->sum('output_amount');
                $movement_concepts = MovementFundRotatory::whereBetween('created_at', [$date_ini, $date_fin])->orderBy('id');

             } else {
                if ($final_date != '') {
                    $date_fin = $request->final_date.' 23:59:59';
                    $total_entry_amount = MovementFundRotatory::where('created_at',  "<=", $date_fin)->orderBy('id')->get()->sum('entry_amount');
                    $total_output_amount = MovementFundRotatory::where('created_at',  "<=", $date_fin)->orderBy('id')->get()->sum('output_amount');
                    $movement_concepts = MovementFundRotatory::where('created_at',  "<=", $date_fin)->orderBy('created_at');

                } else {
                    $date_fin = Carbon::now();
                    if ($initial_date != '') {
                        $date_ini = $request->initial_date.' 00:00:00';
                        $total_entry_amount = MovementFundRotatory::where('created_at', ">=", $date_ini)->orderBy('id')->get()->sum('entry_amount');
                        $total_output_amount = MovementFundRotatory::where('created_at', ">=", $date_ini)->orderBy('id')->get()->sum('output_amount');
                        $movement_concepts = MovementFundRotatory::where('created_at', ">=", $date_ini)->orderBy('id');
                    } else {
                        $total_entry_amount = MovementFundRotatory::orderBy('id')->get()->sum('entry_amount');
                        $total_output_amount = MovementFundRotatory::orderBy('id')->get()->sum('output_amount');
                        $movement_concepts = MovementFundRotatory::orderBy('id');
                    }
                }
            }

            $mov_concepts = $movement_concepts->get();
            $movement_concepts =  $movement_concepts->paginate($per_page);

            if(sizeof($mov_concepts) == 0){
                $total_entry_amount =0;$total_output_amount=0;$last_mov_balance=0;
            }else{
                   $last_mov = $mov_concepts->last();
                   $last_mov_balance = $last_mov->balance;
            }
            foreach ($movement_concepts as $movement_concept){
                $movement_concept->is_last =$movement_concept->is_last();
                $movement_concept->type_movement_fund_rotatory = $movement_concept->movement_concept->type ;
            }
            return response()->json([
                'total_entry_amount' => $total_entry_amount,
                'total_output_amount' => $total_output_amount,
                'final_balance'=>$last_mov_balance,
                'movement_concepts' =>  $movement_concepts
            ]);

        }else{
           // abort(409, 'Descargar pdf... aun no disponible :-(');
           if($print_pdf){
           return $this->print_cash_report($request);
           }
        }
    }

    /**
    * Cierre de gestion mov fondo rotatorio.
    * @bodyParam description string required descripcion del movimiento. Example: Cierre gestion
    * @bodyParam role_id integer required Role con el cual es registrado. Example: 92
    * @authenticated
    * @responseFile responses/movements/closing_movements.200.json
    */
    public function closing_movements(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'role_id' => 'required|exists:roles,id',

        ]);

        DB::beginTransaction();
        try{
            $last_mov = MovementFundRotatory::orderBy('id')->get();
            $last_mov = $last_mov->last();

            $closing_concept = MovementConcept::whereName('CIERRE DE FONDO ROTATORIO')->first();

            if($last_mov->balance > 0){
                $movement_fund_rotatory = new MovementFundRotatory;
                $movement_fund_rotatory->user_id = Auth::id();
                $movement_fund_rotatory->role_id = $request->role_id;
                $movement_fund_rotatory->description = $request->description;
                $movement_fund_rotatory->output_amount = $last_mov->balance;
                $movement_fund_rotatory->balance = $last_mov->balance - $last_mov->balance;
                $movement_fund_rotatory->movement_concept_id = $closing_concept->id;
                $correlative = Util::correlative('cierre');
                $movement_fund_rotatory->movement_concept_code = $closing_concept->abbreviated_supporting_document.'-'.$correlative.'/'.Carbon::now()->year;

                $movement_fund_rotatory = MovementFundRotatory::create($movement_fund_rotatory->toArray());
                DB::commit();
                return $movement_fund_rotatory;
            }else{
                abort(409, 'Ya tiene saldo 0 no puede realizar un cierre ');
            }
        }catch (\Exception $e){
            DB::rollback();
          throw $e;
        }
    }
    /**
     * Reporte de cajas fondo rotatorio
     * @queryParam initial_date Fecha inicial. Example: 2021-01-01
     * @queryParam final_date Fecha Final. Example: 2021-05-01
     * @responseFile responses/movements/cash_report.200.json
     * @authenticated
     */ 
    public function print_cash_report(request $request, $standalone = true)
    {
        $movement_concept = MovementConcept::whereName('DESEMBOLSO ANTICIPO')->first();
        $initial_date = request('initial_date') ?? '';
        $final_date = request('final_date') ?? '';
        $state_vigente='Vigente';
        $conditions = [];
        //desde aqui
        if ($initial_date != '' && $final_date != '') {
            $date_ini = $request->initial_date.' 00:00:00';
            $date_fin = $request->final_date.' 23:59:59';

            $movement_lists = DB::table('movement_fund_rotatories')
              ->whereBetween('movement_fund_rotatories.created_at', [$date_ini, $date_fin])
              ->join('movement_concepts as mc','mc.id','=','movement_fund_rotatories.movement_concept_id')
              ->whereNull("movement_fund_rotatories.deleted_at")
              ->select('movement_fund_rotatories.created_at as disbursement_date_loan','movement_fund_rotatories.description as concept','*')
              ->orderBy('movement_fund_rotatories.id')
              ->get();
        } else {
            if ($final_date != '') {
                $date_fin = $request->final_date.' 23:59:59';   
            $movement_lists = DB::table('movement_fund_rotatories')
                ->where('movement_fund_rotatories.created_at',  "<=", $date_fin)
                ->join('movement_concepts as mc','mc.id','=','movement_fund_rotatories.movement_concept_id')
                ->whereNull("movement_fund_rotatories.deleted_at")
                ->select('movement_fund_rotatories.created_at as disbursement_date_loan','movement_fund_rotatories.description as concept','*')
                ->orderBy('movement_fund_rotatories.id')
                ->get();
            } else {
                $date_fin = Carbon::now();
                if ($initial_date != '') {
                    $date_ini = $request->initial_date.' 00:00:00';
                    $movement_lists = DB::table('movement_fund_rotatories')
                    ->where('movement_fund_rotatories.created_at', ">=", $date_ini)
                    ->join('movement_concepts as mc','mc.id','=','movement_fund_rotatories.movement_concept_id')
                    ->whereNull("movement_fund_rotatories.deleted_at")
                    ->select('movement_fund_rotatories.created_at as disbursement_date_loan','movement_fund_rotatories.description as concept','*')
                    ->orderBy('movement_fund_rotatories.id')
                    ->get();
                } else {
                    $movement_lists = DB::table('movement_fund_rotatories')
                    ->join('movement_concepts as mc','mc.id','=','movement_fund_rotatories.movement_concept_id')
                    ->whereNull("movement_fund_rotatories.deleted_at")
                    ->select('movement_fund_rotatories.created_at as disbursement_date_loan','movement_fund_rotatories.description as concept','*')
                    ->orderBy('movement_fund_rotatories.id')
                    ->get();
                }
            }
        }
            $movement_list_array = collect([]);
            foreach ($movement_lists as $movement_list) {
                $movement_list_array->push($movement_list);
            }
            $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ASUNTOS ADMINISTRATIVOS',
                'unity' => '',
                'table' => [
                    ['Fecha', Carbon::now()->format('d-m-Y')],
                    ['Hora', Carbon::now()->format('H:i:s')],
                    ['Usuario', Auth::user()->username]
                ]
            ],
            'title' => 'REPORTE DIARIO DE CAJA',
            'initial_date' => $initial_date? $initial_date: Carbon::parse($movement_list_array[0]->disbursement_date_loan)->format('d-m-Y'),
            'final_date' => $final_date,
            'movement_lists' => $movement_list_array,
            'file_title' => 'reporte de desembolsos',
        ];
            $file_name = 'Reporte salidas fondo rotatorio.pdf';
            $view = view()->make('loan.reports_tesoreria.cash_report')->with($data)->render();
            if ($standalone) {
                return Util::pdf_to_treasury_receipt([$view],'letter', $request->copies ?? 1);
            }
            return $view;
    }



}
