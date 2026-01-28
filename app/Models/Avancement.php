<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avancement extends Model
{
    protected $table = 'Avancements';
    protected $primaryKey = 'IdAvancement';

    protected $fillable = [
        'IdUtilisateur',
        'EntiteId',
        'EntiteType',
        'DateOuverture',
        'EstTermine',
        'DateCompletion'
    ];

    protected $casts = [
        'DateOuverture' => 'datetime',
        'EstTermine' => 'boolean',
        'DateCompletion' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'IdUtilisateur');
    }

    public function entite()
    {
        return $this->morphTo('Entite', 'EntiteType', 'EntiteId');
    }
}
