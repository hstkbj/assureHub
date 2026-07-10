<?php

namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgenceController extends Controller
{

    public function index(Request $request)
    {
        return response()->json(Agence::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom_agence'  => 'required|string|max:150',
            'code_agence' => 'nullable|string|max:20|unique:agences,code_agence',
            'responsable' => 'nullable|string|max:150',
            'telephone'   => 'nullable|string|max:20',
            'ville'       => 'nullable|string|max:100',
            'adresse'     => 'nullable|string|max:255',
            'quartier'    => 'nullable|string|max:100',
        ]);

        $agence = Agence::create([...$validated, 'statut' => 'actif']);

        return response()->json($agence, 201);
    }

    public function show(Agence $agence)
    {
        return response()->json($agence->load('users', 'clients'));
    }

    public function update(Request $request, Agence $agence)
    {
        $validated = $request->validate([
            'nom_agence'  => 'sometimes|string|max:150',
            'responsable' => 'nullable|string|max:150',
            'telephone'   => 'nullable|string|max:20',
            'ville'       => 'nullable|string|max:100',
            'statut'      => 'sometimes|in:actif,inactif',
        ]);

        $agence->update($validated);

        return response()->json($agence);
    }

    public function destroy(Agence $agence)
    {
        $agence->delete();

        return response()->json(null, 204);
    }

}
