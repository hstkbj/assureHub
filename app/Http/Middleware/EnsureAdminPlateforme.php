<?php

namespace App\Http\Middleware;

use App\Models\AdminPlateforme;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPlateforme
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() instanceof AdminPlateforme) {
            return response()->json([
                'message' => "Accès réservé aux administrateurs de la plateforme."
            ], 403);
        }

        return $next($request);
    }
}
