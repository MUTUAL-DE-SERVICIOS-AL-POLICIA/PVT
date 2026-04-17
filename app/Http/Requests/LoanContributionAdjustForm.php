<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Waavi\Sanitizer\Laravel\SanitizesInput;

class LoanContributionAdjustForm extends FormRequest
{    use SanitizesInput;
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
            'type_affiliate'=>['string','in:lender,guarantor,cosigner'],
            'description'=>['string','min:3'],
            'period_date'=>['date_format:"Y-m-d"'],
            'loan_id'=>['integer','nullable','exists:loans,id'],
            'affiliate_id'=>['integer','exists:affiliates,id'],
            'adjustable_id'=>['integer'],
            'adjustable_type' => ['string'],
            'amount' =>['numeric'],
            'type_adjust'=>['string','in:adjust,liquid,last_eco_com,reprogramming'],
            'database_name'=>['nullable','string','in:PVT,SISMU'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],        
        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 3) as $key => $rule) {
                    array_push($rules[$key], 'required');
                }
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
            'description' => 'trim|uppercase',
        ];
    }
}
