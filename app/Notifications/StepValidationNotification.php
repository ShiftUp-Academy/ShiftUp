<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StepValidationNotification extends Notification
{
    protected $etape;
    protected $reponseAdmin;
    protected $statut;

    public function __construct($etape, $reponseAdmin, $statut)
    {
        $this->etape = $etape;
        $this->reponseAdmin = $reponseAdmin;
        $this->statut = $statut;
    }

    public function via(object $notifiable): array
    {
        return [\App\Notifications\Channels\FrenchDatabaseChannel::class, 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->statut === 'Validé' ? 'validée' : 'rejetée';
        $subject = "Validation de votre étape : " . $this->etape->Titre;

        return (new MailMessage)
                    ->subject($subject)
                    ->view('emails.notification', [
                        'prenom' => $notifiable->profil->Prenom ?? 'Membre ShiftUp',
                        'titre' => $subject,
                        'description' => $this->reponseAdmin,
                        'actionText' => 'Voir mon avancement',
                        'actionUrl' => url('/programmes'),
                        'introLines' => ["L'administrateur a " . $statusLabel . " votre réponse pour l'étape : " . $this->etape->Titre]
                    ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $statusLabel = $this->statut === 'Validé' ? 'validée' : 'rejetée';
        return [
            'message' => "Votre étape \"" . $this->etape->Titre . "\" a été " . $statusLabel . ".",
            'icone' => $this->statut === 'Validé' ? 'fas fa-check-circle' : 'fas fa-times-circle',
            'lien' => '/programmes',
            'TypeObjet' => 'validation_etape'
        ];
    }
}
