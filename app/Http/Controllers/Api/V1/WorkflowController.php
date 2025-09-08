<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Workflow;
use App\ObservationType;
use Util;

/** @group Módulos
* Flujos disponibles en el sistema
*/
class WorkflowController extends Controller
{
    public static function append_data(Workflow $workflow)
    {
        $workflow->module = $workflow->module;
        return $workflow;
    }

    public function index()
    {
        $workflows = Workflow::where('module_id', 6)->get();
        $workflows->transform(function ($workflow) {
            return self::append_data($workflow);
        });
        return $workflows;
    }    

    public function show(Workflow $workflow)
    {
        return $this->append_data($workflow);
    }

    public function update(Request $request, Workflow $workflow)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'shortened' => 'required|string',
        ]);
        $workflow->fill($request->all());
        $workflow->save();
        return response()->json(self::append_data($workflow), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'shortened' => 'required|string',
        ]);

        $workflow = Workflow::create([
            'module_id' => 6,
            'name' => $request->name,
            'shortened' => $request->shortened,
        ]);
        return response()->json(['message' => 'Registro creado exitosamente', 'data' => $workflow], 201);
    }

    public function destroy(Workflow $workflow)
    {
        if($workflow->procedure_modality()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el flujo porque tiene modalidades asociados'
            ], 409);
        }
        $workflow->delete();
        return response()->json([
            'message' => 'Registro eliminado correctamente'
        ], 200);
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

    public function get_workflows(Module $module)
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
}
