<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $table = 'Utilisateurs';
    protected $primaryKey = 'IdUtilisateur';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'Email',
        'MotDePasseHash',
        'GoogleId',
        'Role',
    ];

    protected $hidden = [
        'MotDePasseHash',
    ];

    protected $casts = [
        'DateCreation' => 'datetime',
        'DateMiseAJour' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->MotDePasseHash;
    }

    public function profil()
    {
        return $this->hasOne(ProfilUtilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }
}
