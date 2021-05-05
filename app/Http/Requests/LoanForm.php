<?php

namespace App\Http\Requests;

use Waavi\Sanitizer\Laravel\SanitizesInput;
use App\Rules\LoanIntervalAmount;
use App\Rules\LoanIntervalTerm;
use App\Rules\LoanDestiny;
use App\Rules\LoanRole;
use App\Rules\ProcedureRequirements;
use App\Loan;
use App\ProcedureModality;
use App\Rules\LoanParameterGuarantor;
use App\Rules\LoanParameterIndebtedness;
use App\Rules\LoanIntervalMaxLender;
use App\Rules\LoanIntervalMaxCosigner;
use Illuminate\Foundation\Http\FormRequest;

class LoanForm extends FormRequest
{
    use SanitizesInput;

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

    protected function prepareForValidation():void
    {
       if($this->parent_loan_id == 0 && $this->has('parent_loan_id')){
            $this->merge([
                'parent_loan_id' => null,
            ]);
        }
        if($this->property_id==0 && $this->has('property_id')){
            $this->merge([
                'property_id' => null,
            ]);
        }
        if($this->financial_entity_id==0 && $this->has('financial_entity_id')){
            $this->merge([
                'financial_entity_id' => null,
            ]);
        }
        if($this->remake_loan_id == 0 && $this->has('remake_loan_id')){
            $this->merge([
                'remake_loan_id' => null,
            ]);
        }
    }

