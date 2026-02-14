<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Utilisateur;

class ActiviteAdminNotification extends Notification
{
    // Removed ShouldQueue to send emails immediately
    // use Queueable;

    protected $message;
    protected $type;
    protected $lien;
    protected $data;

    /**
     * Create a new notification instance.
     *
     * @param string $message Message de notification
     * @param string $type Type d'activité (commande, consultation, temoignage)
     * @param string $lien Lien vers la page d'admin concernée
     * @param mixed $data Données supplémentaires (optionnel)
     */
    public function __construct($message, $type, $lien, $data = null)
    {
        $this->message = $message;
        $this->type = $type;
        $this->lien = $lien;
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return [\App\Notifications\Channels\FrenchDatabaseChannel::class, 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Nouvelle activité sur ShiftUp : ' . $this->type)
                    ->greeting('Bonjour Admin,')
                    ->line($this->message)
                    ->action('Gérer maintenant', url($this->lien))
                    ->line('Bon courage !');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'icone' => $this->getIcone(),
            'lien' => $this->lien,
            'TypeObjet' => $this->type,
            'data' => $this->data
        ];
    }

    protected function getIcone()
    {
        return match(strtolower($this->type)) {
            'commande' => 'fas fa-shopping-cart',
            'consultation' => 'fas fa-question-circle',
            'temoignage' => 'fas fa-comment-dots',
            default => 'fas fa-bell'
        };
    }
}
