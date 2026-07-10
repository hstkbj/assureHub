<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use Illuminate\Http\Request;

class EcheanceController extends Controller
{
    /**
     * Liste toutes les échéances d'un contrat précis.
     */
    public function parAssurance(Request $request, Assurance $assurance)
    {
        $connecte = $request->user();

        if (in_array($connecte->role, ['admin_agence', 'commercial'])
            && $assurance->agence_id !== $connecte->agence_id) {
            abort(403, "Vous n'avez pas accès à ce contrat.");
        }

        return response()->json($assurance->echeances()->orderBy('numero_echeance')->get());
    }

    /**
     * Liste les échéances à venir dans les X prochains jours (utile pour
     * un tableau de bord "échéances proches"), toutes agences confondues
     * pour un admin_entreprise, ou filtrées pour les autres rôles.
     */
    public function aVenir(Request $request)
    {
        $connecte = $request->user();
        $jours = $request->jours ?? 7;

        $query = \App\Models\Echeance::with('assurance')
            ->where('statut', '!=', 'paye')
            ->whereDate('date_echeance', '<=', now()->addDays($jours));

        if (in_array($connecte->role, ['admin_agence', 'commercial'])) {
            $query->whereHas('assurance', fn($q) => $q->where('agence_id', $connecte->agence_id));
        }

        return response()->json($query->orderBy('date_echeance')->paginate($request->per_page ?? 20));
    }
}
