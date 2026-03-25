<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offre extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'Offres';

    protected $primaryKey = 'IdOffre';

    protected $fillable = [
        'Titre',
        'Descriptions',
        'LienPhoto',
        'ReductionGlobal',
        'DureeJours',
        'Statut'
    ];

    protected $casts = [
        'Titre' => 'array',
        'Descriptions' => 'array',
    ];

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    public function programmes()
    {
        return $this->hasMany(OffreProgramme::class, 'IdOffre', 'IdOffre');
    }

    public function coachings()
    {
        return $this->hasMany(OffreCoaching::class, 'IdOffre', 'IdOffre');
    }
}
