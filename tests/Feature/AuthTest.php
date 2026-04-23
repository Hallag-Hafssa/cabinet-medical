<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── INSCRIPTION ─────────────────────────────────────

    public function test_page_inscription_accessible(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_inscription_patient_reussie(): void
    {
        $response = $this->post('/register', [
            'nom' => 'Tazi',
            'prenom' => 'Sara',
            'email' => 'sara@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'telephone' => '0612345678',
            'date_naissance' => '1995-03-15',
            'sexe' => 'femme',
        ]);

        $response->assertRedirect(route('patient.rdv.index'));
        $this->assertAuthenticated();

        // Vérifie que l'utilisateur a le rôle patient
        $user = User::where('email', 'sara@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('patient', $user->role);

        // Vérifie que la fiche patient est créée automatiquement
        $this->assertNotNull($user->patient);
        $this->assertEquals('femme', $user->patient->sexe);
    }

    public function test_inscription_echoue_email_duplique(): void
    {
        User::factory()->create(['email' => 'existe@test.com']);

        $response = $this->post('/register', [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'existe@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_inscription_echoue_mot_de_passe_court(): void
    {
        $response = $this->post('/register', [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@test.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_inscription_echoue_confirmation_mot_de_passe(): void
    {
        $response = $this->post('/register', [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ─── CONNEXION ───────────────────────────────────────

    public function test_page_connexion_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_connexion_reussie(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'role' => 'patient',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
    }

    public function test_connexion_echoue_mauvais_mot_de_passe(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'mauvais_mdp',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_connexion_echoue_email_inexistant(): void
    {
        $response = $this->post('/login', [
            'email' => 'nexistepas@test.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    // ─── DÉCONNEXION ─────────────────────────────────────

    public function test_deconnexion_reussie(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
    }

    // ─── REDIRECTION PAR RÔLE ────────────────────────────

    public function test_patient_redirige_vers_ses_rdv(): void
    {
        $user = User::factory()->create(['role' => 'patient']);
        Patient::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // Le dashboard redirige selon le rôle
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect(route('patient.rdv.index'));
    }

    public function test_admin_redirige_vers_dashboard_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect(route('admin.dashboard'));
    }

    // ─── CONTRÔLE D'ACCÈS PAR RÔLE ──────────────────────

    public function test_patient_ne_peut_pas_acceder_admin(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_patient_ne_peut_pas_acceder_pages_medecin(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($user)->get('/medecin/planning');
        $response->assertStatus(403);
    }

    public function test_medecin_ne_peut_pas_acceder_admin(): void
    {
        $user = User::factory()->create(['role' => 'medecin']);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_visiteur_redirige_vers_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect(route('login'));
    }

    // ─── MOT DE PASSE HASHÉ ─────────────────────────────

    public function test_mot_de_passe_est_hashe(): void
    {
        $this->post('/register', [
            'nom' => 'Hash',
            'prenom' => 'Test',
            'email' => 'hash@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'hash@test.com')->first();
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(\Hash::check('password123', $user->password));
    }
}
