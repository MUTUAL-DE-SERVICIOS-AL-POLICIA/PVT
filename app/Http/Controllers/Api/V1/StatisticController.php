<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatisticForm;
use App\Module;
use App\Loan;
use App\LoanPayment;
use App\Helpers\Util;

/** @group Estadísticas
* Estadísticas de los trámites almacenados en la base de datos
*/
class StatisticController extends Controller
{
    public $filters;

    public function __construct()
    {
        $this->filters = [
            'prestamos' => [
                'role_loans' => [
                    'display_name' => 'Número de trámites por área',
                    'method' => 'loans_by_state'
                ],
                'role_amortizations' => [
                    'display_name' => 'Número de trámites por área',
                    'method' => 'amortizations_by_role'
                ],
                'procedure_type_loans' => [
                    'display_name' => 'Número de trámites por tipo trámite',
                    'method' => 'loans_by_procedure_type'
                ],
                'procedure_type_amortizations' => [
                    'display_name' => 'Número de trámites por tipo trámite',
                    'method' => 'amortizations_by_procedure_type'
                ],
                'user_loans' => [
                    'display_name' => 'Número de trámites por usuario',
                    'method' => 'loans_by_user'
                ],
                'user_amortizations' => [
                    'display_name' => 'Número de amortizaciones por usuario',
                    'method' => 'amortizations_by_user'
                ],
            ]
        ];
    }

    public function get_filters(){
        return $this->filters;
    }

    /**
    * Datos estadísticos
    * Devuelve los datos estatdísticos de acuerdo al filtro seleccionado
    * @queryParam module Nombre de módulo para obtener las estadísticas. Example: prestamos
    * @queryParam filter Filtro para consultar en la base de datos. Example: procedure_type_amortizations
    * @queryParam filter Filtro para consultar en la base de datos. Example: role_amortizations
    * @queryParam filter Filtro para consultar en la base de datos. Example: procedure_type_loans
    * @queryParam filter Filtro para consultar en la base de datos. Example: role_loans
    * @queryParam filter Filtro para consultar en la base de datos. Example: user_loans
    * @authenticated
    * @responseFile responses/statistic/index.200.json
    */
    public function index(StatisticForm $request)
    {
        $module = Module::whereName($request->module)->first();
        return $this->{$this->filters[$request->module][$request->filter]['method']}($module, $request->role_id);
    }

    /**
    * Filtros disponibles
    * Devuelve el listado de los filtros disponibles para el módulo seleccionado
    * @urlParam module required Nombre de módulo para obtener los filtros disponibles. Example: prestamos
    * @authenticated
    * @responseFile responses/statistic/show.200.json
    */
    public function show($module)
    {
        if (!array_key_exists($module, $this->get_filters())) abort(404, 'Módulo inexistente');
        return [$this->get_filters()[$module]];
    }

    public function loans_by_role(Module $module,$role_id)
    {
        return Util:: process_by_role(new Loan(), $module,$role_id);
    }

    public function loans_by_state(Module $module,$role_id)
    {
        return Util:: process_by_state(new Loan(), $module,$role_id);
    }

    public function amortizations_by_role(Module $module)
    {
        return Util:: process_by_role(new LoanPayment(), $module);
    }

    public function loans_by_procedure_type(Module $module)
    {
        $procedure_loans = $module->procedure_types()->where('name', 'LIKE', '%Préstamo%')->orderBy('name')->get();
        return Util:: process_by_procedure_type(new Loan(), $procedure_loans, $module);
    }

    public function amortizations_by_procedure_type(Module $module,$role_id)
    {
        $workflows = $module->amortization_workflows;
        return Util:: process_by_procedure_type(new LoanPayment(), $workflows, $module, $role_id);
    }

    public function amortizations_by_user(Module $module,$role_id){
        $procedure_amortizations = $module->procedure_types()->where('name', 'LIKE', '%Amortización%')->orderBy('name')->get();
        return Util::amortizations_by_user(new LoanPayment(), $procedure_amortizations, $module,$role_id);
    }

    public function loans_by_user(Module $module,$role_id)
    {
        $workflows = $module->loan_workflows;
        return Util::loans_by_user(new Loan(), $workflows, $module,$role_id);
    }
}