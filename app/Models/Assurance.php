<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assurance extends Model
{
    protected $fillable = [
        'agence_id', 'code', 'client_id', 'categorie_id', 'user_id',
        'immatricule', 'carburant', 'puissance_fiscale', 'place', 'poids', 'annee_fiscale',
        'duree', 'type_client', 'date_debut', 'date_fin',
        'frequence_paiement', 'montant_total', 'montant_paye_cumule', 'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function echeances()
    {
        return $this->hasMany(Echeance::class, 'assurance_id')->orderBy('numero_echeance');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'assurance_id');
    }

    public function rappels()
    {
        return $this->hasMany(Rappel::class, 'assurance_id');
    }

    public function commission()
    {
        return $this->hasOne(Commission::class, 'assurance_id');
    }

    /**
     * Catégorie du véhicule — vit dans la base CENTRALE, donc pas de vraie
     * relation Eloquent belongsTo possible (bases différentes). On fait
     * un accesseur qui va chercher l'info à la demande.
     */
    public function getCategorieAttribute(): ?Categorie
    {
        return Categorie::find($this->categorie_id);
    }

    public function getSoldeRestantAttribute(): float
    {
        return (float) $this->montant_total - (float) $this->montant_paye_cumule;
    }
}
