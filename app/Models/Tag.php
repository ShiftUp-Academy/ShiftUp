<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $table = 'Tags';
    protected $primaryKey = 'IdTag';

    protected $fillable = ['Nom'];

    public function questionsLibres(): BelongsToMany
    {
        return $this->belongsToMany(QuestionLibre::class, 'questions_tags', 'IdTag', 'IdQuestion');
    }
}
