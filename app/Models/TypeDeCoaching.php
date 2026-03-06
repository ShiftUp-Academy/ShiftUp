<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeDeCoaching extends Model
{
    use HasFactory, SoftDeletes;

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
