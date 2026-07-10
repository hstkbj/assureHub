<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Réservé à admin_entreprise et admin_agence — un commercial ne doit
     * pas pouvoir consulter le journal d'audit.
     */
    public function index(Request $request)
    {
        $connecte = $request->user();

        if ($connecte->role === 'commercial') {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $query = ActivityLog::with('user');

        if ($connecte->role === 'admin_agence') {
            $query->where('agence_id', $connecte->agence_id);
        } elseif ($request->agence_id) {
            $query->where('agence_id', $request->agence_id);
        }

        $query->when($request->action, fn($q) => $q->where('action', $request->action));

        return response()->json($query->latest()->paginate($request->per_page ?? 30));
    }
}
