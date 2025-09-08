<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Module;
use App\ObservationType;
use Util;

/** @group Módulos
* Módulos disponibles en el sistema
*/
class ModuleController extends Controller
{
    /**
    * Lista de módulos
    * Devuelve el listado con los datos paginados
    * @queryParam name Filtro por nombre. Example: prestamos
    * @queryParam sortBy Vector de ordenamiento. Example: [name]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [false]
    * @queryParam per_page Número de datos por página. Example: 10
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/module/index.200.json
    */
    public function index(Request $request)
    {
        $filter = $request->has('name') ? ['name' => $request->name] : [];
        if (!Auth::user()->hasRole('TE-admin')) {
            $filter['id'] = Auth::user()->modules;
        }
        return Util::search_sort(new Module(), $request, $filter);
    }

    /**
    * Detalle de módulo
    * Devuelve el detalle de un módulo mediante su ID
    * @urlParam module required ID de afiliado. Example: 3
    * @authenticated
    * @responseFile responses/module/show.200.json
    */
    public function show(Module $module)
    {
        return $module;
    }

    /**
    * Roles asociados al módulo
    * Devuelve la lista de roles asociados a un módulo
    * @urlParam module required ID del módulo. Example: 6
    * @authenticated
    * @responseFile responses/module/get_roles.200.json
    */
    public function get_roles(Module $module)
    {
        return $module->roles()->get();
    }

    /**
    * Tipos de trámite asociados al módulo
    * Devuelve la lista de tipos de trámites asociados a un módulo
    * @urlParam module required ID del módulo. Example: 3
    * @authenticated
    * @responseFile responses/module/get_procedure_types.200.json
    */
    public function get_procedure_types(Module $module)
    {
        return $module->procedure_types;
    }

    /**
    * Tipos de observaciones asociados al módulo
    * Devuelve la lista de tipos de observaciones asociados a un módulo
    * @urlParam module required ID del módulo. Example: 6
    * @authenticated
    * @responseFile responses/module/get_observation_types.200.json
    */
    public function get_observation_types(Module $module)
    {
        return $module->observation_types;
    }
    /**
    * Tipos de modalidades de préstamo asociados al módulo
    * Devuelve la lista de tipos de modalidades de préstamo asociados a un módulo
    * @urlParam module required ID del módulo. Example: 6
    * @authenticated
    * @responseFile responses/module/get_modality_types.200.json
    */
    public function get_modality_types(Module $module)
    {
        return $module->procedure_types()->where('name','LIKE', '%Préstamo%')->get();
    }

    /**
    * Tipos de amortizaciones de préstamo asociados al módulo
    * Devuelve la lista de tipos de amortizaciones de préstamo asociados a un módulo
    * @urlParam module required ID del módulo. Example: 6
    * @authenticated
    * @responseFile responses/module/get_amortization_types.200.json
    */
    public function get_amortization_types(Module $module)
    {
        return $module->procedure_types()->where('name','LIKE', '%Amortización%')->get();
    }

    public function get_workflows(Module $module)
    {
        return $module->workflows;
    }

    public function get_loan_workflows(Module $module)
    {
        return $module->loan_workflows;
    }
    
    public function get_payment_workflows(Module $module)
    {
        return $module->amortization_workflows;
    }
}
