<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrilleTarif extends Model
{
    protected $table = 'grille_tarifs';

    protected $fillable = [
        'categorie_id', 'cv_min', 'cv_max', 'carburant', 'type_client', 'duree',
        'place', 'poids', 'montant', 'date_debut_validite', 'date_fin_validite',
    ];

    protected $casts = [
        'date_debut_validite' => 'date',
        'date_fin_validite'   => 'date',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    /**
     * Scope pratique : trouve le tarif applicable pour un véhicule donné.
     * Utilisation : GrilleTarif::applicable($categorieId, $cv, $carburant, $typeClient, $duree)->first();
     */
    public function scopeApplicable($query, $categorieId, $cv, $carburant, $typeClient, $duree)
    {
        return $query->where('categorie_id', $categorieId)
            ->where('carburant', $carburant)
            ->where('type_client', $typeClient)
            ->where('duree', $duree)
            ->where('cv_min', '<=', $cv)
            ->where('cv_max', '>=', $cv)
            ->where('date_debut_validite', '<=', now())
            ->where(function ($q) {
                $q->whereNull('date_fin_validite')->orWhere('date_fin_validite', '>=', now());
            });
    }
}
