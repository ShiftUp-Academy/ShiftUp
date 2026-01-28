<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class Etape extends Model
{
    use SoftDeletes;
    protected $table = 'Etapes';
    protected $primaryKey = 'IdEtape';
    public $timestamps = true;

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'IdLecon',
        'Titre',
        'Descriptions',
        'Statut',
        'TypeEtape',
        'Ordre',
        'PointsOfferts',
        'NecessiteValidationAdmin'
    ];
    public function lecon(): BelongsTo
    {
        return $this->belongsTo(Lecon::class, 'IdLecon', 'IdLecon');
    }
    public function questions(): HasMany
    {
        return $this->hasMany(QuestionEtape::class, 'IdEtape', 'IdEtape')->orderBy('Ordre');
    }
    public function reponsesUtilisateurs(): HasMany
    {
        return $this->hasMany(ReponseEtapeUtilisateur::class, 'IdEtape', 'IdEtape');
    }
}
