<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use App\Services\TarificationService;
use App\Services\EcheancierService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssuranceController extends Controller
{
    public function __construct(
        protected TarificationService $tarificationService,
        protected EcheancierService $echeancierService
    ) {}

    public function index(Request $request)
    {
        $connecte = $request->user();

        $query = Assurance::with('client');

        if (in_array($connecte->role, ['admin_agence', 'commercial'])) {
            $query->where('agence_id', $connecte->agence_id);
        } elseif ($request->agence_id) {
            $query->where('agence_id', $request->agence_id);
        }

        $query->when($request->statut, fn($q) => $q->where('statut', $request->statut));

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function store(Request $request)
    {
        $connecte = $request->user();

        $validated = $request->validate([
            'agence_id'           => 'required|exists:agences,id',
            'client_id'           => 'required|exists:clients,id',
            'categorie_id'        => 'required|integer',
            'immatricule'         => 'required|string|max:20',
            'carburant'           => 'required|string|max:20',
            'puissance_fiscale'   => 'required|integer',
            'place'               => 'nullable|string|max:20',
            'poids'               => 'nullable|string|max:20',
            'duree'               => 'required|string|max:20',
            'type_client'         => 'required|string|max:30',
            'date_debut'          => 'required|date',
            'frequence_paiement'  => 'required|in:journalier,hebdomadaire,mensuel,enBloc',
        ]);

        if (in_array($connecte->role, ['admin_agence', 'commercial'])
            && (int) $validated['agence_id'] !== $connecte->agence_id) {
            return response()->json(['message' => "Vous ne pouvez créer un contrat que dans votre propre agence."], 403);
        }

        try {
            $montantTotal = $this->tarificationService->calculerMontant($validated);
            $dateFin = $this->tarificationService->calculerDateFin($validated['date_debut'], $validated['duree']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $assurance = Assurance::create([
            ...$validated,
            'code'                => 'ASR-' . strtoupper(Str::random(8)),
            'user_id'             => $connecte->id,
            'date_fin'            => $dateFin,
            'montant_total'       => $montantTotal,
            'montant_paye_cumule' => 0,
            'statut'              => 'enCours',
        ]);

        $this->echeancierService->genererEcheancier($assurance);

        return response()->json($assurance->load('echeances'), 201);
    }

    public function show(Request $request, Assurance $assurance)
    {
        $this->verifierAcces($request, $assurance);

        return response()->json($assurance->load(['client', 'echeances', 'paiements']));
    }

    public function update(Request $request, Assurance $assurance)
    {
        $this->verifierAcces($request, $assurance);

        $validated = $request->validate([
            'statut' => 'sometimes|in:enCours,enAttente,enRetard,enPause',
        ]);

        $assurance->update($validated);

        return response()->json($assurance);
    }

    public function destroy(Request $request, Assurance $assurance)
    {
        $this->verifierAcces($request, $assurance);

        $assurance->delete();

        return response()->json(null, 204);
    }

    protected function verifierAcces(Request $request, Assurance $assurance): void
    {
        $connecte = $request->user();

        if (in_array($connecte->role, ['admin_agence', 'commercial'])
            && $assurance->agence_id !== $connecte->agence_id) {
            abort(403, "Vous n'avez pas accès à ce contrat.");
        }
    }
}
