<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommandeItem extends Model
{
    use HasFactory;

    protected $table = 'CommandeItems';
    protected $primaryKey = 'IdCommandeItem';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = null;

    protected $fillable = [
        'IdCommande',
        'IdProgrammeFormation',
        'IdOffre',
        'PrixAchat',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class, 'IdCommande', 'IdCommande');
    }

    public function programme()
    {
        return $this->belongsTo(ProgrammeFormation::class, 'IdProgrammeFormation', 'IdProgrammeFormation');
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class, 'IdOffre', 'IdOffre');
    }
}
