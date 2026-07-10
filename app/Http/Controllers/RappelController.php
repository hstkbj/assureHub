<?php

namespace App\Http\Controllers;

use App\Models\Rappel;
use Illuminate\Http\Request;

class RappelController extends Controller
{
    public function index(Request $request)
    {
        $connecte = $request->user();

        $query = Rappel::with('assurance.client');

        if (in_array($connecte->role, ['admin_agence', 'commercial'])) {
            $query->where('agence_id', $connecte->agence_id);
        } elseif ($request->agence_id) {
            $query->where('agence_id', $request->agence_id);
        }

        $query->when($request->statut, fn($q) => $q->where('statut', $request->statut));

        return response()->json($query->latest('date_prevue')->paginate($request->per_page ?? 20));
    }
}
