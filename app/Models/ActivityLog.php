<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'agence_id', 'user_id', 'action', 'description', 'ip_adresse',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
