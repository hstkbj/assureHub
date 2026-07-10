<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $connecte = $request->user();

        $query = Commission::with(['assurance', 'user']);

        if ($connecte->role === 'commercial') {
            // Un commercial ne voit que SES propres commissions
            $query->where('user_id', $connecte->id);
        } elseif ($connecte->role === 'admin_agence') {
            $query->where('agence_id', $connecte->agence_id);
        } elseif ($request->agence_id) {
            $query->where('agence_id', $request->agence_id);
        }

        $query->when($request->statut, fn($q) => $q->where('statut', $request->statut));

        return response()->json($query->latest()->paginate($request->per_page ?? 20));
    }
}
