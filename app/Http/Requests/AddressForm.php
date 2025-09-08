<?php

namespace App\Http\Requests;

use Waavi\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;

class AddressForm extends FormRequest
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
            'city_address_id' => 'exists:cities,id',
            'zone' =>'nullable|min:3',
            'street' =>'nullable|min:3',
            'housing_unit' =>'nullable',
            'description' =>'nullable|min:3',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 1) as $key => $rule) {
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

    public function filters()
    {
        return [
            'zone' => 'trim|uppercase',
            'street' => 'trim|uppercase',
            'housing_unit' => 'trim|uppercase',
            'number_address' => 'trim|uppercase',
            'description' =>'trim|uppercase',
            'latitude' => 'trim',
            'longitude' => 'trim',
        ];
    }
}