<?php

namespace App\Http\Requests;

use Waavi\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;

class SpouseForm extends FormRequest
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
            'first_name' => 'required|alpha_spaces|min:3',
            'last_name' => 'sometimes|required_without:mothers_last_name|nullable|alpha_spaces|min:3',
            'affiliate_id' => 'required|exists:affiliates,id', 
            'identity_card' => 'required|min:3',
            'civil_status' => 'required|in:C,D,S,V',
            'city_birth_id' => 'required|exists:cities,id', 
            'birth_date' => 'required|nullable|date_format:"Y-m-d"',   
            'second_name' =>'nullable|alpha_spaces|min:3',
            'mothers_last_name' =>'sometimes|required_without:last_name|nullable|alpha_spaces|min:3',
            'due_date' => 'nullable|date_format:"Y-m-d"',
            'marriage_date' => 'nullable|date_format:"Y-m-d"',
            'surname_husband' => 'nullable|min:3',
            'date_death' => 'nullable|date_format:"Y-m-d"',
            'reason_death' => 'nullable|min:3',
            'death_certificate_number' => 'nullable|min:3',
            'due_date' => 'nullable|date_format:"Y-m-d"'
        ];
        switch ($this->method()) {
            case 'POST': {
                // foreach (array_slice($rules, 0, 8) as $key => $rule) {
                //     $rules[$key] = implode('|', ['required', $rule]);
                // }
                $rules['identity_card'] = implode('|', ['unique:spouses', $rules['identity_card']]);
                return $rules;
            }
            
            case 'PUT':
            case 'PATCH':{
                return $rules;
            }
        }
    }

    public function filters()
    {
        return [
            'first_name' => 'trim|uppercase',
            'last_name' => 'trim|uppercase',
            'second_name' => 'trim|uppercase',
            'mothers_last_name' => 'trim|uppercase',
            'surname_husband' => 'trim|uppercase',
            'reason_death' => 'trim|uppercase',
            'identity_card' => 'trim|uppercase'
        ];
    }

    public function messages(){
        return[
            'first_name.required' => 'El campo Primer Nombre del Conyuge es requerido',
            'first_name.alpha_spaces' => 'El campo Primer Nombre del Conyuge solo puede contener letras y espacios.',
            'first_name.min' =>'El campo Primer Nombre del Conyuge debe ser de al menos :min',
            'last_name.required_without' => 'El campo Apellido Paterno del Conyuge es requerido cuando el Apellido Materno no esta presente',
            'last_name.alpha_spaces' => 'El campo Apellido Paterno del Conyuge solo puede contener letras y espacios.',
            'last_name.min' =>'El campo Apellido Paterno del Conyuge debe ser de al menos :min',
            'city_identity_card_id.required' => 'El campo Ciudad de expedición del Conyuge es requerido',
            'city_identity_card_id.exists' =>'La selección Ciudad de expedición del Conyuge es inválida',
            'birth_date.required' =>'Elcampo Fecha de nacimiento del Conyuge es requerido',
            'birth_date.date_format' =>'El campo fecha de nacimiento del Conyuge no coincide con el formato :format.',
            'city_birth_id.required' => 'El campo Ciudad de nacimiento del Conyuge es requerido',
            'city_birth_id.exists' =>'La selección Ciudad de nacimiento del Conyuge es inválida.', 
            'identity_card.min' =>'El campo Carnet de Identidad del Conyuge debe ser de almenos :min',
            'identity_card.required' => 'El campo Carnet de Identidad del Conyuge es requerido',
            'identity_card.unique' =>'El campo Carnet de Identidad del Conyuge ya existe.',
            'civil_status.required' =>'El campo Estado Civil del Conyuge es requerido',
            'civil_status.in' =>'La selección Estado Civil del Conyuge es inválida',
            'second_name.alpha_spaces' =>'El campo Segundo Nombre del Conyuge solo puede contener letras y espacios.',
            'second_name.min' =>'El campo Segundo Nombre del Conyuge debe ser de al menos :min',
            'mothers_last_name.required_without' => 'El campo Apellido Materno del Conyuge es requerido cuando el Apellido Paterno no esta presente',
            'mothers_last_name.alpha_spaces' =>'El campo Apellido Materno del Conyuge solo puede contener letras y espacios.',
            'mothers_last_name.min' =>'El campo Apellido Materno del Conyuge debe ser de al menos :min',
            'due_date.date_format' =>'El campo Fecha de vencimiento de CI del Conyuge no coincide con el formato :format.',
            'marriage_date.date_format' =>'El campo Fecha de casamiento del Conyuge no coincide con el formato :format.',
            'surname_husband.min' =>'El campo Apellido del Esposo del Conyuge debe ser de al menos :min',
            'date_death.date_format' =>'El campo fecha de fallecimiento del Conyuge no coincide con el formato :format.',
            'reason_death.min' => 'El campo Motivo de Fallecimiento del Conyuge debe ser de al menos :min',
            'death_certificate_number.min' =>'El campo Número de certificado de defunción del Conyuge debe ser de al menos :min',
            'due_date.date_format' =>'El campo fecha de vencimiento de CI del Conyuge no coincide con el formato :format.',

        ];
    }
}
