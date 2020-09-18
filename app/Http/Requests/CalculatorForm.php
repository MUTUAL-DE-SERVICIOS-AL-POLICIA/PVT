<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class CalculatorForm extends FormRequest
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
        return[
            'liquid_calification' => ['required', 'array', 'min:1'],
            'liquid_calification.*.affiliate_id' => ['required', 'integer', 'exists:affiliates,id'],
            'liquid_calification.*.parent_loan_id' => ['integer', 'nullable', 'exists:loans,id'],
            'liquid_calification.*.contributions' => ['required', 'array', 'min:1'],
            'liquid_calification.*.contributions.*.payable_liquid' => ['required'],
            'liquid_calification.*.contributions.*.border_bonus' => ['required'],
            'liquid_calification.*.contributions.*.seniority_bonus' => ['required'],
            'liquid_calification.*.contributions.*.public_security_bonus' => ['required'],
            'liquid_calification.*.contributions.*.east_bonus' => ['required']
        ];
    }

}
