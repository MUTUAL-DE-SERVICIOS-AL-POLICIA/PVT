<?php

namespace App\Http\Controllers\Api\V1;

use Util;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Affiliate;
use App\City;
use App\Loan;
use App\LoanState;
use Illuminate\Support\Facades\Schema;
use App\ProcedureDocument;
use App\ProcedureModality;
use App\PaymentType;
use App\Http\Requests\LoanForm;
use Carbon;

/** @group Préstamos
* Datos de los trámites de préstamos y sus relaciones
*/
class LoanController extends Controller
{
    private function append_data($loan) {
        $loan->balance = $loan->balance;
        $loan->estimated_quota = $loan->estimated_quota;
        $loan->defaulted = $loan->defaulted;
    }
    /**
    * Lista de Préstamos
    * Devuelve el listado con los datos paginados
    * @queryParam search Parámetro de búsqueda. Example: 3000
    * @queryParam sortBy Vector de ordenamiento. Example: []
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [true]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @response
    * {
    *     "current_page": 1,
    *     "data": [
    *         {
    *             "id": 2,
    *             "code": null,
    *             "disbursable_id": 1453,
    *             "disbursable_type": "spouses",
    *             "procedure_modality_id": 32,
    *             "disbursement_date": "2020-02-13",
    *             "parent_loan_id": null,
    *             "parent_reason": null,
    *             "request_date": "2020-02-13",
    *             "amount_requested": 2000,
    *             "city_id": 4,
    *             "loan_interest_id": 1,
    *             "loan_state_id": 1,
    *             "amount_approved": null,
    *             "loan_term": 3,
    *             "disbursement_type_id": 1,
    *             "created_at": "2020-02-13 16:32:43",
    *             "updated_at": "2020-02-13 16:32:43",
    *             "balance": 2000,
    *             "estimated_quota": 707.06,
    *             "defaulted": false
    *         }, {} 
    *     ],
    *     "first_page_url": "http://192.168.2.242/api/v1/loan?page=1",
    *     "from": 1,
    *     "last_page": 1,
    *     "last_page_url": "http://192.168.2.242/api/v1/loan?page=1",
    *     "next_page_url": null,
    *     "path": "http://192.168.2.242/api/v1/loan",
    *     "per_page": 10,
    *     "prev_page_url": null,
    *     "to": 3,
    *     "total": 3
    * }
    */
    public function index(Request $request)
    {
        $data = Util::search_sort(new Loan(), $request);
        foreach ($data as $item) {
            $this->append_data($item);
        }
        return $data;
    }

    /**
    * Nuevo préstamo
    * Inserta nuevo préstamo
    * @bodyParam procedure_modality_id integer required ID de modalidad. Example: 35
    * @bodyParam amount_requested integer required monto solicitado. Example: 3000
    * @bodyParam city_id integer required ID de la ciudad. Example: 2
    * @bodyParam loan_term integer required plazo. Example: 3
    * @bodyParam disbursement_type_id integer required Tipo de desembolso. Example: 1
    * @bodyParam lenders required array Lista de IDs de afiliados Titular de préstamo. Example: [1,6]
    * @bodyParam guarantors array Lista de IDs de afiliados Garante de préstamo. Example: [3,4]
    * @bodyParam parent_loan_id integer ID de Préstamo Padre. Example: 1
    * @bodyParam parent_reason enum (refinanciado, reprogramado) Tipo de trámite hijo. Example: refinanciado
    * @bodyParam disbursable_id integer ID de afiliado a quien se desembolsará. Example: 1
    * @authenticated
    * @response
    * {
    *    "disbursable_id": "1",
    *    "disbursable_type": "affiliates",
    *    "procedure_modality_id": 35,
    *    "loan_interest_id": 4,
    *    "amount_requested": 3000,
    *    "city_id": 2,
    *    "loan_term": 3,
    *    "disbursement_type_id": 1,
    *    "code": null,
    *    "disbursement_date": "2020-02-01",
    *    "parent_loan_id": "1",
    *    "parent_reason": null,
    *    "amount_approved": "3000",
    *    "loan_state_id": 1,
    *    "request_date": "2020-02-17T13:38:18.562649Z",
    *    "updated_at": "2020-02-17 09:38:18",
    *    "created_at": "2020-02-17 09:38:18",
    *    "id": 5,
    *    "modality": {
    *        "id": 35,
    *        "procedure_type_id": 10,
    *        "name": "Corto plazo con disponibilidad de letra \"A\"",
    *        "shortened": "PCP-DLA",
    *        "is_valid": true
    *    }
    * }
    */
    public function store(LoanForm $request)
    {
        $saved = $this->save_loan($request);
        $loan = $saved->loan;
        $request = $saved->request;
        $request->lenders = collect($request->lenders);
        $request->guarantors = collect($request->guarantors);
        $request->guarantors = $request->guarantors->diff($request->lenders);
        $percentage = Loan::get_percentage($request->lenders);
        foreach ($request->lenders as $affiliate) {
            $affiliates[$affiliate] = [
                'payment_percentage' =>$percentage,
                'guarantor' => false
            ];
        }
        if($request->guarantors){
            $percentage = Loan::get_percentage($request->guarantors);
            foreach ($request->guarantors as $affiliate) {
                $affiliates[$affiliate] = [
                    'payment_percentage' =>$percentage,
                    'guarantor' => true
                ];
            }
        }
        $loan->loan_affiliates()->sync($affiliates);
        return $loan;
    }

