<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdminPlateforme extends Authenticatable
{

    use HasApiTokens;

    protected $connection = 'central';
    protected $table = 'admins_plateforme';

    protected $fillable = ['nom', 'email', 'password', 'statut'];

    protected $hidden = ['password'];

    protected $casts = [
        'statut' => 'boolean',
    ];
}
