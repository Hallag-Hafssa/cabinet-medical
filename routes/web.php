<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\OrdonnanceController;
use App\Http\Controllers\DisponibiliteController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Routes authentifiées (tous les rôles)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [AuthController::class, 'profil'])->name('profil');
    Route::put('/profil', [AuthController::class, 'updateProfil'])->name('profil.update');

    /*
    |----------------------------------------------------------------------
    | Patient : ses propres RDV
    |----------------------------------------------------------------------
    */
    Route::middleware('role:patient')->prefix('patient')->name('patient.')->group(function () {
        Route::get('/rendez-vous', [RendezVousController::class, 'mesRendezVous'])->name('rdv.index');
        Route::get('/rendez-vous/create', [RendezVousController::class, 'create'])->name('rdv.create');
        Route::post('/rendez-vous', [RendezVousController::class, 'store'])->name('rdv.store');
        Route::put('/rendez-vous/{rendezVous}/annuler', [RendezVousController::class, 'annuler'])->name('rdv.annuler');
        Route::get('/historique', [PatientController::class, 'historique'])->name('historique');
    });

    /*
    |----------------------------------------------------------------------
    | Médecin : consultations, ordonnances, planning
    |----------------------------------------------------------------------
    */
    Route::middleware('role:medecin')->prefix('medecin')->name('medecin.')->group(function () {
        // Planning
        Route::get('/planning', [RendezVousController::class, 'planning'])->name('planning');
        Route::resource('/disponibilites', DisponibiliteController::class)->except(['show']);

        // RDV du jour
        Route::get('/rendez-vous', [RendezVousController::class, 'mesPatientsAujourdhui'])->name('rdv.index');
        Route::put('/rendez-vous/{rendezVous}/confirmer', [RendezVousController::class, 'confirmer'])->name('rdv.confirmer');

        // Consultations
        Route::get('/consultation/create/{rendezVous}', [ConsultationController::class, 'create'])->name('consultation.create');
        Route::post('/consultation', [ConsultationController::class, 'store'])->name('consultation.store');
        Route::get('/consultation/{consultation}', [ConsultationController::class, 'show'])->name('consultation.show');

        // Ordonnances
        Route::get('/ordonnance/create/{consultation}', [OrdonnanceController::class, 'create'])->name('ordonnance.create');
        Route::post('/ordonnance', [OrdonnanceController::class, 'store'])->name('ordonnance.store');
        Route::get('/ordonnance/{ordonnance}/pdf', [OrdonnanceController::class, 'exportPDF'])->name('ordonnance.pdf');
    });

    /*
    |----------------------------------------------------------------------
    | Secrétaire : gestion patients et RDV
    |----------------------------------------------------------------------
    */
    Route::middleware('role:secretaire,admin')->prefix('secretaire')->name('secretaire.')->group(function () {
        // Patients
        Route::resource('/patients', PatientController::class);
        Route::get('/patients/search', [PatientController::class, 'search'])->name('patients.search');

        // RDV (gestion complète)
        Route::resource('/rendez-vous', RendezVousController::class);
        Route::put('/rendez-vous/{rendezVous}/confirmer', [RendezVousController::class, 'confirmer'])->name('rdv.confirmer');
        Route::put('/rendez-vous/{rendezVous}/annuler', [RendezVousController::class, 'annuler'])->name('rdv.annuler');
    });

    /*
    |----------------------------------------------------------------------
    | Admin : gestion complète + dashboard statistique
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('/utilisateurs', AdminController::class);

        // API pour les graphiques Chart.js
        Route::get('/stats/rdv-par-mois', [AdminController::class, 'statsRdvParMois'])->name('stats.rdv');
        Route::get('/stats/patients-par-specialite', [AdminController::class, 'statsPatientsParSpecialite'])->name('stats.specialites');
        Route::get('/stats/taux-annulation', [AdminController::class, 'statsTauxAnnulation'])->name('stats.annulation');
        Route::get('/stats/rdv-par-jour', [AdminController::class, 'statsRdvParJour'])->name('stats.rdv-jour');
    });
});
