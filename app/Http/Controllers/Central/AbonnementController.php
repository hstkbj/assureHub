<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use Illuminate\Http\Request;

class AbonnementController extends Controller
{
    public function index(Request $request)
    {
        $query = Abonnement::with(['tenant', 'plan']);

        $query->when($request->tenant_id, fn($q) => $q->where('tenant_id', $request->tenant_id));
        $query->when($request->statut, fn($q) => $q->where('statut', $request->statut));

        return response()->json($query->latest()->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id'   => 'required|exists:tenants,id',
            'plan_id'     => 'required|exists:plans,id',
            'date_debut'  => 'required|date',
            'date_fin'    => 'nullable|date|after:date_debut',
            'periodicite' => 'required|in:mensuel,annuel',
        ]);

        // On désactive un éventuel abonnement précédemment actif pour ce tenant
        Abonnement::where('tenant_id', $validated['tenant_id'])
            ->where('statut', 'actif')
            ->update(['statut' => 'expire']);

        $validated['statut'] = 'actif';

        $abonnement = Abonnement::create($validated);

        return response()->json($abonnement->load('plan'), 201);
    }

    public function show(Abonnement $abonnement)
    {
        return response()->json($abonnement->load(['tenant', 'plan', 'factures']));
    }

    public function update(Request $request, Abonnement $abonnement)
    {
        $validated = $request->validate([
            'date_fin' => 'nullable|date',
            'statut'   => 'sometimes|in:actif,expire,suspendu,annule',
        ]);

        $abonnement->update($validated);

        return response()->json($abonnement);
    }

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
