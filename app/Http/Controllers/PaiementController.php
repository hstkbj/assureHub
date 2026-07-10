<?php

namespace App\Http\Controllers;

use App\Models\Echeance;
use App\Models\Paiement;
use App\Services\EcheancierService;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function __construct(protected EcheancierService $echeancierService) {}

    public function index(Request $request)
    {
        $connecte = $request->user();

        $query = Paiement::with('assurance');

        if (in_array($connecte->role, ['admin_agence', 'commercial'])) {
            $query->where('agence_id', $connecte->agence_id);
        } elseif ($request->agence_id) {
            $query->where('agence_id', $request->agence_id);
        }

        $query->when($request->assurance_id, fn($q) => $q->where('assurance_id', $request->assurance_id));

        return response()->json($query->latest()->paginate($request->per_page ?? 20));
    }

    public function payerEcheance(Request $request, Echeance $echeance)
    {
        $connecte = $request->user();
        $assurance = $echeance->assurance;

        if (in_array($connecte->role, ['admin_agence', 'commercial'])
            && $assurance->agence_id !== $connecte->agence_id) {
            abort(403, "Vous n'avez pas accès à ce contrat.");
        }

        $validated = $request->validate([
            'montant'               => 'required|numeric|min:1',
            'mode_paiement'         => 'required|string|max:30',
            'reference_transaction' => 'nullable|string|max:100',
        ]);

        $paiement = Paiement::create([
            'agence_id'              => $assurance->agence_id,
            'assurance_id'           => $assurance->id,
            'echeance_id'            => $echeance->id,
            'montant'                => $validated['montant'],
            'mode_paiement'          => $validated['mode_paiement'],
            'reference_transaction'  => $validated['reference_transaction'] ?? null,
            'encaisse_par'           => $connecte->id,
        ]);

        $this->echeancierService->enregistrerPaiement($echeance, $validated['montant']);

        return response()->json([
            'paiement'  => $paiement,
            'echeance'  => $echeance->fresh(),
            'assurance' => $assurance->fresh(),
        ], 201);
    }
}
