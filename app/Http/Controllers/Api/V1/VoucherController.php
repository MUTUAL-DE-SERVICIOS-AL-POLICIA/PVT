<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Voucher;
use App\LoanState;
use App\LoanPaymentState;
use Illuminate\Http\Request;
use App\Http\Requests\VoucherForm;
use Util;
use Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V1\LoanController;
use App\Affiliate;
use App\LoanPayment;

/** @group Tesoreria
* Datos de los registros de cobros
*/
class VoucherController extends Controller
{
    /**
    * Listado de cobros
    * Devuelve el listado con los datos paginados
    * @queryParam user_id Filtro por id de usuario. Example: 123
    * @queryParam loan_payment_id Filtro por id de préstamo. Example: 2
    * @queryParam loan_payments Filtro por tipo de cobro. Example: loan_payments
    * @queryParam search Parámetro de búsqueda. Example: TRANS000001-2020
    * @queryParam sortBy Vector de ordenamiento. Example: [created_at]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [false]
    * @queryParam per_page Número de datos por página. Example: 1
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/voucher/index.200.json
    */
    public function index(Request $request)
    {
        $filter = [];
        if ($request->has('user_id')) {
            $filter['user_id'] = $request->user_id;
        }
        if ($request->has('loan_payments')) {
            $filter['payable_type'] = "loan_payments";
        }
        if ($request->has('loan_payment_id')) {
            $filter['payable_id'] = $request->loan_payment_id;
            $filter['payable_type'] = "loan_payments";
        }
        return Util::search_sort(new Voucher(), $request, $filter);
    }
    /**
    * Detalle de registro de cobro
    * Devuelve el detalle de un voucher mediante su ID
    * @urlParam voucher required ID de voucher. Example: 1
    * @authenticated
    * @responseFile responses/voucher/show.200.json
    */
    public function show(Voucher $voucher)
    {
        return $voucher;
    }


    /**
    * Editar registro de cobro
    * Edita el Pago realizado.
    * @urlParam voucher required ID del registro de pago. Example: 2
    * @bodyParam voucher_type_id integer required ID de tipo de voucher. Example: 1
    * @bodyParam voucher_number integer número de voucher. Example: 12354121
	* @bodyParam description string Texto de descripción. Example: Penalizacion regularizada
    * @authenticated
    * @responseFile responses/voucher/update.200.json
    */
    public function update(VoucherForm $request, Voucher $voucher)
    {
        if (Auth::user()->can('update-payment')) {
            $update = $request->only('voucher_type_id', 'description', 'voucher_number');
        }
        $voucher->fill($update);
        $voucher->save();
        return  $voucher;
    }

    /**
    * Anular registro de cobro
    * @urlParam voucher required ID del pago. Example: 1
    * @authenticated
    * @responseFile responses/voucher/destroy.200.json
    */
    public function destroy(Voucher $voucher)
    {
        $payable_type = Voucher::findOrFail($voucher->id);
        if($payable_type->payable_type = "loan_payments")
        {
            $state = LoanPaymentState::whereName('Pendiente de Pago')->first();
            $loanPayment = $voucher->payable;
            $loanPayment->state()->associate($state);
            $loanPayment->save();
        }
        $voucher->delete();
        return $voucher;
    }
      /**
    * Anular registro de vaucher y registro de cobro
    * @urlParam voucher required ID del vaucher. Example: 1
    * @authenticated
    * @responseFile responses/voucher/delete_voucher_payment.200.json
    */
    public function delete_voucher_payment($id_payment){
        $voucher = Voucher::findOrFail($id_payment);
        if($voucher->payable_type = "loan_payments")
        {
            $state = LoanPaymentState::whereName('Anulado')->first();
            $loan_payment = $voucher->payable;
            $loan_payment->state()->associate($state);
            $loan_payment->save();
            $loan_payment->delete();
        }
        $voucher->delete();
        return $voucher;

    } 
    /** @group Tesoreria
    * Impresión del Voucher de Pago
    * Devuelve un pdf del Voucher acorde a un ID de pago
    * @urlParam voucher required ID del pago. Example: 2
    * @queryParam copies Número de copias del documento. Example: 2
    * @authenticated
    * @responseFile responses/voucher/print_voucher.200.json
    */

    public function print_voucher(Request $request, Voucher $voucher, $standalone = true)
    {
        $affiliate = Affiliate::findOrFail($voucher->affiliate_id);
        $lenders = [];
        $lenders[] = LoanController::verify_spouse_disbursable($affiliate)->disbursable;
        $data = [
            'header' => [
                'direction' => 'DIRECCIÓN DE ESTRATEGIAS SOCIALES E INVERSIONES',
                'unity' => 'UNIDAD DE INVERSIÓN EN PRÉSTAMOS',
                'table' => [
                    ['Código', $voucher->code],
                    ['Fecha', Carbon::now()->format('d/m/Y')],
                    ['Hora', Carbon::now()->format('h:m:s a')],
                    ['Usuario', Auth::user()->username]
                ]
            ],
            'title' => 'RECIBO OFICIAL',
            'voucher' => $voucher,
            'lenders' => collect($lenders)
        ];
        $information= $this->get_information_loan($voucher);
        $file_name = implode('_', ['voucher', $voucher->code]) . '.pdf';
        $view = view()->make('loan.payments.payment_voucher')->with($data)->render();
        if ($standalone) return Util::pdf_to_base64([$view], $file_name, $information, 'letter', $request->copies ?? 1);
        return $view;
    }

    public function get_information_loan(Voucher $voucher)
    {
        $file_name='';
        if($voucher->payable_type == 'loan_payments'){
            $loan = LoanPayment::findOrFail($voucher->payable_id)->loan;
            $lend='';
            foreach ($loan->lenders as $lender) {
                $lenders[] = LoanController::verify_spouse_disbursable($lender);
            }
            foreach ($loan->lenders as $lender) {
                $lend=$lend.'*'.' ' . $lender->first_name .' '. $lender->second_name .' '. $lender->last_name.' '. $lender->mothers_last_name;
            }
            
            $loan_affiliates= $loan->loan_affiliates[0]->first_name;
            $file_name =implode(' ', ['Información:',$loan->code,$loan->modality->name,$lend]);
        }
        return $file_name;
    }
}
