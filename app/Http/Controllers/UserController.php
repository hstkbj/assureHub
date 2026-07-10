<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * Liste les utilisateurs.
     * - admin_entreprise : voit tout le monde, dans toutes les agences
     * - admin_agence     : ne voit que les utilisateurs de SA propre agence
     * - commercial       : accès refusé
     */
    public function index(Request $request)
    {
        $connecte = $request->user();

        if ($connecte->role === 'commercial') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $query = User::query();

        if ($connecte->role === 'admin_agence') {
            $query->where('agence_id', $connecte->agence_id);
        } elseif ($request->agence_id) {
            $query->where('agence_id', $request->agence_id);
        }

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    /**
     * Crée un utilisateur (agent).
     * - admin_entreprise : peut créer n'importe quel rôle, dans n'importe quelle agence
     * - admin_agence     : peut seulement créer des "commercial" dans SA propre agence
     * - commercial       : accès refusé
     */
    public function store(Request $request)
    {
        $connecte = $request->user();

        if ($connecte->role === 'commercial') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $validated = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'password'  => 'required|string|min:8',
            'role'      => 'required|in:admin_entreprise,admin_agence,commercial',
            'agence_id' => 'nullable|exists:agences,id',
        ]);

        if ($connecte->role === 'admin_agence') {
            // Un admin d'agence ne peut créer que des commerciaux, dans SA propre agence
            if ($validated['role'] !== 'commercial') {
                return response()->json([
                    'message' => "Vous ne pouvez créer que des utilisateurs de rôle 'commercial'."
                ], 403);
            }
            $validated['agence_id'] = $connecte->agence_id;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['statut'] = true;

        $user = User::create($validated);

        return response()->json($user->only(['id', 'nom', 'prenom', 'email', 'role', 'agence_id']), 200);
    }

    public function show(Request $request, User $user)
    {
        $this->verifierAcces($request, $user);

        return response()->json($user->only(['id', 'nom', 'prenom', 'email', 'telephone', 'role', 'agence_id', 'statut']));
    }

    public function update(Request $request, User $user)
    {
        $this->verifierAcces($request, $user);

        $validated = $request->validate([
            'nom'       => 'sometimes|string|max:100',
            'prenom'    => 'sometimes|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'statut'    => 'sometimes|boolean',
            'password'  => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->only(['id', 'nom', 'prenom', 'email', 'role', 'agence_id', 'statut']));
    }

    public function destroy(Request $request, User $user)
    {
        $this->verifierAcces($request, $user);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $user->delete();

        return response()->json(null, 204);
    }

    /**
     * Vérifie qu'un admin_agence n'agit que sur les utilisateurs de sa propre agence.
     */
    protected function verifierAcces(Request $request, User $cible): void
    {
        $connecte = $request->user();

        if ($connecte->role === 'commercial') {
            abort(403, 'Accès non autorisé.');
        }

        if ($connecte->role === 'admin_agence' && $cible->agence_id !== $connecte->agence_id) {
            abort(403, "Vous n'avez pas accès à cet utilisateur.");
        }
    }
}
