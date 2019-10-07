<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use App\Affiliate;
class AffiliateForm extends FormRequest
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
        $this->sanitize();
        $rules = [
            'first_name' => 'alpha_spaces|min:3',
            'last_name' => 'alpha_spaces|min:3',
            'gender' => 'in:M,F',
            'birth_date' => 'date_format:y/m/d',
            'city_birth_id' => 'exists:cities,id',
            'identity_card' => 'unique:affiliates|min:3',
            'city_identity_card_id' => 'exists:cities,id',
            'phone_number'=>'integer|nullable',
            'cell_phone_number'=>'integer|nullable',
            'civil_status' => 'in:C,D,S,V',
            'affiliate_state_id' => 'exists:affiliate_state,id',
            'degree_id' => 'exists:degrees,id',
            'pension_entity_id' => 'exists:pension_entities,id',


        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 7) as $key => $rule) {
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
    public function sanitize(){
        $input = $this->all();
        if (array_key_exists('first_name', $input)) $input['first_name'] = mb_strtoupper($input['first_name']);
        if (array_key_exists('second_name', $input)) $input['second_name'] = mb_strtoupper($input['second_name']);
        if (array_key_exists('last_name', $input)) $input['last_name'] = mb_strtoupper($input['last_name']);
        if (array_key_exists('mothers_last_name', $input)) $input['mothers_last_name'] = mb_strtoupper($input['mothers_last_name']);
        if (array_key_exists('reason_death', $input)) $input['reason_death'] = mb_strtoupper($input['reason_death']);
        if (array_key_exists('identity_card', $input)) $input['identity_card'] = mb_strtoupper($input['identity_card']);
        if (array_key_exists('surname_husband', $input)) $input['surname_husband'] = mb_strtoupper($input['surname_husband']);
        if (array_key_exists('gender', $input)) $input['gender'] = mb_strtoupper($input['gender']);
        if (array_key_exists('civil_status', $input)) $input['civil_status'] = mb_strtoupper($input['civil_status']);

        $this->replace($input);
    } 
}





