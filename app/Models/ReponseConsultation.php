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

    protected $casts = [
        'Titre' => 'array',
        'Descriptions' => 'array',
    ];

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    public function getTitreAttribute($value)
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($decoded)) {
            $locale = app()->getLocale();
            return $decoded[$locale] ?? $decoded['fr'] ?? reset($decoded) ?? '';
        }
        return is_string($decoded) ? $decoded : $value;
    }

    public function getDescriptionsAttribute($value)
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($decoded)) {
            $locale = app()->getLocale();
            return $decoded[$locale] ?? $decoded['fr'] ?? reset($decoded) ?? '';
        }
        return is_string($decoded) ? $decoded : $value;
    }

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
