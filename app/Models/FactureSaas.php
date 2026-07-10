<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactureSaas extends Model
{
    protected $connection = 'central';
    protected $table = 'factures_saas';

    protected $fillable = [
        'abonnement_id', 'tenant_id', 'montant',
        'date_emission', 'date_echeance', 'statut', 'date_paiement', 'mode_paiement',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
    ];

    public function abonnement()
    {
        return $this->belongsTo(Abonnement::class, 'abonnement_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
