<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    protected $fillable = [
        'nom_agence', 'code_agence', 'responsable', 'telephone',
        'adresse', 'ville', 'quartier', 'statut',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'agence_id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'agence_id');
    }

    public function assurances()
    {
        return $this->hasMany(Assurance::class, 'agence_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'agence_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'agence_id');
    }
}
