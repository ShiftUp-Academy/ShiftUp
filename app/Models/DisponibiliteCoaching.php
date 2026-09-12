<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DisponibiliteCoaching extends Model
{
    use HasFactory;

    protected $table = 'DisponibiliteCoaching';
    protected $primaryKey = 'IdDisponibilite';

    protected $fillable = [
        'DateDisponible',
        'HeureDebut',
        'HeureFin',
        'EstReserve',
        'BlockedByReservationId',
    ];

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = null;

    public function reservations()
    {
        return $this->hasMany(ReservationCoaching::class, 'IdDisponibilite', 'IdDisponibilite');
    }

    public function blockedByReservation()
    {
        return $this->belongsTo(ReservationCoaching::class, 'BlockedByReservationId', 'IdReservation');
    }
}
