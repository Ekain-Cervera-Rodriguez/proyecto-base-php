<?php

namespace App\Http\Middleware;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SentinelACL
{

    protected Guard $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next)
    {

        $currentRouteName = $request->route()->getName();

        $usuario = Auth::user();

        if ($usuario === null) {
            return response()->unauthorized('Usuario no encontrado');
        }

        $user = Sentinel::findById($usuario->id);

        if (!$user->hasAccess($currentRouteName)) {
            return response()->forbidden('No tiene permiso para acceder al módulo.');
        }

        return $next($request);
    }
}
