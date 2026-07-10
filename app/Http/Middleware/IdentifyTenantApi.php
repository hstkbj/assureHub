<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Tenancy;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrairement au web (identification par sous-domaine), une API consommée
 * par une app mobile/SPA identifie mieux le tenant via un header explicite.
 *
 * Le client (app mobile, front SPA) doit envoyer sur CHAQUE requête :
 *   X-Tenant: nsia-assurance
 *
 * Enregistrez ce middleware sous l'alias 'tenant.api' dans bootstrap/app.php :
 *   $middleware->alias(['tenant.api' => \App\Http\Middleware\IdentifyTenantApi::class]);
 */
class IdentifyTenantApi
{
    public function __construct(protected Tenancy $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant');

        if (!$tenantId) {
            return response()->json([
                'message' => 'Le header X-Tenant est requis pour identifier votre entreprise.'
            ], 400);
        }

        try {
            $this->tenancy->initialize($tenantId);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Entreprise introuvable ou inactive.'
            ], 404);
        }

        return $next($request);
    }
}
