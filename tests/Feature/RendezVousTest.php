<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Medecin;
use App\Models\Specialite;
use App\Models\Disponibilite;
use App\Models\RendezVous;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RendezVousTest extends TestCase
{
    use RefreshDatabase;

    private User $patientUser;
    private Patient $patient;
    private Medecin $medecin;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un médecin avec disponibilités
        $specialite = Specialite::create(['nom' => 'Médecine générale']);
        $medecinUser = User::factory()->create(['role' => 'medecin']);
        $this->medecin = Medecin::create([
            'user_id' => $medecinUser->id,
            'specialite_id' => $specialite->id,
            'matricule' => 'MED-TEST',
        ]);

        // Disponibilités : lundi à vendredi 9h-17h
        foreach (['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'] as $jour) {
            Disponibilite::create([
                'medecin_id' => $this->medecin->id,
                'jour_semaine' => $jour,
                'heure_debut' => '09:00',
                'heure_fin' => '17:00',
            ]);
        }

        // Créer un patient
        $this->patientUser = User::factory()->create(['role' => 'patient']);
        $this->patient = Patient::create(['user_id' => $this->patientUser->id]);
    }

    // ─── PRISE DE RDV ────────────────────────────────────

    public function test_patient_peut_voir_formulaire_rdv(): void
    {
        $response = $this->actingAs($this->patientUser)->get('/patient/rendez-vous/create');
        $response->assertStatus(200);
    }

    public function test_patient_peut_prendre_rdv(): void
    {
        // Trouver le prochain lundi
        $prochainLundi = now()->next('Monday')->setHour(10)->setMinute(0);

        $response = $this->actingAs($this->patientUser)->post('/patient/rendez-vous', [
            'medecin_id' => $this->medecin->id,
            'date_heure' => $prochainLundi->format('Y-m-d H:i:s'),
            'motif' => 'Consultation de routine',
        ]);

        $response->assertRedirect(route('patient.rdv.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rendez_vous', [
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'statut' => 'en_attente',
            'motif' => 'Consultation de routine',
        ]);
    }

    public function test_rdv_refuse_si_creneau_passe(): void
    {
        $datePasse = now()->subDay();

        $response = $this->actingAs($this->patientUser)->post('/patient/rendez-vous', [
            'medecin_id' => $this->medecin->id,
            'date_heure' => $datePasse->format('Y-m-d H:i:s'),
            'motif' => 'Test',
        ]);

        $response->assertSessionHasErrors('date_heure');
    }

    public function test_rdv_refuse_si_medecin_inexistant(): void
    {
        $response = $this->actingAs($this->patientUser)->post('/patient/rendez-vous', [
            'medecin_id' => 9999,
            'date_heure' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'motif' => 'Test',
        ]);

        $response->assertSessionHasErrors('medecin_id');
    }

    // ─── CONFLIT D'HORAIRE ───────────────────────────────

    public function test_rdv_refuse_si_conflit_horaire(): void
    {
        $prochainLundi = now()->next('Monday')->setHour(10)->setMinute(0);

        // Premier RDV
        RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => $prochainLundi,
            'duree_minutes' => 30,
            'statut' => 'confirme',
        ]);

        // Deuxième patient tente le même créneau
        $autrePatientUser = User::factory()->create(['role' => 'patient']);
        $autrePatient = Patient::create(['user_id' => $autrePatientUser->id]);

        $response = $this->actingAs($autrePatientUser)->post('/patient/rendez-vous', [
            'medecin_id' => $this->medecin->id,
            'date_heure' => $prochainLundi->format('Y-m-d H:i:s'),
            'motif' => 'Test conflit',
        ]);

        $response->assertSessionHasErrors('date_heure');
    }

    // ─── STATUTS ─────────────────────────────────────────

    public function test_confirmer_rdv(): void
    {
        $rdv = RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now()->addDays(3),
            'duree_minutes' => 30,
            'statut' => 'en_attente',
        ]);

        $medecinUser = $this->medecin->user;
        $response = $this->actingAs($medecinUser)->put("/medecin/rendez-vous/{$rdv->id}/confirmer");

        $response->assertSessionHas('success');
        $this->assertEquals('confirme', $rdv->fresh()->statut);
    }

    public function test_annuler_rdv(): void
    {
        $rdv = RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now()->addDays(3),
            'duree_minutes' => 30,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($this->patientUser)->put("/patient/rendez-vous/{$rdv->id}/annuler");

        $response->assertSessionHas('success');
        $this->assertEquals('annule', $rdv->fresh()->statut);
    }

    public function test_terminer_rdv(): void
    {
        $rdv = RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now()->subHour(),
            'duree_minutes' => 30,
            'statut' => 'confirme',
        ]);

        $rdv->terminer();
        $this->assertEquals('termine', $rdv->fresh()->statut);
    }

    // ─── SCOPES ──────────────────────────────────────────

    public function test_scope_a_venir(): void
    {
        // RDV passé
        RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now()->subDay(),
            'statut' => 'termine',
        ]);

        // RDV futur
        RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now()->addDays(5),
            'statut' => 'confirme',
        ]);

        // RDV annulé futur (ne doit pas apparaître)
        RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now()->addDays(7),
            'statut' => 'annule',
        ]);

        $aVenir = RendezVous::aVenir()->get();
        $this->assertCount(1, $aVenir);
        $this->assertEquals('confirme', $aVenir->first()->statut);
    }

    // ─── LISTE DES RDV ──────────────────────────────────

    public function test_patient_voit_ses_rdv(): void
    {
        RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now()->addDays(2),
            'statut' => 'en_attente',
            'motif' => 'Ma consultation',
        ]);

        $response = $this->actingAs($this->patientUser)->get('/patient/rendez-vous');
        $response->assertStatus(200);
        $response->assertSee('Ma consultation');
    }
}
