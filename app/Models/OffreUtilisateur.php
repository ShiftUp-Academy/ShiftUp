<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffreUtilisateur extends Model
{
    protected $table = 'OffresUtilisateurs';
    protected $primaryKey = 'IdOffreUtilisateur';
    public $timestamps = false;

    protected $fillable = [
        'IdOffre',
        'IdUtilisateur',
        'DateAttribution'
    ];
}
