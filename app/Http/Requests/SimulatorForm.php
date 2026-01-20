<?php

namespace App\Http\Requests;

use App\ProcedureModality;
use App\Rules\LoanIntervalAmount;
use App\Rules\LoanIntervalTerm;


use Illuminate\Foundation\Http\FormRequest;

class SimulatorForm extends FormRequest
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
        if ($this->procedure_modality_id) {
            $procedure_modality = ProcedureModality::findOrFail($this->procedure_modality_id);
        }else{
            return[
                'procedure_modality_id' => ['required','exists:procedure_modalities,id']
            ];
        }
        return[
            'amount_requested'=> ['required','min:1', 'max:700000', new LoanIntervalAmount($procedure_modality)],
            'months_term'=> ['required', 'integer', 'min:1', 'max:240', new LoanIntervalTerm($procedure_modality)],
            'guarantor' => ['nullable', 'boolean'],
            'liquid_qualification_calculated_lender' => ['nullable', 'required_if:guarantor,true'],
            'liquid_calculated' => ['required', 'array', 'min:1'],
            'liquid_calculated.*.affiliate_id' => ['required'],
            'liquid_calculated.*.liquid_qualification_calculated' => ['required']
        ];
    }

    public function messages()
    {
        return [
            'liquid_qualification_calculated_lender.required_if' => 'La cuota total del titular es requerido',
            'amount_requested.required_if' => 'El monto solicitado es requerido',
            'months_term.required_if' => 'El plazo solicitado es requerido',
        ];
    }
}
