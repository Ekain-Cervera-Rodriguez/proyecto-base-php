<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BitacoraAcceso;
use App\Models\RoleUser;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticateController extends Controller
{

    public function getAccessToken(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$user = Sentinel::authenticate($credentials)) {
                return response()->unauthorized('Usuario y/o contraseña incorrectos');
            }

            $roleUser = RoleUser::query()->where('user_id', $user->id)->first();
            $role = Sentinel::findRoleById($roleUser->role_id);

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->unauthorized('Usuario eliminado');
            }

            BitacoraAcceso::query()->create([
                'user_id' => $user->id,
                'descripcion' => "El usuario $user->nombre $user->apellido_paterno $user->apellido_materno ha iniciado sesión.",
            ]);

            return response()->json(compact('user', 'role', 'token'));
        } catch (ThrottlingException $e) {
            return response()->tooManyRequests('Demasiados intentos fallidos, intenta de nuevo más tarde.');
        } catch (JWTException $e) {
            return response()->json(['error' => 'No se pudo crear el token.'], 500);
        }
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshAccessToken()
    {
        return response()->json(['token' => auth()->refresh()]);
    }


}
