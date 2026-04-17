<?php

namespace App\Http\Requests;

use Waavi\Sanitizer\Laravel\SanitizesInput;
use Illuminate\Foundation\Http\FormRequest;
use App\LoanProperty;

class LoanPropertyForm extends FormRequest
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
            'land_lot_number'=>'alpha_dash|min:1',
            'neighborhood_unit'=>'string|min:3',
            'location'=>'string|min:3',
            'surface'=>'min:1',
            'cadastral_code'=>'integer|min:3',
            'limit'=>'string|min:3',
            'public_deed_number' => 'string|min:1',
            'net_realizable_value'=>'min:1',
            'lawyer'=>'string|min:1',
            'registration_number'=>'string|min:1',
            'real_folio_number'=>'string|min:1',
            'public_deed_date'=>'date_format:"Y-m-d',
            'measurement'=>'string|min:1',
            'real_city_id'=>'integer|exists:cities,id',
        ];

        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 14) as $key => $rule) {
                    $rules[$key] = implode('|', ['required', $rule]);
                }
                return $rules;
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
            'land_lot_number' => 'trim|uppercase',
            'neighborhood_unit' => 'trim|uppercase',
            'urbanization' => 'trim|uppercase',
            'surface' => 'trim|uppercase',
            'limit' => 'trim|uppercase',
            'public_deed_number' => 'trim|uppercase',
            'lawyer' => 'trim|uppercase',
            'registration_number' => 'trim|uppercase',
            'real_folio_number' => 'trim|uppercase',
            'measurement' => 'trim|uppercase'
        ];
    }
}
