<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        return response()->json(Plan::where('actif', true)->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'                  => 'required|string|max:50',
            'prix_mensuel'         => 'required|numeric|min:0',
            'prix_annuel'          => 'nullable|numeric|min:0',
            'limite_agences'       => 'nullable|integer|min:1',
            'limite_agents'        => 'nullable|integer|min:1',
            'limite_clients'       => 'nullable|integer|min:1',
            'fonctionnalites'      => 'nullable|string',
        ]);

        $validated['actif'] = true;

        $plan = Plan::create($validated);

        return response()->json($plan, 201);
    }

    public function show(Plan $plan)
    {
        return response()->json($plan);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'nom'             => 'sometimes|string|max:50',
            'prix_mensuel'    => 'sometimes|numeric|min:0',
            'prix_annuel'     => 'nullable|numeric|min:0',
            'limite_agences'  => 'nullable|integer|min:1',
            'limite_agents'   => 'nullable|integer|min:1',
            'limite_clients'  => 'nullable|integer|min:1',
            'fonctionnalites' => 'nullable|string',
            'actif'           => 'sometimes|boolean',
        ]);

        $plan->update($validated);

        return response()->json($plan);
    }

    public function destroy(Plan $plan)
    {
        // On désactive plutôt que supprimer, pour ne pas casser les abonnements existants
        $plan->update(['actif' => false]);

        return response()->json(['message' => 'Plan désactivé.']);
    }
}
