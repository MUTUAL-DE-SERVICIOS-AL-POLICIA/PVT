<?php

namespace App\Http\Requests;

use App\Module;
use App\Rules\ModuleObservation;
use Illuminate\Foundation\Http\FormRequest;
use Waavi\Sanitizer\Laravel\SanitizesInput;

class ObservationForm extends FormRequest
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
        $route = explode('/', explode('api/', url()->current())[1])[1];
        switch ($route) {
            case 'loan':
                $module = Module::whereName('prestamos')->first();
                break;
            case 'affiliate':
                $module = false;
                break;
            default:
                $module = null;
        }
        if ($module === null) abort(404, 'Módulo inexistente');
        $rules = [
            'observation_type_id' => ['integer', 'exists:observation_types,id'],
            'message' => ['string', 'min:1', 'max:255']
        ];
        if ($module) array_push($rules['observation_type_id'], new ModuleObservation($module));
        switch ($this->method()) {
            case 'POST':
                foreach (array_slice($rules, 0, 2) as $key => $rule) {
                    array_push($rules[$key], 'required');
                }
                break;
            case 'PUT':
            case 'PATCH':
                foreach ($rules as $key => $rule) {
                    $rules['update.' . $key] = $rule;
                    $rules['original.' . $key] = array_merge($rule, ['required']);
                    unset($rules[$key]);
                }
                $rules['update.enabled'] = ['boolean'];
                $rules['original.enabled'] = array_merge($rules['update.enabled'], ['required']);
                $rules['original.date'] = ['date', 'required'];
                $rules['original.user_id'] = ['integer', 'exists:users,id', 'required'];
                break;
            case 'DELETE':
                $rules['enabled'] = ['boolean'];
                $rules['date'] = ['date'];
                $rules['user_id'] = ['integer', 'exists:users,id'];
                foreach ($rules as $key => $rule) {
                    $rules[$key] = array_merge($rule, ['required']);
                }
                break;
        }
        return $rules;
    }

    public function filters()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'message' => 'trim|uppercase'
                ];
                break;
            case 'DELETE' || 'PATCH' || 'PUT':
                return [
                    'original.observation_type_id' => 'trim',
                    'original.message' => 'trim',
                    'original.date' => 'trim',
                    'update.observation_type_id' => 'trim',
                    'update.message' => 'trim|uppercase',
                    'update.date' => 'trim'
                ];
                break;
        }
    }
}
