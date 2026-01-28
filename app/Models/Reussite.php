<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Reussite extends Model
{
    protected $table = 'Reussite';
    protected $primaryKey = 'IdReussite';

    protected $fillable = [
        'Nom',
        'Descriptions',
        'LienIcone',
        'TypeDeclencheur',
        'SeuilPoints',
        'IdReference',
        'Statut'
    ];

    /**
     * Get the users who earned this achievement/badge.
     */
    public function utilisateurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'badges_utilisateurs', 'IdReussite', 'IdUtilisateur')
                    ->withTimestamps();
    }
}
