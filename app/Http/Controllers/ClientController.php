<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $connecte = $request->user();

        $query = Client::query();

        if (in_array($connecte->role, ['admin_agence', 'commercial'])) {
            $query->where('agence_id', $connecte->agence_id);
        } elseif ($request->agence_id) {
            $query->where('agence_id', $request->agence_id);
        }

        $query->when($request->recherche, function ($q) use ($request) {
            $q->where('nom', 'like', "%{$request->recherche}%")
              ->orWhere('prenom', 'like', "%{$request->recherche}%")
              ->orWhere('telephone', 'like', "%{$request->recherche}%");
        });

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $connecte = $request->user();

        $validated = $request->validate([
            'agence_id'      => 'required|exists:agences,id',
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'genre'          => 'nullable|in:M,F',
            'date_naissance' => 'nullable|date',
            'telephone'      => 'required|string|unique:clients,telephone',
            'email'          => 'nullable|email',
            'npi'            => 'nullable|string|unique:clients,npi',
            'ville'          => 'nullable|string|max:100',
            'quartier'       => 'nullable|string|max:100',
        ]);

        if (in_array($connecte->role, ['admin_agence', 'commercial'])
            && (int) $validated['agence_id'] !== $connecte->agence_id) {
            return response()->json(['message' => "Vous ne pouvez enregistrer un client que dans votre propre agence."], 403);
        }

        $validated['user_id'] = $connecte->id;

        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    public function show(Request $request, Client $client)
    {
        $this->verifierAcces($request, $client);

        return response()->json($client->load('assurances'));
    }

    public function update(Request $request, Client $client)
    {
        $this->verifierAcces($request, $client);

        $validated = $request->validate([
            'nom'            => 'sometimes|string|max:100',
            'prenom'         => 'sometimes|string|max:100',
            'telephone'      => 'sometimes|string|unique:clients,telephone,' . $client->id,
            'email'          => 'nullable|email',
            'ville'          => 'nullable|string|max:100',
            'quartier'       => 'nullable|string|max:100',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    public function destroy(Request $request, Client $client)
    {
        $this->verifierAcces($request, $client);

        $client->delete();

        return response()->json(null, 204);
    }

    protected function verifierAcces(Request $request, Client $client): void
    {
        $connecte = $request->user();

        if (in_array($connecte->role, ['admin_agence', 'commercial'])
            && $client->agence_id !== $connecte->agence_id) {
            abort(403, "Vous n'avez pas accès à ce client.");
        }
    }
}
