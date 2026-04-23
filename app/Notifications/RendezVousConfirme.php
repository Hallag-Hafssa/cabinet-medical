<?php

namespace App\Notifications;

use App\Models\RendezVous;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RendezVousConfirme extends Notification
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
        $rdv = $this->rendezVous->load(['medecin.user', 'medecin.specialite']);

        return (new MailMessage)
            ->subject('Confirmation de votre rendez-vous')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line('Votre rendez-vous a été enregistré avec succès.')
            ->line('**Médecin** : Dr. ' . $rdv->medecin->user->nom_complet)
            ->line('**Spécialité** : ' . $rdv->medecin->specialite->nom)
            ->line('**Date** : ' . $rdv->date_heure->format('d/m/Y à H:i'))
            ->line('**Motif** : ' . ($rdv->motif ?? 'Non précisé'))
            ->action('Voir mes rendez-vous', url('/patient/rendez-vous'))
            ->line('Merci de votre confiance.');
    }
}
