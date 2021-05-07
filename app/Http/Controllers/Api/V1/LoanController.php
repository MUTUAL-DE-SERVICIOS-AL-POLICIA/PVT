<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Api\V1\CalculatorController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Affiliate;
use App\City;
use App\User;
use App\Loan;
use App\Tag;
use App\LoanState;
use App\RecordType;
use App\ProcedureDocument;
use App\ProcedureModality;
use App\PaymentType;
use App\Role;
use App\RoleSequence;
use App\LoanPayment;
use App\Voucher;
use App\Sismu;
use App\Record;
use App\ProcedureType;
use App\Contribution;
use App\AidContribution;
use App\LoanContributionAdjust;
use App\LoanGlobalParameter;
use App\Http\Requests\LoansForm;
use App\Http\Requests\LoanForm;
use App\Http\Requests\LoanPaymentForm;
use App\Http\Requests\ObservationForm;
use App\Http\Requests\DisbursementForm;
use App\Events\LoanFlowEvent;
use Carbon;
use App\Helpers\Util;
use App\Http\Controllers\Api\V1\LoanPaymentController;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivoPrimarioExport;

/** @group Préstamos
* Datos de los trámites de préstamos y sus relaciones
*/
class LoanController extends Controller
{
    public static function append_data(Loan $loan, $with_lenders = false)
    {
        $loan->indebtedness_calculated = $loan->indebtedness_calculated;
        $loan->liquid_qualification_calculated = $loan->liquid_qualification_calculated;
        $loan->balance = $loan->balance;
        $loan->estimated_quota = $loan->estimated_quota;
        $loan->defaulted = $loan->defaulted;
        $loan->observed = $loan->observed;
        if ($with_lenders) {
            $loan->lenders = $loan->lenders;
            $loan->guarantors = $loan->guarantors;
        }
        $loan->personal_references = $loan->personal_references;
        $loan->cosigners = $loan->cosigners;
        $loan->data_loan = $loan->data_loan;
        //$loan->user = $loan->records_user;
        $loan->city = $loan->city;
        $loan->observations = $loan->observations->last();
        $loan->modality=$loan->modality->procedure_type;
        $loan->tags = $loan->tags;
        if($loan->parent_loan){
            $loan->parent_loan->balance = $loan->parent_loan->balance;
            $loan->parent_loan->estimated_quota = $loan->parent_loan->estimated_quota;
        }
        $loan->intereses=$loan->interest;
        if($loan->parent_reason=='REFINANCIAMIENTO'){
            $loan->balance_parent_loan_refinancing = $loan->balance_parent_refi();
            $loan->date_cut_refinancing=$loan->date_cut_refinancing();
        }else{
            $loan->balance_parent_loan_refinancing = null;
            $loan->date_cut_refinancing=null;
        }
        $loan->payment_type;
        $loan->state;
        //$loan->procedure=$loan->modality;
        //$loan->loan_contribution = $loan->loan_contribution_adjusts;
        return $loan;
    }

