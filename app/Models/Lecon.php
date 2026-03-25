<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class Lecon extends Model
{
    use SoftDeletes;
    protected $table = 'Lecons';
    protected $primaryKey = 'IdLecon';
    public $timestamps = true;

    // Standard timestamps used
    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'IdProgramme',
        'IdTheme',
        'Titre',
        'Statut',
        'Descriptions',
        'TypeLecon',
        'SourceType',
        'Contenu',
        'DelaiDrop',
        'NombreDePages',
        'Ordre',
        'PointsOfferts'
    ];

    protected $casts = [
        'Titre' => 'array',
        'Descriptions' => 'array',
        'Contenu' => 'array',
    ];

    /**
     * Get the program that owns the lesson.
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(ProgrammeFormation::class, 'IdProgramme', 'IdProgrammeFormation');
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class, 'IdTheme', 'IdTheme');
    }

    /**
     * Get the steps (exercises) for the lesson.
     */
    public function etapes(): HasMany
    {
        return $this->hasMany(Etape::class, 'IdLecon', 'IdLecon')->orderBy('Ordre');
    }

    /**
     * Get the free questions related to this lesson.
     */
    public function questionsLibres(): HasMany
    {
        return $this->hasMany(QuestionLibre::class, 'IdLecon', 'IdLecon');
    }
}
