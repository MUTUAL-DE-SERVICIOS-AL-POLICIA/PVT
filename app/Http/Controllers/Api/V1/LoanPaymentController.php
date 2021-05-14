<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;
use App\LoanPayment;
use App\User;
use App\Voucher;
use App\LoanState;
use App\LoanPaymentState;
use App\Affiliate;
use App\Spouse;
use App\LoanGlobalParameter;
use App\Http\Requests\LoanPaymentsForm;
use App\Http\Requests\VoucherForm;
use App\Events\LoanFlowEvent;
use App\Events\LoanPaymentFlowEvent;
use App\RoleSequence;
use Carbon;
use DB;
use App\Helpers\Util;
use App\Http\Controllers\Api\V1\LoanController;
use App\Exports\ArchivoPrimarioExport;
use App\Exports\FileWithMultipleSheets;
use Maatwebsite\Excel\Facades\Excel;
use App\Loan;
use App\Role;
use App\ProcedureModality;
use App\PaymentType;
//use App\AmortizationType;
use App\ProcedureType;
use App\AffiliateStateType;
use App\AffiliateState;
use App\Imports\LoanPaymentImport;
use App\Tag;
use Carbon\CarbonImmutable;

/** @group Cobranzas
* Datos de los trámites de Cobranzas
*/
class LoanPaymentController extends Controller
{
    public static function append_data(LoanPayment $loanPayment, $with_state = false)
    {
        $loanPayment->loan = $loanPayment->loan;
        $loanPayment->affiliate = $loanPayment->affiliate;
        if ($with_state) $loanPayment->state = $loanPayment->state;
        $loanPayment->modality->procedure_type = $loanPayment->modality->procedure_type;
        return $loanPayment;
    }
    /**
    * Lista de Registro de pagos
    * Devuelve el listado con los datos paginados
    * @queryParam role_id integer Ver préstamos del rol, si es 0 se muestra la lista completa. Example: 73
    * @queryParam state_id integer ID del estado del registro de pago. Example 6
    * @queryParam loan_id integer ID del tramite de préstamo. Example 1
    * @queryParam user_id integer ID del usuario de préstamo. Example 1
    * @queryParam validated Booleano para filtrar trámites válidados. Example: 1
    * @queryParam procedure_type_id ID para filtrar trámites por tipo de trámite. Example: 30
    * @queryParam trashed Booleano para obtener solo eliminados. Example: 1
    * @queryParam search Parámetro de búsqueda. Example: 2000
    * @queryParam sortBy Vector de ordenamiento. Example: []
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [true]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/loan_payment/index.200.json
     */
    public function index(Request $request){
        $filters = [];
        $relations = [];
        if (!$request->has('role_id')) {
            if (Auth::user()->can('show-all-loan') || Auth::user()->can('show-all-payment-loan')) {
                $request->role_id = 0;
            } else {
                $role = Auth::user()->roles()->whereHas('module', function($query) {
                    return $query->whereName('prestamos');
                })->orderBy('name')->first();
                if ($role) {
                    $request->role_id = $role->id;
                } else {
                    abort(403);
                }
            }
        } else {
            $request->role_id = (integer)$request->role_id;
            if (($request->role_id == 0 && !Auth::user()->can('show-all-loan') && !Auth::user()->can('show-all-payment-loan')) || ($request->role_id != 0 && !Auth::user()->roles->pluck('id')->contains($request->role_id))) {
                abort(403);
            }
        }
        if ($request->role_id != 0) {
            $filters = [
                'role_id' => $request->role_id
            ];
        }
        if ($request->has('validated')) $filters['validated'] = $request->boolean('validated');
        if ($request->has('procedure_type_id')) {
            $relations['modality'] = [
                'procedure_type_id' => $request->procedure_type_id
            ];
        }
        if ($request->has('state_id')) {
            $relations['state'] = [
                'state_id' => $request->state_id
            ];
        }
        if ($request->has('loan_id')) {
            $relations['loan'] = [
                'loan_id' => $request->loan_id
            ];
        }
        if ($request->has('user_id')) {
            $relations['user'] = [
                'user_id' => $request->user_id
            ];
        }
        /*else{ // considerar para devoluciones
            if($request->validated){
                $filters['validated'] = $request->boolean('validated');
                $relations['users'] = [
                    'user_id' => null
                ];
            }
        }*/
        $data = Util::search_sort(new LoanPayment(), $request, $filters, $relations);
        $data->getCollection()->transform(function ($loanPayment) {
            return self::append_data($loanPayment, true);
        });
        return $data;
    }


    /**
    * Kardex de pagos
    * Devuelve el kardex de pagos con los datos paginados
    * @queryParam loan_id integer required ID del tramite de préstamo. Example 1
    * @queryParam search Parámetro de búsqueda. Example: PAY000016
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/loan_payment/indexKardex.200.json
     */
    public function indexKardex(Request $request){
        $loan = Loan::whereId($request->loan_id)->first();
        $loan->balance = $loan->balance;
        $loan['estimated_quota'] = $loan->estimated_quota;
        $loan['interest'] = $loan->interest;
        $payments = collect();
            $loanPayments = LoanPayment::where('loan_id', $request->loan_id)->orderBy('id')->get();
            foreach($loanPayments as $loanPayment)
            {
                if($loanPayment->state->name == 'Pagado' || $loanPayment->state->name == 'Pendiente por confirmar')
                {
                    $loanPayment->state = LoanPaymentState::whereId($loanPayment->state_id)->first();
                    $loanPayment->modality;
                    $payments->push($loanPayment);
                }
            }
        $loan->payments = $payments;
        return $loan;
    }

    /**
    * Detalle de Registro de pago
    * Devuelve el detalle de un registro de pago mediante su ID
    * @urlParam loan_payment required ID de registro de pago. Example: 4
    * @authenticated
    * @responseFile responses/loan_payment/show.200.json
    */
    public function show(LoanPayment $loanPayment)
    {
        if (Auth::user()->can('show-all-loan') || Auth::user()->roles()->whereHas('module', function($query) {
            return $query->whereName('prestamos');
        })->pluck('id')->contains($loanPayment->role_id)) {
            return self::append_data($loanPayment, true);
        } else {
            abort(403);
        }
    }

    /**
    * Editar Registro de pago
    * Edita el Registro de Pago realizado.
    * @urlParam loan_payment required ID del pago realizado. Example: 1
	* @bodyParam description string Texto de descripción. Example: Penalizacion regularizada
    * @bodyParam validated boolean Estado validación del tramite de cobro. Example: true
    * @bodyParam voucher string Comprobante de pago GAR-ABV o D-10/20 o CONT-123. Example: CONT-123
    * @bodyParam affiliate_id integer ID del afiliado. Example: 57950
    * @bodyParam paid_by enum Pago realizado por Titular(T) o Garante(G). Example: T
    * @bodyParam procedure_modality_id integer ID de la modalidad de amortización. Example: 53
    * @authenticated
    * @responseFile responses/loan_payment/update.200.json
    */
    public function update(Request $request, LoanPayment $loanPayment)
    {
        $payment_procedure_type = $loanPayment->modality->procedure_type->name;
        $Pagado = LoanPaymentState::whereName('Pagado')->first()->id;
        $pendiente_pago = LoanPaymentState::whereName('Pendiente por confirmar')->first()->id;
        $request->validate([
            'description' => 'nullable|string|min:2',
            'validated' => 'boolean',
            'procedure_modality_id'=> 'exists:procedure_modalities,id',
           // 'amortization_type_id'=> 'exists:amortization_types,id',
            'affiliate_id'=> 'exists:affiliates,id',
            'voucher'=> 'nullable|string|min:3',
            'paid_by'=> 'string|in:T,G',
        ]);
        if (Auth::user()->can('update-payment-loan')) {
            $update = $request->only('description', 'validated','procedure_modality_id','affiliate_id','voucher','paid_by');
        }
        if($payment_procedure_type != 'Amortización Directa' && $request->validated) $loanPayment->state_id=$Pagado;
        if($payment_procedure_type != 'Amortización Directa' && !$request->validated) $loanPayment->state_id=$pendiente_pago;
        $user_id = auth()->id();
        $loanPayment->fill($update);
        $loanPayment->save();
        $loanPayment->update(['user_id' => $user_id]);
        if($request->validated && $loanPayment->state_id == $Pagado || $request->validated && $request->state_id == $Pagado){
            $loanPayment->update(['loan_payment_date' => Carbon::now()]);
            $loanPayment->save();        
        }
        return  $loanPayment;
    }

