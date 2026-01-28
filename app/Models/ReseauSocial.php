<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReseauSocial extends Model
{
    protected $table = 'ReseauxSociaux';
    protected $primaryKey = 'IdReseauSocial';
    public $timestamps = false; // Pas de DateCreation/DateMiseAJour dans la migration pour cette table

    protected $fillable = [
        'IdUtilisateur',
        'Nom',
        'Lien'
    ];

    public function profil()
    {
        return $this->belongsTo(ProfilUtilisateur::class, 'IdUtilisateur', 'IdProfil');
    }}
