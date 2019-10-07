<?php

namespace App\Http\Controllers\Api\V1;

use JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use Ldap;

/** @resource Authenticate
 *
 * Resource to authenticate via username/password credentials
 */

class AuthController extends Controller
{
  /**
   * Get a JWT via given credentials.
   *
   * Login, return a JsonWebToken to request as "Bearer" Authorization header
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(AuthForm $request)
  {
    $token = false;
    if ($request->username == 'admin') {
      $token = Auth::attempt($request->all());
    } else {
      $user = User::whereUsername($request->username)->first();
      if ($user) {
        if (!$user->active) {
          return response()->json([
            'message' => 'No autorizado',
            'errors' => [
              'type' => ['Usuario desactivado'],
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
                $ldap->unbind();
              }
            }
          }
        }
      }
    }
    if ($token) {
      \Log::info("Usuario ".Auth::user()->username." autenticado desde la dirección ".request()->ip());
      return response()->json([
        'message' => 'Authenticated successfully',
        'token' => $token,
        'token_type' => 'Bearer',
        'roles' => array_values(array_filter(Auth::user()->roles()->pluck('name')->toArray())),
        'permissions' => array_values(array_filter(Auth::user()->allPermissions()->pluck('name')->toArray()))
      ], 200);
    }
    return response()->json([
      'message' => 'No autorizado',
      'errors' => [
        'type' => ['Usuario o contraseña incorrectos'],
      ],
    ], 401);
  }

  /**
   * Get the authenticated User.
   *
   * Login, return a JsonWebToken to request as "Bearer" Authorization header
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function show()
  {
    return response()->json(Auth::user());
  }

  /**
   * Log the user out (Invalidate the token).
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function destroy()
  {
    Auth::logout();
    return response()->json([
      'message' => 'Logged out successfully',
    ], 201);
  }

  /**
   * Refresh a token.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function update()
  {
    return response()->json([
      'message' => 'Token refreshed',
      'token' => Auth::refresh(),
      'token_type' => 'Bearer'
    ], 200);
  }

  public function guard()
  {
    return Auth::Guard('api');
  }
}
