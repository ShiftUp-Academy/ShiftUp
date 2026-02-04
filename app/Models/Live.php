<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Live extends Model
{
    use SoftDeletes;

    protected $table = 'Lives';
    protected $primaryKey = 'IdLive';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'Titre',
        'IdCategorie',
        'Descriptions',
        'LienPhoto',
        'DateDebut',
        'DateFin',
        'LienGoogleMeet',
        'LienReplay',
        'IdAuteur',
        'Statut'
    ];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'IdCategorie', 'IdCategorie');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'IdAuteur', 'IdUtilisateur');
    }
}
