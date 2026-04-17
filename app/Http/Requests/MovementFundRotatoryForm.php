<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Waavi\Sanitizer\Laravel\SanitizesInput;

class MovementFundRotatoryForm extends FormRequest
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
            'description'=>'string',
            'role_id'=>'integer|exists:roles,id',
            'user_id'=>'integer|exists:users,id|nullable',
            'loan_id'=>'integer|exists:loans,id|nullable',
            'entry_amount'=>'numeric|nullable',
            'output_amount'=>'numeric|nullable',
            'date_check_delivery' => 'date_format:"Y-m-d|nullable',
        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 2) as $key => $rule) {
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
            'description' => 'trim|uppercase'
        ];
    }
}
