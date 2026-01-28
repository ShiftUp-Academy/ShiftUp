<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailReponseUtilisateur extends Model
{
    protected $table = 'DetailsReponsesUtilisateur';
    protected $primaryKey = 'IdDetail';

    protected $fillable = [
        'IdReponse',
        'IdQuestion',
        'TexteReponse',
        'IdOptionChoisie'
    ];

    /**
     * Get the submission header.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(ReponseEtapeUtilisateur::class, 'IdReponse', 'IdReponse');
    }

    /**
     * Get the question related to this answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionEtape::class, 'IdQuestion', 'IdQuestion');
    }

    /**
     * Get the option selected (if applicable).
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(OptionQuestion::class, 'IdOptionChoisie', 'IdOption');
    }
}
