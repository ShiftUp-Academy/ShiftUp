<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use SoftDeletes;

    protected $table = 'Consultations';

    protected $primaryKey = 'IdConsultation';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = null;

    protected $fillable = [
        'IdUtilisateur',
        'Titre',
        'Question',
        'Statut',
        'IdLecon',
        'IdCategorie',
        'SourceType'
    ];

    protected $casts = [
        'Titre' => 'array',
        'Question' => 'array',
    ];

    public function lecon(): BelongsTo
    {
        return $this->belongsTo(Lecon::class, 'IdLecon', 'IdLecon');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'IdCategorie', 'IdCategorie');
    }

    /**
     * Get the user who owns the consultation.
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(Utilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * Get the questions linked to this consultation space.
     */
    public function questionsLibres(): HasMany
    {
        return $this->hasMany(QuestionLibre::class, 'IdConsultation', 'IdConsultation');
    }

    public function reponseConsultations()
    {
        return $this->belongsToMany(
            ReponseConsultation::class,
            'ReponseConsultation_Items',
            'IdConsultation',
            'IdReponseConsultation'
        );
    }
}