    /**
    * Lista de Préstamos
    * Devuelve el listado con los datos paginados
    * @queryParam role_id Ver préstamos del rol, si es 0 se muestra la lista completa. Example: 73
    * @queryParam affiliate_id Ver préstamos del afiliado. Example: 529
    * @queryParam trashed Booleano para obtener solo eliminados. Example: 1
    * @queryParam validated Booleano para filtrar trámites válidados. Example: 1
    * @queryParam procedure_type_id ID para filtrar trámites por tipo de trámite. Example: 9
    * @queryParam search Parámetro de búsqueda. Example: 2000
    * @queryParam sortBy Vector de ordenamiento. Example: []
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [true]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/loan/index.200.json
    */
    public function index(Request $request)
    {
        $filters = [];
        $relations = [];
        if (!$request->has('role_id')) {
            if (Auth::user()->can('show-all-loan')) {
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
            if (($request->role_id == 0 && !Auth::user()->can('show-all-loan')) || ($request->role_id != 0 && !Auth::user()->roles->pluck('id')->contains($request->role_id))) {
                abort(403);
            }
        }
        if ($request->role_id != 0) {
            if(!Auth::user()->can('show-all-loan')){
                if($request->has('trashed') && !Auth::user()->can('show-deleted-loan')) abort(403);
            }
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
        if ($request->has('affiliate_id')) {
            $relations['lenders'] = [
                'affiliate_id' => $request->affiliate_id
            ];
        }
        if ($request->has('user_id')) {
            $filters['user_id'] = $request->user_id;
        }
        else{
            if($request->validated){
                $filters['validated'] = $request->validated;
                $filters['user_id'] = null;
            }
        }
        $data = Util::search_sort(new Loan(), $request, $filters, $relations);
        $data->getCollection()->transform(function ($loan) {
            return self::append_data($loan, true);
        });
        return $data;
    }

    /** Mis Prestamos
    * Devuelve los prestamos que fueron derivados al usuario
    * @queryParam user_id required id del usuario. Example:70
    * @queryParam per_page ver cantidad de prestamos. Example:1
    * @queryParam page Numero de pagina. Example:1
    * @authenticated
    * @responseFile responses/loan/my_loans.200.json
    */
    public function my_loans(Request $request){
        if(!$request->per_page){
            $request->per_page = 0;
        }
        $loans = Loan::whereUser_idAndValidated($request->user_id, false)->paginate($request->per_page);
        $loans->getCollection()->transform(function ($loan) {
            return self::append_data($loan, true);
        });
        return $loans;
    }

    /**
    * Nuevo préstamo
    * Inserta nuevo préstamo
    * @bodyParam procedure_modality_id integer required ID de modalidad. Example: 46
    * @bodyParam amount_requested integer required monto solicitado. Example: 26000
    * @bodyParam city_id integer required ID de la ciudad. Example: 4
    * @bodyParam loan_term integer required plazo. Example: 40
    * @bodyParam refinancing_balance numeric  Monto saldo de refinanciamiento. Example: 1052.26
    * @bodyParam guarantor_amortizing boolean true si es de amortizacion garante. Example: false
    * @bodyParam payment_type_id integer required Tipo de desembolso. Example: 1
    * @bodyParam financial_entity_id integer ID de entidad financiera. Example: 1
    * @bodyParam number_payment_type integer Número de cuenta o Número de cheque para el de desembolso. Example: 10000541214
    * @bodyParam liquid_qualification_calculated numeric required Total de bono calculado. Example: 2000
    * @bodyParam indebtedness_calculated numeric required Indice de endeudamiento. Example: 52.26
    * @bodyParam parent_loan_id integer ID de Préstamo Padre. Example: 1
    * @bodyParam parent_reason enum (REFINANCIAMIENTO, REPROGRAMACIÓN) Tipo de trámite hijo. Example: REFINANCIAMIENTO
    * @bodyParam property_id integer ID de bien inmueble. Example: 4
    * @bodyParam destiny_id integer required ID destino de Préstamo. Example: 2
    * @bodyParam documents array required Lista de IDs de Documentos solicitados. Example: [294,283,296,305,306,307,308,309,310,311,312,313,284,44,274]
    * @bodyParam notes array Lista de notas aclaratorias. Example: [Informe de baja policial, Carta de solicitud]
    * @bodyParam personal_references array Lista de IDs de personas de referencia del préstamo. Example: [1]
    * @bodyParam cosigners array Lista de IDs de codeudores no afiliados a la muserpol. Example: [2,3]
    * @bodyParam user_id integer ID del usuario. Example: 1.
    * @bodyParam remake_loan_id integer ID del prestamo que se esta rehaciendo. Example: 1
    * @bodyParam delivery_contract_date string Fecha de entrega del contrato al afiliado. Example: 2021-04-05
    * @bodyParam return_contract_date string Fecha de devolución del contrato del afiliado. Example: 2021-04-07
    * @bodyParam lenders array required Lista de afiliados Titular(es) del préstamo.
    * @bodyParam lenders[0].affiliate_id integer required ID del afiliado. Example: 47461
    * @bodyParam lenders[0].payment_percentage numeric required porcentage de pago del afiliado. Example: 50.6
    * @bodyParam lenders[0].payable_liquid_calculated numeric required ID del afiliado. Example: 2000
    * @bodyParam lenders[0].bonus_calculated integer required ID del afiliado. Example: 300
    * @bodyParam lenders[0].quota_previous numeric required ID del afiliado. Example: 514.6
    * @bodyParam lenders[0].quota_treat numeric required cuota del afiliado. Example: 514.6
    * @bodyParam lenders[0].indebtedness_calculated numeric required ID del afiliado. Example: 34
    * @bodyParam lenders[0].liquid_qualification_calculated numeric required ID del afiliado. Example: 2000
    * @bodyParam lenders[0].contributionable_ids array required  Ids de las contribuciones asocidas al prestamo por afiliado. Example: [1,2,3]
    * @bodyParam lenders[0].contributionable_type enum required  Nombre de la tabla de contribuciones . Example: contributions
    * @bodyParam lenders[0].loan_contributions_adjust_ids array required Ids de los ajustes de la(s) contribución(s). Example: [1,2]
    * @bodyParam lenders[1].affiliate_id integer required ID del afiliado. Example: 22773
    * @bodyParam lenders[1].payment_percentage numeric required porcentage de pago del afiliado. Example: 50.6
    * @bodyParam lenders[1].payable_liquid_calculated numeric required ID del afiliado. Example: 2000
    * @bodyParam lenders[1].bonus_calculated integer required ID del afiliado. Example: 300
    * @bodyParam lenders[1].quota_previous numeric required ID del afiliado. Example: 514.6
    * @bodyParam lenders[1].quota_treat numeric required cuota del afiliado. Example: 514.6
    * @bodyParam lenders[1].indebtedness_calculated numeric required ID del afiliado. Example: 34
    * @bodyParam lenders[1].liquid_qualification_calculated numeric required ID del afiliado. Example: 2000
    * @bodyParam lenders[1].contributionable_ids array required Ids de las contribuciones asocidas al prestamo por afiliado. Example: [1,2,3]
    * @bodyParam lenders[1].contributionable_type enum required Nombre de la tabla de contribuciones . Example: contributions
    * @bodyParam lenders[1].loan_contributions_adjust_ids array required Ids de los ajustes de la(s) contribución(s). Example: [3]
    * @bodyParam guarantors array Lista de afiliados Garante(es) del préstamo.
    * @bodyParam guarantors[0].affiliate_id integer required ID del afiliado. Example: 51925
    * @bodyParam guarantors[0].payment_percentage numeric required porcentage de pago del afiliado. Example: 50.6
    * @bodyParam guarantors[0].payable_liquid_calculated numeric required ID del afiliado. Example: 2000
    * @bodyParam guarantors[0].bonus_calculated integer required ID del afiliado. Example: 300
    * @bodyParam guarantors[0].indebtedness_calculated numeric required ID del afiliado. Example: 34
    * @bodyParam guarantors[0].quota_treat numeric required cuota del afiliado garante. Example: 514.6
    * @bodyParam guarantors[0].liquid_qualification_calculated numeric required ID del afiliado. Example: 2000
    * @bodyParam guarantors[0].contributionable_ids array required Ids de las contribuciones asocidas al prestamo por afiliado. Example: [1,2,3]
    * @bodyParam guarantors[0].contributionable_type enum required  Nombre de la tabla de contribuciones. Example: contributions
    * @bodyParam guarantors[0].loan_contributions_adjust_ids array required  Ids de los ajustes de la(s) contribución(s). Example: []
    * @bodyParam data_loan array Datos Sismu.
    * @bodyParam data_loan[0].code string required Codigo del prestamo en el Sismu. Example: PRESTAMO123
    * @bodyParam data_loan[0].amount_approved numeric required Monto aprovado del prestamo del Sismu. Example: 5000.50
    * @bodyParam data_loan[0].loan_term integer required Plazo del prestamo del Sismu. Example: 25
    * @bodyParam data_loan[0].balance numeric required saldo del prestamo del Sismu. Example: 10000.50
    * @bodyParam data_loan[0].estimated_quota numeric required cuota del prestamo del Sismu. Example: 1000.50
    * @bodyParam data_loan[0].date_cut_refinancing date Fecha de corte de refinanciamineto Example: 2021-04-07
    * @authenticated
    * @responseFile responses/loan/store.200.json
    */
    public function store(LoanForm $request)
    {
        $roles = Auth::user()->roles()->whereHas('module', function($query) {
            return $query->whereName('prestamos');
        })->pluck('id');
        $procedure_modality = ProcedureModality::findOrFail($request->procedure_modality_id);
        $request->merge([
            'role_id' => $procedure_modality->procedure_type->workflow->pluck('role_id')->intersect($roles)->first()
        ]);        
        if (!$request->role_id) abort(403, 'Debe crear un flujo de trabajo');
        // Guardar préstamo
        $saved = $this->save_loan($request);
        // Relacionar afiliados y garantes
        $loan = $saved->loan;
        $request = $saved->request;
        // Relacionar documentos requeridos y opcionales
        $date = Carbon::now()->toISOString();
        $documents = [];
        foreach ($request->documents as $document_id) {
            if ($loan->submitted_documents()->whereId($document_id)->doesntExist()) {
                $documents[$document_id] = [
                    'reception_date' => $date
                ];
            }
        }
        $loan->submitted_documents()->syncWithoutDetaching($documents);
        // Relacionar notas
        if ($request->has('notes')) {
            foreach ($request->notes as $message) {
                $loan->notes()->create([
                    'message' => $message,
                    'date' => Carbon::now()
                ]);
            }
        }
        //rehacer préstamo
        if($request->has('remake_loan_id')&& $request->remake_loan_id != null){
            $remake_loan = Loan::find($request->remake_loan_id);
            $this->destroyAll($remake_loan);
            $this->happenRecordLoan($remake_loan,$loan->id);
            Util::save_record($loan, 'datos-de-un-tramite', Util::concat_action($loan,'rehízo préstamo: '.$loan->code));
        }

        //Etiqueta Sismu 
        $user = User::whereUsername('admin')->first();
        $sismu_tag = Tag::whereSlug('sismu')->first();
        if(empty($loan->parent_loan_id)){
            if($loan->parent_reason == 'REFINANCIAMIENTO' || $loan->parent_reason == 'REPROGRAMACIÓN'){
                $loan ->tags()->detach($sismu_tag);
                $loan ->tags()->attach([$sismu_tag->id => [
                    'user_id' => $user->id,
                    'date' => Carbon::now()
                ]]);
                Util::save_record($loan, 'datos-de-un-tramite', Util::concat_action($loan,'etiquetado: Préstamo proveniente del Sismu'));
            } 
        }
        // Generar PDFs
        $file_name = implode('_', ['solicitud', 'prestamo', $loan->code]) . '.pdf';       
        if(Auth::user()->can('print-contract-loan')){
            if($loan->modality->loan_modality_parameter->print_contract_platform){
                $loan->attachment = Util::pdf_to_base64([
                    $this->print_form(new Request([]), $loan, false),
                    $this->print_contract(new Request([]), $loan, false)
                ], $file_name, 'legal', $request->copies ?? 1);
            }else{
                $loan->attachment = Util::pdf_to_base64([
                    $this->print_form(new Request([]), $loan, false),
                ], $file_name, 'legal', $request->copies ?? 1);
            }
        }else{
            $loan->attachment = Util::pdf_to_base64([
                $this->print_form(new Request([]), $loan, false),
            ], $file_name, 'legal', $request->copies ?? 1);
        }
        return $loan;
    }

    /**
    * Detalle de Préstamo
    * Devuelve el detalle de un préstamo mediante su ID
    * @urlParam loan required ID de préstamo. Example: 4
    * @authenticated
    * @responseFile responses/loan/show.200.json
    */
    public function show(Loan $loan)
    {
        if (Auth::user()->can('show-all-loan') || Auth::user()->can('show-payment-loan') || Auth::user()->roles()->whereHas('module', function($query) {
            return $query->whereName('prestamos');
        })->pluck('id')->contains($loan->role_id)) {
            return self::append_data($loan, true);
        } else {
            abort(403);
        }
    }

    /**
    * Actualizar préstamo
    * Actualizar datos principales de préstamo
    * @urlParam loan required ID del préstamo. Example: 1
    * @bodyParam date_signal boolean true si no se envia fecha  y false da señal de que se enviara fecha en el campo disbursement_dateExample: true
    * @bodyParam procedure_modality_id integer ID de modalidad. Example: 41
    * @bodyParam amount_requested integer monto solicitado. Example: 2000
    * @bodyParam city_id integer ID de la ciudad. Example: 6
    * @bodyParam loan_term integer plazo. Example: 2
    * @bodyParam refinancing_balance numeric  Monto saldo de refinanciamiento. Example: 1052.26
    * @bodyParam guarantor_amortizing boolean true si es de amortizacion garante. Example: false
    * @bodyParam payment_type_id integer Tipo de desembolso. Example: 1
    * @bodyParam liquid_qualification_calculated numeric Total de bono calculado. Example: 2000
    * @bodyParam indebtedness_calculated numeric Indice de endeudamiento. Example: 52.26
    * @bodyParam disbursement_date date Fecha de desembolso. Example: 2020-02-01
    * @bodyParam num_accounting_voucher string numero de comprobante contable.Example: 107
    * @bodyParam parent_loan_id integer ID de Préstamo Padre. Example: 1
    * @bodyParam parent_reason enum (REFINANCIAMIENTO, REPROGRAMACIÓN) Tipo de trámite hijo. Example: REFINANCIAMIENTO
    * @bodyParam property_id integer ID de bien inmueble. Example: 4
    * @bodyParam financial_entity_id integer ID de entidad financiera. Example: 1
    * @bodyParam number_payment_type integer Número de cuenta o Número de cheque para el de desembolso. Example: 10000541214
    * @bodyParam destiny_id integer ID destino de Préstamo. Example: 1
    * @bodyParam role_id integer Rol al cual derivar o devolver. Example: 81
    * @bodyParam validated boolean Estado validación del préstamo. Example: true
    * @bodyParam personal_references array Lista de personas de referencia del préstamo. Example: [1]
    * @bodyParam cosigners array Lista de codeudores no afiliados a la muserpol. Example: [2,3]
    * @bodyParam user_id integer ID del usuario. Example: 1.
    * @bodyParam remake_loan_id integer ID del prestamo que se esta rehaciendo. Example: 1
    * @bodyParam delivery_contract_date string Fecha de entrega del contrato al afiliado. Example: 2021-04-05
    * @bodyParam return_contract_date string Fecha de devolución del contrato del afiliado. Example: 2021-04-07
    * @bodyParam lenders array Lista de afiliados Titular(es) del préstamo.
    * @bodyParam lenders[0].affiliate_id integer ID del afiliado.Example: 47461
    * @bodyParam lenders[0].payment_percentage numeric porcentage de pago del afiliado. Example: 50.6
    * @bodyParam lenders[0].payable_liquid_calculated numeric ID del afiliado. Example: 2000
    * @bodyParam lenders[0].bonus_calculated integer ID del afiliado. Example: 300
    * @bodyParam lenders[0].quota_previous numeric ID del afiliado. Example: 514.6
    * @bodyParam lenders[0].quota_treat numeric required cuota del afiliado. Example: 514.6
    * @bodyParam lenders[0].indebtedness_calculated numeric ID del afiliado. Example: 34
    * @bodyParam lenders[0].liquid_qualification_calculated numeric ID del afiliado. Example: 2000
    * @bodyParam lenders[0].contributionable_ids array  Ids de las contribuciones asocidas al prestamo por afiliado. Example: [1,2,3]
    * @bodyParam lenders[0].contributionable_type enum Nombre de la tabla de contribuciones . Example: contributions
    * @bodyParam lenders[0].loan_contributions_adjust_ids array Ids de los ajustes de la(s) contribución(s). Example: [1,2]
    * @bodyParam lenders[0].quota_treat cuota del titular. Example: 2315.86
    * @bodyParam lenders[1].affiliate_id integer ID del afiliado. Example: 22773
    * @bodyParam lenders[1].payment_percentage numeric porcentage de pago del afiliado. Example: 50.6
    * @bodyParam lenders[1].payable_liquid_calculated numeric ID del afiliado. Example: 2000
    * @bodyParam lenders[1].bonus_calculated integer ID del afiliado. Example: 300
    * @bodyParam lenders[1].quota_previous numeric ID del afiliado. Example: 514.6
    * @bodyParam lenders[1].quota_treat numeric required cuota del afiliado. Example: 514.6
    * @bodyParam lenders[1].indebtedness_calculated numeric ID del afiliado. Example: 34
    * @bodyParam lenders[1].liquid_qualification_calculated numeric ID del afiliado. Example: 2000
    * @bodyParam lenders[1].contributionable_ids array  Ids de las contribuciones asocidas al prestamo por afiliado. Example: [1,2,3]
    * @bodyParam lenders[1].contributionable_type enum Nombre de la tabla de contribuciones . Example: contributions
    * @bodyParam lenders[1].loan_contributions_adjust_ids array Ids de los ajustes de la(s) contribución(s). Example: [3]
    * @bodyParam lenders[1].quota_treat cuota del titular. Example: 2315.86
    * @bodyParam guarantors array Lista de afiliados Garante(es) del préstamo.
    * @bodyParam guarantors[0].affiliate_id integer ID del afiliado. Example: 51925
    * @bodyParam guarantors[0].payment_percentage numeric porcentage de pago del afiliado. Example: 50.6
    * @bodyParam guarantors[0].payable_liquid_calculated numeric ID del afiliado. Example: 2000
    * @bodyParam guarantors[0].bonus_calculated integer ID del afiliado. Example: 300
    * @bodyParam guarantors[0].quota_treat numeric  cuota del afiliado garante. Example: 514.6
    * @bodyParam guarantors[0].indebtedness_calculated numeric ID del afiliado. Example: 34
    * @bodyParam guarantors[0].liquid_qualification_calculated numeric ID del afiliado. Example: 2000
    * @bodyParam guarantors[0].contributionable_ids array  Ids de las contribuciones asocidas al prestamo por afiliado. Example: [1,2,3]
    * @bodyParam guarantors[0].contributionable_type enum Nombre de la tabla de contribuciones . Example: contributions
    * @bodyParam guarantors[0].loan_contributions_adjust_ids array Ids de los ajustes de la(s) contribución(s). Example: []
    * @bodyParam guarantors[0].quota_treat cuota del garante. Example: 2315.86
    * @authenticated
    * @responseFile responses/loan/update.200.json
    */
    public function update(LoanForm $request, Loan $loan)
    {    $message = [];
         if($request->date_signal == true || ($request->date_signal == false && $request->has('disbursement_date') && $request->disbursement_date != NULL)){
            $state_id = LoanState::whereName('Desembolsado')->first()->id;
            $request['state_id'] = $state_id;
            /*$hour = Carbon::now()->hour;
            $minute = Carbon::now()->minute;
            $second = Carbon::now()->second;
            $date = Carbon::parse($request['disbursement_date']);
            $date->addHours($hour);
            $date->addMinutes($minute);
            $date->addSeconds($second);
            $date = Carbon::parse($date);
            $request['disbursement_date'] = $date;*/
        //si es refinanciamiento o reprogramacion colocar la etiqueta correspondiente al padre del préstamo   
            if($loan->parent_loan_id != null){
                $user = User::whereUsername('admin')->first();
                $refinancing_tag = Tag::whereSlug('refinanciamiento')->first();
                $reprogramming_tag = Tag::whereSlug('reprogramacion')->first();
                $parent_loan  = Loan::find($loan->parent_loan_id);
                if($loan->parent_reason == 'REFINANCIAMIENTO'){
                        $parent_loan ->tags()->detach($refinancing_tag);
                        $parent_loan ->tags()->attach([$refinancing_tag->id => [
                            'user_id' => $user->id,
                            'date' => Carbon::now()
                        ]]);
                    Util::save_record($parent_loan, 'datos-de-un-tramite', Util::concat_action($parent_loan,'etiquetado: Préstamo refinanciado'));
                } 
                if($loan->parent_reason == 'REPROGRAMACIÓN'){
                        $parent_loan ->tags()->detach($reprogramming_tag);
                        $parent_loan ->tags()->attach([$reprogramming_tag->id => [
                            'user_id' => $user->id,
                            'date' => Carbon::now()
                        ]]);
                    Util::save_record($parent_loan, 'datos-de-un-tramite', Util::concat_action($parent_loan,'etiquetado: Préstamo reprogramado'));
                }
            }
        }
    if(Auth::user()->can('disbursement-loan')) {
        if($request->date_signal == true){
            $loan['disbursement_date'] = Carbon::now();
            $state_id = LoanState::whereName('Desembolsado')->first()->id;
            $loan['state_id'] = $state_id;
            $loan->save();
        }else{
            if($request->date_signal == false){
                if($request->has('disbursement_date') && $request->disbursement_date != NULL){
                    if(Auth::user()->can('change-disbursement-date')) {
                    $loan['disbursement_date'] = $request->disbursement_date;
                    $state_id = LoanState::whereName('Desembolsado')->first()->id;
                    $loan['state_id'] = $state_id;
                    $loan->save();
                    }  else return $message['validate'] = "El usuario no tiene los permisos necesarios para realizar el registro" ;
                } 
            }    
       } 
    }
        $saved = $this->save_loan($request, $loan);
        return $saved->loan;   
    }

    /**
    * Anular préstamo
    * @urlParam loan required ID del préstamo. Example: 1
    * @authenticated
    * @responseFile responses/loan/destroy.200.json
    */
    public function destroy(Loan $loan)
    {
        $state = LoanState::whereName('Anulado')->first();
        $loan->state()->associate($state);
        $loan->save();
        $loan->delete();
        if($loan->data_loan)
        $loan->data_loan->delete();
        return $loan;
    }

    private function save_loan(Request $request, $loan = null)
    {
        /** Verificando información de los titulares y garantes */
        if($request->lenders && $request->guarantors){
            $lenders_guarantors = array_merge($request->lenders, $request->guarantors);
            foreach ($lenders_guarantors as $lender_guarantor) {
                $information_affiliate = Affiliate::findOrFail($lender_guarantor['affiliate_id']);
                $is_valid_information = Affiliate::verify_information($information_affiliate);
                if(!$is_valid_information) abort(409, 'Debe actualizar los datos personales de titulares y garantes');
            }
        }
        /** fin validacion */
        if (Auth::user()->can(['update-loan', 'create-loan']) && ($request->has('lenders') || $request->has('guarantors'))) {
            $request->lenders = collect($request->has('lenders') ? $request->lenders : [])->unique();
            $request->guarantors = collect($request->has('guarantors') ? $request->guarantors : [])->unique();
            $a = 0;
            foreach ($request->lenders as $lender) {
                $affiliates[$a] = $lender['affiliate_id'];
                $a++;
            }
            if (!$request->has('disbursable_id')) {
                $disbursable_id = $request->lenders[0]['affiliate_id'];
            } else {
                if (!in_array($request->disbursable_id, $affiliates)) abort(404);
                $disbursable_id = $request->disbursable_id;
            }
            $disbursable = Affiliate::findOrFail($disbursable_id);
        }
        if ($loan) {
            $exceptions = ['code', 'role_id'];
            if ($request->has('validated')) {
                if (!Auth::user()->roles()->pluck('id')->contains($loan->role_id)) {
                    array_push($exceptions, 'validated');
                }
            }
            if (Auth::user()->can('update-loan')) {
                $loan->fill(array_merge($request->except($exceptions), isset($disbursable) ? (array)self::verify_spouse_disbursable($disbursable) : []));
            }
            if (in_array('validated', $exceptions)) $loan->validated = $request->validated;
            if ($request->has('role_id')) {
                if ($request->role_id != $loan->role_id) {
                    $loan->role()->associate(Role::find($request->role_id));
                    $loan->validated = false;
                    event(new LoanFlowEvent([$loan]));
                }
            }
        } else {
            $loan = new Loan(array_merge($request->all(), (array)self::verify_spouse_disbursable($disbursable), ['amount_approved' => $request->amount_requested]));
        }

        //heredar el codigo del prestamo padre
        /*if($loan->parent_loan_id)
        {
            if(substr($loan->parent_loan->code, -3) != substr($loan->parent_reason,0,3))
                $loan->code = Loan::find($loan->parent_loan_id)->code." - ".substr($loan->parent_reason,0,3);
            else
                $loan->code = $loan->parent_loan->code;
        }*/

        //rehacer obtener cod 
        if($request->has('remake_loan_id')&& $request->remake_loan_id != null)
        {
            $remake_loan = Loan::find($request->remake_loan_id);
            $loan->code = $remake_loan->code;
        }

        $loan->save();

        if($request->has('data_loan') && $request->parent_loan_id == null && $request->parent_reason != null && !$request->has('id')){
            $data_loan = $request->data_loan[0];
            $loan->data_loan()->create($data_loan);
        }
        if($request->loan!=null && $request->has('data_loan')){
            $data_loan = $request->data_loan[0];
            $loan->data_loan()->update($data_loan);
        }

        if (Auth::user()->can(['update-loan', 'create-loan']) && ($request->has('lenders') || $request->has('guarantors'))) {
            $affiliates = []; $a = 0; $previous = 0; $indebtedness = 0;
            foreach ($request->lenders as $affiliate) {
                if($request->parent_loan_id)
                {
                    $quota_previous = $affiliate['quota_previous'];
                }else{
                    $quota_previous = $previous;
                }
                if (array_key_exists('indebtedness_calculated', $affiliate)) {
                    $indebtedness = $affiliate['indebtedness_calculated'];
                }else{
                    $indebtedness = 0;
                }
                $affiliates[$a] = [
                    'affiliate_id' => $affiliate['affiliate_id'],
                    'payment_percentage' => $affiliate['payment_percentage'],
                    'payable_liquid_calculated' => $affiliate['payable_liquid_calculated'],
                    'bonus_calculated' => $affiliate['bonus_calculated'],
                    'quota_previous' => $quota_previous,
                    'quota_treat' => $affiliate['quota_treat'],
                    'indebtedness_calculated' => $indebtedness,
                    'liquid_qualification_calculated' => $affiliate['liquid_qualification_calculated'],
                    'guarantor' => false,
                    'contributionable_type' => $affiliate['contributionable_type'],
                    'contributionable_ids' => json_encode($affiliate['contributionable_ids']),
                ];
                if(array_key_exists('loan_contributions_adjust_ids', $affiliate)){
                    $idsajust=$affiliate['loan_contributions_adjust_ids'];
                }else{
                    $idsajust=[];
                }
                foreach ($idsajust as $adjustid){
                    $ajuste=LoanContributionAdjust::find($adjustid);
                    $ajuste->loan_id=$loan->id;
                    $ajuste->update();
                }
                $a++;
            }           
            if($request->guarantors){
                foreach ($request->guarantors as $affiliate) {
                    $affiliates[$a] = [
                        'affiliate_id' => $affiliate['affiliate_id'],
                        'payment_percentage' => $affiliate['payment_percentage'],
                        'payable_liquid_calculated' => $affiliate['payable_liquid_calculated'],
                        'bonus_calculated' => $affiliate['bonus_calculated'],
                        'quota_previous' => $previous,
                        'quota_treat' => $affiliate['quota_treat'],
                        'indebtedness_calculated' => $affiliate['indebtedness_calculated'],
                        'liquid_qualification_calculated' => $affiliate['liquid_qualification_calculated'],
                        'guarantor' => true,
                        'contributionable_type'=>$affiliate['contributionable_type'],
                        'contributionable_ids'=>json_encode($affiliate['contributionable_ids']),
                    ];
                    if(array_key_exists('loan_contributions_adjust_ids', $affiliate)){
                        $idsajust=$affiliate['loan_contributions_adjust_ids'];
                    }else{
                        $idsajust=[];
                    }
                    foreach ($idsajust as $adjustid){
                        $ajuste=LoanContributionAdjust::find($adjustid);
                        $ajuste->loan_id=$loan->id;
                        $ajuste->update();
                    }
                    
                    $a++;
                }
            }
            if (count($affiliates) > 0) $loan->loan_affiliates()->sync($affiliates);
        }
        if (Auth::user()->can(['update-loan', 'create-loan']) && ($request->has('personal_references') || $request->has('cosigners'))) {
            $persons = [];
            if($request->personal_references){
                foreach ($request->personal_references as $personal_reference) {
                    $persons[$personal_reference] = [
                        'cosigner' => false
                    ];
                }
            }
            if($request->cosigners){
                foreach ($request->cosigners as $cosigner) {
                    $persons[$cosigner] = [
                        'cosigner' => true
                    ];
                }
            }
            if (count($persons) > 0) $loan->loan_persons()->sync($persons);
        }
        return (object)[
            'request' => $request,
            'loan' => $loan
        ];
    }

    /**
    * Actualización de documentos
    * Actualiza los datos para cada documento presentado
    * @urlParam loan required ID del préstamo. Example: 8
    * @urlParam document required ID de préstamo. Example: 40
    * @bodyParam is_valid boolean required Validez del documento. Example: true
    * @bodyParam comment string Comentario para añadir a la presentación. Example: Documento actualizado a la gestión actual
    * @authenticated
    * @responseFile responses/loan/update_document.200.json
    */
    public function update_document(Request $request, Loan $loan, ProcedureDocument $document)
    {
        $request->validate([
            'is_valid' => 'required|boolean',
            'comment' => 'string|nullable|min:1'
        ]);
        $loan->submitted_documents()->updateExistingPivot($document->id, $request->all());
        return $loan->submitted_documents;
    }

    /**
    * Lista de documentos entregados
    * Obtiene la lista de los documentos presentados para el trámite
    * @urlParam loan required ID del préstamo. Example: 8
    * @authenticated
    * @responseFile responses/loan/get_documents.200.json
    */
    public function get_documents(Loan $loan)
    {
        return $loan->submitted_documents_list;
    }

    /**
    * Actualización de sismu
    * Actualiza los datos del sismu
    * @urlParam loan required ID del préstamo. Example: 3
    * @bodyParam data_loan array Datos Sismu.
    * @bodyParam data_loan[0].code string  Codigo del prestamo en el Sismu. Example: PRESTAMO123
    * @bodyParam data_loan[0].amount_approved numeric Monto aprovado del prestamo del Sismu. Example: 5000.50
    * @bodyParam data_loan[0].loan_term integer Plazo del prestamo del Sismu. Example: 25
    * @bodyParam data_loan[0].balance numeric saldo del prestamo del Sismu. Example: 10000.50
    * @bodyParam data_loan[0].estimated_quota numeric cuota del prestamo del Sismu. Example: 1000.50
    * @bodyParam data_loan[0].date_cut_refinancing date Fecha de corte de refinanciamineto Example: 2021-04-07
    * @authenticated
    * @responseFile responses/loan/update_sismu.200.json
    */
    public function update_sismu(Request $request, Loan $loan)
    {
        if($request->has('data_loan')){
            $data_loan = $request->data_loan[0];
            $loan->data_loan()->update($data_loan);
        }
        return $loan->data_loan;
    }

    /**
    * Desembolso Afiliado
    * Devuelve los datos del o la cónyugue en caso de que hubiera fallecido a quien se hace el desembolso del préstamo
    * @urlParam loan required ID del préstamo. Example: 2
    * @authenticated
    * @responseFile responses/loan/get_disbursable.200.json
    */
    public function get_disbursable(Loan $loan)
    {
        return $loan->disbursable;
    }

    public static function verify_spouse_disbursable(Affiliate $affiliate)
    {
        $object = (object)[
            'disbursable_type' => 'affiliates',
            'disbursable_id' => $affiliate->id,
            'disbursable' => $affiliate
        ];
        if ($object->disbursable->dead) {
            $spouse = $object->disbursable->spouse;
            if ($spouse) {
                $object = (object)[
                    'disbursable_type' => 'spouses',
                    'disbursable_id' => $spouse->id,
                    'disbursable' => $spouse
                ];
            } else {
                abort(409, 'Debe actualizar la información de cónyugue para afiliados fallecidos');
            }
        }
        $needed_keys = ['city_birth', 'city_identity_card', 'city_identity_card', 'address'];
        foreach ($needed_keys as $key) {
            if (!$object->disbursable[$key]) abort(409, 'Debe actualizar los datos personales del titular y garantes');
        }
        return $object;
    }

    public function switch_states()
    {
        $user = User::whereUsername('admin')->first();
        $amortizing_tag = Tag::whereSlug('amortizando')->first();
        $defaulted_tag = Tag::whereSlug('mora')->first();
        $defaulted_loans = 0;
        $amortizing_loans = 0;

        // Switch amortizing loans to defaulted
        $loans = Loan::whereHas('state', function($query) {
            $query->whereName('Desembolsado');
        })->whereHas('tags', function($q) {
            $q->whereSlug('amortizando');
        })->get();
        foreach ($loans as $loan) {
            if ($loan->defaulted) {
                $loan->tags()->detach($amortizing_tag);
                $loan->tags()->attach([$defaulted_tag->id => [
                    'user_id' => $user->id,
                    'date' => Carbon::now()
                ]]);
                $defaulted_loans++;
                foreach ($loan->lenders as $lender) {
                    $lender->records()->create([
                        'user_id' => $user->id,
                        'record_type_id' => RecordType::whereName('etiquetas')->first()->id,
                        'action' => 'etiquetó en mora'
                    ]);
                }
            }
        }

        // Switch defaulted loans to amortizing
        $loans = Loan::whereHas('state', function($query) {
            $query->whereName('Desembolsado');
        })->whereHas('tags', function($q) {
            $q->whereSlug('mora');
        })->get();
        foreach ($loans as $loan) {
            if (!$loan->defaulted) {
                $loan->tags()->detach($defaulted_tag);
                $loan->tags()->attach([$amortizing_tag->id => [
                    'user_id' => $user->id,
                    'date' => Carbon::now()
                ]]);
                $amortizing_loans++;
            }
        }

        return response()->json([
            'defaulted' => $defaulted_loans,
            'amortizing' => $amortizing_loans
        ]);
    }

    /**
    * Impresión de Contrato
    * Devuelve un pdf del contrato acorde a un ID de préstamo
    * @urlParam loan required ID del préstamo. Example: 6
    * @queryParam copies Número de copias del documento. Example: 2
    * @authenticated
    * @responseFile responses/loan/print_contract.200.json
    */
    public function print_contract(Request $request, Loan $loan, $standalone = true)
    {
        $procedure_modality = $loan->modality;
        $parent_loan = "";
        if($loan->parent_loan_id) $parent_loan = Loan::findOrFail($loan->parent_loan_id);
        $lenders = [];
        foreach ($loan->lenders as $lender) {
            $lenders[] = self::verify_spouse_disbursable($lender);
        }
        $guarantors = [];
        foreach ($loan->guarantors as $guarantor) {
            $guarantors[] = $guarantor;
        }
        $employees = [
            ['position' => 'Director General Ejecutivo'],
            ['position' => 'Director de Asuntos Administrativos']
        ];
        foreach ($employees as $key => $employee) {
            $employees[$key] = Util::request_rrhh_employee($employee['position']);
        }
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => []
            ],
            'employees' => $employees,
            'title' => $procedure_modality->name,
            'loan' => $loan,
            'lenders' => collect($lenders),
            'guarantors' => collect($guarantors),
            'parent_loan' => $parent_loan
        ];
        $file_name = implode('_', ['contrato', $procedure_modality->shortened, $loan->code]) . '.pdf';
        $modality_type = $procedure_modality->procedure_type->name;
        switch($modality_type){
            case 'Préstamo Anticipo':
				$view_type = 'advance';
            	break;
            case 'Préstamo a Corto Plazo':
				$view_type = 'short';
            	break;
            case 'Refinanciamiento Préstamo a Corto Plazo':
				$view_type = 'short';
            	break;
            case 'Préstamo a Largo Plazo':
				$view_type = 'long';
            	break;
            case 'Refinanciamiento Préstamo a Largo Plazo':
				$view_type = 'long';
            	break;
            case 'Préstamo Hipotecario':
				$view_type = 'hypothecary';
            	break;
            case 'Refinanciamiento Préstamo Hipotecario':
				$view_type = 'hypothecary';
            	break;
        }
        $information_loan= $this->get_information_loan($loan);
        if($loan->parent_loan_id != null && $loan->parent_reason == "REPROGRAMACIÓN" || $loan->parent_loan_id ==null && $loan->parent_reason == "REPROGRAMACIÓN")
        $view_type = 'reprogramming';
		$view = view()->make('loan.contracts.' . $view_type)->with($data)->render();
        if ($standalone) return Util::pdf_to_base64contract([$view], $file_name,$information_loan,'legal', $request->copies ?? 1);
        return $view;
    }
    
