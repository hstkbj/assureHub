<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Colonnes personnalisées qu'on a ajoutées via la migration
     * "add_custom_columns_to_tenants_table". Sans cette liste, Eloquent
     * refuserait de les remplir via Tenant::create([...]).
     */
    protected $fillable = [
        'id',
        'nom_commercial',
        'raison_sociale',
        'numero_agrement',
        'email',
        'telephone',
        'adresse_siege',
        'ville_siege',
        'sous_domaine',
        'pays',
        'statut',
    ];

    /**
     * stancl/tenancy stocke par défaut certaines données dans la colonne
     * JSON "data" plutôt que dans de vraies colonnes. Comme on a choisi de
     * créer de vraies colonnes (plus facile à requêter), on informe le
     * package de NE PAS les traiter comme des "custom columns virtuelles"
     * dans data — elles restent des colonnes SQL classiques.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'nom_commercial',
            'raison_sociale',
            'numero_agrement',
            'email',
            'telephone',
            'adresse_siege',
            'ville_siege',
            'sous_domaine',
            'pays',
            'statut',
        ];
    }

    /**
     * Relation vers l'abonnement actif de cette entreprise (base centrale)
     */
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'tenant_id');
    }

    public function abonnementActif()
    {
        return $this->hasOne(Abonnement::class, 'tenant_id')->where('statut', 'actif');
    }
}