    /**
    * Detalle de Préstamo
    * Devuelve el detalle de un préstamo mediante su ID
    * @urlParam loan required ID de préstamo. Example: 4
    * @response
    * {
    *    "id": 4,
    *    "code": null,
    *    "disbursable_id": 1,
    *    "disbursable_type": "affiliates",
    *    "procedure_modality_id": 35,
    *    "disbursement_date": "2020-02-01",
    *    "parent_loan_id": null,
    *    "parent_reason": null,
    *    "request_date": "2020-02-17",
    *    "amount_requested": 3000,
    *    "city_id": 2,
    *    "loan_interest_id": 4,
    *    "loan_state_id": 1,
    *    "amount_approved": 3000,
    *    "loan_term": 3,
    *    "disbursement_type_id": 1,
    *    "created_at": "2020-02-17 14:52:40",
    *    "updated_at": "2020-02-17 14:52:40",
    *    "balance": 3000,
    *    "estimated_quota": 1033.52,
    *    "defaulted": false,
    *    "lenders": [
    *        {
    *            "id": 6,
    *            "user_id": 11,
    *            "affiliate_state_id": 4,
    *            "city_identity_card_id": 4,
    *            "city_birth_id": 2,
    *            "degree_id": 7,
    *            "unit_id": 1,
    *            "category_id": 8,
    *            "pension_entity_id": 2,
    *            "identity_card": "2022760",
    *            "registration": "500718RRA",
    *            "type": "Comando",
    *            "last_name": "RODRIGUEZ",
    *            "mothers_last_name": "ROLDAN",
    *            "first_name": "ALBERTO",
    *            "second_name": null,
    *            "surname_husband": null,
    *            "gender": "M",
    *            "civil_status": "C",
    *            "birth_date": "1950-07-18",
    *            "date_entry": "1972-01-01",
    *            "date_death": "2015-08-08",
    *            "reason_death": "TRAUMATISMO CRANEO ENCEFÁLICO GRAVE",
    *            "date_derelict": null,
    *            "reason_derelict": null,
    *            "change_date": "2016-07-01",
    *            "phone_number": "(6) 423-792",
    *            "cell_phone_number": "(603)-18901",
    *            "afp": true,
    *            "nua": 17346472,
    *            "item": 27468,
    *            "created_at": "2017-06-01 10:42:18",
    *            "updated_at": "2018-09-05 07:59:09",
    *            "deleted_at": null,
    *            "service_years": null,
    *            "service_months": null,
    *            "death_certificate_number": "59244",
    *            "due_date": null,
    *            "is_duedate_undefined": true,
    *            "affiliate_registration_number": null,
    *            "file_code": null,
    *            "pivot": {
    *                "loan_id": 4,
    *                "affiliate_id": 6,
    *                "payment_percentage": "50"
    *            }
    *        },{}
    *   ],
    *   "guarantors": []
    * }
    */
    public function show($id)
    {
        $item = Loan::findOrFail($id);
        $item->lenders;
        $item->guarantors;
        $this->append_data($item);
        return $item;
    }

