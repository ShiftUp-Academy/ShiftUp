<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressionUtilisateur extends Model
{
    protected $table = 'ProgressionUtilisateur';
    protected $primaryKey = 'IdProgression';

    protected $fillable = [
        'IdUtilisateur',
        'IdProgramme',
        'IdLecon',
        'IdEtape',
        'EstTermine',
        'PointsGagnes'
    ];

    protected $casts = [
        'EstTermine' => 'boolean',
    ];

    /**
     * Get the user.
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'IdUtilisateur', 'IdUtilisateur');
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
     * Get the step context.
     */
    public function etape(): BelongsTo
    {
        return $this->belongsTo(Etape::class, 'IdEtape', 'IdEtape');
    }
}
