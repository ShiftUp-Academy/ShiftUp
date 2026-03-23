<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProgrammeFormation extends Model
{
    use SoftDeletes;
    protected $table = 'ProgrammeFormation';
    protected $primaryKey = 'IdProgrammeFormation';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'Type',
        'Titre',
        'Langue',
        'LienPhoto',
        'ApercuVideo',
        'Statut',
        'StatutVerrouillageProgression',
        'Descriptions',
        'DateSeminaire',
        'HeureSeminaire',
        'LieuSeminaire',
        'NombreDeJours',
        'ModaliteSeminaire',
        'LienGoogleMeet',
        'Prix',
        'PointsOfferts',
        'IdCategorie',
        'idAuteur'
    ];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'IdCategorie', 'IdCategorie');
    }


    public function auteur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'idAuteur', 'IdUtilisateur');
    }


    public function lecons(): HasMany
    {
        return $this->hasMany(Lecon::class, 'IdProgramme', 'IdProgrammeFormation')->orderBy('Ordre');
    }

    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class, 'IdProgramme', 'IdProgrammeFormation')->orderBy('Ordre');
    }

    public function questionsLibres(): HasMany
    {
        return $this->hasMany(QuestionLibre::class, 'IdProgramme', 'IdProgrammeFormation');
    }
}
