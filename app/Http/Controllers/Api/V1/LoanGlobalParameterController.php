<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\LoanGlobalParameter;
use Util;

/** @group Préstamos
* Datos de los Parametros Globales de préstamos
*/
class LoanGlobalParameterController extends Controller
{
    /**
    * Lista de Parametros Globales de Préstamos
    * Devuelve el listado con los datos paginados
    * @queryParam search Parámetro de búsqueda. Example: 20
    * @queryParam sortBy Vector de ordenamiento. Example: []
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [true]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @response
    * {
    *    "current_page": 1,
    *    "data": [
    *        {
    *            "id": 1,
    *            "offset_day": 7,
    *            "livelihood_amount": 510,
    *            "created_at": "2020-02-27 16:29:10",
    *            "updated_at": "2020-02-27 16:29:10"
    *        }
    *    ],
    *    "first_page_url": "http://192.168.2.242/api/v1/global_parameter?page=1",
    *    "from": 1,
    *    "last_page": 1,
    *    "last_page_url": "http://192.168.2.242/api/v1/global_parameter?page=1",
    *    "next_page_url": null,
    *    "path": "http://192.168.2.242/api/v1/global_parameter",
    *    "per_page": 10,
    *    "prev_page_url": null,
    *    "to": 1,
    *    "total": 1
    * }
    */
    public function index(Request $request)
    {
        return Util::search_sort(new LoanGlobalParameter(), $request);   
    }

    /**
    * Nuevo Parametro Global de Préstamo
    * Inserta nuevo Parametro Global de préstamo
    * @bodyParam offset_day integer required fecha de corte. Example: 7
    * @bodyParam livelihood_amount integer required monto de subsistencia. Example: 500
    * @authenticated
    * @response
    * {
    *     "offset_day":7, 
    *     "livelihood_amount":500 
    * }
    */
    public function store(Request $request)
    {
        return LoanGlobalParameter::create($request->all());
    }

    /**
    * Detalle de Parametros Globales de Préstamo
    * Devuelve el detalle de un parametro global de préstamo mediante su ID
    * @urlParam global_parameter required ID de parametro global de préstamo. Example: 1
    * @response
    * {
    *    "id": 1,
    *    "offset_day": 7,
    *    "livelihood_amount": 510,
    *    "created_at": "2020-02-27 16:29:10",
    *   "updated_at": "2020-02-27 16:29:10"
    * }
    */
    public function show($id)
    {
        return LoanGlobalParameter::findOrFail($id);
    }

    /**
    * Actualizar Parametros Globales de Préstamo
    * Actualizar datos principales de Parametros Globales de Préstamo
    * @urlParam id required ID de Parametros Globales de Préstamo. Example: 1
    * @bodyParam offset_day integer required fecha de corte. Example: 8
    * @bodyParam livelihood_amount integer required monto de subsistencia. Example: 500
    * @authenticated
    * @response
    * {
    *    "id": 1,
    *    "offset_day": 8,
    *    "livelihood_amount": 500,
    *    "created_at": "2020-02-27 16:29:10",
    *    "updated_at": "2020-02-27 18:22:37"
    * }
    */
    public function update(Request $request, $id)
    {
        $parameter = LoanGlobalParameter::findOrFail($id);
        $parameter->fill($request->all());
        $parameter->save();
        return  $parameter;
    }

    /**
    * Eliminar un Parametro Global de Préstamo
    * @urlParam id required ID de Parametro Global de Préstamo. Example: 5
    * @authenticated
    * @response
    * {
    *    "id": 5,
    *    "offset_day": 7,
    *    "livelihood_amount": 500,
    *    "created_at": "2020-02-27 18:24:43",
    *    "updated_at": "2020-02-27 18:24:43"
    * }
    */
    public function destroy($id)
    {
        $parameter = LoanGlobalParameter::findOrFail($id);
        $parameter->delete();
        return $parameter;
    }
}
