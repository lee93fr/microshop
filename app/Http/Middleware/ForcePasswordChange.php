<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            // Allow the forced change route and logout through
            if ($request->routeIs('password.forced.show', 'password.forced.update', 'logout')) {
                return $next($request);
            }

            return redirect()->route('password.forced.show')
                ->with('warning', 'Vous devez choisir un nouveau mot de passe avant de continuer.');
        }

        return $next($request);
    }
}
