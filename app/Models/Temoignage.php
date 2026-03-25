<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Temoignage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'Temoignage';

    protected $primaryKey = 'IdTemoignage';
    
    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'IdUtilisateur',
        'Type',
        'ContenuTexte',
        'CheminFichier',
        'Statut',
        'DateCreation',
        'DateMiseAJour'
    ];

    protected $casts = [
        'ContenuTexte' => 'array',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }
}
