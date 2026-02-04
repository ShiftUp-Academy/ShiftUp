<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffreCoaching extends Model
{
    protected $table = 'OffreCoachings';
    protected $primaryKey = 'IdOffreCoaching';
    public $timestamps = false;

    protected $fillable = [
        'IdOffre',
        'IdTypeCoaching',
        'ReductionSpecifique'
    ];

    public function offre()
    {
        return $this->belongsTo(Offre::class, 'IdOffre', 'IdOffre');
    }

    public function coaching()
    {
        return $this->belongsTo(TypeDeCoaching::class, 'IdTypeCoaching', 'IdTypeCoaching');
    }
}
