<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ReponseConsultation extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'ReponseConsultations';
    protected $primaryKey = 'IdReponseConsultation';

    protected $fillable = [
        'IdCategorie',
        'Titre',
        'Descriptions',
        'LienVideo',
        'Statut'
    ];

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'IdCategorie', 'IdCategorie');
    }

    public function questions()
    {
        return $this->belongsToMany(
            Consultation::class,
            'ReponseConsultation_Items',
            'IdReponseConsultation',
            'IdConsultation'
        );
    }
}
