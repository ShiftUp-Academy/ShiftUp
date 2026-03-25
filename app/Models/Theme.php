<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Theme extends Model
{
    use SoftDeletes;
    protected $table = 'Themes';
    protected $primaryKey = 'IdTheme';
    
    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'IdProgramme',
        'Titre',
        'Descriptions',
        'Statut',
        'Ordre'
    ];

    protected $casts = [
        'Titre' => 'array',
        'Descriptions' => 'array',
    ];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(ProgrammeFormation::class, 'IdProgramme', 'IdProgrammeFormation');
    }

    public function lecons(): HasMany
    {
        return $this->hasMany(Lecon::class, 'IdTheme', 'IdTheme')->orderBy('Ordre');
    }
}
