<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        return response()->json(Categorie::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:100|unique:categories,nom',
            'description' => 'nullable|string',
        ]);

        $categorie = Categorie::create($validated);

        return response()->json($categorie, 201);
    }

    public function show(Categorie $categorie)
    {
        return response()->json($categorie->load('tarifs'));
    }

    public function update(Request $request, Categorie $categorie)
    {
        $validated = $request->validate([
            'nom'         => 'sometimes|string|max:100|unique:categories,nom,' . $categorie->id,
            'description' => 'nullable|string',
        ]);

        $categorie->update($validated);

        return response()->json($categorie);
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->tarifs()->exists()) {
            return response()->json([
                'message' => "Impossible de supprimer : des tarifs sont rattachés à cette catégorie."
            ], 409);
        }

        $categorie->delete();

        return response()->json(null, 204);
    }
}
