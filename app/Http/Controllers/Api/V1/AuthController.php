<?php

namespace App\Http\Controllers\Api\V1;

use JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use Ldap;

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

    public function setSelectedRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer',
        ]);

        session(['selected_role_id' => $request->role_id]);

        return response()->json([
            'message' => 'Rol seleccionado correctamente.',
            'selected_role_id' => session('selected_role_id'),
        ]);
    }

    public function clearSelectedRole()
    {
        session()->forget('selected_role_id');

        return response()->json([
            'message' => 'Rol eliminado de la sesión.',
        ]);
    }
}
