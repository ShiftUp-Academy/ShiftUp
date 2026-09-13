<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReponseAdminNotification extends Notification
{
    // Removed Queueable to send emails immediately
    // use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $consultation;
    protected $reponse;

    public function __construct($consultation, $reponse)
    {
        $this->consultation = $consultation;
        $this->reponse = $reponse;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [\App\Notifications\Channels\FrenchDatabaseChannel::class, 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Essayer de récupérer l'image du programme lié si disponible
        $image = null;
        if ($this->consultation->lecon && $this->consultation->lecon->theme && $this->consultation->lecon->theme->programme) {
             $image = $this->consultation->lecon->theme->programme->Image;
        }

        $titreConsultation = $this->consultation->Titre;
        if (is_array($titreConsultation)) {
            $titreConsultation = $titreConsultation[app()->getLocale()] ?? $titreConsultation['fr'] ?? reset($titreConsultation) ?? '';
        }

        $desc = $this->reponse->Descriptions ?? $this->reponse->Titre ?? '';
        if (is_array($desc)) {
            $desc = $desc[app()->getLocale()] ?? $desc['fr'] ?? reset($desc) ?? '';
        }

        return (new MailMessage)
                    ->subject('Réponse à votre question : ' . $titreConsultation)
                    ->view('emails.notification', [
                        'prenom' => $notifiable->profil->Prenom ?? 'Membre ShiftUp',
                        'titre' => 'Réponse à : ' . $titreConsultation,
                        'description' => \Illuminate\Support\Str::limit(strip_tags((string)$desc), 200), // Extrait de la réponse
                        'image' => $image,
                        'actionText' => 'Voir la réponse complète',
                        'actionUrl' => url('/consultations'),
                        'introLines' => ['L\'administrateur vient de répondre à votre consultation. Voici un aperçu de la réponse :']
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        $titreConsultation = $this->consultation->Titre;
        if (is_array($titreConsultation)) {
            $titreConsultation = $titreConsultation[app()->getLocale()] ?? $titreConsultation['fr'] ?? reset($titreConsultation) ?? '';
        }

        return [
            'message' => 'L\'admin a répondu à votre question : ' . $titreConsultation,
            'icone' => 'fas fa-reply',
            'lien' => '/consultations',
            'TypeObjet' => 'reponse'
        ];
    }
}
