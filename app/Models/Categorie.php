<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    use SoftDeletes;

    protected $table = 'Categories';
    protected $primaryKey = 'IdCategorie';
    public $timestamps = false;

    protected $fillable = ['Nom', 'Statut', 'Descriptions'];
    
    protected $casts = [
        'Nom' => 'array',
        'Descriptions' => 'array',
    ];

    public function questionsLibres()
    {
        return $this->hasMany(QuestionLibre::class, 'IdCategorie', 'IdCategorie');
    }
}
