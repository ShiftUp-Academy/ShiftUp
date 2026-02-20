<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReussiteProgression extends Model
{
    use HasFactory;

    protected $table = 'user_reussite_progressions';

    protected $fillable = [
        'user_id',
        'reussite_id',
        'progression_actuelle',
        'progression_requise',
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
