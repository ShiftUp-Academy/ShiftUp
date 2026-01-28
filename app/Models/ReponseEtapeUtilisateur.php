<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReponseEtapeUtilisateur extends Model
{
    protected $table = 'ReponsesEtapesUtilisateur';
    protected $primaryKey = 'IdReponse';

    protected $fillable = [
        'IdUtilisateur',
        'IdEtape',
        'StatutValidation',
        'ReponseAdmin',
        'DateValidation'
    ];

    protected $casts = [
        'DateValidation' => 'datetime',
    ];

    /**
     * Get the user who submitted.
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * Get the associated step.
     */
    public function etape(): BelongsTo
    {
        return $this->belongsTo(Etape::class, 'IdEtape', 'IdEtape');
    }

    /**
     * Get the details (individual question answers) for this submission.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailReponseUtilisateur::class, 'IdReponse', 'IdReponse');
    }
}