    /**
    * Actualizar préstamo
    * Actualizar datos principales de préstamo
    * @urlParam id required ID de préstamo. Example: 1
    * @bodyParam disbursable_id integer required ID de afiliado a quien se hara el desembolso. Example: 1
    * @bodyParam disbursable_type string required (affiliates,spouses). Example: affiliates
    * @bodyParam procedure_modality_id integer required ID de modalidad. Example: 35
    * @bodyParam amount_requested integer required monto solicitado. Example: 5000
    * @bodyParam city_id integer required ID de la ciudad. Example: 2
    * @bodyParam loan_term integer required plazo. Example: 3
    * @bodyParam disbursement_type_id integer required Tipo de desembolso. Example: 1
    * @queryParam lenders required array Lista de IDs de afiliados Titular de préstamo. Example: [1,6]
    * @queryParam guarantors required array Lista de IDs de afiliados Garante de préstamo. Example: []
    * @bodyParam disbursement_date date Fecha de desembolso. Example: 2020-02-01
    * @bodyParam parent_loan_id integer ID de Préstamo Padre. Example: 1
    * @bodyParam parent_reason enum (refinanciado,reprogramado)  . Example: refinanciado
    * @bodyParam amount_approved integer Monto Aprobado. Example: 3000
    * @authenticated
    * @response
    * {
    *    "id": 1,
    *    "code": null,
    *    "disbursable_id": "1",
    *    "disbursable_type": "affiliates",
    *    "procedure_modality_id": 35,
    *    "disbursement_date": "2020-02-01",
    *    "parent_loan_id": null,
    *    "parent_reason": null,
    *    "request_date": "2020-02-17",
    *    "amount_requested": 5000,
    *    "city_id": 2,
    *    "loan_interest_id": 4,
    *    "loan_state_id": 1,
    *    "amount_approved": "3000",
    *    "loan_term": 3,
    *    "disbursement_type_id": 1,
    *    "created_at": "2020-02-17 10:37:48",
    *    "updated_at": "2020-02-17 10:39:41",
    *    "modality": {
    *        "id": 35,
    *        "procedure_type_id": 10,
    *        "name": "Corto plazo con disponibilidad de letra \"A\"",
    *        "shortened": "PCP-DLA",
    *        "is_valid": true
    *    }
    * }
    */
    public function update(LoanForm $request, $id)
    {
        $saved = $this->save_loan($request);
        $loan = $saved->loan;
        $request = $saved->request;
        $request->lenders = collect($request->lenders)->unique();
        $request->guarantors = collect($request->guarantors)->unique();
        $request->guarantors = $request->guarantors->diff($request->lenders);
        $percentage = Loan::get_percentage($request->lenders);
        foreach ($request->lenders as $affiliate) {
            $affiliates[$affiliate] = [
                'payment_percentage' =>$percentage,
                'guarantor' => false
            ];
        }
        if($request->guarantors){
            $percentage = Loan::get_percentage($request->guarantors);
            foreach ($request->guarantors as $affiliate) {
                $affiliates[$affiliate] = [
                    'payment_percentage' =>$percentage,
                    'guarantor' => true
                ];
            }
        }
        $loan->loan_affiliates()->sync($affiliates);
        return $loan;
    }

    /**
    * Eliminar préstamo
    * @urlParam id required ID de préstamo. Example: 1
    * @authenticated
    * @response
    * {
    *    "id": 1,
    *    "code": null,
    *    "disbursable_id": 1,
    *    "disbursable_type": "affiliates",
    *    "procedure_modality_id": 35,
    *    "disbursement_date": "2020-02-01",
    *    "parent_loan_id": null,
    *    "parent_reason": null,
    *    "request_date": "2020-02-17",
    *    "amount_requested": 5000,
    *    "city_id": 2,
    *    "loan_interest_id": 4,
    *    "loan_state_id": 1,
    *    "amount_approved": 3000,
    *    "loan_term": 3,
    *    "disbursement_type_id": 1,
    *    "created_at": "2020-02-17 10:37:48",
    *    "updated_at": "2020-02-17 10:39:41"
    * }
    */
    public function destroy($id)
    {
        $loan = Loan::findOrFail($id);
        $loan->loan_affiliates()->sync([]);
        $loan->delete();
        return $loan;
    }

