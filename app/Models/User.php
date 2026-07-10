<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'agence_id', 'nom', 'prenom', 'email', 'telephone',
        'role', 'password', 'photo', 'statut',
        'code_password', 'date_expiration_code_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'statut' => 'boolean',
        'date_expiration_code_password' => 'date',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function clientsEnregistres()
    {
        return $this->hasMany(Client::class, 'user_id');
    }

    public function assurancesCreees()
    {
        return $this->hasMany(Assurance::class, 'user_id');
    }

    public function paiementsEncaisses()
    {
        return $this->hasMany(Paiement::class, 'encaisse_par');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'user_id');
    }
}
