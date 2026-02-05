<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    use HasFactory;

    protected $table = 'Paniers';
    protected $primaryKey = 'IdPanier';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'IdUtilisateur',
        'Statut',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    public function items()
    {
        return $this->hasMany(PanierItem::class, 'IdPanier', 'IdPanier');
    }
}
