<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'agence_id', 'user_id', 'nom', 'prenom', 'genre', 'date_naissance',
        'telephone', 'email', 'npi', 'ville', 'quartier',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function agentEnregistrant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assurances()
    {
        return $this->hasMany(Assurance::class, 'client_id');
    }
}
