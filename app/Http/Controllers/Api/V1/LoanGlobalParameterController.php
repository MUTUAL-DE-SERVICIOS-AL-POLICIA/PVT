<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\LoanGlobalParameter;
use Util;
use App\LoanProcedure;
use Carbon;

/** @group Parámetros de Préstamos
* Datos de los Parámetros para trámites de préstamos
*/
class LoanGlobalParameterController extends Controller
{
    /**
    * Lista de Parámetros de Préstamos
    * Devuelve el listado con los datos paginados
    * @queryParam search Parámetro de búsqueda. Example: 20
    * @queryParam sortBy Vector de ordenamiento. Example: []
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [true]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/loan_global_parameter/index.200.json
    */
    public function index(Request $request)
    {
        $loan_procedure = LoanProcedure::where('is_enable', true)->first()->id;
        return LoanGlobalParameter::where('loan_procedure_id', $loan_procedure)->paginate(1);
    }

    /**
    * Detalle de Parámetros de Préstamo
    * Devuelve el detalle de un parametro global de préstamo mediante su ID
    * @urlParam loan_global_parameter required ID de parametro global de préstamo. Example: 1
    * @authenticated
    * @responseFile responses/loan_global_parameter/show.200.json
    */
    public function show(LoanGlobalParameter $loan_global_parameter)
    {
        return $loan_global_parameter;
    }

    /**
    * Actualizar Parámetros Globales de Préstamo
    * Actualizar datos principales de Parámetros Globales de Préstamo
    * @urlParam loan_global_parameter required ID de Parámetros Globales de Préstamo. Example: 1
    * @bodyParam offset_day integer required fecha de corte. Example: 8
    * @bodyParam livelihood_amount integer required monto de subsistencia. Example: 500
    * @bodyParam day_current_interest integer required Dias de interes corriente de un mes. Example: 31    
    * @authenticated
    * @responseFile responses/loan_global_parameter/update.200.json
    */
    public function update(Request $request, LoanGlobalParameter $loan_global_parameter)
    {
        $loan_global_parameter->fill($request->all());
        $loan_global_parameter->save();
        return  $loan_global_parameter;
    }

    /**
    * Obtener el ultimo parametro global de un Préstamo
    * Obtiene el ultimo parametro global registrado
    * @urlParam loan_global_parameter required ID de Parámetros Globales de Préstamo. Example: 1
    * @authenticated
    * @responseFile responses/loan_global_parameter/get_last_global_parameter.200.json
    */
    public function get_last_global_parameter()
    {
        $loan_global_parameter = LoanGlobalParameter::latest()->first();
        return  $loan_global_parameter;
    }
}
