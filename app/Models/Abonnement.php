<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id', 'plan_id', 'date_debut', 'date_fin', 'periodicite', 'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function factures()
    {
        return $this->hasMany(FactureSaas::class, 'abonnement_id');
    }
}
