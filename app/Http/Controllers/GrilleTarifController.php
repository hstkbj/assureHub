<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\GrilleTarif;
use Illuminate\Http\Request;

class GrilleTarifController extends Controller
{
    public function index(Request $request)
    {
        $query = GrilleTarif::with('categorie');

        $query->when($request->categorie_id, fn($q) => $q->where('categorie_id', $request->categorie_id));
        $query->when($request->carburant, fn($q) => $q->where('carburant', $request->carburant));
        $query->when($request->duree, fn($q) => $q->where('duree', $request->duree));

        return response()->json($query->paginate($request->per_page ?? 30));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categorie_id'        => 'required|exists:categories,id',
            'cv_min'              => 'required|integer|min:0',
            'cv_max'              => 'required|integer|gte:cv_min',
            'carburant'           => 'required|string|max:20',
            'type_client'         => 'required|string|max:30',
            'duree'               => 'required|string|max:20',
            'place'               => 'nullable|string|max:20',
            'poids'               => 'nullable|string|max:20',
            'montant'             => 'required|numeric|min:0',
            'date_debut_validite' => 'nullable|date',
            'date_fin_validite'   => 'nullable|date|after:date_debut_validite',
        ]);

        $tarif = GrilleTarif::create($validated);

        return response()->json($tarif, 201);
    }

    public function show(GrilleTarif $grilleTarif)
    {
        return response()->json($grilleTarif->load('categorie'));
    }

    public function update(Request $request, GrilleTarif $grilleTarif)
    {
        $validated = $request->validate([
            'cv_min'              => 'sometimes|integer|min:0',
            'cv_max'              => 'sometimes|integer|gte:cv_min',
            'montant'             => 'sometimes|numeric|min:0',
            'date_fin_validite'   => 'nullable|date',
        ]);

        $grilleTarif->update($validated);

        return response()->json($grilleTarif);
    }

    public function destroy(GrilleTarif $grilleTarif)
    {
        $grilleTarif->delete();

        return response()->json(null, 204);
    }
}
