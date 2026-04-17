<?php

namespace App\Http\Requests;

use Waavi\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserForm extends FormRequest
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
     *  Validation rules to be applied to the input.
     *
     *  @return array
     */
    public function rules()
    {
        $rules = [
            'cn' => 'alpha_spaces|min:3',
            'givenname' => 'alpha_spaces|min:3',
            'uid' => 'alpha_num|min:3|unique:users,username',
            //'password' => 'string|min:5',
            'title' => 'string',
            //'active' => 'boolean'
        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 5) as $key => $rule) {
                    $rules[$key] = implode('|', ['required', $rule]);
                }
                return $rules;
            }
            case 'PUT':
            case 'PATCH': {
                return $rules;
            }
        }
    }

    /**
     *  Filters to be applied to the input.
     *
     *  @return array
     */
    public function filters()
    {
        return [
            'username' => 'trim|lowercase',
            'first_name' => 'trim|uppercase',
            'position' => 'trim|uppercase',
            'last_name' => 'trim|uppercase',
            'password' => 'hash',
        ];
    }

    public function customFilters() {
        return [
            'hash' => function($value, $options = []) {
                return Hash::make($value);
            }
        ];
    }
}