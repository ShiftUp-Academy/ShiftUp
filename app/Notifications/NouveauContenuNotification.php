<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouveauContenuNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    protected $contenu;
    protected $typeContenu;

    public function __construct($contenu, $typeContenu)
    {
        $this->contenu = $contenu;
        $this->typeContenu = $typeContenu;
    }

    public function via(object $notifiable): array
    {
        return [\App\Notifications\Channels\FrenchDatabaseChannel::class, 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $titre = $this->contenu->Titre ?? $this->contenu->Nom ?? $this->contenu->NomDeType ?? 'Nouveau Contenu';
        $description = $this->contenu->Descriptions ?? $this->contenu->Description ?? '';
        
        $imagePath = $this->contenu->LienPhoto ?? $this->contenu->Image ?? $this->contenu->Photo ?? null;
        
        $image = url('images/categorie.jpg'); // Correction accent possible source d'erreur

        if ($imagePath) {
            if (str_starts_with($imagePath, 'http')) {
                $image = $imagePath;
            } else {
                // S'assurer que le chemin commence par / pour url()
                $path = str_starts_with($imagePath, '/') ? $imagePath : '/' . $imagePath;
                $image = url($path);
            }
        }

        return (new MailMessage)
                    ->subject('Nouveau ' . $this->typeContenu . ' sur ShiftUp : ' . $titre)
                    ->view('emails.notification', [
                        'prenom' => $notifiable->profil->Prenom ?? 'Membre ShiftUp',
                        'titre' => $this->typeContenu . ' : ' . $titre,
                        'description' => $description,
                        'image' => $image,
                        'actionText' => 'Découvrir maintenant',
                        'actionUrl' => url($this->getLien()),
                        'introLines' => [
                            'Nous sommes ravis de vous annoncer l\'arrivée d\'un nouveau contenu qui pourrait vous intéresser !',
                            'Merci de faire partie de la communauté ShiftUp !'
                        ]
                    ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Nouveau ' . $this->typeContenu . ' : ' . ($this->contenu->Titre ?? $this->contenu->Nom),
            'icone' => $this->getIcone(),
            'lien' => $this->getLien(),
            'TypeObjet' => $this->typeContenu
        ];
    }

    protected function getIcone()
    {
        return match(strtolower($this->typeContenu)) {
            'programme' => 'fas fa-graduation-cap',
            'seminaire' => 'fas fa-users',
            'coaching' => 'fas fa-user-tie',
            'live' => 'fas fa-video',
            'offre' => 'fas fa-tag',
            default => 'fas fa-bell'
        };
    }

    protected function getLien()
    {
        return match(strtolower($this->typeContenu)) {
            'programme' => '/programmes',
            'seminaire' => '/seminaires',
            'coaching' => '/coaching',
            'live' => '/live',
            'offre' => '/offres',
            default => '/'
        };
    }
}
