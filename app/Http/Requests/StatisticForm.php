<?php

namespace App\Http\Requests;

use Waavi\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\StatisticFilter;

class StatisticForm extends FormRequest
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
        return [
            'module' => ['required', 'exists:modules,name'],
            'filter' => ['required', new StatisticFilter($this->input('module'))]
        ];
    }

    public function filters()
    {
        return [
            'module' => 'trim|lowercase',
            'filter' => 'trim|lowercase'
        ];
    }
}