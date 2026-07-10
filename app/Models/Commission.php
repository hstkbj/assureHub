<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'agence_id', 'assurance_id', 'user_id', 'montant', 'statut',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function assurance()
    {
        return $this->belongsTo(Assurance::class, 'assurance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
