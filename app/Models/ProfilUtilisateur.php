<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilUtilisateur extends Model
{
    use HasFactory;
    protected $table = 'ProfilsUtilisateurs';
    protected $primaryKey = 'IdProfil';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'IdUtilisateur',
        'Prenom',
        'Nom',
        'Metier',
        'PhotoProfil',
        'NumeroTelephone',
        'Adresse',
        'Biographie',
        'EmailContact',
    ];

    protected $casts = [
        'DateCreation' => 'datetime',
        'DateMiseAJour' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }
    public function reseauxSociaux()
    {
        return $this->hasMany(ReseauSocial::class, 'IdUtilisateur', 'IdProfil');
    }
}
