<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OptionQuestion extends Model
{
    protected $table = 'OptionsQuestions';
    protected $primaryKey = 'IdOption';
    public $timestamps = false;

    protected $fillable = [
        'IdQuestion',
        'TexteOption',
        'EstCorrecte',
        'Ordre'
    ];

    protected $casts = [
        'EstCorrecte' => 'boolean',
    ];

    /**
     * Get the question that owns the option.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionEtape::class, 'IdQuestion', 'IdQuestion');
    }
}
