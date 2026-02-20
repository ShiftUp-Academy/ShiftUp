<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reussite extends Model
{
    use HasFactory;

    protected $table = 'reussites';

    protected $fillable = [
        'nom',
        'description',
        'video_link',
        'type_action',
        'valeur_requise',
        'seuil_points',
        'points_recompense',
        'est_actif'
    ];

    protected $casts = [
        'valeur_requise' => 'array',
        'est_actif' => 'boolean',
    ];

    public function utilisateurs()
    {
        return $this->belongsToMany(Utilisateur::class, 'user_reussites', 'reussite_id', 'user_id')
                    ->withPivot(['date_obtention', 'context_type', 'context_id'])
                    ->withTimestamps('created_at', 'updated_at');
    }
}
