<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Voucher;

class VoucherForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
            $rules = [
                'voucher_type_id' => 'exists:voucher_types,id',
                'voucher_amount_total'=>'nullable|numeric|min:1',
                'voucher_payment_date'=>'date_format:"Y-m-d"',
                'bank_pay_number' => 'nullable|required_if:voucher_type_id,3|integer|min:1',
                'description' => 'min:3|nullable'
            ];
            switch ($this->method()) {
                case 'POST': {
                    foreach (array_slice($rules, 0, 2) as $key => $rule) {
                        $rules[$key] = implode('|', ['required', $rule]);
                    }
                    return $rules;
                }
                case 'PUT':
                case 'PATCH':{
                    return $rules;
                }
            }
    }

    public function messages()
    {
        return [
            'voucher_type_id.required' => 'El tipo de voucher es requerido',
            'bank_pay_number.required_if' => 'El número de voucher es requerido si realizó deposito bancario',
            'voucher_amount_total.required'=>'El monto del voucher es requerido',
            'voucher_payment_date.required'=>'Fecha del pago'
        ];
    }
}
