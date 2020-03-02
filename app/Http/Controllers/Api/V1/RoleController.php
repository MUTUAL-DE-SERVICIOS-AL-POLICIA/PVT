<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Role;

/** @group Roles
* Datos de los roles disponibles en el sistema
*/
class RoleController extends Controller
{
    /**
    * Lista de roles
    * Devuelve el listado de los roles disponibles en el sistema
    * @queryParam name Filtrar roles por nombre. Example: PRE-area-de-recepcion
    * @authenticated
    * @response
    * [
    *     {
    *         "id": 7,
    *         "module_id": 9,
    *         "display_name": "Área de Contabilidad",
    *         "action": "Comprobante Realizado",
    *         "created_at": "2017-06-01 10:41:03",
    *         "updated_at": "2019-10-11 16:45:43",
    *         "correlative": null,
    *         "name": "AF-area-de-contabilidad"
    *     }, {}
    * ]
    */
    public function index(Request $request)
    {
        $query = Role::orderBy('name');
        if ($request->has('name')) $query = $query->whereName($request->name);
        return $query->get();
    }

    /**
    * Detalle de rol
    * Devuelve el detalle de un rol mediante su ID
    * @urlParam role required ID de rol. Example: 42
    * @authenticated
    * @response
    * {
    *     "id": 42,
    *     "module_id": 4,
    *     "display_name": "Área de Archivo",
    *     "action": "Revisado",
    *     "created_at": "2018-08-20 08:07:15",
    *     "updated_at": "2019-10-11 16:45:43",
    *     "correlative": null,
    *     "name": "CAM-area-de-archivo"
    * }
    */
    public function show($id)
    {
        return Role::findOrFail($id);
    }

    /**
    * Obtener permisos de rol
    * Devuelve un listado con los IDs de los permisos asignados al rol
    * @urlParam id required ID de rol. Example: 73
    * @authenticated
    * @response
    * [
    *     574,
    *     579,
    *     585
    * ]
    */
    public function get_permissions($id) {
        $role = Role::findOrFail($id);
        return $role->permissions()->where('name', '!=', null)->get()->pluck('id');
    }

    /**
    * Establecer permisos a un rol
    * Asignar permisos a un rol determinado
    * @urlParam id required ID de rol. Example: 40
    * @authenticated
    * @response
    * [
    *     574,
    *     579,
    *     585
    * ]
    */
    public function set_permissions(Request $request, $id) {
        $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);
        $role = Role::findOrFail($id);
        $role->syncPermissions($request->permissions);
        return $role->permissions()->where('name', '!=', null)->get()->pluck('id');
    }
}
