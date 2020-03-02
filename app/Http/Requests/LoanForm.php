<?php

namespace App\Http\Requests;
use Waavi\Sanitizer\Laravel\SanitizesInput;
use App\Loan;

use Illuminate\Foundation\Http\FormRequest;

class LoanForm extends FormRequest
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
            'procedure_modality_id'=>'integer|exists:procedure_modalities,id',  
            'amount_requested'=>'integer|min:200|max:700000',
            'city_id'=>'integer|exists:cities,id',
            'loan_term'=>'integer|min:2|max:240',
            'disbursement_type_id'=>'exists:payment_types,id',
            'lenders'=>'array|min:1|exists:affiliates,id',
            'guarantors'=>'array|exists:affiliates,id',
            'disbursable_id'=>'integer',
            'disbursable_type'=>'in:affiliates,spouses',
            'account_number'=>'nullable|integer',
            'code'=>'nullable', 
            'disbursement_date'=>'nullable|date_format:"Y-m-d"',
            'parent_loan_id'=>'nullable|exists:loans,id',
            'parent_reason'=> 'nullable|in:refinanciado,reprogramado',
            'loan_interest_id'=>'exists:loan_interests,id',
            'loan_state_id'=>'exists:loan_states,id',
            'amount_approved'=>'integer|min:200|max:700000'
       ];  

        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 6) as $key => $rule) {
                    $rules[$key] = implode('|', ['required', $rule]);
                }
                return $rules;
            }
            case 'PUT':
            case 'PATCH':{
                return $rules;
            }
        }
        return $rules;
    }
    public function filters()
    {
        return [
            //'code' => 'trim|uppercase',
        ];
    }
}
