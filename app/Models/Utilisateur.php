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
    ];

    protected $hidden = [
        'MotDePasseHash',
    ];

    protected $casts = [
        'DateCreation' => 'datetime',
        'DateMiseAJour' => 'datetime',
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

    /**
     * Badges/Achievements earned by this user.
     */
    public function badges()
    {
        return $this->belongsToMany(Reussite::class, 'badges_utilisateurs', 'IdUtilisateur', 'IdReussite')
                    ->withTimestamps();
    }

    /**
     * User's shopping cart.
     */
    public function panier()
    {
        return $this->hasOne(Panier::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * User's purchase history.
     */
    public function commandes()
    {
        return $this->hasMany(Commande::class, 'IdUtilisateur', 'IdUtilisateur');
    }

    /**
     * Surcharger la relation de notification pour utiliser les colonnes en français.
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'Destinataire', 'TypeDestinataire', 'IdDestinataire')
                    ->orderBy('DateCreation', 'desc');
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return  array<string, string>|string
     */
    public function routeNotificationForMail($notification)
    {
        $emails = [$this->Email]; // Email de connexion

        // Ajouter l'email de contact du profil s'il existe et est différent
        if ($this->profil && !empty($this->profil->EmailContact) && $this->profil->EmailContact !== $this->Email) {
            $emails[] = $this->profil->EmailContact;
        }

        return $emails;
    }
}
