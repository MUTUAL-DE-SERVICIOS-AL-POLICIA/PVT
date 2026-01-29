<?php

namespace App\Http\Controllers\Api\V1;

use JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use Ldap;
use Illuminate\Http\Request;

/** @group Autenticación
* Abre el acceso a la aplicación mediante llaves JSON WebToken de tipo Bearer
*/
class AuthController extends Controller
{
    /**
    * Usuario autenticado
    * Devuelve el usuario actualmente autenticado
    * @authenticated
    * @responseFile responses/auth/index.200.json
    */
    public function index()
    {
        return Auth::user();
    }

    /**
    * Obtener token
    * El token servirá para consultar rutas protegidas
    * @bodyParam username string required Nombre de usuario. Example: admin
    * @bodyParam password string required Contraseña. Example: admin
    * @responseFile responses/auth/store.200.json
    */
    public function store(AuthForm $request)
    {
        $token = false;
        if (env('APP_ENV') != 'production') {
            $request->merge([
                'password' => $request->username
            ]);
            $user = User::whereUsername($request->username)->first();
            if ($user) {
                $user->password = Hash::make($request->username);
                $user->save();
            }
        }
        if ($request->username == 'admin') {
            $token = Auth::attempt($request->all());
        } else {
            $user = User::whereUsername($request->username)->first();
            if ($user) {
                if (!$user->active) {
                    return response()->json([
                        'message' => 'No autorizado',
                        'errors' => (object)[
                            'username' => ['Usuario desactivado'],
                        ],
                    ], 401);
                } else {
                    if (!env("LDAP_AUTHENTICATION")) {
                        $token = Auth::attempt($request->all());
                    } else {
                        $ldap = new Ldap();
                        if ($ldap->verify_open_port()) {
                            if ($ldap->bind($request->username, $request->password)) {
                                if (!Hash::check($request->password, $user->password)) {
                                    $user->password = Hash::make($request->password);
                                    $user->save();
                                }
                                $token = Auth::login($user);
                            }
                        }
                    }
                }
            }
        }
        $allowed_user = Auth::user();
        if ($allowed_user) {
            $allowed_user->roles()->updateExistingPivot(
                $allowed_user->roles()->pluck('roles.id')->toArray(),
                ['role_active' => false]
            );
        }
        if ($token) {
            \Log::channel('access')->info("Usuario ".Auth::user()->username." autenticado desde la dirección ".request()->ip());
            //return $token;
            return [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 18000,
                ];
        }
        return response()->json([
            'message' => 'No autorizado',
            'errors' => (object)[
                'username' => ['Usuario o contraseña incorrectos'],
            ],
        ], 401);
    }

    /**
    * Cerrar sesión
    * El token se deshabilita
    * @authenticated
    * @responseFile responses/auth/logout.200.json
    */
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Sesión terminada',
        ], 201);
    }

    /**
    * Refrescar token
    * El token actual se deshabilita y se genera otro para alargando el tiempo de sesión
    * @authenticated
    * @responseFile responses/auth/refresh.200.json
    */
    public function refresh()
    {
        return Auth::refresh();
    }

    public function guard()
    {
        return Auth::Guard('api');
    }

    public function setActiveRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = Auth::user();
        $user->roles()->updateExistingPivot(
            $user->roles()->pluck('roles.id')->toArray(),
            ['role_active' => false]
        );
        $user->roles()->updateExistingPivot(
            $request->role_id,
            ['role_active' => true]
        );

        return response()->json([
            'message' => 'Rol activo actualizado',
        ]);
    }

    public function clearSelectedRole()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
        $user->roles()->updateExistingPivot(
            $user->roles()->pluck('roles.id')->toArray(),
            ['role_active' => false]
        );

        return response()->json([
            'message' => 'Rol activo eliminado correctamente.',
        ]);
    }
}
