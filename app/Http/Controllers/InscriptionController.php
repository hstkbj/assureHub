<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class InscriptionController extends Controller
{
    public function creerEntreprise(Request $request)
    {
        $validated = $request->validate([
            // Entreprise
            'nom_commercial'  => 'required|string|max:150',
            'numero_agrement' => 'nullable|string|max:50',
            'email'           => 'required|email|unique:tenants,email',
            'telephone'       => 'nullable|string|max:20',
            'sous_domaine'    => 'required|string|max:100|unique:tenants,sous_domaine|alpha_dash',

            // Premier utilisateur (admin_entreprise)
            'admin_nom'       => 'required|string|max:100',
            'admin_prenom'    => 'required|string|max:100',
            'admin_email'     => 'required|email',
            'admin_password'  => 'required|string|min:8',
        ]);

        $tenantId = Str::slug($validated['nom_commercial']) . '-' . Str::random(6);

        $tenant = Tenant::create([
            'id'              => $tenantId,
            'nom_commercial'  => $validated['nom_commercial'],
            'numero_agrement' => $validated['numero_agrement'] ?? null,
            'email'           => $validated['email'],
            'telephone'       => $validated['telephone'] ?? null,
            'sous_domaine'    => $validated['sous_domaine'],
            'statut'          => 'actif',
        ]);

        $tenant->domains()->create([
            'domain' => $validated['sous_domaine'] . '.localhost',
        ]);

        // Crée uniquement l'admin, SANS agence (agence_id = null),
        // puisqu'aucune agence n'existe encore à ce stade.
        $tenant->run(function () use ($validated) {
            User::create([
                'agence_id' => null,
                'nom'       => $validated['admin_nom'],
                'prenom'    => $validated['admin_prenom'],
                'email'     => $validated['admin_email'],
                'password'  => Hash::make($validated['admin_password']),
                'role'      => 'admin_entreprise',
                'statut'    => true,
            ]);
        });

        return response()->json([
            'message'   => 'Entreprise et administrateur créés avec succès.',
            'tenant_id' => $tenant->id,
            'a_utiliser_dans_header_x_tenant' => $tenant->id,
        ], 200);
    }
}
