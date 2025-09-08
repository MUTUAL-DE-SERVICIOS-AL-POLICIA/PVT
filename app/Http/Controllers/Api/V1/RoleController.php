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
    * @queryParam name Filtrar roles por nombre. Example: PRE-recepcion
    * @authenticated
    * @responseFile responses/role/index.200.json
    */
    public function index(Request $request)
    {
        $query = Role::where('module_id',6)->orderBy('name');
        if ($request->has('name')) $query = $query->whereName($request->name);
        return $query->get();
    }

    /**
    * Detalle de rol
    * Devuelve el detalle de un rol mediante su ID
    * @urlParam role required ID de rol. Example: 42
    * @authenticated
    * @responseFile responses/role/show.200.json
    */
    public function show(Role $role)
    {
        return $role;
    }

    /**
    * Obtener permisos de rol
    * Devuelve un listado con los IDs de los permisos asignados al rol
    * @urlParam role required ID de rol. Example: 73
    * @authenticated
    * @responseFile responses/role/get_permissions.200.json
    */
    public function get_permissions(Role $role) {
        return response()->json([
            'permissions' => $role->permissions()->where('name', '!=', null)->get()->pluck('id')
        ]);
    }

    /**
    * Establecer permisos a un rol
    * Asignar permisos a un rol determinado
    * @urlParam role required ID de rol. Example: 40
    * @bodyParam permissions array required Listado de IDs de permisos.
    * @bodyParam permissions[*] integer required ID de permiso. Example: 31
    * @authenticated
    * @responseFile responses/role/set_permissions.200.json
    */
    public function set_permissions(Request $request, Role $role) {
        $request->validate([
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);
        $role->syncPermissions($request->permissions);
        return response()->json([
            'permissions' => $role->permissions()->where('name', '!=', null)->get()->pluck('id')
        ]);
    }
}