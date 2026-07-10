<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'nom', 'prix_mensuel', 'prix_annuel',
        'limite_agences', 'limite_agents', 'limite_clients', 'limite_contrats_mois',
        'fonctionnalites', 'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'plan_id');
    }
}
