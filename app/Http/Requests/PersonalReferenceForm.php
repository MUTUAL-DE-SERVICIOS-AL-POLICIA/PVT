<?php

namespace App\Http\Requests;

use Waavi\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use App\PersonalReference;

class PersonalReferenceForm extends FormRequest
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
    public function rules()
    {
        $rules = [
            'first_name'=>['string','alpha_spaces','min:3'],
            'city_identity_card_id'=>['integer','exists:cities,id',$this->cosigner? 'required':'nullable'],
            'identity_card'=>['string','min:3',$this->cosigner? 'required':'nullable'],
            'civil_status' => ['in:C,D,S,V',$this->cosigner? 'required':'nullable'],
            'gender' =>['in:M,F',$this->cosigner? 'required':'nullable'],
            'city_birth_id' =>['integer','exists:cities,id',$this->cosigner? 'required':'nullable'],
            'address'=>['string',$this->cosigner? 'required':'nullable'],
            'last_name' => 'sometimes|required_without:mothers_last_name|nullable|alpha_spaces|min:3',
            'mothers_last_name' => 'sometimes|required_without:last_name|nullable|alpha_spaces|min:3',
            'second_name'=>['string','nullable','alpha_spaces','min:3'],
            'surname_husband'=>['string','nullable','alpha_spaces','min:3'],
            'phone_number'=>['nullable'],
            'cell_phone_number'=>['nullable'],
        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 1 ) as $key => $rule) {
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
            'first_name' => 'trim|uppercase',
            'second_name' => 'trim|uppercase',
            'last_name' => 'trim|uppercase',
            'mothers_last_name' => 'trim|uppercase',
            'identity_card' => 'trim|uppercase',
            'surname_husband' => 'trim|uppercase',
            'gender' => 'trim|uppercase',
            'civil_status' => 'trim|uppercase',
        ];
    }
   
}
