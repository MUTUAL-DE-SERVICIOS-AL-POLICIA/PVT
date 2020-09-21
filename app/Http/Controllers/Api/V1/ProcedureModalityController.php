<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ProcedureModality;
use Util;

/** @group Modalidad de trámites
* Trámites agrupados por modalidad de acuerdo a filtro de tipo de procedimiento
*/
class ProcedureModalityController extends Controller
{
    /**
    * Lista de modalidad de trámites
    * Devuelve el listado con los datos paginados
    * @queryParam procedure_type_id Filtro de ID del tipo de procedimiento. Example: 9
    * @queryParam sortBy Vector de ordenamiento. Example: [name]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [false]
    * @queryParam per_page Número de datos por página. Example: 10
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/procedure_modality/index.200.json
     */
    public function index(Request $request)
    {
        $filter = $request->has('procedure_type_id') ? ['procedure_type_id' => $request->procedure_type_id] : [];
        return Util::search_sort(new ProcedureModality(), $request, $filter);
    }

    /**
    * Detalle de una modalidad de trámite
    * Devuelve el detalle de una modalidad de trámite mediante su ID
    * @urlParam procedure_modality required ID de la modalidad de trámite. Example: 4
    * @authenticated
    * @responseFile responses/procedure_modality/show.200.json
    */
    public function show(ProcedureModality $procedure_modality)
    {
        return $procedure_modality;
    }

    /**
    * Requisitos para una modalidad de préstamo
    * Devuelve los documentos requeridos para cada modalidad
    * @urlParam procedure_modality ID de la modalidad. Example: 9
    * @authenticated
    * @responseFile responses/procedure_modality/get_requirements.200.json
    */
    public function get_requirements(ProcedureModality $procedure_modality) {
        return $procedure_modality->requirements_list;
    }

    /**
    * Parametros para una modalidad de préstamo
    * Devuelve los parametros para cada modalidad
    * @urlParam procedure_modality required ID de la modalidad. Example: 32
    * @authenticated
    * @responseFile responses/procedure_modality/get_loan_modality_parameter.200.json
    */
    public function get_loan_modality_parameter(ProcedureModality $procedure_modality) {
        if(!$procedure_modality->loan_modality_parameter) abort(403, 'Parametro de préstamo no encontrado');
        return $procedure_modality->loan_modality_parameter;
    }
}