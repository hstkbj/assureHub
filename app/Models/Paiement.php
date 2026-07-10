<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'agence_id', 'assurance_id', 'echeance_id', 'montant',
        'date_paiement', 'mode_paiement', 'reference_transaction', 'encaisse_par',
    ];

    protected $casts = [
        'date_paiement' => 'date',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function assurance()
    {
        return $this->belongsTo(Assurance::class, 'assurance_id');
    }

    public function echeance()
    {
        return $this->belongsTo(Echeance::class, 'echeance_id');
    }

    public function encaissePar()
    {
        return $this->belongsTo(User::class, 'encaisse_par');
    }
}
