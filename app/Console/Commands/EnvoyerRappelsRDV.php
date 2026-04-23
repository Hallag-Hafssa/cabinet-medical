<?php

namespace App\Console\Commands;

use App\Models\RendezVous;
use App\Notifications\RendezVousRappel;
use Illuminate\Console\Command;

class EnvoyerRappelsRDV extends Command
{
    protected $signature = 'rdv:rappels';
    protected $description = 'Envoyer un email de rappel pour les RDV de demain';

    public function handle(): void
    {
        $rdvsDemain = RendezVous::with(['patient.user'])
            ->whereDate('date_heure', now()->addDay()->toDateString())
            ->whereIn('statut', ['en_attente', 'confirme'])
            ->get();

        foreach ($rdvsDemain as $rdv) {
            $rdv->patient->user->notify(new RendezVousRappel($rdv));
        }

        $this->info("Rappels envoyés : {$rdvsDemain->count()} emails.");
    }
}

/*
 * À enregistrer dans routes/console.php (Laravel 11) :
 *
 * use Illuminate\Support\Facades\Schedule;
 *
 * Schedule::command('rdv:rappels')->dailyAt('18:00');
 */
