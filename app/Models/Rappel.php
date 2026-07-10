<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rappel extends Model
{
    protected $fillable = [
        'agence_id', 'assurance_id', 'type_rappel', 'montant_calcule',
        'date_prevue', 'canal', 'statut', 'date_envoi',
    ];

    protected $casts = [
        'date_prevue' => 'date',
        'date_envoi'  => 'datetime',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function assurance()
    {
        return $this->belongsTo(Assurance::class, 'assurance_id');
    }
}
