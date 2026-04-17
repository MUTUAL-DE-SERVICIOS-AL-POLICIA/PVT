<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Waavi\Sanitizer\Laravel\SanitizesInput;

class MovementConceptForm extends FormRequest
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
            'name' => 'string',
            'shortened' => 'string',
            'description' => 'string|nullable',
            'type' => 'string|in:INGRESO,EGRESO',
            'role_id' => 'integer|exists:roles,id',
            'user_id' =>'integer|nullable',
        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 4) as $key => $rule) {
                    $rules[$key] = implode('|', ['required', $rule]);
                }
                return $rules;
            }
            //case 'GET':
            case 'PUT':
            case 'PATCH':{
                return $rules;
            }
        }
    }
    public function filters()
    {
        return [
            'name' => 'trim|uppercase',
            'description' => 'trim|uppercase',
            'shortened'=> 'trim|uppercase',
        ];
    }
}