    public function get_information_loan(Loan $loan)
    {
        $lend='';
        foreach ($loan->lenders as $lender) {
            $lenders[] = self::verify_spouse_disbursable($lender);
        }
        foreach ($loan->lenders as $lender) {
            $lend=$lend.'*'.' ' . $lender->first_name .' '. $lender->second_name .' '. $lender->last_name.' '. $lender->mothers_last_name;
        }
        
        $loan_affiliates= $loan->loan_affiliates[0]->first_name;
        $file_name =implode(' ', ['Información:',$loan->code,$loan->modality->name,$lend]); 
    
        return $file_name;
    }

    /**
    * Impresión de Formulario de solicitud
    * Devuelve el pdf del Formulario de solicitud acorde a un ID de préstamo
    * @urlParam loan required ID del préstamo. Example: 1
    * @queryParam copies Número de copias del documento. Example: 2
    * @authenticated
    * @responseFile responses/loan/print_form.200.json
    */
    public function print_form(Request $request, Loan $loan, $standalone = true)
    {
        $lenders = [];
        $is_dead = false;
        foreach ($loan->lenders as $lender) {
            array_push($lenders, self::verify_spouse_disbursable($lender)->disbursable);
            if($lender->dead) $is_dead = true;
        }
        $persons = collect([]);
        $loans = collect([]);
        foreach ($lenders as $lender) {
            //balance de otros prestamos
            if(!$lender->affiliate_id){
                foreach($lender->current_loans as $current_loans){
                    $loans->push([
                        'code' => $current_loans->code,
                        'balance' => $current_loans->balance,
                        'origin' => "PVT",
                    ]);
                }
        }
        else{
            foreach($lender->current_loans() as $current_loans){
                $loans->push([
                    'code' => $current_loans->code,
                    'balance' => $current_loans->balance,
                    'origin' => "PVT",
                ]);
            }
        }
            $loans_sismu = $this->get_balance_sismu($lender->identity_card);
            foreach($loans_sismu as $sismu){
                $loans->push([
                    'code' => $sismu->PresNumero,
                    'balance' => $sismu->PresSaldoAct,
                    'origin' => "SISMU"
                ]);
            }
            //
            $persons->push([
                'id' => $lender->id,
                'full_name' => implode(' ', [$lender->title, $lender->full_name]),
                'identity_card' => $lender->identity_card_ext,
                'position' => 'SOLICITANTE',
            ]);
            $lender->loans_balance = $loans;
        }
        foreach ($loan->guarantors as $guarantor) {
            $persons->push([
                'id' => $lender->id,
                'full_name' => implode(' ', [$guarantor->title, $guarantor->full_name]),
                'identity_card' => $guarantor->identity_card_ext,
                'position' => 'GARANTE'
            ]);
        }
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => [
                    ['Tipo', $loan->modality->procedure_type->second_name],
                    ['Modalidad', $loan->modality->shortened],
                    ['Usuario', Auth::user()->username]
                ]
            ],
            'title' => 'SOLICITUD DE ' . ($loan->parent_loan  ? $loan->parent_reason : 'PRÉSTAMO'),
            'loan' => $loan,
            'lenders' => collect($lenders),
            'signers' => $persons,
            'is_dead'=> $is_dead
        ];
        $information_loan= $this->get_information_loan($loan);
        $file_name = implode('_', ['solicitud', 'prestamo', $loan->code]) . '.pdf';
        $view = view()->make('loan.forms.request_form')->with($data)->render();
        if ($standalone) return Util::pdf_to_base64([$view], $file_name,$information_loan, 'legal', $request->copies ?? 1);
        return $view;
    }

    /**
    * Impresión del plan de pagos
    * Devuelve un pdf del plan de pagos acorde a un ID de préstamo
    * @urlParam loan required ID del préstamo. Example: 6
    * @queryParam copies Número de copias del documento. Example: 2
    * @authenticated
    * @responseFile responses/loan/print_plan.200.json
    */
    public function print_plan(Request $request, Loan $loan, $standalone = true)
    {
        if($loan->disbursement_date){
            $procedure_modality = $loan->modality;
            $lenders = [];
            $is_dead = false;
            foreach ($loan->lenders as $lender) {
                array_push($lenders, self::verify_spouse_disbursable($lender)->disbursable);
                //$lenders[] = self::verify_spouse_disbursable($lender)->disbursable;
                if($lender->dead) $is_dead = true;
            }
            $data = [
                'header' => [
                    'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                    'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                    'table' => [
                        ['Tipo', $loan->modality->procedure_type->second_name],
                        ['Modalidad', $loan->modality->shortened],
                        ['Fecha', Carbon::now()->format('d/m/Y')],
                        ['Hora', Carbon::now()->format('h:m:s a')],
                        ['Usuario', Auth::user()->username],
                    ]
                ],
                'title' => 'PLAN DE PAGOS',
                'loan' => $loan,
                'lenders' => collect($lenders),
                'is_dead'=> $is_dead
            ];
            $information_loan= $this->get_information_loan($loan);
            $file_name = implode('_', ['plan', $procedure_modality->shortened, $loan->code]) . '.pdf';
            $view = view()->make('loan.payments.payment_plan')->with($data)->render();
            if ($standalone) return Util::pdf_to_base64([$view], $file_name, $information_loan, 'legal', $request->copies ?? 1);
            return $view;
        }else{
            return "Prestamo no desembolsado";
        }
    }

    /**
    * Impresión formulario de Calificación
    * Devuelve el pdf del Formulario de Calificación acorde a un ID de préstamo
    * Devuelve datos relacionadas con el préstamo
    * @urlParam loan required ID del préstamo Example: 1
    * @queryParam copies Número de copias del documento. Example: 2
    * @authenticated
    * @responseFile responses/loan/print_qualification.200.json
    */
  
    public function print_qualification(Request $request, Loan $loan, $standalone = true){
        $procedure_modality = $loan->modality;
        $parent_reason=$loan->parent_reason;
        $parent_loan_id=$loan->parent_loan_id;
        $estimated=LoanPayment::where('loan_id',$parent_loan_id)->get();
        $estimated=$estimated->last(); 
        $loan_type_title=" ";    
    if($parent_loan_id == null && !$parent_reason == null){
        $loan_type_title = $loan->parent_reason== "REFINANCIAMIENTO" ? "SISMU"." ".$loan->parent_reason:"SISMU REFINANCIAMIENTO";
    }
    if(!$parent_loan_id == null && !$parent_reason == null){
        $loan_type_title = $loan->parent_reason== "REFINANCIAMIENTO" ? "REFINANCIAMIENTO":"REPROGRAMACIÓN";
    }
        $lenders = [];     
        foreach ($loan->lenders as $lender) {
            $lenders[] = self::verify_spouse_disbursable($lender);         
        }
        $data = [
           'header' => [
               'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
               'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
               'table' => [
                   ['Tipo', $loan->modality->procedure_type->second_name],
                   ['Modalidad', $loan->modality->shortened],
                   ['Usuario', Auth::user()->username]
               ]
           ],
           'loan' => $loan,
           'lenders' => collect($lenders), 
           'Loan_type_title'=>$loan_type_title, 
           'estimated'=>$estimated
       ];
       $information_loan= $this->get_information_loan($loan);
       $file_name =implode('_', ['calificación', $procedure_modality->shortened, $loan->code]) . '.pdf'; 
       $view = view()->make('loan.forms.qualification_form')->with($data)->render();
       if ($standalone) return  Util::pdf_to_base64([$view], $file_name, $information_loan, 'legal', $request->copies ?? 1);  
       return $view; 
   }

    /**
    * Lista de Notas aclaratorias
    * Devuelve la lista de notas relacionadas con el préstamo
    * @urlParam loan required ID del préstamo. Example: 2
    * @authenticated
    * @responseFile responses/loan/get_notes.200.json
    */
    public function get_notes(Loan $loan)
    {
        return $loan->notes;
    }

    /**
    * Flujo de trabajo
    * Devuelve la lista de roles anteriores para devolver o posteriores para derivar el trámite
    * @urlParam loan required ID del préstamo. Example: 2
    * @authenticated
    * @responseFile responses/loan/get_flow.200.json
    */
    public function get_flow(Loan $loan)
    {
        $records = $loan->records;
        $previous_user = [];
        $user = '';
        $record = response()->json(RoleSequence::flow($loan->modality->procedure_type->id, $loan->role_id));
        $previous = $record->getData()->previous;
        $next = $record->getData()->next;
        foreach($previous as $prev){
            $user = Record::whereRole_id($prev)->whereRecord_type_id(3)->whereRecordable_id($loan->id)->first();
            if($user)
                array_push($previous_user, $user->user_id);
            else
                array_push($previous_user, '');
        }
        //return $previous_user;
        $data = [
            "current" => $loan->role_id,
            "previous" => $previous,
            "previous_user" => $previous_user,
            "next" => $next,
            "next_user" => $next // por implementar si se solicita
        ];
        return $data;
        //return response()->json(RoleSequence::flow($loan->modality->procedure_type->id, $loan->role_id));
    }

    /** @group Cobranzas
    * Cálculo de siguiente pago
    * Devuelve el número de cuota, días calculados, días de interés que alcanza a pagar con la cuota, días restantes por pagar, montos de interés, capital y saldo a capital.
    * @urlParam loan required ID del préstamo. Example: 41426
    * @bodyParam affiliate_id integer required id del afiliado. Example: 2020-04-15
    * @bodyParam estimated_date date required Fecha para el cálculo del interés. Example: 2020-04-15
    * @bodyParam paid_by enum required Pago realizado por Titular(T) o Garante(G). Example: T
    * @bodyParam procedure_modality integer required id de la modalidad. Example: 54
    * @bodyParam estimated_quota float Monto para el cálculo. Example: 650
    * @bodyParam adjust refinanciamiento con antecedente(1) o sin antecedente(0). Example: 1
    * @authenticated
    * @responseFile responses/loan/get_next_payment.200.json}
    */
    public function get_next_payment(LoanPaymentForm $request, Loan $loan)
    {
        return $loan->next_payment2($request->input('affiliate_id'),$request->input('estimated_date', null), $request->input('paid_by'), $request->input('procedure_modality_id'), $request->input('estimated_quota', null), $request->input('adjust', false));
    }

    /** @group Cobranzas
    * Nuevo Registro de pago
    * Inserta una cuota de acuerdo a un monto y fecha estimados.
    * @urlParam loan required ID del préstamo. Example: 2
	* @bodyParam estimated_date date Fecha para el cálculo del interés. Example: 2020-04-30
	* @bodyParam estimated_quota float Monto para el cálculo de los días de interés pagados. Example: 600
    * @bodyParam description string Texto de descripción. Example: Penalizacion regularizada
    * @bodyParam voucher string Comprobante de pago GAR-ABV o D-10/20 o CONT-123. Example: CONT-123
    * @bodyParam amortization_type_id integer required ID del tipo de pago. Example: 1
    * @bodyParam affiliate_id integer required ID del afiliado. Example: 57950
    * @bodyParam paid_by enum required Pago realizado por Titular(T) o Garante(G). Example: T
    * @bodyParam procedure_modality_id integer required ID de la modalidad de amortización. Example: 53
    * @bodyParam user_id integer required ID del usuario. Example: 95
    * @bodyParam state boolean refinanciamiento con antecedente(1) o sin antecedente(0). Example: 1
    * @authenticated
    * @responseFile responses/loan/set_payment.200.json
    */
    public function set_payment(LoanPaymentForm $request, Loan $loan)
    {
        if($loan->balance!=0){
            $payment = $loan->next_payment2($request->input('affiliate_id'), $request->input('estimated_date', null), $request->input('paid_by'), $request->input('procedure_modality_id'), $request->input('estimated_quota', null), $request->input('adjust'));
            $payment->description = $request->input('description', null);
            if($request->state)
                $payment->state_id = LoanState::whereName('Pendiente por confirmar')->first()->id;
            else
                $payment->state_id = LoanState::whereName('Pendiente de Pago')->first()->id;
            $payment->role_id = Role::whereName('PRE-cobranzas')->first()->id;
            if($request->has('procedure_modality_id')){
                $modality = ProcedureModality::findOrFail($request->procedure_modality_id)->procedure_type;
                if($modality->name == "Amortización Directa") $payment->validated = true;
            }
            $payment->procedure_modality_id = $request->input('procedure_modality_id');
            $payment->voucher = $request->input('voucher', null);
            $payment->amortization_type_id = $request->input('amortization_type_id');
            $payment->affiliate_id = $request->input('affiliate_id');
            $payment->paid_by = $request->input('paid_by');
            if($request->has('user_id')){
                $payment->user_id = $request->user_id;
            }else{
                $payment->user_id = auth()->id();
            }
            //$payment->user_id = $request->user_id;
            //$payment->validated = true;
            $loan_payment = $loan->payments()->create($payment->toArray());
            //generar PDF
            $information_loan= $this->get_information_loan($loan);
            $file_name = implode('_', ['pagos', $loan->modality->shortened, $loan->code]) . '.pdf';
            $loanpayment = new LoanPaymentController;
            $payment->attachment = Util::pdf_to_base64([
                $loanpayment->print_loan_payment(new Request([]), $loan_payment, false)
            ], $file_name,$information_loan, 'legal', $request->copies ?? 1);
            return $payment;
        }else{
            abort(403, 'El préstamo ya fue liquidado');
        }
    }

    /** @group Cobranzas
    * Lista de pagos
    * Devuelve el listado de los pagos ordenados por cuota de manera descendente
    * @urlParam loan required ID del préstamo. Example: 2
    * @queryParam trashed Booleano para obtener solo pagos eliminadas. Example: 1
    * @authenticated
    * @responseFile responses/loan/get_payments.200.json
    */
    public function get_payments(Request $request, Loan $loan)
    {
        $query = $loan->payments();
        if ($request->boolean('trashed')) $query = $query->onlyTrashed();
        return $query->get();
    }

    /** @group Observaciones de Préstamos
    * Lista de observaciones
    * Devuelve el listado de observaciones del trámite
    * @urlParam loan required ID del préstamo. Example: 2
    * @queryParam trashed Booleano para obtener solo observaciones eliminadas. Example: 1
    * @authenticated
    * @responseFile responses/loan/get_observations.200.json
    */
    public function get_observations(Request $request, Loan $loan)
    {
        $query = $loan->observations();
        if ($request->boolean('trashed')) $query = $query->onlyTrashed();
        return $query->get();
    }

    /** @group Observaciones de Préstamos
    * Nueva observación
    * Inserta una nueva observación asociada al trámite
    * @urlParam loan required ID del préstamo. Example: 2
    * @bodyParam observation_type_id integer required ID de tipo de observación. Example: 2
    * @bodyParam message string required Mensaje adjunto a la observación. Example: Subsanable en una semana
    * @authenticated
    * @responseFile responses/loan/set_observation.200.json
    */
    public function set_observation(ObservationForm $request, Loan $loan)
    {
        $observation = $loan->observations()->make([
            'message' => $request->message ?? null,
            'observation_type_id' => $request->observation_type_id,
            'date' => Carbon::now()
        ]);
        $observation->user()->associate(Auth::user());
        $observation->save();
        return $observation;
    }

    /** @group Observaciones de Préstamos
    * Actualizar observación
    * Actualiza los datos de una observación asociada al trámite
    * @urlParam loan required ID del préstamo. Example: 2
    * @bodyParam original.user_id integer required ID de usuario que creó la observación. Example: 123
    * @bodyParam original.observation_type_id integer required ID de tipo de observación original. Example: 2
    * @bodyParam original.message string required Mensaje de la observación original. Example: Subsanable en una semana
    * @bodyParam original.date date required Fecha de la observación original. Example: 2020-04-14 21:16:52
    * @bodyParam original.enabled boolean required Estado de la observación original. Example: false
    * @bodyParam update.enabled boolean Estado de la observación a actualizar. Example: true
    * @authenticated
    * @responseFile responses/loan/update_observation.200.json
    */
    public function update_observation(ObservationForm $request, Loan $loan)
    {
        $observation = $loan->observations();
        foreach (collect($request->original)->only('user_id', 'observation_type_id', 'message', 'date', 'enabled')->put('observable_id', $loan->id)->put('observable_type', 'loans') as $key => $value) {
            $observation = $observation->where($key, $value);
        }
        if ($observation->count() === 1) {
            $obs = $observation->first();
            if (isset($request->update['enabled'])) {
                if ($request->update['enabled']) {
                    $message = 'subsanó observación: ';
                } else {
                    $message = 'observó: ';
                }
            } else {
                $message = 'modificó observación: ';
            }
            Util::save_record($obs, 'observaciones', $message . $obs->message, $obs->observable);
            $observation->update(collect($request->update)->only('observation_type_id', 'message', 'enabled')->toArray());
        }
        return $loan->observations;
    }

    /** @group Observaciones de Préstamos
    * Eliminar observación
    * Elimina una observación del trámite siempre y cuando no haya sido modificada
    * @urlParam loan required ID del préstamo. Example: 2
    * @bodyParam user_id integer required ID de usuario que creó la observación. Example: 123
    * @bodyParam observation_type_id integer required ID de tipo de observación. Example: 2
    * @bodyParam message string required Mensaje de la observación. Example: Subsanable en una semana
    * @bodyParam date required Fecha de la observación. Example: 2020-04-14 21:16:52
    * @bodyParam enabled boolean required Estado de la observación. Example: false
    * @authenticated
    * @responseFile responses/loan/unset_observation.200.json
    */
    public function unset_observation(ObservationForm $request, Loan $loan)
    {
        $request->request->add(['observable_type' => 'loans', 'observable_id' => $loan->id]);
        $observation = $loan->observations();
        foreach ($request->except('created_at','updated_at','deleted_at') as $key => $value) {
            $observation = $observation->where($key, $value);
        }
        $observation = $observation->whereColumn('created_at','updated_at');
        if ($observation->count() == 1) {
            $observation->delete();
            return $loan->observations;
        } else {
            abort(404, 'La observación fue modificada, no se puede eliminar');
        }
    }

    /**
    * Derivar en lote
    * Deriva o devuelve trámites en un lote mediante sus IDs
    * @bodyParam ids array required Lista de IDs de los trámites a derivar. Example: [1,2,3]
    * @bodyParam role_id integer required ID del rol al cual derivar o devolver. Example: 82
    * @authenticated
    * @responseFile responses/loan/bulk_update_role.200.json
    */
    public function bulk_update_role(LoansForm $request)
    {
        if(!$request->user_id) 
            $user_id = null;
        else
            $user_id = $request->user_id;
        $sequence = null;
        $from_role = null;
        $to_role = $request->role_id;
        $loans = Loan::whereIn('id', $request->ids)->where('role_id', '!=', $request->role_id)->orderBy('code');
        $derived = $loans->get();
        $to_role = Role::find($to_role);
        if (count(array_unique($loans->pluck('role_id')->toArray()))) $from_role = $derived->first()->role_id;
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
        $loans->update(array_merge($request->only('role_id'), ['validated' => false], ['user_id' => $user_id]));
        $derived->transform(function ($loan) {
            return self::append_data($loan, false);
        });
        event(new LoanFlowEvent($derived));
        // PDF template
        $data = [
            'type' => 'loan',
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'Área de ' . $from_role->display_name,
                'table' => [
                    ['Fecha', Carbon::now()->isoFormat('L')],
                    ['Hora', Carbon::now()->format('H:i')],
                    ['Usuario', Auth::user()->username]
                ]
            ],
            'title' => ($flow_message['type'] == 'derivacion' ? 'DERIVACIÓN' : 'DEVOLUCIÓN') . ' DE TRÁMITES - MODALIDAD ' . $derived->first()->modality->second_name,
            'procedures' => $derived,
            'roles' => [
                'from' => $from_role,
                'to' => $to_role
            ]
        ];
        $information_derivation='Fecha: '.Str::slug(Carbon::now()->isoFormat('LLL'), ' ').'  enviado a  '.$from_role->display_name;
        $file_name = implode('_', ['derivacion', 'prestamos', Str::slug(Carbon::now()->isoFormat('LLL'), '_')]) . '.pdf';
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

    /** @group Cobranzas
    * Impresión del Kardex de Pagos
    * Devuelve un pdf del Kardex de pagos acorde a un ID de préstamo
    * @urlParam loan required ID del préstamo. Example: 1
    * @queryParam folded boolean tipo de kardex, desplegado o no desplegado. Example: true
    * @queryParam copies Número de copias del documento. Example: 2
    * @authenticated
    * @responseFile responses/loan/print_kardex.200.json
    */

    public function print_kardex(Request $request, Loan $loan, $standalone = true)
    {
        if($loan->disbursement_date){
            $procedure_modality = $loan->modality;
            $lenders = [];
            foreach ($loan->lenders as $lender) {
                $lenders[] = self::verify_spouse_disbursable($lender)->disbursable;
            }
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
                'title' => 'KARDEX DE PAGOS',
                'loan' => $loan,
                'lenders' => collect($lenders)
            ];
            $information_loan= $this->get_information_loan($loan);
            $file_name = implode('_', ['kardex', $procedure_modality->shortened, $loan->code]) . '.pdf';
            if($request->folded == "true")
                $view = view()->make('loan.payments.payment_kardex')->with($data)->render();
            else
                $view = view()->make('loan.payments.payment_kardex_unfolded')->with($data)->render();
            if ($standalone) return Util::pdf_to_base64([$view], $file_name, $information_loan, 'legal', $request->copies ?? 1);
            return $view;
        }else{
            return "prestamo no desembolsado";
        }
    }

    /**
    * Evaluacion de prestamo para refinanciamiento
    * Devuelve un array con los estados de las validaciones
    * @urlParam loan required id de prestamo a evaluar. Example: 28
    * @bodyParam type_procedure boolean required si es true la evaluacion evalua refinanciamiento caso contrario evalua reprogramacion
    * @authenticated
    * @responseFile responses/loan/loan_evaluate.200.json
    */
    public function validate_re_loan(Request $request, Loan $loan){
        $loan_payments = $loan->payments->sortBy('quota_number');
        $capital_paid = 0;
        $message = array();
        if($request->type_procedure == true){
            foreach($loan_payments as $payment){
                $capital_paid = $capital_paid + $payment->capital_payment;
            }
            $percentage_paid = round(($capital_paid/$loan->amount_approved)*100,2);
            if($percentage_paid<25){
                $message['percentage'] = false;
            }
            else {
                $message['percentage'] = true;
            }
        }
        if($loan->balance >= ($loan->estimated_quota*3)){
            //if (count($loan->getPlanAttribute())>3){
            $message['paids'] = true;
        }
        else{
            $message['paids'] = false;
        }

        if (!$loan->defaulted){
            $message['defaulted'] = true;
        }
        else{
            $message['defaulted'] = false;
        }
        //pagos consecutivo
        if ($loan->verify_payment_consecutive()){
            $message['manual_payments'] = true;
        }
        else{
            $message['manual_payments'] = false;
        }
        return $message;
    }

    /**
    * Evaluacion de Afiliado
    * Devuelve mensaje de error 403
    * @urlParam affiliate_id required id del afiliado a evaluar. Example: 52540
    * @bodyParam affiliate_id integer required la evaluacion del afiliado en caso de que este aprobado devuelve true caso contrario devuelve error 403
    * @authenticated
    * @responseFile responses/loan/affiliate_evaluate.200.json
    */
    public function validate_affiliate($affiliate_id){
     
         $message['validate'] = false;
        $affiliate = Affiliate::findOrFail($affiliate_id);
        $loan_global_parameter = LoanGlobalParameter::latest()->first();
        $loan_disbursement = count($affiliate->disbursement_loans);
        $loan_process = count($affiliate->process_loans);
        if ($affiliate->affiliate_state){
            if($affiliate->affiliate_state->affiliate_state_type->name != "Baja" && $affiliate->affiliate_state->affiliate_state_type->name != ""){
                if((count($affiliate->spouses) === 0 && $affiliate->affiliate_state->name != 'Fallecido') || (count($affiliate->spouses) !== 0 && $affiliate->affiliate_state->name  == 'Fallecido')) {
                   /* if($affiliate->identity_card != null && $affiliate->city_identity_card_id != null){*/
                        if($affiliate->civil_status != null){
                            //if($affiliate->financial_entity_id != null && $affiliate->account_number != null && $affiliate->sigep_status != null){
                                if($affiliate->birth_date != null && $affiliate->city_birth_id != null){
                                    if($affiliate->affiliate_state->affiliate_state_type->name != 'Pasivo'){
                                        if($loan_process < $loan_global_parameter->max_loans_process ){
                                            if($loan_disbursement < $loan_global_parameter->max_loans_active){
                                                $message['validate'] = true;
                                            }else{
                                                $message['validate'] ='El afiliado no puede tener más de ' .$loan_global_parameter->max_loans_active. ' préstamos desembolsados. Actualemnte ya tiene '. $loan_disbursement .' préstamos desembolsados.';
                                                 } 
                                        }else{
                                            $message['validate'] = 'El afiliado no puede tener más de '.$loan_global_parameter->max_loans_process.' trámite en proceso. Actualmente ya tiene '.$loan_process.' préstamos en proceso.';
                                            }
                                    }elseif($affiliate->pension_entity_id ==  null){
                                            $message['validate'] = 'El afiliado no tiene registrado su ente Gestor.';
                                            }else{
                                                if($loan_process < $loan_global_parameter->max_loans_process ){
                                                    if($loan_disbursement < $loan_global_parameter->max_loans_active){
                                                         $message['validate'] = true;
                                                        }else{
                                                    $message['validate'] ='El afiliado no puede tener más de ' .$loan_global_parameter->max_loans_active. ' préstamos desembolsados. Actualemnte ya tiene '. $loan_disbursement .' préstamos desembolsados.';
                                                    } 
                                                }else{
                                                    $message['validate'] = 'El afiliado no puede tener más de '.$loan_global_parameter->max_loans_process.' trámite en proceso. Actualmente ya tiene '.$loan_process.' préstamos en proceso.';
                                                }  
                                            }
                                }else{
                                    $message['validate'] = 'El afiliado no tiene registrado su fecha de nacimiento ó ciudad de nacimiento.';
                                }
                            /*}
                           else{
                            $message['validate'] = 'El afiliado no tiene registrado la entidad financiera';
                            }*/ 
                        }
                        else{
                        $message['validate'] = 'El afiliado no tiene registrado su estado civil.';
                        }
                    /* }
                    else{
                        $message['validate'] = 'El afiliado no tiene registrado su CI ó ciudad de expedición del CI.';
                    }     */ 
                }
                else{ 
                    $message['validate'] = 'El afiliado no puede acceder a un préstamo por estar fallecido ó estar fallecido y no tener registrado a un(a) conyugue.';
                }
            }
           else{   
            $message['validate'] = 'El afiliado no puede acceder a un préstamo por estar dado de baja ó no tener registrado su estado.';
            }
        }
        else{   
            $message['validate'] = 'El afiliado no puede acceder a un préstamo por estar dado de baja ó no tener registrado su estado.';
        } 
        return $message;
    }
    //Destruir todo el préstamo
    public function destroyAll(Loan $loan)
    {
       if($loan->payments){
            if($loan->data_loan) $loan->data_loan->forceDelete();

            if($loan->loan_contribution_adjusts) $loan->loan_contribution_adjusts()->forceDelete();

            if($loan->lenders) $loan->lenders()->detach();

            if($loan->loan_persons) $loan->loan_persons()->detach();
            
            if($loan->submitted_documents) $loan->submitted_documents()->detach();
            
            if($loan->tags) $loan->tags()->detach();
            //$loan->forceDelete();
            $options=[$loan->id];
            $loan = Loan::withoutEvents(function() use($options){
                $loan = Loan::findOrFail($options[0])->forceDelete();
                return $loan;
            }
        );

       }else{
        abort(403, 'No se puede reahacer el préstamo existen registros de cobros');
       } 
    return $loan;
    }

    //actualizar el record de todo el prestamo anterior al actual
    public function happenRecordLoan(Loan $loan,$id_new_loan)
    {
      $records_remake_loan=$loan->records;
        foreach($records_remake_loan as $record_remake_loan ){ 
            $record_remake_loan->recordable_id = $id_new_loan; 
            $record_remake_loan->update();            
        }
        return $id_new_loan;

    }
    
    //limpiar datos sin relacion
    public function clear_data_base(){

    }
    /**
    * Obtener relacion de procedure hermano 
    * Obtiene la relacion entre procedures, ejemplo en el caso de Refinanciamiento
    * @bodyParam id_loan integer required Devuelve el objeto del procedure, en el caso de acticipo devuelve un array vacio Example: 1
    * @authenticated
    * @responseFile responses/loan/get_procedure_relation.200.json
    */
    public function procedure_brother(Request $request){
        $request->validate([
            'id_loan' => 'required|exists:loans,id'
        ]);
        $loan = Loan::findOrFail($request->id_loan);
        $procedure=$loan->modality->procedure_type;
        $procedure_ref=[];
    
        if($procedure->name=='Préstamo a Corto Plazo' || $procedure->name=='Refinanciamiento Préstamo a Corto Plazo'){
            $procedure_ref = ProcedureType::where('name','=','Refinanciamiento Préstamo a Corto Plazo')->first();
        }else{
            if($procedure->name=='Préstamo a Largo Plazo' || $procedure->name=='Refinanciamiento Préstamo a Largo Plazo'){
                $procedure_ref = ProcedureType::where('name','=','Refinanciamiento Préstamo a Largo Plazo')->first();
            }else{
                if($procedure->name=='Préstamo Hipotecario' || $procedure->name=='Refinanciamiento Préstamo Hipotecario'){
                    $procedure_ref = ProcedureType::where('name','=','Refinanciamiento Préstamo Hipotecario')->first();
                }
            }
        }
        return  $procedure_ref;
    }

    public function get_balance_sismu($ci){
        $query = "select Prestamos.PresNumero, Prestamos.PresSaldoAct, Prestamos.PresEstPtmo
        from Prestamos
        join Padron on Padron.IdPadron = Prestamos.IdPadron
        where Padron.PadCedulaIdentidad = '$ci'
        and Prestamos.PresEstPtmo = 'V'";
        $prestamos = DB::connection('sqlsrv')->select($query);
        return $prestamos;
    }
    /** 
   * Editar Monto y Plazo del Prestamo
   * Edita monto y plazo del prestam
   * @urlParam loan required ID del Prestamo. Example: 5
   * @bodyParam amount_approved numeric required Monto aprovado del prestamo. Example: 2000
   * @bodyParam loan_term integer required Plazo del prestamo. Example: 25
   * @authenticated
   * @responseFile responses/loan/edit_amounts_loan_term.200.json
   */

  public function edit_amounts_loan_term(Request $request, Loan $loan){
        $request->validate([
        'amount_approved' => 'required|numeric',
        'loan_term' => 'required|integer'
    ]);
        $procedure_modality = ProcedureModality::findOrFail($loan->procedure_modality_id);
        if($request->amount_approved <= $procedure_modality->loan_modality_parameter->maximum_amount_modality && $request->amount_approved >= $procedure_modality->loan_modality_parameter->minimum_amount_modality){
            if($request->loan_term <= $procedure_modality->loan_modality_parameter->maximum_term_modality && $request->loan_term >= $procedure_modality->loan_modality_parameter->minimum_term_modality){
                    $quota_estimated = CalculatorController::quota_calculator($procedure_modality,$request->loan_term,$request->amount_approved);
                    $new_indebtedness_calculated = ($quota_estimated/$loan->liquid_qualification_calculated)*100;
                    if(count($loan->guarantors)>0 || count($loan->lenders) > 1){
                        if($new_indebtedness_calculated <= $loan->indebtedness_calculated){
                            $loan->amount_approved = $request->amount_approved;
                            $loan->loan_term = $request->loan_term;
                            $loan->indebtedness_calculated = $new_indebtedness_calculated;
                            $loan->save(); 
                            $lenders_update = [];
                            foreach ($loan->lenders  as $lender) {     
                                $quota_estimated = ($quota_estimated/100)*$lender->pivot->payment_percentage;
                                $lenders_update = [
                                    'loan_id' => $loan->id,
                                    'affiliate_id' => $lender->pivot->affiliate_id,
                                    'payment_percentage' => $lender->pivot->payment_percentage,
                                    'payable_liquid_calculated' =>(float)$lender->pivot->payable_liquid_calculated,
                                    'bonus_calculated' => (float)$lender->pivot->bonus_calculated,
                                    'quota_previous' => (float)$lender->pivot->quota_previous,
                                    'quota_treat' => $quota_estimated,
                                    'indebtedness_calculated' => $new_indebtedness_calculated,
                                    'liquid_qualification_calculated' => (float)$lender->pivot->liquid_qualification_calculated,
                                    'guarantor' => false,
                                    'contributionable_type' => $lender->pivot->contributionable_type,
                                    'contributionable_ids' => $lender->pivot->contributionable_ids
                                    ];
                                $lender->loans()->where('id',$loan->id)->sync([$lenders_update]);
                            }
                            $guarantor_update = [];
                            foreach ($loan->guarantors  as $guarantor) {
                                $new_quota_estimated = ($quota_estimated/100)*$guarantor->pivot->payment_percentage;
                                $new_indebtedness_calculated = ($quota_estimated/(float)$guarantor->pivot->liquid_qualification_calculated)*100;
                                $guarantor_update = [
                                    'loan_id' => $loan->id,
                                    'affiliate_id' => $guarantor->pivot->affiliate_id,
                                    'payment_percentage' => $guarantor->pivot->payment_percentage,
                                    'payable_liquid_calculated' =>(float)$guarantor->pivot->payable_liquid_calculated,
                                    'bonus_calculated' => (float)$guarantor->pivot->bonus_calculated,
                                    'quota_previous' => (float)$guarantor->pivot->quota_previous,
                                    'quota_treat' => $new_quota_estimated,
                                    'indebtedness_calculated' => $new_indebtedness_calculated,
                                    'liquid_qualification_calculated' => (float)$guarantor->pivot->liquid_qualification_calculated,
                                    'guarantor' => true,
                                    'contributionable_type' => $guarantor->pivot->contributionable_type,
                                    'contributionable_ids' => $guarantor->pivot->contributionable_ids
                                ];
                                $guarantor->loans()->where('id',$loan->id)->sync([$guarantor_update]);
                            } 
                            return self::append_data($loan, true);       
                        }else{
                        $message['message'] = 'El índice de endeudamiento no debe ser superior a '.$loan->indebtedness_calculated.'%, evaluación realizada anteriormente';
                    }
                }else{
                    if($new_indebtedness_calculated<=$procedure_modality->loan_modality_parameter->debt_index){
                        $loan->amount_approved = $request->amount_approved;
                        $loan->loan_term = $request->loan_term;
                        $loan->indebtedness_calculated = $new_indebtedness_calculated;
                        $loan->save(); 
                        $lenders_update = [];
                        foreach ($loan->lenders  as $lender) {
                            $lenders_update = [
                                'loan_id' => $loan->id,
                                'affiliate_id' => $lender->pivot->affiliate_id,
                                'payment_percentage' => $lender->pivot->payment_percentage,
                                'payable_liquid_calculated' =>(float)$lender->pivot->payable_liquid_calculated,
                                'bonus_calculated' => (float)$lender->pivot->bonus_calculated,
                                'quota_previous' => (float)$lender->pivot->quota_previous,
                                'quota_treat' => $quota_estimated,
                                'indebtedness_calculated' => $new_indebtedness_calculated,
                                'liquid_qualification_calculated' => (float)$lender->pivot->liquid_qualification_calculated,
                                'guarantor' => false,
                                'contributionable_type' => $lender->pivot->contributionable_type,
                                'contributionable_ids' => $lender->pivot->contributionable_ids
                            ];
                            $lender->loans()->where('id',$loan->id)->sync([$lenders_update]);
                        }
                        return self::append_data($loan, true);
                    }else {
                        $message['message'] = 'El índice de endeudamiento no debe ser superior a '.$procedure_modality->loan_modality_parameter->debt_index.'%';
                        }
                    }    
                }else{
                    $message['message'] = 'No se pudo realizar la edicion. El plazo en meses solicitado no corresponde a la modalidad';
                    } 
            }else{
                $message['message'] = 'No se pudo realizar la edicion. El monto solicitado no corresponde a la modalidad';
                } 
    return $message;    
    }

    /** 
   * Actualizar monto de desembolso por refinanciamiento del prestamo
   * Actualizar monto de desembolso por refinanciamiento del prestamo caso del PVT y Sismu con el permiso update-refinancing-balance
   * @urlParam loan required ID del Prestamo. Example: 8
   * @authenticated
   * @responseFile responses/loan/update_refinancing_balance.200.json
   */
   public function update_balance_refinancing(Loan $loan){
    $balance_parent = 0;
       if($loan->data_loan){
        $balance_parent=$loan->balance_parent_refi();
        $loan->refinancing_balance=$loan->amount_approved - $balance_parent;
        $loan->update();
       }else{
           if($loan->parent_loan){
            $balance_parent = $loan->balance_parent_refi();
            $loan->refinancing_balance=$loan->amount_approved - $balance_parent;
            $loan->update();
           }else
                abort(409, 'No es un préstamo de tipo refinanciamiento! ');
       }
        $loan_res = collect([
            'balance_parent_loan_refinancing' => $balance_parent
        ])->merge($loan);

        return $loan_res;
   }

   /** 
   * Listar prestamos generando reportes
   * Lista todos los prestamos con opcion a busquedas
   * @queryParam sortDesc Vector de orden descendente(0) o ascendente(1). Example: 0
   * @queryParam per_page Número de datos por página. Example: 8
   * @queryParam page Número de página. Example: 1
   * @queryParam excel Valor booleano para descargar  el docExcel. Example: true
   * @queryParam id_loan Buscar ID del Préstamo. Example: 1
   * @queryParam code_loan  Buscar código del Préstamo. Example: PTMO000012-2021
   * @queryParam id_affiliate  Buscar por ID del affiliado. Example: 33121
   * @queryParam identity_card_affiliate  Buscar por nro de CI del afiliado. Example: 10069775
   * @queryParam registration_affiliate  Buscar por Matricula del afiliado. Example: 100697MDF
   * @queryParam last_name_affiliate Buscar por primer apellido del afiliado. Example: RIVERA
   * @queryParam mothers_last_name_affiliate Buscar por segundo apellido del afiliado. Example: ARTEAG
   * @queryParam first_name_affiliate Buscar por primer Nombre del afiliado. Example: ABAD
   * @queryParam second_name_affiliate Buscar por segundo Nombre del afiliado. Example: FAUST
   * @queryParam surname_husband_affiliate Buscar por Apellido de casada Nombre del afiliado. Example: De LA CRUZ
   * @queryParam sub_modality_loan Buscar por sub modalidad del préstamo. Example: Corto plazo sector activo
   * @queryParam modality_loan Buscar por Modalidad del prestamo. Example: Préstamo a corto plazo
   * @queryParam amount_approved_loan Buscar monto aprobado del afiliado. Example: 25000
   * @queryParam state_type_affiliate Buscar por tipo de estado del afiliado. Example: Activo
   * @queryParam state_affiliate Buscar por estado del affiliado. Example: Servicio
   * @queryParam quota_loan Buscar por la quota del prestamo. Example: 1500
   * @queryParam state_loan Buscar por el estado del prestamo. Example: En proceso
   * @queryParam guarantor_loan_affiliate Buscar los garantes del préstamo. Example: false
   * @queryParam pension_entity_affiliate Buscar por la La pension entidad del afiliado. Example: SENASIR
   * @queryParam disbursement_date_loan Buscar por fecha de desembolso. Example: 2021
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
    $identity_card_affiliate = request('identity_card_affiliate') ?? '';
    $registration_affiliate = request('registration_affiliate') ?? '';
 
    $last_name_affiliate = request('last_name_affiliate') ?? '';
    $mothers_last_name_affiliate = request('mothers_last_name_affiliate') ?? '';
    $first_name_affiliate = request('first_name_affiliate') ?? '';
    $second_name_affiliate = request('second_name_affiliate') ?? '';
    $surname_husband_affiliate = request('surname_husband_affiliate') ?? '';
 
    $sub_modality_loan = request('sub_modality_loan') ?? '';
    $modality_loan = request('modality_loan') ?? '';
    $amount_approved_loan = request('amount_approved_loan') ?? '';
    $state_type_affiliate = request('state_type_affiliate') ?? '';
    $state_affiliate = request('state_affiliate') ?? '';
    $state_loan = request('state_loan') ?? '';
 
    $quota_loan = request('quota_loan') ?? '';
    $guarantor_loan_affiliate = request('guarantor_loan_affiliate') ?? '';
    $pension_entity_affiliate = request('pension_entity_affiliate') ?? '';

    $disbursement_date_loan = request('disbursement_date_loan') ?? '';
 
    $amount_approved_loan = request('amount_approved_loan') ?? '';
 
       if ($id_loan != '') {
        array_push($conditions, array('loans.id', 'ilike', "%{$id_loan}%"));
      }
 
      if ($code_loan != '') {
        array_push($conditions, array('loans.code', 'ilike', "%{$code_loan}%"));
      }
  
      if ($id_affiliate != '') {
        array_push($conditions, array('affiliates.id', 'ilike', "%{$id_affiliate}%"));
      }
      if ($identity_card_affiliate != '') {
        array_push($conditions, array('affiliates.identity_card', 'ilike', "%{$identity_card_affiliate}%"));
      }
      if ($registration_affiliate != '') {
        array_push($conditions, array('affiliates.registration', 'ilike', "%{$registration_affiliate}%"));
      }
  
      if ($last_name_affiliate != '') {
        array_push($conditions, array('affiliates.last_name', 'ilike', "%{$last_name_affiliate}%"));
      }
      if ($mothers_last_name_affiliate != '') {
        array_push($conditions, array('affiliates.mothers_last_name', 'ilike', "%{$mothers_last_name_affiliate}%"));
      }
 
      if ($first_name_affiliate != '') {
        array_push($conditions, array('affiliates.first_name', 'ilike', "%{$first_name_affiliate}%"));//
      }
      if ($second_name_affiliate != '') {
        array_push($conditions, array('affiliates.second_name', 'ilike', "%{$second_name_affiliate}%"));
      }
      if ($surname_husband_affiliate != '') {
        array_push($conditions, array('affiliates.surname_husband', 'ilike', "%{$surname_husband_affiliate}%"));
      }
  
      if ($sub_modality_loan != '') {
        array_push($conditions, array('procedure_modalities.name', 'ilike', "%{$sub_modality_loan}%"));
      }
      if ($modality_loan != '') {
        array_push($conditions, array('procedure_types.name', 'ilike', "%{$modality_loan}%"));
      }
 
      if ($amount_approved_loan != '') {
        array_push($conditions, array('loans.amount_approved', 'ilike', "%{$amount_approved_loan}%"));
      }
      if ($state_type_affiliate != '') {
        array_push($conditions, array('affiliate_state_types.name', 'ilike', "%{$state_type_affiliate}%"));
      }
      if ($state_affiliate != '') {
        array_push($conditions, array('affiliate_states.name', 'ilike', "%{$state_affiliate}%"));
      }
  
      if ($quota_loan != '') {
        array_push($conditions, array('loan_affiliates.quota_treat', 'ilike', "%{$quota_loan}%"));
      }
      if ($state_loan != '') {
        array_push($conditions, array('loan_states.name', 'ilike', "%{$state_loan}%"));
      }
      if ($guarantor_loan_affiliate != '') {
        array_push($conditions, array('loan_affiliates.guarantor', 'ilike', "%{$guarantor_loan_affiliate}%"));
      }
      if ($pension_entity_affiliate != '') {
        array_push($conditions, array('pension_entities.name', 'ilike', "%{$pension_entity_affiliate}%"));
      }
      if ($disbursement_date_loan != '') {
        array_push($conditions, array('loans.disbursement_date', 'ilike', "%{$disbursement_date_loan}%"));
      }
 
      if($excel==true){
       
       $list_loan = DB::table('loans')
               ->join('procedure_modalities','loans.procedure_modality_id', '=', 'procedure_modalities.id')
               ->join('procedure_types','procedure_modalities.procedure_type_id', '=', 'procedure_types.id')
               ->join('loan_states','loans.state_id', '=', 'loan_states.id')
               ->join('cities','loans.city_id', '=', 'cities.id')
               ->join('loan_affiliates','loans.id', '=', 'loan_affiliates.loan_id')
               ->join('affiliates','loan_affiliates.affiliate_id', '=', 'affiliates.id')
               ->join('affiliate_states','affiliates.affiliate_state_id', '=', 'affiliate_states.id')
               ->join('affiliate_state_types','affiliate_states.affiliate_state_type_id', '=', 'affiliate_state_types.id')
               ->leftjoin('pension_entities','affiliates.pension_entity_id', '=', 'pension_entities.id')
               ->whereNull('loans.deleted_at')
               ->where($conditions)
               ->select('loans.id as id_loan','loans.code as code_loan','affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate',
               'affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
               'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate',
               'procedure_modalities.name as sub_modality_loan','procedure_types.second_name as modality_loan','loans.amount_approved as amount_approved_loan',
               'affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate','loan_affiliates.quota_treat as quota_loan','loan_states.name as state_loan',
               'loan_affiliates.guarantor as guarantor_loan_affiliate','pension_entities.name as pension_entity_affiliate','loans.disbursement_date as disbursement_date_loan')
               //->where('affiliates.identity_card','LIKE'.'%'.$request->identity_card.'%')
               ->orderBy('loans.code', $order_loan)
               ->get();
 
               foreach ($list_loan as $loan) {
                 $padron = Loan::where('id', $loan->id_loan)->first();
                 $loan->balance_loan=$padron->balance;
               }
               $File="ListadoPrestamos";
               $data=array(
                   array("Id del préstamo", "Codigo", "ID afiliado", "Nro de carnet", "Matrícula", "Primer apellido","Segundo apellido","Primer nombre","Segundo nombre","Apellido casada","Sub modalidad",
                   "Modalidad","Monto del prestamo", "estado del affiliado","Tipo de estado del affiliado","Cuota del prestamo","Estado del préstamo","Es garante?","Entidad de pensión del afiliado",'Saldo préstamo','Fecha desembolso' )
               );
               foreach ($list_loan as $row){
                   array_push($data, array(
                       $row->id_loan,
                       $row->code_loan,
                       $row->id_affiliate,
                       $row->identity_card_affiliate,
                       $row->registration_affiliate,
                       $row->last_name_affiliate,
                       $row->mothers_last_name_affiliate,
                       $row->first_name_affiliate,
                       $row->second_name_affiliate,
                       $row->surname_husband_affiliate,
                       $row->sub_modality_loan,
                       $row->modality_loan,
                       $row->amount_approved_loan,
                       $row->state_type_affiliate,
                       $row->state_affiliate,
                       $row->quota_loan,
                       $row->state_loan,
                       $row->guarantor_loan_affiliate,
                       $row->pension_entity_affiliate,
                       $row->balance_loan,
                       $row->disbursement_date_loan
                   ));
               }
               $export = new ArchivoPrimarioExport($data);
               return Excel::download($export, $File.'.xlsx');
      }else{
      
       $list_loan = DB::table('loans')
               ->join('procedure_modalities','loans.procedure_modality_id', '=', 'procedure_modalities.id')
               ->join('procedure_types','procedure_modalities.procedure_type_id', '=', 'procedure_types.id')
               ->join('loan_states','loans.state_id', '=', 'loan_states.id')
               ->join('cities','loans.city_id', '=', 'cities.id')
               ->join('loan_affiliates','loans.id', '=', 'loan_affiliates.loan_id')
               ->join('affiliates','loan_affiliates.affiliate_id', '=', 'affiliates.id')
               ->join('affiliate_states','affiliates.affiliate_state_id', '=', 'affiliate_states.id')
               ->join('affiliate_state_types','affiliate_states.affiliate_state_type_id', '=', 'affiliate_state_types.id')
               ->leftjoin('pension_entities','affiliates.pension_entity_id', '=', 'pension_entities.id')
               ->whereNull('loans.deleted_at')
               ->where($conditions)
               ->select('loans.id as id_loan','loans.code as code_loan','affiliates.id as id_affiliate','affiliates.identity_card as identity_card_affiliate',
               'affiliates.registration as registration_affiliate','affiliates.last_name as last_name_affiliate','affiliates.mothers_last_name as mothers_last_name_affiliate',
               'affiliates.first_name as first_name_affiliate','affiliates.second_name as second_name_affiliate','affiliates.surname_husband as surname_husband_affiliate',
               'procedure_modalities.name as sub_modality_loan','procedure_types.second_name as modality_loan','loans.amount_approved as amount_approved_loan',
               'affiliate_state_types.name as state_type_affiliate','affiliate_states.name as state_affiliate','loan_affiliates.quota_treat as quota_loan','loan_states.name as state_loan',
               'loan_affiliates.guarantor as guarantor_loan_affiliate','pension_entities.name as pension_entity_affiliate','loans.disbursement_date as disbursement_date_loan')
               ->orderBy('loans.code', $order_loan)
               ->paginate($pagination_rows);
 
               $list_loan->getCollection()->transform(function ($list_loan) {
                 $padron = Loan::findOrFail($list_loan->id_loan);
                 $list_loan->balance_loan=$padron->balance;
                 return $list_loan;
               });
           return $list_loan;
      }
   }

}
