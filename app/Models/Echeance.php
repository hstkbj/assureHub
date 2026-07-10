<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echeance extends Model
{
    use HasFactory;

    protected $fillable = [
        'assurance_id', 'numero_echeance', 'date_echeance',
        'montant_du', 'montant_paye', 'statut'
    ];

    protected $casts = [
        'date_echeance' => 'date',
    ];

    public function assurance()
    {
        return $this->belongsTo(Assurance::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}
