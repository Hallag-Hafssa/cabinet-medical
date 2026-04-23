<?php

namespace App\Notifications;

use App\Models\RendezVous;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RendezVousRappel extends Notification
{
    use Queueable;

    public function __construct(
        protected RendezVous $rendezVous
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rdv = $this->rendezVous->load(['medecin.user']);

        return (new MailMessage)
            ->subject('Rappel : rendez-vous demain')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line('Nous vous rappelons votre rendez-vous prévu demain.')
            ->line('**Médecin** : Dr. ' . $rdv->medecin->user->nom_complet)
            ->line('**Date** : ' . $rdv->date_heure->format('d/m/Y à H:i'))
            ->action('Voir mes rendez-vous', url('/patient/rendez-vous'))
            ->line('À demain !');
    }
}