    public function rules():array
    {
        $hypothecary = false;
        $refinanciamiento=false;
        $sismu = false;

        if ($this->procedure_modality_id) {
            $procedure_modality = ProcedureModality::findOrFail($this->procedure_modality_id);
            if(strpos($procedure_modality->name, 'Refinanciamiento') !== false ) $refinanciamiento = true;
        } 

        if ($this->procedure_modality_id) {
            $procedure_modality = ProcedureModality::findOrFail($this->procedure_modality_id);
            if($procedure_modality->procedure_type->name == "Préstamo hipotecario" || $procedure_modality->procedure_type->name == "Refinanciamiento Préstamo hipotecario") $hypothecary = true;
        } else {
            $procedure_modality = $this->loan->modality;
        }
        if($this->parent_loan_id == null && $this->parent_reason != null)
        $sismu = true;
        $quota_previous = false;
        if($this->parent_loan_id>0) $quota_previous = true;
        $rules = [
            'procedure_modality_id' => ['integer', 'exists:procedure_modalities,id'],
            'amount_requested' => ['integer', 'min:200', 'max:700000', new LoanIntervalAmount($procedure_modality)],
            'city_id' => ['integer', 'exists:cities,id'],
            'loan_term' => ['integer', 'min:1', 'max:240', new LoanIntervalTerm($procedure_modality)],
            'payment_type_id' => ['integer', 'exists:payment_types,id'],
            'destiny_id' => ['integer', 'exists:loan_destinies,id', new LoanDestiny($procedure_modality)],
            'documents' => ['array', 'min:1', new ProcedureRequirements($procedure_modality)],
            'liquid_qualification_calculated' => ['numeric'],
            'indebtedness_calculated' => ['numeric', 'max:90', new LoanParameterIndebtedness($procedure_modality)],
            'lenders' => ['array','min:1', new LoanIntervalMaxLender($procedure_modality)],
            'personal_references' => ['array', 'exists:personal_references,id' ],
            'lenders.*.affiliate_id' => ['required', 'integer', 'exists:affiliates,id'],
            'lenders.*.payment_percentage' => ['required', 'numeric'],
            'lenders.*.payable_liquid_calculated' => ['required', 'numeric'],
            'lenders.*.bonus_calculated' => ['required', 'numeric'],
            'lenders.*.quota_previous' => ['numeric', $quota_previous? 'required':'nullable'],
            'lenders.*.quota_treat' => ['nullable','numeric'],
            'lenders.*.indebtedness_calculated' => ['nullable', 'numeric'],
            'lenders.*.liquid_qualification_calculated' => ['required', 'numeric'],
            'lenders.*.contributionable_ids' => ['array'],
            'lenders.*.contributionable_type'  => ['string','required','in:contributions,aid_contributions,loan_contribution_adjusts'],
            'lenders.*.loan_contributions_adjust_ids'  => ['array','nullable','exists:loan_contribution_adjusts,id'],
            'property_id' => ['nullable', $hypothecary? 'required':'nullable','exists:loan_properties,id'],
            'guarantors' => ['array',new LoanParameterGuarantor($procedure_modality)],
            'guarantors.*.affiliate_id' => ['required', 'integer', 'exists:affiliates,id'],
            'guarantors.*.payment_percentage' => ['required', 'numeric'],
            'guarantors.*.payable_liquid_calculated' => ['required', 'numeric'],
            'guarantors.*.bonus_calculated' => ['required', 'numeric'],
            'guarantors.*.quota_previous' => ['numeric'],
            'guarantors.*.quota_treat' => ['nullable','numeric'],
            'guarantors.*.indebtedness_calculated' => ['required', 'numeric'],
            'guarantors.*.liquid_qualification_calculated' => ['required', 'numeric'],
            'guarantors.*.contributionable_ids' => ['array'],
            'guarantors.*.contributionable_type' => ['string','required','in:contributions,aid_contributions,loan_contribution_adjusts'],
            'guarantors.*.loan_contributions_adjust_ids'  => ['array','nullable','exists:loan_contribution_adjusts,id'],
            'data_loan' =>['array', $sismu? 'required':'nullable'],
            'data_loan.*.code'=>['required','string'],
            'data_loan.*.amount_approved'=>['required','numeric'],
            'data_loan.*.loan_term'=>['required','integer'],
            'data_loan.*.balance'=>['required','numeric'],
            'data_loan.*.estimated_quota'=>['required','numeric'],
            'data_loan.*.date_cut_refinancing'=>['nullable', 'date_format:"Y-m-d"'],
            'cosigners' => ['array',new LoanIntervalMaxCosigner($procedure_modality),'exists:personal_references,id'],
            'documents.*' => ['exists:procedure_documents,id'],
            'disbursable_id' => ['integer'],
            'disbursable_type' => ['string', 'in:affiliates,spouses'],
            'number_payment_type' => ['nullable', 'integer', 'min:6'],
            'disbursement_date' => ['nullable', 'date_format:"Y-m-d"'],
            //'num_budget_certification' => ['string','nullable'],
            'num_accounting_voucher' => ['string','nullable'],
            'parent_loan_id' => ['integer', 'nullable', 'exists:loans,id'],
            'parent_reason'=> ['string', 'nullable',$refinanciamiento? 'required':'nullable', 'in:REFINANCIAMIENTO,REPROGRAMACIÓN'],
            'state_id' => ['exists:loan_states,id'],
            'amount_approved' => ['integer', 'min:200', 'max:700000', new LoanIntervalAmount($procedure_modality)],
            'notes' => ['array'],
            'validated' => ['boolean'],
            'guarantor_amortizing' => ['boolean'],
            'financial_entity_id' => ['nullable', 'integer', 'exists:financial_entities,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'remake_loan_id'=>['integer', 'nullable', 'exists:loans,id'],
            'delivery_contract_date' => ['nullable', 'date_format:"Y-m-d"'],
            'return_contract_date' => ['nullable', 'date_format:"Y-m-d"']
        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, $procedure_modality->loan_modality_parameter->personal_reference? 11:10 ) as $key => $rule) {
                    array_push($rules[$key], 'required');
                }
                if ($procedure_modality->loan_modality_parameter->guarantors) {
                    array_push($rules['guarantors'], 'required');
                }
                return $rules;
            }
            case 'PUT':
            case 'PATCH':{
                $rules['role_id'] = ['integer', 'exists:roles,id', new LoanRole($this->loan->id)];
                return $rules;
            }
        }
        return $rules;
    }

    public function filters()
    {
        return [
            'parent_reason' => 'trim|uppercase',
            'validated' => 'cast:boolean'
        ];
    }
    public function messages(){
        return[
            'parent_loan_id.required' => 'El campo ID del préstamo Padre es requerido.',
            'parent_reason.required' => 'El campo parent_reason es requerido.',
            'remake_loan_id.exists' => 'No existe el Id del préstamo a rehacer'
        ];
    }
}
