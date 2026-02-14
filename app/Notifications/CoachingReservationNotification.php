<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoachingReservationNotification extends Notification
{
    protected $reservation;
    protected $typeNotification; // 'admin' ou 'client'

    /**
     * Create a new notification instance.
     */
    public function __construct($reservation, $typeNotification = 'admin')
    {
        $this->reservation = $reservation;
        $this->typeNotification = $typeNotification;
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
        $clientName = ($this->reservation->utilisateur->profil->Prenom ?? '') . ' ' . ($this->reservation->utilisateur->profil->Nom ?? '');
        $coachingName = $this->reservation->type->NomDeType ?? 'Coaching';
        $date = $this->reservation->disponibilite->DateDisponible;
        $heure = $this->reservation->HeureDebutReservation;

        if ($this->typeNotification === 'admin') {
            return (new MailMessage)
                ->subject('Nouvelle réservation de coaching : ' . $clientName)
                ->view('emails.notification', [
                    'prenom' => 'Admin',
                    'titre' => 'Nouvelle Réservation Coaching',
                    'description' => "Client : {$clientName}\nType : {$coachingName}\nDate : {$date} à {$heure}",
                    'image' => url('images/categorie.jpg'),
                    'actionText' => 'Gérer les réservations',
                    'actionUrl' => url('/admin/coachings'),
                    'introLines' => [
                        "Une nouvelle demande de coaching vient d'être effectuée par {$clientName}.",
                        "Pensez à traiter cette demande rapidement."
                    ]
                ]);
        } else {
            return (new MailMessage)
                ->subject('Confirmation de votre réservation - ShiftUp')
                ->view('emails.notification', [
                    'prenom' => $this->reservation->utilisateur->profil->Prenom ?? 'Membre ShiftUp',
                    'titre' => 'Confirmation de Réservation',
                    'description' => "Coaching : {$coachingName}\nDate : {$date}\nHeure : {$heure}\nStatut : En attente de confirmation.",
                    'image' => url('images/categorie.jpg'),
                    'actionText' => 'Accéder à mon compte',
                    'actionUrl' => url('/profil'),
                    'introLines' => [
                        "Votre demande de réservation a bien été reçue. Nous vous confirmerons le créneau très bientôt.",
                        "Merci de votre confiance !"
                    ]
                ]);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $clientName = ($this->reservation->utilisateur->profil->Prenom ?? '') . ' ' . ($this->reservation->utilisateur->profil->Nom ?? '');
        
        if ($this->typeNotification === 'admin') {
            return [
                'message' => 'Nouvelle réservation de ' . $clientName . ' pour ' . ($this->reservation->type->NomDeType ?? 'Coaching'),
                'icone' => 'fas fa-calendar-check',
                'lien' => '/admin/coachings',
                'TypeObjet' => 'ReservationCoaching'
            ];
        } else {
            return [
                'message' => 'Votre réservation pour ' . ($this->reservation->type->NomDeType ?? 'Coaching') . ' est enregistrée.',
                'icone' => 'fas fa-clock',
                'lien' => '/profil',
                'TypeObjet' => 'ReservationCoaching'
            ];
        }
    }
}