    private function save_loan(Request $request, $id = null)
    {
        if (!$request->has('guarantors')) $request->guarantors = [];
        $request->lenders = array_unique($request->lenders);
        $request->guarantors = array_unique($request->guarantors);
        if (!$request->has('disbursable_id')) {
            $disbursable_id = $request->lenders[0];
        } else {
            if (!in_array($request->disbursable_id, $request->lenders)) abort(404);
            $disbursable_id = $request->disbursable_id;
        }
        if ($id) {
            $loan = Loan::findOrFail($id);
            $loan->fill(array_merge($request->all(), (array)self::verify_spouse_disbursable($disbursable_id)));
        } else {
            $loan = new Loan(array_merge($request->all(), (array)self::verify_spouse_disbursable($disbursable_id), ['amount_approved' => $request->amount_requested]));
        }
        $loan->save();
        return (object)[
            'request' => $request,
            'loan' => $loan
        ];
    }

    /**
    * Registro de documentos presentados
    * Registra todos los documentos entregados por parte del prestatario
    * @urlParam id required ID de préstamo. Example: 2
    * @queryParam documents required array Lista de IDs de Documentos solicitados. Example: [306,305]
    * @response
    * {
    *    "attached": [
    *        306,
    *        305
    *    ],
    *    "detached": [],
    *    "updated": []
    * }
    */
    public function submit_documents(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);
        $date = Carbon::now()->toISOString();
        $documents = [];
        foreach ($request->documents as $document_id) {
            $document_id = intval($document_id);
            ProcedureDocument::findOrFail($document_id);
            if ($loan->submitted_documents()->whereId($document_id)->doesntExist()) {
                $documents[$document_id] = [
                    'reception_date' => $date
                ];
            }
        }
        return $loan->submitted_documents()->syncWithoutDetaching($documents);
    }

    /**
    * Desembolso Afiliado
    * Devuelve los datos del o la cónyugue en caso de que hubiera fallecido a quien se hace el desembolso del préstamo
    * @urlParam id required ID de préstamo. Example: 2
    * @response
    * {
    *     "id": 1,
    *    "user_id": 1,
    *    "affiliate_state_id": 5,
    *    "city_identity_card_id": 2,
    *    "city_birth_id": 2,
    *    "degree_id": 7,
    *    "unit_id": 1,
    *    "category_id": 8,
    *    "pension_entity_id": 2,
    *    "identity_card": "1020566",
    *    "registration": "440808ACG",
    *    "type": "Comando",
    *    "last_name": "ALVAREZ",
    *    "mothers_last_name": "CURCUY",
    *    "first_name": "GARY",
    *    "second_name": null,
    *    "surname_husband": null,
    *    "gender": "M",
    *    "civil_status": "C",
    *    "birth_date": "1944-08-08",
    *    "date_entry": "2010-07-19",
    *    "date_death": null,
    *    "reason_death": "",
    *    "date_derelict": "2017-04-01",
    *    "reason_derelict": null,
    *    "change_date": "2016-07-01",
    *    "phone_number": "(6) 446-978",
    *    "cell_phone_number": "",
    *    "afp": true,
    *    "nua": 1301101,
    *    "item": 27446,
    *    "created_at": "2017-06-01 10:42:18",
    *    "updated_at": "2019-06-12 17:08:45",
    *    "deleted_at": null,
    *    "service_years": 41,
    *    "service_months": 10,
    *    "death_certificate_number": "",
    *    "due_date": null,
    *    "is_duedate_undefined": true,
    *    "affiliate_registration_number": null,
    *    "file_code": null
    * }
    */
    public function get_disbursable($id){
        $loan = Loan::findOrFail($id);
        return $loan->disbursable;
    }

    public static function verify_spouse_disbursable($disbursable_id)
    {
        $affiliate = Affiliate::findOrFail($disbursable_id);
        if ($affiliate->dead) {
            $spouse = $affiliate->spouse;
            if ($spouse) return (object)[
                'disbursable_type' => 'spouses',
                'disbursable_id' => $spouse->id,
                'disbursable' => $spouse
            ];
        }
        return (object)[
            'disbursable_type' => 'affiliates',
            'disbursable_id' => $affiliate->id,
            'disbursable' => $affiliate
        ];
    }

    /**
    * Impresión de los requisitos
    * Devuelve un pdf de los requisitos acorde a una modalidad
    * @queryParam lenders required array Lista de IDs de afiliados Titular de préstamo. Example: [1,6]
    * @queryParam procedure_modality_id integer required ID de la modalidad del préstamo. Example: 35
    * @queryParam city_id integer required ID de la ciudad. Example: 2
    * @queryParam amount_requested integer monto solicitado. Example: 5000
    * @queryParam loan_term integer plazo. Example: 3
    * @queryParam parent_loan_id integer ID de préstamo padre. Example: 1
    * @authenticated
    */
    public function print_requirements(Request $request)
    {
        $parent_loan = $request->has('parent_loan_id') ? Loan::find($request->parent_loan_id) : null;
        $lenders = [];
        foreach ($request->lenders as $lender) {
            array_push($lenders, self::verify_spouse_disbursable($lender)->disbursable);
        }
        $procedure_modality = ProcedureModality::findOrFail($request->procedure_modality_id);
        $date = Carbon::now();
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => [
                    ['Tipo', $procedure_modality->procedure_type->second_name],
                    ['Modalidad', $procedure_modality->shortened],
                    ['Usuario', auth()->user()->username ?? 'prueba']
                ]
            ],
            'title' => 'REQUISITOS PARA SOLICITUD DE ' . ($parent_loan ? 'REFINANCIAMIENTO' : 'PRÉSTAMO'),
            'lenders' => $lenders,
            'procedure_modality' => $procedure_modality,
            'city' => City::findOrFail($request->city_id),
            'parent_loan' => $parent_loan,
            'amount_requested' => $request->amount_requested,
            'loan_term' => $request->loan_term
        ];
        $file_name = implode('_', ['solicitud', 'prestamo']) . '.pdf';
        $footerHtml = view()->make('partials.footer')->with(array('paginator' => true, 'print_date' => true, 'date' => Carbon::now()->ISOFormat('L H:m')))->render();
        $options = [
            'orientation' => 'portrait',
            'page-width' => '216',
            'page-height' => '279',
            'margin-top' => '8',
            'margin-bottom' => '16',
            'margin-left' => '5',
            'margin-right' => '7',
            'encoding' => 'UTF-8',
            'footer-html' => $footerHtml,
            'user-style-sheet' => public_path('css/report-print.min.css')
        ];
        $pdf = \PDF::loadView('procedure_modality.requirements', $data);
        $pdf->setOptions($options);
        return $pdf->stream($file_name);
    }

    // TODO
    public function switch_states()
    {
        $loans = Loan::whereHas('state', function($query) {
            $query->whereName('Desembolsado');
        })->get();
        foreach ($loans as $loan) {
            if ($loan->defaulted) {
                // Verify if it has defaulted tag
                // Attach defaulted tag
            } // Else detach defaulted tag
        }
    }

    /**
    * Impresión de Contrato
    * Devuelve un pdf del contrato acorde a un ID de préstamo
    * @queryParam loan_id required integer ID del préstamo. Example: 1
    * @authenticated
    * @response
    */

    public function print_contract($id)
    {
        $loan = Loan::findOrFail($id);
        $procedure_modality = $loan->modality;
        $lenders = [];
        foreach ($loan->lenders as $lender) {
            $lenders[] = self::verify_spouse_disbursable($lender->id);
        }
        $employees = [
            ['position' => 'Director General Ejecutivo'],
            ['position' => 'Director de Asuntos Administrativos']
        ];
        foreach ($employees as $key => $employee) {
            $request = collect(json_decode(file_get_contents(env("RRHH_URL") . '/position?name=' . $employee['position']), true));
            if ($request->count() == 1) {
                $position = $request->first();
            } else {
                abort(404);
            }
            $request = collect(json_decode(file_get_contents(implode('/', [env("RRHH_URL"), 'position', $position['id'], 'employee'])), true));
            $employees[$key]['name'] = Util::trim_spaces(implode(' ', [$request['first_name'], $request['second_name'], $request['last_name'], $request['mothers_last_name']]));
            $employees[$key]['identity_card'] = $request['identity_card'];
            $request = collect(json_decode(file_get_contents(implode('/', [env("RRHH_URL"), 'city', $request['city_identity_card_id']])), true));
            $employees[$key]['identity_card'] .= ' ' . $request['shortened'];
        }
        $date = Carbon::now();
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => []
            ],
            'employees' => $employees,
            'title' => $procedure_modality->name,
            'loan' => $loan,
            'lenders' => $lenders
        ];
        $file_name = implode('_', ['contrato', $procedure_modality->shortened, $id]) . '.pdf';
        $footerHtml = view()->make('partials.footer')->with(array('paginator' => true, 'print_date' => true, 'date' => Carbon::now()->ISOFormat('L H:m')))->render();
        $options = [
            'orientation' => 'portrait',
            'page-width' => '216',
            'page-height' => '279',
            'margin-top' => '8',
            'margin-bottom' => '16',
            'margin-left' => '5',
            'margin-right' => '8',
            'encoding' => 'UTF-8',
            'footer-html' => $footerHtml,
            'user-style-sheet' => public_path('css/report-print.min.css')
        ];
        $pdf = \PDF::loadView('loan.contracts.advance', $data);
        $pdf->setOptions($options);
        return $pdf->stream($file_name);
    }
    /**
    * Impresión de Formulario de Solicitud de Préstamo
    * Devuelve un pdf del Formulario de solicitud
    * @queryParam lenders required array Lista de IDs de afiliados Titular de préstamo. Example: [529]
    * @queryParam procedure_modality_id required integer ID de la modalidad del préstamo. Example: 32
    * @queryParam amount_requested required integer monto solicitado. Example: 2000
    * @queryParam disbursement_type_id required integer Tipo de desembolso. Example: 2
    * @queryParam loan_term required integer plazo. Example: 2
    * @queryParam destination required string destino de préstamo. Example: Salud
    * @queryParam account_number string número de cuenta de Banco Unión. Example: 1-9334298
    * @authenticated
    */
    public function print_form(Request $request)
    {
        $request->validate([
            'disbursement_type_id' => 'required|exists:payment_types,id',
            'procedure_modality_id' => 'required|exists:procedure_modalities,id',
            'amount_requested'=>'required|integer|min:200|max:700000',
            'lenders'=>'required|array|exists:affiliates,id',
            'loan_term'=>'required|integer|min:1|max:240',
            'destination'=>'required|string',
        ]);
        $procedure_modality = ProcedureModality::findOrFail($request->procedure_modality_id);
        $payment_type = PaymentType::findOrFail($request->disbursement_type_id);
        $lenders = [];
        foreach ($request->lenders as $lender) {
            array_push($lenders, self::verify_spouse_disbursable($lender)->disbursable);
        }
        $date = Carbon::now();
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => [
                    ['Tipo', $procedure_modality->procedure_type->second_name],
                    ['Modalidad', $procedure_modality->shortened],
                    ['Usuario', auth()->user()->username ?? 'prueba']
                ]
            ],
            'title' => 'Ref. : SOLICITUD DE PRÉSTAMO '.$procedure_modality->procedure_type->second_name,
            'procedure_modality' => $procedure_modality,
            'lenders' => $lenders,
            'amount_requested' => $request->amount_requested,
            'loan_term' => $request->loan_term,
            'destination' => $request->destination,
            'account_number' => $request->account_number,
            'payment_type' => $payment_type
        ];
        $file_name = implode('_', ['formulario', 'solicitud_préstamo']) . '.pdf';
        $footerHtml = view()->make('partials.footer')->with(array('paginator' => true, 'print_date' => true, 'date' => Carbon::now()->ISOFormat('L H:m')))->render();
        $options = [
            'orientation' => 'portrait',
            'page-width' => '216',
            'page-height' => '279',
            'margin-top' => '8',
            'margin-bottom' => '16',
            'margin-left' => '15',
            'margin-right' => '20',
            'encoding' => 'UTF-8',
            'footer-html' => $footerHtml,
            'user-style-sheet' => public_path('css/report-print.min.css')
        ];
        $pdf = \PDF::loadView('loan.forms.request_form', $data);
        $pdf->setOptions($options);
        return $pdf->stream($file_name);
        return 0;
    }
}