    /**
    * Anular Registro de Pago
    * @urlParam loan_payment required ID del pago. Example: 1
    * @authenticated
    * @responseFile responses/loan_payment/destroy.200.json
    */
    public function destroy(LoanPayment $loanPayment)
    {
        $PendientePago = LoanPaymentState::whereName('Pendiente de Pago')->first()->id;
        $PendienteAjuste = LoanPaymentState::whereName('Pendiente por confirmar')->first()->id;
        if ($loanPayment->state_id == $PendientePago || $loanPayment->state_id == $PendienteAjuste){
            $state = LoanPaymentState::whereName('Anulado')->first();
            $loanPayment->state()->associate($state);
            $loanPayment->save();
            $loanPayment->delete();
            return $loanPayment;  
        }else{
            abort(403, 'El registro a eliminar no está en estado Pendiente');
        }
    }

    /** @group Tesoreria
    * Registro de cobro de Préstamo
    * Insertar registro de pago (loan_payment).
    * @urlParam loan_payment required ID del registro de pago. Example: 2
    * @bodyParam voucher_type_id integer required ID de tipo de voucher. Example: 1
    * @bodyParam bank_pay_number integer número de voucher bancario. Example: 12354121
    * @bodyParam voucher_amount_total number Monto del voucher. Example: 55.58
    * @bodyParam voucher_payment_date date Fecha de pago. Example: 2021-01-01
    * @bodyParam description string Texto de descripción. Example: Penalizacion regularizada
    * @authenticated
    * @responseFile responses/loan_payment/set_voucher.200.json
    */
    public function set_voucher(VoucherForm $request, LoanPayment $loanPayment)
    {
        $Liquidado = LoanState::whereName('Liquidado')->first()->id;
        $Pagado = LoanPaymentState::whereName('Pagado')->first()->id;
        $PendientePago = LoanPaymentState::whereName('Pendiente de Pago')->first()->id;

        if ($loanPayment->state_id == $PendientePago){
            DB::beginTransaction();
            try {
                $payment = new Voucher;
                $payment->user_id = auth()->id();
                //$payment->affiliate_id = $loanPayment->loan->disbursable_id;
                $payment->voucher_type_id = $request->input('voucher_type_id');
                $payment->total = $request->input('voucher_amount_total');
                $payment->payment_date = $request->input('voucher_payment_date');
                //$payment->paid_amount = $loanPayment->estimated_quota;
                //$payment->payment_type_id = $request->payment_type_id;
                $payment->description = $request->input('description', null);
                $payment->bank_pay_number = $request->input('bank_pay_number', null);
                $voucher = $loanPayment->voucher_treasury()->create($payment->toArray());
                $loanPayment->update(['state_id' => $Pagado,'user_id' => $payment->user_id,'validated'=>true,'loan_payment_date'=>Carbon::now()]);
                if($loanPayment->loan->verify_balance() == 0)
                {
                    $loan = Loan::whereId($loanPayment->loan_id);
                    $loan->update(['state_id' => $Liquidado]);
                }
                if($loanPayment->loan->payments->count() == 1 && $loanPayment->loan->payments->first()->state_id == $Pagado){
                    $user = User::whereUsername('admin')->first();
                    $amortizing_tag = Tag::whereSlug('amortizando')->first();
                    $loanPayment->loan->tags()->attach([$amortizing_tag->id => [
                        'user_id' => $user->id,
                        'date' => Carbon::now()
                    ]]);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return $e;
            }
            return $payment;
        }
        abort(403, 'Registro de Pago no realizado porque no está pendiente de pago');
    }

    /**
    * Derivar en lote
    * Deriva o devuelve trámites en un lote mediante sus IDs
    * @bodyParam ids array required Lista de IDs de los trámites a derivar. Example: [28,29]
    * @bodyParam role_id integer required ID del rol al cual derivar o devolver. Example: 91
    * @authenticated
    * @responseFile responses/loan_payment/bullk_update_role.200.json
    */
    public function bulk_update_role( LoanPaymentsForm $request)
    {
        if(!$request->user_id) 
            $user_id = null;
        else
            $user_id = $request->user_id;
        $sequence = null;
        $from_role = null;
        $to_role = $request->role_id;

        $PendientePago = LoanPaymentState::whereName('Pendiente de Pago')->first()->id;

        $to_role = $request->role_id;
        $loanPayment =  LoanPayment::whereIn('id',$request->ids)->where('role_id', '!=', $request->role_id)->where('state_id',$PendientePago)->orderBy('code');
        $derived = $loanPayment->get();
        $to_role = Role::find($to_role);
        
       
        //
        if (count(array_unique($loanPayment->pluck('role_id')->toArray()))) $from_role = $derived->first()->role_id;
        if ($from_role) {
            $from_role = Role::find($from_role);
            $flow_message = $this->flow_message($derived->first()->modality->procedure_type->id, $from_role, $to_role);
        }
        $derived->map(function ($item, $key) use ($from_role, $to_role, $flow_message) {
            if (!$from_role) {
                $item['from_role_id'] = $item['role_id'];
                $from_role = Role::find($item['role_id']);
                $flow_message = $this->flow_message($item->modality->procedure_type->id, $from_role, $to_role);
            }
            $item['role_id'] = $to_role->id;
            $item['validated'] = false;
            Util::save_record($item, $flow_message['type'], $flow_message['message']);
        });
        //

        $loanPayment->update(array_merge($request->only('role_id'), ['validated' => false], ['user_id' => $user_id]));
        
        $derived->transform(function ($loanPaymen) {
            return self::append_data($loanPaymen, true);
        });

        event(new LoanPaymentFlowEvent($derived));

        // PDF template
        $data = [
            'type' => 'loan_payment',
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'Área de ' . $from_role->display_name,
                'table' => [
                    ['Fecha', Carbon::now()->isoFormat('L')],
                    ['Hora', Carbon::now()->format('H:i')],
                    ['Usuario', Auth::user()->username]
                ]
            ],
            'title' => ($flow_message['type'] == 'derivacion' ? 'DERIVACIÓN' : 'DEVOLUCIÓN') . ' DE TRÁMITES - MODALIDAD ' . $derived->first()->modality->procedure_type->second_name,
            'procedures' => $derived,
            'roles' => [
                'from' => $from_role,
                'to' => $to_role
            ]
        ];
        $information_derivation='Fecha: '.Str::slug(Carbon::now()->isoFormat('LLL'), ' ').'  enviado a  '.$from_role->display_name;
        $file_name = implode('_', ['derivacion', 'Cobros', Str::slug(Carbon::now()->isoFormat('LLL'), '_')]) . '.pdf';
        $view = view()->make('flow.bulk_flow_procedures')->with($data)->render();
        return response()->json([
            'attachment' => Util::pdf_to_base64([$view], $file_name,$information_derivation, 'letter', $request->copies ?? 1, false),
            'derived' => $derived
        ]);
    }

