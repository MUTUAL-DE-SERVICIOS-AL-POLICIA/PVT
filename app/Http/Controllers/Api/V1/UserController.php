<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserForm;
use App\User;
use Ldap;
use Util;

/** @group Usuarios
* Datos de los usuarios y métodos para obtener y establecer sus relaciones
*/
class UserController extends Controller
{
    private $db_users = ['admin', 'asistente'];

    /**
    * Lista de usuarios
    * Devuelve el listado con los datos paginados
    * @queryParam active Usuarios activos(1) o inactivos(0). Example: 1
    * @queryParam search Parámetro de búsqueda. Example: TORRE
    * @queryParam sortBy Vector de ordenamiento. Example: [last_name]
    * @queryParam sortDesc Vector de orden descendente(true) o ascendente(false). Example: [false]
    * @queryParam per_page Número de datos por página. Example: 8
    * @queryParam page Número de página. Example: 1
    * @authenticated
    * @responseFile responses/user/index.200.json
    */
    public function index(Request $request)
    {
        $filter = $request->has('active') ? [
            'active' => $request->boolean('active'),
            'username' => ['!=', Auth::user()->username],
            'username' => ['!=', 'admin']
        ] : [];
        return Util::search_sort(new User(), $request, $filter);
    }

    /**
    * Nuevo usuario
    * Inserta nuevo usuario
    * @bodyParam first_name string required Nombres. Example: JUAN
    * @bodyParam last_name string required Apellidos. Example: TORRES
    * @bodyParam username string required Nombre de usuario. Example: jtorres
    * @bodyParam password string required Contraseña: as$32rW!%V
    * @bodyParam position string Cargo. Example: PROFESIONAL DE COBRANZAS
    * @authenticated
    * @responseFile responses/user/store.200.json
    */
    public function store(UserForm $request)
    {
        if (env("LDAP_AUTHENTICATION")) {
            $ldap = new Ldap();
            if (is_null($ldap->get_entry($request->uid, 'uid'))) {
                abort(404);
            }
        }
        $user = new User;
        //$user->city_id = 1;
        $user->first_name = $request->givenname;
        $user->last_name = $request->sn;
        $user->username = $request->uid;
        $user->password = Hash::make($user->uid);
        $user->position = $request->title;
        //$user->is_commission = false;
        //$user->phone = 54654;
        $user->active = true;
        $user->save();
        return $user;
        //return User::create($user->toArray());
    }

    /**
    * Detalle de usuario
    * @urlParam user required ID de usuario. Example: 138
    * @authenticated
    * @responseFile responses/user/show.200.json
    */
    public function show(User $user)
    {
        return $user;
    }

    /**
    * Actualizar usuario
    * @urlParam user required ID de usuario. Example: 138
    * @bodyParam first_name string Nombres. Example: JUAN
    * @bodyParam last_name string Apellidos. Example: TORRES
    * @bodyParam username string Nombre de usuario. Example: jtorres
    * @bodyParam password string Contraseña: as$32rW!%V
    * @bodyParam position string Cargo. Example: PROFESIONAL DE COBRANZAS
    * @authenticated
    * @responseFile responses/user/update.200.json
    */
    public function update(UserForm $request, User $user)
    {
        if ($request->has('password') && !env("LDAP_AUTHENTICATION")) {
            if ($request->has('old_password')) {
                if (!(Auth::user()->id == $id && Hash::check($request->input('old_password'), $user->password))) {
                    return response()->json([
                        'message' => 'Invalid',
                        'errors' => [
                            'type' => ['Contraseña anterior incorrecta'],
                        ],
                    ], 409);
                }
            } else {
                unset($request['password']);
            }
        }
        $user->fill($request->all());
        $user->save();
        return $user;
    }

    /**
    * Eliminar usuario
    * Elimina un usuario solo en caso de que no tenga acciones registradas
    * @urlParam user required ID de usuario. Example: 138
    * @authenticated
    * @responseFile responses/user/destroy.200.json
    */
    public function destroy(User $user)
    {
        if ($user->has_actions || in_array($user->username, $this->db_users)) {
            abort(403, 'El usuario aún tiene acciones registradas');
        } else {
            $user->records()->delete();
            $user->delete();
            return $user;
        }
    }

    /**
    * Obtener permisos de usuario
    * Devuelve un listado con los IDs de los permisos asignados al usuario
    * @urlParam user required ID de usuario. Example: 69
    * @authenticated
    * @response
    * @responseFile responses/user/get_permissions.200.json
    */
    public function get_permissions(User $user)
    {
        return response()->json([
            'permissions' => $user->allPermissions()->pluck('id')
        ]);
    }

    /**
    * Obtener roles de usuario
    * Devuelve un listado con los IDs de los roles asignados al usuario
    * @urlParam user required ID de usuario. Example: 69
    * @authenticated
    * @responseFile responses/user/get_roles.200.json
    */
    public function get_roles(User $user)
    {
        if (Auth::user()->id == $user->id || Auth::user()->can('show-role')) {
            return response()->json([
                'roles' => $user->roles()->pluck('id')
            ]);
        }
        abort(401);
    }

    /**
    * Establecer roles de usuario
    * Asignar roles a un usuario
    * @urlParam user required ID de usuario. Example: 69
    * @bodyParam roles array required Vector con los objetos de roles. Example: [37,42,7]
    * @authenticated
    * @responseFile responses/user/set_roles.200.json
    */
    public function set_roles(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);
        $user->syncRoles($request->roles);
        return response()->json([
            'roles' => $user->roles()->pluck('id')
        ]);
    }

    /** @group Ldap
    * Usuarios no registrados
    * Devuelve un listado con datos de los usuarios del Active Directory que no se encuentran registrados en el sistema
    * @authenticated
    * @responseFile responses/ldap/unregistered_users.200.json
    */
    public function unregistered_users()
    {
        $ldap = new Ldap();
        $unregistered_users = collect($ldap->get_entries())->pluck('uid')->diff(User::get()->pluck('username')->all());
        $items = [];
        foreach($unregistered_users as $user) {
            array_push($items, $ldap->get_entry($user, 'uid'));
        }
        return response()->json(collect($items)->sortBy('sn')->values());
    }

    /** @group Ldap
    * Sincronizar usuarios
    * Cambia el estado de activo a inactivo para los usuarios del sistema que no se encuentren en el Active Directory
    * @authenticated
    * @responseFile responses/ldap/synchronize_users.200.json
    */
    public function synchronize_users()
    {
        $ldap = new Ldap();
        $discharged_users = collect(User::where(function($query) {
            foreach($this->db_users as $user) {
                $query->where('username', '!=', $user);
            }
        })->whereActive(true)->get()->pluck('username')->all())->diff(collect($ldap->get_entries())->pluck('uid'));
        $items = [];
        foreach($discharged_users as $user) {
            array_push($items, User::whereUsername($user)->first());
        }
        return response()->json(collect($items)->sortBy('last_name')->values());
    }
    /**
    * Obtener roles y permisos de usuario
    * Devuelve un listado de roles y permisos asignados al usuario de la sesion
    * @urlParam module_id required ID de de Modulo . Example: 6
    * @authenticated
    * @responseFile responses/user/get_roles_permission.200.json
    */
    public function role_permision(Request $module_id)
    {
        $user =  Auth::user();
        $roles = $user->roles;
        $items = [];
          foreach($roles as $role){
              array_push($items,$role->module,$role->permissions);
          }
        $role_permission = [];
          foreach($roles as $role){
              array_push($role_permission,$role);
          }
          return $role_permission;
      }  
}