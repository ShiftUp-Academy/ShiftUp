<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $table = 'Utilisateurs';
    protected $primaryKey = 'IdUtilisateur';

    const CREATED_AT = 'DateCreation';
    const UPDATED_AT = 'DateMiseAJour';

    protected $fillable = [
        'Email',
        'MotDePasseHash',
        'GoogleId',
        'Role',
        'DerniereConnexion',
        'Newsletter',
        'PermissionsModerateur',
        'Points',
    ];

    protected $hidden = [
        'MotDePasseHash',
    ];

    protected $casts = [
        'DateCreation' => 'datetime',
        'DateMiseAJour' => 'datetime',
        'DerniereConnexion' => 'datetime',
        'Newsletter' => 'boolean',
        'PermissionsModerateur' => 'array',
    ];

    public function getAuthPassword()
    {
        return $this->MotDePasseHash;
    }

    public function getAuthPasswordName()
    {
        return 'MotDePasseHash';
    }

    public function profil()
    {
        return $this->hasOne(ProfilUtilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * Programs authored by this user (if admin).
     */
    public function programmesAuteur()
    {
        return $this->hasMany(ProgrammeFormation::class, 'idAuteur', 'IdUtilisateur');
    }

    /**
     * Consultations owned by this user.
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * Free questions asked by this user.
     */
    public function questionsLibres()
    {
        return $this->hasMany(QuestionLibre::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * Exercise responses submitted by this user.
     */
    public function reponsesExercices()
    {
        return $this->hasMany(ReponseEtapeUtilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * User's learning progression.
     */
    public function progression()
    {
        return $this->hasMany(ProgressionUtilisateur::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    public function badges()
    {
        return $this->belongsToMany(Reussite::class, 'user_reussites', 'user_id', 'reussite_id')
                    ->withPivot(['date_obtention', 'context_type', 'context_id'])
                    ->withTimestamps('created_at', 'updated_at');
    }

    public function temoignages()
    {
        return $this->hasMany(Temoignage::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    public function panier()
    {
        return $this->hasOne(Panier::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'Destinataire', 'TypeDestinataire', 'IdDestinataire')
                    ->orderBy('DateCreation', 'desc');
    }

    public function routeNotificationForMail($notification)
    {
        $emails = [$this->Email];

        if ($this->profil && !empty($this->profil->EmailContact) && $this->profil->EmailContact !== $this->Email) {
            $emails[] = $this->profil->EmailContact;
        }

        return $emails;
    }
}
