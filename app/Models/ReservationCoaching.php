<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReservationCoaching extends Model
{
    use HasFactory;

    protected $table = 'ReservationCoaching';
    protected $primaryKey = 'IdReservation';

    protected $fillable = [
        'IdUtilisateur',
        'IdTypeCoaching',
        'IdDisponibilite',
        'HeureDebutReservation',
        'StatutReservation',
        'NoteUtilisateur'
    ];

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    public function type()
    {
        return $this->belongsTo(TypeDeCoaching::class, 'IdTypeCoaching', 'IdTypeCoaching');
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    public function disponibilite()
    {
        return $this->belongsTo(DisponibiliteCoaching::class, 'IdDisponibilite', 'IdDisponibilite');
    }
}
