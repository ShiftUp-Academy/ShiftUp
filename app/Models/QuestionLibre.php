<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuestionLibre extends Model
{
    protected $table = 'QuestionsLibres';
    protected $primaryKey = 'IdQuestionLibre';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = null;

    protected $fillable = [
        'IdUtilisateur',
        'IdProgramme',
        'IdLecon',
        'IdConsultation',
        'Titre',
        'ContenuQuestion',
        'IdCategorie',
        'TypeReponse',
        'ContenuReponse',
        'StatutReponse',
        'DateReponse'
    ];

    protected $casts = [
        'DateReponse' => 'datetime',
        'Titre' => 'array',
        'ContenuQuestion' => 'array',
        'ContenuReponse' => 'array',
    ];

    /**
     * Get the user who asked the question.
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * Get the program context.
     */
    public function programme(): BelongsTo
    {
        return $this->belongsTo(ProgrammeFormation::class, 'IdProgramme', 'IdProgrammeFormation');
    }

    /**
     * Get the lesson context.
     */
    public function lecon(): BelongsTo
    {
        return $this->belongsTo(Lecon::class, 'IdLecon', 'IdLecon');
    }

    /**
     * Get the consultation context.
     */
    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class, 'IdConsultation', 'IdConsultation');
    }

    /**
     * Get the category.
     */
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'IdCategorie', 'IdCategorie');
    }

    /**
     * Get the tags associated with the question.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'QuestionsTags', 'IdQuestion', 'IdTag');
    }
}
