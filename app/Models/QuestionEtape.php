<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionEtape extends Model
{
    protected $table = 'QuestionsEtapes';
    protected $primaryKey = 'IdQuestion';
    public $timestamps = false;

    protected $fillable = [
        'IdEtape',
        'Intitule',
        'TypeQuestion',
        'Ordre'
    ];

    protected $casts = [
        'Intitule' => 'array',
    ];

    /**
     * Get the step that owns current question.
     */
    public function etape(): BelongsTo
    {
        return $this->belongsTo(Etape::class, 'IdEtape', 'IdEtape');
    }

    /**
     * Get the possible options for this question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(OptionQuestion::class, 'IdQuestion', 'IdQuestion')->orderBy('Ordre');
    }
}
