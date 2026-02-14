<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'Id';
    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'Id',
        'Type',
        'TypeDestinataire',
        'IdDestinataire',
        'Donnees',
        'DateLecture'
    ];

    protected $casts = [
        'Donnees' => 'array',
        'DateLecture' => 'datetime',
    ];

    /**
     * Marquer comme lu en français
     */
    public function marquerCommeLu()
    {
        if (is_null($this->DateLecture)) {
            $this->forceFill(['DateLecture' => $this->freshTimestamp()])->save();
        }
    }
}
