<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Mientras el usuario tenga la contraseña inicial, solo puede entrar a su
 * perfil (para cambiarla) o cerrar sesión.
 */
class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user?->must_change_password
            && ! $request->routeIs('profile.edit', 'password.update', 'logout')) {
            return redirect()->route('profile.edit')
                ->with('error', 'Por seguridad, cambia tu contraseña para continuar.');
        }

        return $next($request);
    }
}
