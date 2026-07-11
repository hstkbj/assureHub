<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Plan;
use Illuminate\Http\Request;

class AbonnementController extends Controller
{
    public function monAbonnement(Request $request)
    {
        $tenant = tenant();

        $abonnement = Abonnement::with('plan')
            ->where('tenant_id', $tenant->id)
            ->where('statut', 'actif')
            ->latest()
            ->first();

        return response()->json($abonnement);
    }

    public function souscrire(Request $request)
    {
        if ($request->user()->role !== 'admin_entreprise') {
            return response()->json([
                'message' => "Seul l'administrateur de l'entreprise peut gérer l'abonnement."
            ], 403);
        }

        $validated = $request->validate([
            'plan_id'     => 'required|exists:plans,id',
            'periodicite' => 'required|in:mensuel,annuel',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        if (!$plan->actif) {
            return response()->json(['message' => "Ce plan n'est plus disponible."], 422);
        }

        $tenant = tenant();

        Abonnement::where('tenant_id', $tenant->id)
            ->where('statut', 'actif')
            ->update(['statut' => 'expire']);

        $dateFin = $validated['periodicite'] === 'annuel'
            ? now()->addYear()
            : now()->addMonth();

        $abonnement = Abonnement::create([
            'tenant_id'   => $tenant->id,
            'plan_id'     => $plan->id,
            'date_debut'  => now(),
            'date_fin'    => $dateFin,
            'periodicite' => $validated['periodicite'],
            'statut'      => 'actif',
        ]);

        return response()->json($abonnement->load('plan'), 201);
    }
}
