<?php

namespace Database\Seeders;

use App\Models\{User, Patient, Medecin, Secretaire, Specialite, Disponibilite, RendezVous, Consultation, Ordonnance, Medicament};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Spécialités ─────────────────────────────────
        $specialites = collect([
            'Médecine générale', 'Cardiologie', 'Dermatologie',
            'Pédiatrie', 'Ophtalmologie', 'Orthopédie',
        ])->map(fn ($nom) => Specialite::create(['nom' => $nom, 'description' => "Spécialité en $nom"]));

        // ─── Admin ───────────────────────────────────────
        User::create([
            'nom' => 'Admin', 'prenom' => 'Super',
            'email' => 'admin@cabinet.ma',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'telephone' => '0600000000',
        ]);

        // ─── Secrétaire ─────────────────────────────────
        $secUser = User::create([
            'nom' => 'Alami', 'prenom' => 'Fatima',
            'email' => 'secretaire@cabinet.ma',
            'password' => Hash::make('password'),
            'role' => 'secretaire',
            'telephone' => '0611111111',
        ]);
        Secretaire::create(['user_id' => $secUser->id, 'poste' => 'Accueil']);

        // ─── Médecins ───────────────────────────────────
        $medecinData = [
            ['nom' => 'Benani', 'prenom' => 'Ahmed', 'email' => 'dr.benani@cabinet.ma', 'specialite' => 0, 'matricule' => 'MED-001'],
            ['nom' => 'Tazi', 'prenom' => 'Sara', 'email' => 'dr.tazi@cabinet.ma', 'specialite' => 1, 'matricule' => 'MED-002'],
            ['nom' => 'El Fassi', 'prenom' => 'Youssef', 'email' => 'dr.elfassi@cabinet.ma', 'specialite' => 2, 'matricule' => 'MED-003'],
        ];

        $medecins = [];
        foreach ($medecinData as $data) {
            $user = User::create([
                'nom' => $data['nom'], 'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'medecin',
                'telephone' => '06' . rand(10000000, 99999999),
            ]);
            $medecin = Medecin::create([
                'user_id' => $user->id,
                'specialite_id' => $specialites[$data['specialite']]->id,
                'matricule' => $data['matricule'],
            ]);
            $medecins[] = $medecin;

            // Disponibilités : lundi à vendredi, 9h-12h et 14h-17h
            foreach (['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'] as $jour) {
                Disponibilite::create([
                    'medecin_id' => $medecin->id,
                    'jour_semaine' => $jour,
                    'heure_debut' => '09:00',
                    'heure_fin' => '12:00',
                ]);
                Disponibilite::create([
                    'medecin_id' => $medecin->id,
                    'jour_semaine' => $jour,
                    'heure_debut' => '14:00',
                    'heure_fin' => '17:00',
                ]);
            }
        }

        // ─── Patients ───────────────────────────────────
        $patientData = [
            ['nom' => 'Idrissi', 'prenom' => 'Karim', 'email' => 'karim@patient.ma', 'sexe' => 'homme'],
            ['nom' => 'Bennani', 'prenom' => 'Amina', 'email' => 'amina@patient.ma', 'sexe' => 'femme'],
            ['nom' => 'Chraibi', 'prenom' => 'Omar', 'email' => 'omar@patient.ma', 'sexe' => 'homme'],
            ['nom' => 'Mansouri', 'prenom' => 'Leila', 'email' => 'leila@patient.ma', 'sexe' => 'femme'],
            ['nom' => 'Ziani', 'prenom' => 'Mehdi', 'email' => 'mehdi@patient.ma', 'sexe' => 'homme'],
        ];

        $patients = [];
        foreach ($patientData as $data) {
            $user = User::create([
                'nom' => $data['nom'], 'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'patient',
                'telephone' => '06' . rand(10000000, 99999999),
            ]);
            $patients[] = Patient::create([
                'user_id' => $user->id,
                'date_naissance' => now()->subYears(rand(20, 60))->subDays(rand(1, 365)),
                'sexe' => $data['sexe'],
                'adresse' => 'Casablanca, Maroc',
                'groupe_sanguin' => ['A+', 'B+', 'O+', 'AB+', 'A-', 'O-'][rand(0, 5)],
            ]);
        }

        // ─── Médicaments ────────────────────────────────
        $medicaments = collect([
            ['nom' => 'Paracétamol', 'dosage' => '500mg', 'forme' => 'comprimé'],
            ['nom' => 'Amoxicilline', 'dosage' => '1g', 'forme' => 'comprimé'],
            ['nom' => 'Ibuprofène', 'dosage' => '400mg', 'forme' => 'comprimé'],
            ['nom' => 'Oméprazole', 'dosage' => '20mg', 'forme' => 'gélule'],
            ['nom' => 'Metformine', 'dosage' => '850mg', 'forme' => 'comprimé'],
            ['nom' => 'Vitamine D3', 'dosage' => '1000UI', 'forme' => 'gouttes'],
            ['nom' => 'Doliprane', 'dosage' => '1000mg', 'forme' => 'comprimé'],
            ['nom' => 'Augmentin', 'dosage' => '1g', 'forme' => 'sachet'],
        ])->map(fn ($m) => Medicament::create($m));

        // ─── RDV + Consultations + Ordonnances ──────────
        foreach ($patients as $i => $patient) {
            $medecin = $medecins[$i % count($medecins)];

            // 2 RDV passés (terminés) + 1 à venir
            for ($j = 0; $j < 2; $j++) {
                $datePassee = now()->subDays(rand(5, 30))->setHour(rand(9, 16))->setMinute(0);

                $rdv = RendezVous::create([
                    'patient_id' => $patient->id,
                    'medecin_id' => $medecin->id,
                    'date_heure' => $datePassee,
                    'duree_minutes' => 30,
                    'statut' => 'termine',
                    'motif' => ['Douleurs abdominales', 'Consultation de routine', 'Mal de tête persistant', 'Contrôle annuel'][rand(0, 3)],
                ]);

                $consultation = Consultation::create([
                    'rendez_vous_id' => $rdv->id,
                    'medecin_id' => $medecin->id,
                    'patient_id' => $patient->id,
                    'motif' => $rdv->motif,
                    'diagnostic' => ['Grippe saisonnière', 'Hypertension légère', 'Carence en vitamine D', 'RAS - bilan normal'][rand(0, 3)],
                    'notes' => 'Patient en bon état général.',
                    'compte_rendu' => 'Examen clinique effectué. Prescription de traitement.',
                    'date_consultation' => $datePassee,
                ]);

                // Ordonnance pour certains
                if (rand(0, 1)) {
                    $ordonnance = Ordonnance::create([
                        'consultation_id' => $consultation->id,
                        'medecin_id' => $medecin->id,
                        'patient_id' => $patient->id,
                        'date_ordonnance' => $datePassee->toDateString(),
                        'instructions' => 'Bien respecter les horaires de prise.',
                    ]);

                    // 2-3 médicaments
                    $medsAleatoires = $medicaments->random(rand(2, 3));
                    foreach ($medsAleatoires as $med) {
                        $ordonnance->medicaments()->attach($med->id, [
                            'posologie' => ['1 cp matin et soir', '1 cp 3x/jour', '1 sachet/jour'][rand(0, 2)],
                            'duree' => ['5 jours', '7 jours', '10 jours', '1 mois'][rand(0, 3)],
                        ]);
                    }
                }
            }

            // 1 RDV à venir
            RendezVous::create([
                'patient_id' => $patient->id,
                'medecin_id' => $medecins[rand(0, count($medecins) - 1)]->id,
                'date_heure' => now()->addDays(rand(1, 14))->setHour(rand(9, 16))->setMinute(0),
                'duree_minutes' => 30,
                'statut' => ['en_attente', 'confirme'][rand(0, 1)],
                'motif' => 'Suivi médical',
            ]);
        }

        $this->command->info('Données de démonstration créées avec succès !');
        $this->command->info('Connexion admin  : admin@cabinet.ma / password');
        $this->command->info('Connexion médecin: dr.benani@cabinet.ma / password');
        $this->command->info('Connexion patient: karim@patient.ma / password');
    }
}