    private function flow_message($procedure_type_id, $from_role, $to_role)
    {
        $sequence = RoleSequence::flow($procedure_type_id, $from_role->id);
        if (in_array($to_role->id, $sequence->next->all())) {
            $message = 'derivó';
            $type = 'derivacion';
        } else {
            $message = 'devolvió';
            $type = 'devolucion';
        }
        $message .= ' de ' . $from_role->display_name . ' a ' . $to_role->display_name;
        return [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
    * Eliminar en lote
    * Elimina trámites en un lote mediante sus IDs, aquellos tramites que se encuentre con estato pendiente de pago
    * @bodyParam ids array required Lista de IDs de los trámites a dereliminar. Example: [1,2,3]
    * @authenticated
    * @responseFile responses/loan_payment/bulk_destroy.200.json
    */
    
    public function bulk_destroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1|exists:loan_payments,id'
        ]);

        $paymentsDestroy = collect();
        $PendientePago = LoanPaymentState::whereName('Pendiente de Pago')->first()->id;
        $loanPaymentse =  LoanPayment::whereIn('id',$request->ids)->where('state_id', $PendientePago);
        $loanPayments = $loanPaymentse->get();
        foreach ($loanPayments as $loanPayment) {
                $loanPayment->delete();
                $paymentsDestroy->push($loanPayment);
        }
        return $paymentsDestroy;
    }

    /**
    * Impresión del Registro de Pago de Préstamo
    * Devuelve un pdf del Pago acorde a un ID de registro de pago
    * @urlParam loan_payment required ID del pago. Example: 1
    * @queryParam copies Número de copias del documento. Example: 2
    * @authenticated
    * @responseFile responses/loan_payment/print_loan_payment.200.json
    */
    public function print_loan_payment(Request $request, LoanPayment $loan_payment, $standalone = true)
    { 
        $loan = LoanPayment::findOrFail($loan_payment->id)->loan;
        $affiliate = $loan_payment->affiliate;
        $spouse = $affiliate->spouse;
        if(isset($spouse)){
            $affiliate = $spouse;
            }
        $procedure_modality = $loan->modality;
        $lenders = []; 
        $is_dead = false;
        $quota_treat = 0;
        foreach ($loan->lenders as $lender) {
            $lenders[] = LoanController::verify_spouse_disbursable($lender)->disbursable;
            if($lender->dead) $is_dead = true;
        }
        $global_parameter=LoanGlobalParameter::latest()->first();
        $max_current=$global_parameter->grace_period+$global_parameter->days_current_interest;
        $num_quota=$loan_payment->quota_number;
            if($num_quota == 1){
                $disbursement_date = CarbonImmutable::parse($loan->disbursement_date);
                $estimated_days['current'] = $disbursement_date->diffInDays(CarbonImmutable::parse($loan_payment->estimated_date))+1;
                if($estimated_days['current'] > $max_current)
                    $estimated_days['penal'] = $estimated_days['current'] - $global_parameter->days_current_interest;
                else
                    $estimated_days['penal'] = 0;
            }else{                
                $reg_payment_date = CarbonImmutable::parse($loan_payment->previous_payment_date);
                $estimated_days['current'] = $reg_payment_date->diffInDays(CarbonImmutable::parse($loan_payment->estimated_date));
                if($estimated_days['current'] > $max_current)
                $estimated_days['penal'] = $estimated_days['current'] - $global_parameter->days_current_interest;
                else
                $estimated_days['penal'] = 0;
            }
        $persons = collect([]);
            $persons->push([
                'full_name' => implode(' ', [$affiliate->title, $affiliate->full_name]),
                'identity_card' => $affiliate->identity_card_ext,
                'position' => 'PAGADO POR'." ".$a = $loan_payment->paid_by == "T" ? "TITULAR":"GARANTE"
            ]);   
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => [
                    ['Tipo', $loan->modality->procedure_type->second_name],
                    ['Modalidad', $loan->modality->shortened],
                    ['Fecha', Carbon::now()->format('d/m/Y')],
                    ['Hora', Carbon::now()->format('h:m:s a')],
                    ['Usuario', Auth::user()->username]
                ]
            ],
            'title' => 'AMORTIZACIÓN DE CUOTA',
            'loan' => $loan,
            'lenders' => collect($lenders),
            'loan_payment' => $loan_payment,
            'signers' => $persons,
            'is_dead'=> $is_dead,
            'estimated_days' => $estimated_days
        ];
      
        $information_loan = $this->get_information_loan($loan);
        $file_name = implode('_', ['pagos', $procedure_modality->shortened, $loan->code]) . '.pdf';
        $view = view()->make('loan.payments.payment_loan')->with($data)->render();
        if ($standalone) return Util::pdf_to_base64([$view], $file_name, $information_loan, 'legal', $request->copies ?? 1);
        return $view;
    }

    public function get_information_loan(Loan $loan)
    {
        $lend='';
        foreach ($loan->lenders as $lender) {
            $lenders[] = LoanController::verify_spouse_disbursable($lender);
        }
        foreach ($loan->lenders as $lender) {
            $lend=$lend.'*'.' ' . $lender->first_name .' '. $lender->second_name .' '. $lender->last_name.' '. $lender->mothers_last_name;
        }
        
        $loan_affiliates= $loan->loan_affiliates[0]->first_name;
        $file_name =implode(' ', ['Información:',$loan->code,$loan->modality->name,$lend]); 
    
        return $file_name;
    }

    /**
    * Reactivar Registro de Pago
    * Reactiva un registro de pago
    * @urlParam loan_payment required ID del registro de pago. Example: 2
    * @authenticated
    * @responseFile responses/loan_payment/reactivate.200.json
    */
    public function reactivate($id)
    {
        $loanPayment = LoanPayment::withTrashed()->where('id', '=', $id)->first();
        if(!$loanPayment){
            abort(403, 'No existe el registro de pago a reactivar');
        }
        $loanPayment->restore();
        return $loanPayment;
    }

    public function deleteCanceledPaymentRecord(){
        $PendientePago = LoanPaymentState::whereName('Pendiente de Pago')->first()->id;
        $dias = LoanGlobalParameter::latest()->first()->date_delete_payment;
        $loanPayment = LoanPayment::where('estimated_date','<=',Carbon::now()->subDay($dias))->whereStateId($PendientePago);
        $loanPayment->delete();
    }

    /**
    * Estado del Trámite de Cobro
    * Devuelve el estado del trámite de cobro
    * @urlParam loan_payment required ID de trámite de cobro. Example: 12
    * @authenticated
    * @responseFile responses/loan_payment/get_state.200.json
    */
    public function get_state(LoanPayment $loan_payment)
    {
        if ($loan_payment->state) return $loan_payment->state;
    }

    /**
    * Flujo de trabajo
    * Devuelve la lista de roles anteriores para devolver o posteriores para derivar el trámite
    * @urlParam loan_payment required ID del tramite de cobro. Example: 2
    * @authenticated
    * @responseFile responses/loan_payment/get_flow.200.json
    */
    public function get_flow(LoanPayment $loan_payment)
    {
        return response()->json(RoleSequence::flow($loan_payment->modality->procedure_type->id, $loan_payment->role_id));
    }

    /**
    * Voucher del registro de cobro
    * Devuelve el voucher del registro de cobro realizado por Tesoreria
    * @urlParam loan_payment required ID de trámite de cobro. Example: 1
    * @authenticated
    * @responseFile responses/loan_payment/get_voucher.200.json
    */
    public function get_voucher(LoanPayment $loan_payment)
    {
        if ($loan_payment->voucher_treasury){
            return $loan_payment->voucher_treasury;
        }
        abort(403, 'No existe el registro de pago');
    }


    /**
    * Importar y descargar excel por periodo
    * genera los pagos del periodo y exporta el escel
	* @bodyParam estimated_date date Fecha para el cálculo del interés. Example: 2020-12-31
    * @authenticated
    * @responseFile responses/loan_payment/command_senasir_save_payment.200.json
    */
    public function download(Request $request)
    {
        $period = Carbon::parse($request->estimated_date)->format('M').'-'.Carbon::parse($request->estimated_date)->format('Y');
        $month = CarbonImmutable::parse($request->estimated_date)->format('M');
        $year = Carbon::parse($request->estimated_date)->format('Y');
        $file_name = $month.'-'.$year;
        $extension = '.xlsx';
        if(Storage::disk('public')->has($file_name.$extension)){
            return $file = Storage::disk('public')->download($file_name.$extension);
            //return $file = Storage::file($file_name.$extension);
        }
        else{
            $this->command_senasir_save_payment($request->estimated_date);
            $command_sheet=array(
                array("Fecha de desembolso", "numero", "Tipo", "Matricula Titular", "Matricula Derecho Habiente", "CI", "Extension", "Primer Nombre", "Segundo Nombre", "Paterno", "Materno", "Saldo Actual", "Cuota", "Descuento", "Ciudad", "Interes")
            );
            $command_id = ProcedureModality::where('shortened', 'DES-COMANDO')->first();
            $command = LoanPayment::where('estimated_date',$request->estimated_date)->where('procedure_modality_id', $command_id->id)->get();
            foreach ($command as $row){
                if($row->loan->state->name == 'Vigente'){
                    array_push($command_sheet, array(
                        $row->loan->disbursement_date,
                        $row->loan->code,
                        $row->affiliate->affiliate_state->name,
                        $row->affiliate->registration,
                        $row->affiliate->registration,//verificar
                        $row->affiliate->identity_card,
                        $row->affiliate->city_identity_card->first_shortened,
                        $row->affiliate->first_name,
                        $row->affiliate->second_name,
                        $row->affiliate->last_name,
                        $row->affiliate->mothers_last_name,
                        $row->previous_balance,
                        $row->estimated_quota,
                        $row->estimated_quota,
                        $row->loan->city->name,
                        $row->loan->interest->annual_interest,
                    ));
                }
            }
            $senasir_sheet=array(
                array("Fecha de desembolso", "numero", "Tipo", "Matricula Titular", "Matricula Derecho Habiente", "CI", "Extension", "Primer Nombre", "Segundo Nombre", "Paterno", "Materno", "Saldo Actual", "Cuota", "Descuento", "Ciudad", "Interes")
            );
            $senasir_id = ProcedureModality::where('shortened', 'DES-SENASIR')->first();
            $senasir = LoanPayment::where('estimated_date',$request->estimated_date)->where('procedure_modality_id', $senasir_id->id)->get();
            foreach ($senasir as $row){
                if($row->loan->state->name == 'Vigente'){
                    array_push($command_sheet, array(
                        $row->loan->disbursement_date,
                        $row->loan->code,
                        $row->affiliate->affiliate_state->name,
                        $row->affiliate->registration,
                        $row->affiliate->registration,//verificar
                        $row->affiliate->identity_card,
                        $row->affiliate->city_identity_card->first_shortened,
                        $row->affiliate->first_name,
                        $row->affiliate->second_name,
                        $row->affiliate->last_name,
                        $row->affiliate->mothers_last_name,
                        $row->previous_balance,
                        $row->estimated_quota,
                        $row->estimated_quota,
                        $row->loan->city->name,
                        $row->loan->interest->annual_interest,
                    ));
                }
            }
            $export = new FileWithMultipleSheets($command_sheet, $senasir_sheet, $command_sheet, $senasir_sheet);
            Excel::store($export, $file_name.$extension, 'public');
            return $file = Storage::disk('public')->download($file_name.$extension);
        }
        //return Excel::store($export, $File.'.xlsx', 'ftp');
    }

    /**
    * Listado de pagos automaticos por periodo
    * Devuelve Los pagos generados y pagados por un periodo
    * @bodyParam fecha periodo required fecha del periodo de pagos que se desea visualizar
    * @authenticated
    * @responseFile responses/loan_payment/payments_per_period.200.json
    */
    public function payments_per_period(Request $request)
    {
        $modalities = ProcedureModality::where('shortened', 'DES-COMANDO')->orWhere('shortened', 'DES-SENASIR')->get('id');
        $id = [];
        foreach($modalities as $modality)
            array_push($id, $modality->id);
        $payments = LoanPayment::whereIn('procedure_modality_id', $id)->where('estimated_date',$request->period)->get();
        foreach($payments as $payment)
            $payment->state = $payment->state;
        return $payments;
    }

    
    private function command_senasir_save_payment($estimated_date)
    {
        $estimated_date = $estimated_date? Carbon::parse($estimated_date) : Carbon::now()->endOfMonth();
        $loan_state = LoanState::where('name', 'Vigente')->first();
        $modalities = ProcedureModality::where('name', 'like', '%AFP')->get();
        $id = [];
        foreach($modalities as $modality)
        {
            array_push($id, $modality->id);
        }
        $loans = Loan::where('state_id', $loan_state->id)->whereNotIn('procedure_modality_id', $id)->get();
        $description = 'Por descuento automatico';
        $procedure_modality_command = ProcedureModality::whereShortened('DES-COMANDO')->first();//revisar
        $procedure_modality_senasir = ProcedureModality::whereShortened('DES-SENASIR')->first();//revisar
        $mestimated_date = $estimated_date->month;
        $yestimated_date = $estimated_date->year;
        $voucher = "AUT".'-'.'0'.$mestimated_date.'/'.$yestimated_date;
        $loans_quantity = 0;

        foreach($loans as $loan){
            $date_payment = Carbon::parse($loan->disbursement_date)->endOfMonth()->format('Y-m-d');
            $disbursement_day = Carbon::parse($loan->disbursement_date)->day;
            if($loan->balance != 0){
                if($loan->modality->procedure_type->name == 'Préstamo a Largo Plazo' || $loan->modality->procedure_type->name == 'Préstamo a Corto Plazo'){
                    if(!$loan->guarantor_amortizing){
                        foreach($loan->lenders as $lender)
                        {
                            $paid_by = "T";
                            if($lender->affiliate_state->name == 'Servicio' || $lender->affiliate_state->name == 'Disponibilidad') $procedure_modality = $procedure_modality_command;
                            if($lender->affiliate_state->name == 'Jubilado' && $lender->affiliate_state && $lender->pension_entity->name == 'SENASIR') $procedure_modality = $procedure_modality_senasir;
                            if($disbursement_day < LoanGlobalParameter::latest()->first()->offset_interest_day && $estimated_date == $date_payment){
                                LoanPayment::registry_payment($loan, $estimated_date, $description, $procedure_modality->id, $voucher, $paid_by, $lender->pivot->quota_treat, $lender->id);
                                $loans_quantity++;
                            }
                            else{
                                if($estimated_date > $date_payment)
                                {
                                    LoanPayment::registry_payment($loan, $estimated_date, $description, $procedure_modality->id, $voucher, $paid_by, $lender->pivot->quota_treat, $lender->id);
                                    $loans_quantity++;
                                }
                            }
                        }
                    }
                    else
                    {
                        foreach($loan->guarantors as $guarantor)
                        {
                            $paid_by = "G";
                            if($guarantor->affiliate_state->name == 'Servicio' || $lender->affiliate_state->name == 'Disponibilidad') $procedure_modality = $procedure_modality_command;
                            if($guarantor->affiliate_state->name == 'Jubilado' && $lender->affiliate_state && $lender->pension_entity->name == 'SENASIR') $procedure_modality = $procedure_modality_senasir;
                            if($disbursement_day < LoanGlobalParameter::latest()->first()->offset_interest_day && $estimated_date == $date_payment){
                                LoanPayment::registry_payment($loan, $estimated_date, $description, $procedure_modality->id, $voucher, $paid_by, $lender->pivot->quota_treat, $lender->id);
                                $loans_quantity++;
                            }
                            else{
                                if($estimated_date > $date_payment)
                                {
                                    LoanPayment::registry_payment($loan, $estimated_date, $description, $procedure_modality->id, $voucher, $paid_by, $lender->pivot->quota_treat, $lender->id);
                                    $loans_quantity++;
                                }
                            }
                        }
                    }
                }
                else{
                    foreach($loan->lenders as $lender)
                    {
                        $paid_by = "T";
                        if($lender->affiliate_state->name == 'Servicio' || $lender->affiliate_state->name == 'Disponibilidad') $procedure_modality = $procedure_modality_command;
                        if($lender->affiliate_state->name == 'Jubilado' && $lender->pension_entity && $lender->pension_entity->name == 'SENASIR') $procedure_modality = $procedure_modality_senasir;
                        if($disbursement_day < LoanGlobalParameter::latest()->first()->offset_interest_day && $estimated_date == $date_payment){
                            LoanPayment::registry_payment($loan, $estimated_date, $description, $procedure_modality->id, $voucher, $paid_by, $lender->pivot->quota_treat, $lender->id);
                            $loans_quantity++;
                        }
                        else{
                            if($estimated_date > $date_payment)
                            {
                                LoanPayment::registry_payment($loan, $estimated_date, $description, $procedure_modality->id, $voucher, $paid_by, $lender->pivot->quota_treat, $lender->id);
                                $loans_quantity++;
                            }
                        }
                    }
                }
            }
        }
        return response()->json([
            'loans_quantity' => $loans_quantity
        ]);
    }

    /**
    * Importación pendientes de Pagos Comando SENASIR
    * Realiza la importación de pagos.
	* @bodyParam file file required Archivo de importación. Example: file.xls
    * @bodyParam state boolean required Tipo importacion Activo(1) o Pasivo(0). Example: 1
    * @bodyParam estimated_date date Fecha estimada para la importacion. Example: 2020-12-31
    * @bodyParam voucher_payment string Comprobante de pago. Example: D-12/20
    * @authenticated
    * @responseFile responses/loan_payment/importation_pending.200.json
    */
    public function importation_pending_command_senasir(Request $request)
    {
        $request->validate([
            'file' => 'required',
            'state'=> 'required|boolean',
            'estimated_date'=> 'nullable|date_format:"Y-m-d"',
            'voucher_payment' => 'nullable|string|min:3'
        ]);

        $file = $request->file('file');
        $json = collect([]);
        $payment_automatic = collect([]);//pagos automaticos confirmados
        $payment_no_automatic = collect([]);//pagos no efectivizados
        $array = Excel::toArray(new LoanPaymentImport, $file);
        $pendientePago = LoanPaymentState::whereName('Pendiente de Pago')->first()->id;
        $pendienteConfirmation = LoanPaymentState::whereName('Pendiente por confirmar')->first()->id;
        $pagado = LoanPaymentState::whereName('Pagado')->first()->id;
        $procedure_modality_automatic = ProcedureModality::whereName('A.AUT. Cuota pactada')->first();//revisar
        $procedure_modality_parcial = ProcedureModality::whereName('A.AUT. Parcial')->first();//revisar
        $estimated_date_importation = $request->estimated_date? Carbon::parse($request->estimated_date) : Carbon::now()->endOfMonth();
       // $payment_type = AmortizationType::get();
        //$payment_type_desc = $payment_type->where('name', 'LIKE', 'Descuento automático')->first();
        $contand=0;
        $concatenando='';
        $concatenandoCi='';
        $loanAll=collect([]);
        $loanPayments = new LoanPayment();

        $amount_Affiliate=0;
        
            for($i=1;$i<count($array[0]);$i++){   
                $amount_Affiliate = $array[0][$i][1];
                
                $totalLoanAmount = 0; 
                $have_payment=false;

                if($request->state){
                    $ci=(int)$array[0][$i][0];
                    $affiliate = Affiliate::where('identity_card', '=',$ci)->first();                    
                }else{
                    $matricula= $array[0][$i][0];
                    $affiliate = Affiliate::where('registration', '=',$matricula)->first();
                }
               
                $loanPayments = LoanPayment::where('affiliate_id',$affiliate->id)->where('state_id','=',$pendienteConfirmation)
                                            ->where('procedure_modality_id','=',$procedure_modality_automatic->id)->where('estimated_date','=',$estimated_date_importation)->get();

                
                foreach ($loanPayments as $loanPayment){
                      $payment_estimated_date=Carbon::parse($loanPayment->estimated_date);
                        $totalLoanAmount = $totalLoanAmount + $loanPayment->estimated_quota;
                        $have_payment=true;
                }
                
                if ($totalLoanAmount == $array[0][$i][1] && $have_payment){
                    foreach ($loanPayments as $loanPayment){
                        $loanPayment->state_id = $pagado;
                        if($request->voucher_payment){
                            $loanPayment->voucher = $request->voucher_payment;
                        }
                        $loanPayment->validated = true;
                        $loanPayment->user_id = auth()->id();
                        $loanPayment->update();
                        $payment_automatic->push($loanPayment);
                    }
                }else{
                    foreach ($loanPayments as $loanPayment){
                        $payment_no_automatic->push($loanPayment);
                    }
                }
            }
            return response()->json([
                'payments_automatic' => $payment_automatic,
                'payments_no_automatic' => $payment_no_automatic,
               // 'Contandooo ' =>  $contand,
                //'$concatenando' =>  $concatenando,
               // 'todosloans' => $loanLender,
               // 'Concatenando'=>$concatenandoCi
            ]);
    }

     /**
    * Importación de Pagos Comando SENASIR
    * Realiza la importación de pagos.
	* @bodyParam file file required Archivo de importación. Example: file.xls
    * @bodyParam state boolean required Tipo importacion Activo(1) o Pasivo(0). Example: 1
    * @bodyParam estimated_date date Fecha estimada para la importacion. Example: 2020-12-31
    * @bodyParam voucher_payment string Comprobante de pago. Example: D-12/20
    * @authenticated
    * @responseFile responses/loan_payment/importation_payments.200.json
    */
    public function importation_command_senasir(Request $request)
    {
        $request->validate([
           'file' => 'required',
            'state'=> 'required|boolean',
            'estimated_date'=> 'nullable|date_format:"Y-m-d"',
            //'voucher_payment' => 'nullable|string|min:3'
        ]);
       
        $file = $request->file('file');
        $json = collect([]);
        $payment_automatic = collect([]);//pagos automaticos confirmados
        $payment_no_automatic = collect([]);//pagos no efectivizados
        $array = Excel::toArray(new LoanPaymentImport, $file);
       // $procedure_modality_comando = ProcedureModality::whereShortened("DES-COMANDO")->first()->id;
       // $procedure_modality_senasir = ProcedureModality::whereShortened("DES-SENASIR")->first()->id;
       //amorttizacion a
        $procedure_type_id = ProcedureType::whereName("Amortización Automática")->first()->id;
        $pendiente_confirmar_id = LoanPaymentState::whereName('Pendiente por confirmar')->first()->id;
        $pagado = LoanPaymentState::whereName('Pagado')->first()->id;

        $estimated_date_importation = $request->estimated_date? Carbon::parse($request->estimated_date) : Carbon::now()->endOfMonth();
        $mestimated_date = $estimated_date_importation->month;
        $yestimated_date = $estimated_date_importation->year;
        $voucher_enter= $request->voucher_payment? "AUT".'-'.'0'.$mestimated_date.'/'.$yestimated_date : "AUT".'-'.'0'.$mestimated_date.'/'.$yestimated_date;
        $contand=0;
        $concatenando='';
        $concatenandoCi='';
        $loanAll=collect([]);
        $loanPayments = new LoanPayment();
        $amount_more_affiliate=collect([]);
        $amount_Affiliate=0;
            for($i=1;$i<count($array[0]);$i++){   
                $amount_Affiliate = $array[0][$i][1];
                $totalLoanAmount = 0; 
                $have_payment = false;
                if($request->state){//comando
                    $procedure_modality_id = ProcedureModality::whereShortened("DES-COMANDO")->first()->id;;
                    $ci=(int)$array[0][$i][0];
                    $affiliate = Affiliate::whereIdentityCard($ci)->first(); 
                    $loanPayments = LoanPayment::where('affiliate_id', $affiliate->id)->where('state_id',$pendiente_confirmar_id)
                    ->where('procedure_modality_id', $procedure_modality_id)->where('estimated_date',$estimated_date_importation)->get();
                              
                }else{ //senasir
                    $matricula= $array[0][$i][0];
                    $affiliate = Affiliate::whereRegistration($matricula)->first();
                    if($affiliate == null){
                        $affiliate = Spouse::whereRegistration($matricula)->first()->affiliate; //verificar logica     
                    }                    
                    $procedure_modality_id=ProcedureModality::whereShortened("DES-SENASIR")->first()->id;
                    $loanPayments = LoanPayment::where('affiliate_id',$affiliate->id)->where('state_id',$pendiente_confirmar_id)
                    ->where('procedure_modality_id',$procedure_modality_id)->where('estimated_date',$estimated_date_importation)->get();
                }
                foreach ($loanPayments as $loanPayment){                
                      $payment_estimated_date = Carbon::parse($loanPayment->estimated_date);
                        $totalLoanAmount = $totalLoanAmount + $loanPayment->estimated_quota; 
                        $have_payment = true;
                }
               
                if ($totalLoanAmount == $array[0][$i][1] && $have_payment){
                    foreach ($loanPayments as $loanPayment){
                        $loanPayment->state_id = $pagado;
                        $loanPayment->voucher = $voucher_enter;
                        $loanPayment->validated = true;
                        $loanPayment->user_id = auth()->id();
                        $loanPayment->loan_payment_date = Carbon::now();
                        $loanPayment->update();       
                        $payment_automatic->push($loanPayment);
                    }
                }else{
                    $amount_Affiliate = $array[0][$i][1];
                    $loanLender = collect([]);
                    $loanPaymentsLender = collect([]);
                    $loanGuarantor = collect([]);
                    $loanPaymentsGuarantor = collect([]);
                    foreach ($loanPayments as $loanPayment){
                        if($loanPayment->paid_by == 'T') $loanLender->push($loanPayment->loan);
                        if($loanPayment->paid_by == 'G') $loanGuarantor->push($loanPayment->loan);
                    }
                    //aqui entraria el metodo 
                    //$this->amortization_loan_priorities($loanLender,$loanPayments,$amount_Affiliate,'T',$estimated_date_importation,$affiliate,$request->voucher_payment);
                    
                    $loanLender=$loanLender->sortBy('disbursement_date',SORT_NATURAL);
                    $loanLender=$loanLender->values()->all();//ordenado de  deacuerdo a lo mas antiguo
                   
                    foreach($loanLender as $loanLenderPayment){
                        //$concatenando=$concatenando.' '.$amount_Affiliate.$i;//pruebas
                        
                        $has_affiliate_balance = true;
                        $loanPaymentsLender = $loanPayments->where('paid_by','T')->where('loan_id',$loanLenderPayment->id);//Aqui

                        $loanPaymentsLender=$loanPaymentsLender->first();
                      
                                if($loanPaymentsLender->estimated_quota <= $amount_Affiliate){
                                    $loanPaymentsLender->state_id = $pagado;
                                    $loanPaymentsLender->validated = true;
                                    $loanPaymentsLender->user_id = auth()->id();
                                    $loanPaymentsLender->loan_payment_date = Carbon::now();
                                    $loanPaymentsLender->update();
                               
                                    $payment_automatic->push($loanPaymentsLender);
                                    $amount_Affiliate = $amount_Affiliate - $loanPaymentsLender->estimated_quota;

                                }else{//si el registro de pago es mayor a 0
                                    if($amount_Affiliate > 0){
                                        $loan = $loanPaymentsLender->loan;
                                        $estimated_date=$estimated_date_importation;
                                        $description=$loanPaymentsLender->description;
                                        $procedure_modality=$procedure_modality_id;
                                        $voucher_pago=$loanPaymentsLender->voucher;
                                        $paid_by=$loanPaymentsLender->paid_by;
                                        $estimated_quota=$amount_Affiliate;
                                        $lender = $affiliate;
                                        $loanPaymentsLender->delete();
                                        $validated_payment = false;
                                        $state_id = $pagado;
                                        $new_loanPayment = LoanPayment::registry_payment($loan, $estimated_date, $description, $procedure_modality, $voucher_pago, $paid_by,$estimated_quota,$lender->id, $state_id, $validated_payment);
                                        $new_loanPayment->user_id = auth()->id();
                                        $new_loanPayment->state_id = $pagado;
                                        $new_loanPayment->validated = true;
                                        $new_loanPayment->loan_payment_date = Carbon::now();
                                        $new_loanPayment->update();
                                        $payment_automatic->push($new_loanPayment);
                                        $amount_Affiliate = $amount_Affiliate - $new_loanPayment->estimated_quota;
                                       //mono affiliado 
                                    }else{
                                        $payment_no_automatic->push($loanPaymentsLender);
                                    }
                                }
                    }//para garantias
                    if($amount_Affiliate>0){
                        $loanGuarantor=$loanGuarantor->sortBy('disbursement_date',SORT_NATURAL);
                        $loanGuarantor=$loanGuarantor->values()->all();//ordenado de  deacuerdo a lo mas antiguo

                        //prestamos garantizados 
                        foreach($loanGuarantor as $loanGuarantorPayment){
                            $loanPaymentsGuarantor = $loanPayments->where('paid_by','G')->where('loan_id',$loanGuarantorPayment->id);//Aqui

                            $loanPaymentsGuarantor = $loanPaymentsGuarantor->first();

                            if($loanPaymentsGuarantor->estimated_quota <= $amount_Affiliate){
                                $loanPaymentsGuarantor->state_id = $pagado;
                                $loanPaymentsGuarantor->validated = true;
                                $loanPaymentsGuarantor->user_id = auth()->id();
                                $loanPaymentsGuarantor->loan_payment_date = Carbon::now();
                                $loanPaymentsGuarantor->update();
                                $payment_automatic->push($loanPaymentsLender);
                                $amount_Affiliate=$amount_Affiliate - $loanPaymentsGuarantor->estimated_quota;

                            }else{//si el registro de pago es mayor a 0

                                if($amount_Affiliate > 0){
                                    $loan=$loanPaymentsGuarantor->loan;
                                    $estimated_date=$estimated_date_importation;
                                    $description=$loanPaymentsGuarantor->description;
                                    $procedure_modality=$procedure_modality_id;
                                    $voucher_pago=$loanPaymentsGuarantor->voucher;
                                    //$voucher=$loanPaymentsLender->voucher;
                                    $paid_by=$loanPaymentsGuarantor->paid_by;
                                    //$percentage_quota = 100;
                                    $lender=$affiliate;
                                    $loanPaymentsGuarantor->delete();
                                    $estimated_quota = $amount_Affiliate;
                                   /* if($request->voucher_payment){
                                        $voucher = $voucher_enter;
                                    }else{
                                        $voucher=$loanPaymentsGuarantor->voucher;
                                    }*/   
                                    $state_id = $pagado;
                                    $validated_payment = false;
                                    //registro del pago
                                    $new_loanPayment = LoanPayment::registry_payment($loan, $estimated_date, $description, $procedure_modality, $voucher_pago, $paid_by, $estimated_quota,$lender->id, $state_id,$validated_payment );
                                    $new_loanPayment->validated = true;
                                    $new_loanPayment->state_id = $pagado;
                                    $new_loanPayment->user_id = auth()->id();
                                    $new_loanPayment->loan_payment_date = Carbon::now();
                                    $new_loanPayment->update();
                                    $payment_automatic->push($new_loanPayment);
                                    
                                    $amount_Affiliate = $amount_Affiliate - $new_loanPayment->estimated_quota;//mono affiliado 
                                }else{
                                    $payment_no_automatic->push($loanPaymentsGuarantor);
                                }
                            }
                        }
                    }
                    //verifica si el monto es mayor a garantias
                    if($amount_Affiliate>0){
                        $affiliate_mount = (object)['ci' => $affiliate->identity_card,'matricula' => $affiliate->registration,'monto_excedente' => $amount_Affiliate,'Estado afiliado' => $affiliate->registration];
                        $amount_more_affiliate->push($affiliate_mount);
                    }
                    // $payment_no_automatic->push($loanPaymentsLender);
                }
            }

           /* return response()->json([
                'payments_automatic' => $payment_automatic,
                'payments_no_automatic' => $payment_no_automatic,
                'amount_more_affiliate'=> $amount_more_affiliate
               // 'Contandooo ' =>  $contand,
                //'$concatenando' =>  $concatenando,
               // 'todosloans' => $loanLender,
               // 'Concatenando'=>$concatenandoCi
            ]);*/

        $File=$estimated_date_importation."AffiliadosConPagosExcedentes";
        $data = array(
            array("CI", "Matrícula", "Monto excedente")
        );
        if($amount_more_affiliate != null){
           
        foreach ($amount_more_affiliate as $row){
            array_push($data, array(
                $row->ci,
                $row->matricula,
                $row->monto_excedente
            ));
        }
       } 
        $export = new ArchivoPrimarioExport($data);
        return Excel::download($export, $File.'.xlsx');
        

    }
    /** @group Reportes préstamos
    * Préstamos en móra
    * Descarga en xls los prestamos que se encuentran en Móra.
    */
   
    public function loans_delay(Request $request)
    {
        $loans = Loan::whereHas('state', function($query) {
            $query->whereName('Vigente');
        })->get();
        $delays = collect([]);
        foreach ($loans as $loan) {
            if ($loan->defaulted) {
                $delays->push($loan);
            }
        }
        $File="PrestamosEnMora";
        $data=array(
            array("id","CI","Primer apellido","Segundo apellido","Primer nombre","Segundo nombre","Código del préstamo","Estado del préstamo","Monto aprobado","Tiempo del préstamo","Fecha de desembolso","Días penal","Días Acumulados","Días corrientes")
        );
        foreach ($delays as $row){
            array_push($data, array(
                $row->loan_affiliates()->get()[0]->id,
                $row->loan_affiliates()->get()[0]->identity_card,
                $row->loan_affiliates()->get()[0]->last_name,
                $row->loan_affiliates()->get()[0]->mothers_last_name,
                $row->loan_affiliates()->get()[0]->first_name,
                $row->loan_affiliates()->get()[0]->second_name,
                $row->code,
                $row->state()->get()[0]->name,
                $row->amount_approved,
                $row->loan_term,
                $row->disbursement_date,
                $row->getdelay()->penal,
                $row->getdelay()->accumulated,
                $row->getdelay()->current
            ));
        }
       $export = new ArchivoPrimarioExport($data);
       return Excel::download($export, $File.'.xlsx'); 
    }
    //reporte 
    /** 
   * Listar amortizaciones generando reportes
   * Lista todos los amortizaciones con opcion a busquedas
   * @queryParam sortDesc Vector de orden descendente(0) o ascendente(1). Example: 0
   * @queryParam per_page Número de datos por página. Example: 8
   * @queryParam page Número de página. Example: 1
   * @queryParam excel Valor booleano para descargar  el docExcel. Example: true
   * @queryParam id_loan Buscar ID del Préstamo. Example: 1
   * @queryParam code_loan  Buscar código del Préstamo. Example: PTMO000012-2021
   * @queryParam disbursement_date_loan  Fecha de desembolso. Example: 2021-01-01
   * @queryParam state_type_affiliate  Estado del afiliado. Example: Activo
   * @queryParam id_affiliate ID del afiliado. Example: 1
   * @queryParam identity_card_affiliate ID del afiliado. Example: 667895
   * @queryParam registration_affiliate ID del afiliado. Example: 667895MP
   * @queryParam last_name_affiliate Buscar por primer apellido del afiliado. Example: RIVERA
   * @queryParam mothers_last_name_affiliate Buscar por segundo apellido del afiliado. Example: ARTEAG
   * @queryParam first_name_affiliate Buscar por primer Nombre del afiliado. Example: ABAD
   * @queryParam second_name_affiliate Buscar por segundo Nombre del afiliado. Example: FAUST
   * @queryParam surname_husband_affiliate Buscar por Apellido de casada Nombre del afiliado. Example: De LA CRUZ
   * @queryParam pension_entity_affiliate Buscar por la La pension entidad del afiliado. Example: SENASIR
   * @queryParam sub_modality_loan Buscar por sub modalidad del préstamo. Example: Corto plazo sector activo
   * @queryParam modality_loan Buscar por Modalidad del prestamo. Example: Préstamo a corto plazo
   * @queryParam amount_approved_loan Buscar monto aprobado del afiliado. Example: 25000
   * @queryParam state_type_affiliate Buscar por tipo de estado del afiliado. Example: Activo
   * @queryParam state_affiliate Buscar por estado del affiliado. Example: Servicio
   * @queryParam quota_loan Buscar por la quota del prestamo. Example: 1500
   * @queryParam state_loan Buscar por el estado del prestamo. Example: En proceso
   * @queryParam guarantor_loan_affiliate Buscar los garantes del préstamo. Example: false
   
   * @queryParam disbursement_date_loan Buscar por fecha de desembolso. Example: 2021
   * @authenticated
   * @responseFile responses/loan_payment/list_loan_payment_generate.200.json
   */

  public function list_loan_payments_generate(Request $request){
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

    $pension_entity_affiliate = request('pension_entity_affiliate') ?? '';
    
    $code_payment = request('code_payment') ?? '';
    $estimated_date_payment = request('estimated_date_payment') ?? '';
    $estimated_quota_payment = request('estimated_quota_payment') ?? '';
    $voucher_payment = request('voucher_payment') ?? '';

    $sub_modality_payment = request('sub_modality_payment') ?? '';
    $sub_modality_shortened_payment = request('sub_modality_shortened_payment') ?? '';
    $modality_payment = request('modality_payment') ?? '';

    $state_payment = request('state_payment') ?? '';
    $name_voucher_type = request('name_voucher_type') ?? '';

    $registration_spouse = request('registration_spouse') ?? '';
    $payment_by = request('payment_by') ?? '';

    $loan_payment_date = request('loan_payment_date') ?? '';
    //$amortization_type_payment = request('amortization_type_payment') ?? '';

      if ($id_loan != '') {//1
        array_push($conditions, array('loans.id', 'ilike', "%{$id_loan}%"));
      }
 
      if ($code_loan != '') {//2
        array_push($conditions, array('loans.code', 'ilike', "%{$code_loan}%"));
      }

      if ($disbursement_date_loan != '') {//3
        array_push($conditions, array('loans.disbursement_date', 'ilike', "%{$disbursement_date_loan}%"));
      }

      if ($state_type_affiliate != '') {//4
        array_push($conditions, array('affiliate_state_types.name', 'ilike', "%{$state_type_affiliate}%"));
      }
      if ($state_affiliate != '') {//5
        array_push($conditions, array('affiliate_states.name', 'ilike', "%{$state_affiliate}%"));
      }
  
      if ($id_affiliate != '') {//6
        array_push($conditions, array('affiliates.id', 'ilike', "%{$id_affiliate}%"));
      }
      if ($identity_card_affiliate != '') {//7
        array_push($conditions, array('affiliates.identity_card', 'ilike', "%{$identity_card_affiliate}%"));
      }
      if ($registration_affiliate != '') {//8
        array_push($conditions, array('affiliates.registration', 'ilike', "%{$registration_affiliate}%"));
      }

  
      if ($last_name_affiliate != '') {//9
        array_push($conditions, array('affiliates.last_name', 'ilike', "%{$last_name_affiliate}%"));
      }
      if ($mothers_last_name_affiliate != '') {//10
        array_push($conditions, array('affiliates.mothers_last_name', 'ilike', "%{$mothers_last_name_affiliate}%"));
      }
 
      if ($first_name_affiliate != '') {//11
        array_push($conditions, array('affiliates.first_name', 'ilike', "%{$first_name_affiliate}%"));//
      }
      if ($second_name_affiliate != '') {//12
        array_push($conditions, array('affiliates.second_name', 'ilike', "%{$second_name_affiliate}%"));
      }
      if ($surname_husband_affiliate != '') {//13
        array_push($conditions, array('affiliates.surname_husband', 'ilike', "%{$surname_husband_affiliate}%"));
      }

      if ($pension_entity_affiliate != '') {//14
        array_push($conditions, array('pension_entities.name', 'ilike', "%{$pension_entity_affiliate}%"));
      }

      if ($code_payment != '') {//14
        array_push($conditions, array('loan_payments.code', 'ilike', "%{$code_payment}%"));
      }

      if ($estimated_date_payment != '') {//14
        array_push($conditions, array('loan_payments.estimated_date', 'ilike', "%{$estimated_date_payment}%"));
      }

      if ($estimated_quota_payment != '') {//14
        array_push($conditions, array('loan_payments.estimated_quota', 'ilike', "%{$estimated_quota_payment}%"));
      }
      if ($voucher_payment != '') {//14
        array_push($conditions, array('loan_payments.voucher', 'ilike', "%{$voucher_payment}%"));
      }

      if ($sub_modality_payment != '') {
        array_push($conditions, array('procedure_modalities.name', 'ilike', "%{$sub_modality_payment}%"));
      }
      if ($sub_modality_shortened_payment != '') {
        array_push($conditions, array('procedure_modalities.shortened', 'ilike', "%{$sub_modality_shortened_payment}%"));
      }
      if ($modality_payment != '') {
        array_push($conditions, array('procedure_types.name', 'ilike', "%{$modality_payment}%"));
      }
      if ($state_payment != '') {
        array_push($conditions, array('loan_states.name', 'ilike', "%{$state_payment}%"));
      }
      //name_voucher_type
      if ($name_voucher_type != '') {
        array_push($conditions, array('voucher_types.name', 'ilike', "%{$name_voucher_type}%"));
      }
      if ($registration_spouse != '') {
        array_push($conditions, array('spouses.registration', 'ilike', "%{$registration_spouse}%"));
      }
      if ($payment_by != '') {
        array_push($conditions, array('loan_payments.paid_by', 'ilike', "%{$payment_by}%"));
      }
      if ($loan_payment_date != '') {
        array_push($conditions, array('loan_payments.loan_payment_date', 'ilike', "%{$loan_payment_date}%"));
      }
      
 
      if($excel==true){
       
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
                'loan_payments.previous_balance as previous_balance',DB::raw("(loan_payments.previous_balance - loan_payments.capital_payment) as current_balance"))
                ->orderBy('loan_payments.code', $order_loan)
                ->get();
      
               $File="ListadoAmortizaciones";
               $data=array(
                   array("Id del préstamo", "Código préstamo", "Fecha desembolso préstamo","estado del afiliado","Tipo de estado del afiliado","ID afiliado", "Nro de carnet", "Matrícula", "Primer apellido","Segundo apellido","Primer nombre","Segundo nombre","Apellido casada",
                   "Entidad de pensión del afiliado","Código pago","fecha de pago","Total pagado","Nro comprobante","Modalidad pago","Modalidad pago nombre","Tipo amortización","Estado del pago","Tipo de voucher","Matrícula esposa",
                   "Pagado por","Capital pagado","Interés corriente pagado","Interés penal pagado","Interés corriente pendiente","Interés penal pendiente","Total pagado","Saldo anterior","Saldo actual","fecha y hora de cobro")
               );
               foreach ($list_loan as $row){
                   array_push($data, array(
                       $row->id_loan,
                       $row->code_loan,
                       $row->disbursement_date_loan,
                       $row->state_type_affiliate,
                       $row->state_affiliate,
                       $row->id_affiliate,
                       $row->identity_card_affiliate,
                       $row->registration_affiliate,
                       $row->last_name_affiliate,
                       $row->mothers_last_name_affiliate,
                       $row->first_name_affiliate,
                       $row->second_name_affiliate,
                       $row->surname_husband_affiliate,
                       $row->pension_entity_affiliate,
                       $row->code_payment,
                       $row->estimated_date_payment,
                       $row->estimated_quota_payment,
                       $row->voucher_payment,
                       $row->sub_modality_payment,
                       $row->sub_modality_shortened_payment,
                       $row->modality_payment,
                       $row->state_payment,
                       $row->name_voucher_type,
                       $row->registration_spouse,$row->payment_by,$row->capital_payment,$row->interest_payment,$row->penal_payment,
                       $row->interest_current_pending,$row->interest_penal_pending,$row->estimated_quota_payment,$row->previous_balance,$row->current_balance,$row->loan_payment_date

                   ));
               }
               $export = new ArchivoPrimarioExport($data);
               return Excel::download($export, $File.'.csv');
      }else{
      $loan_payments='loan_payments';
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
                //->where($conditions,'vouchers.payable_type','=',$loan_payments)
                ->where($conditions)
                ->select('loans.id as id_loan','loans.code as code_loan','loans.disbursement_date as disbursement_date_loan','affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate',
                'affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate','affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
                'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate','pension_entities.name as pension_entity_affiliate','loan_payments.code as code_payment','loan_payments.estimated_date as estimated_date_payment','loan_payments.loan_payment_date as loan_payment_date',
                'loan_payments.voucher as voucher_payment',
                'procedure_modalities.name as sub_modality_payment','procedure_modalities.shortened as sub_modality_shortened_payment','procedure_types.name as modality_payment','loan_payment_states.name as state_payment','voucher_types.name as name_voucher_type','spouses.registration as registration_spouse',
                'loan_payments.paid_by as payment_by','loan_payments.capital_payment as capital_payment','loan_payments.interest_payment as interest_payment','loan_payments.penal_payment as penal_payment','loan_payments.interest_remaining as interest_current_pending','loan_payments.penal_remaining as interest_penal_pending','loan_payments.estimated_quota as estimated_quota_payment',
                'loan_payments.previous_balance as previous_balance',DB::raw("(loan_payments.previous_balance - loan_payments.capital_payment) as current_balance"))
                ->orderBy('loan_payments.code', $order_loan)
                ->paginate($pagination_rows);
           return $list_loan;
      }
   }
}
    
