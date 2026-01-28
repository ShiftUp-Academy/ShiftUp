<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeDeCoaching extends Model
{
    use HasFactory;

    protected $table = 'TypeDeCoaching';
    protected $primaryKey = 'IdTypeCoaching';

    protected $fillable = [
        'NomDeType',
        'Descriptions',
        'Prix',
        'Statut'
    ];

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    public function reservations()
    {
        return $this->hasMany(ReservationCoaching::class, 'IdTypeCoaching', 'IdTypeCoaching');
    }
}
