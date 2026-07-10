<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $connection = 'central';

    protected $fillable = ['nom', 'description'];

    public function tarifs()
    {
        return $this->hasMany(GrilleTarif::class, 'categorie_id');
    }
}
