<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Categorie extends Model
{
    protected $table = 'Categories';
    protected $primaryKey = 'IdCategorie';
    public $timestamps = false;

    protected $fillable = ['Nom', 'Statut', 'Descriptions'];

    public function questionsLibres()
    {
        return $this->hasMany(QuestionLibre::class, 'IdCategorie', 'IdCategorie');
    }
}
