<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanierItem extends Model
{
    use HasFactory;

    protected $table = 'PanierItems';
    protected $primaryKey = 'IdPanierItem';

    public $timestamps = false; // Using DateAjout instead

    protected $fillable = [
        'IdPanier',
        'IdProgrammeFormation',
        'IdOffre',
        'PrixAuMomentDeLAjout',
        'DateAjout',
    ];

    public function panier()
    {
        return $this->belongsTo(Panier::class, 'IdPanier', 'IdPanier');
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
