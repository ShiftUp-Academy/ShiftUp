<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffreProgramme extends Model
{
    protected $table = 'OffreProgrammes';
    protected $primaryKey = 'IdOffreProgramme';
    public $timestamps = false;

    protected $fillable = [
        'IdOffre',
        'IdProgrammeFormation',
        'ReductionSpecifique'
    ];

    public function offre()
    {
        return $this->belongsTo(Offre::class, 'IdOffre', 'IdOffre');
    }

    public function programme()
    {
        return $this->belongsTo(ProgrammeFormation::class, 'IdProgrammeFormation', 'IdProgrammeFormation');
    }
}
