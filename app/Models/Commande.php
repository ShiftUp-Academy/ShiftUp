<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $table = 'Commandes';
    protected $primaryKey = 'IdCommande';

    const CREATED_AT = 'DateCommande';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'IdUtilisateur',
        'Reference',
        'MontantTotal',
        'Statut',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    public function items()
    {
        return $this->hasMany(CommandeItem::class, 'IdCommande', 'IdCommande');
    }
}
