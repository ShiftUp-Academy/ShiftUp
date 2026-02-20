<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserReussite extends Pivot
{
    protected $table = 'user_reussites';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'reussite_id',
        'date_obtention',
    ];

    protected $casts = [
        'date_obtention' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'user_id', 'IdUtilisateur');
    }

    public function reussite()
    {
        return $this->belongsTo(Reussite::class);
    }
}
