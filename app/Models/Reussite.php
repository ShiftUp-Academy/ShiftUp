<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Reussite extends Model
{
    use HasFactory, SoftDeletes;


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
        'nom' => 'json',
        'description' => 'json',
        'valeur_requise' => 'array',
        'est_actif' => 'boolean',
    ];

    public function getNomAttribute($value)
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($decoded)) {
            $locale = app()->getLocale();
            return $decoded[$locale] ?? $decoded['fr'] ?? reset($decoded) ?? '';
        }
        return is_string($decoded) ? $decoded : $value;
    }

    public function getDescriptionAttribute($value)
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($decoded)) {
            $locale = app()->getLocale();
            return $decoded[$locale] ?? $decoded['fr'] ?? reset($decoded) ?? '';
        }
        return is_string($decoded) ? $decoded : $value;
    }

    public function utilisateurs()
    {
        return $this->belongsToMany(Utilisateur::class, 'user_reussites', 'reussite_id', 'user_id')
                    ->withPivot(['date_obtention', 'context_type', 'context_id'])
                    ->withTimestamps('created_at', 'updated_at');
    }
}
