<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Models\Notification as NotificationModel;

class FrenchDatabaseChannel
{
    /**
     * Envoyer la notification donnée.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \App\Models\Notification
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toDatabase($notifiable);

        return NotificationModel::create([
            'Id' => $notification->id,
            'Type' => get_class($notification),
            'TypeDestinataire' => get_class($notifiable),
            'IdDestinataire' => $notifiable->getKey(),
            'Donnees' => $data,
            'DateLecture' => null,
        ]);
    }
}
