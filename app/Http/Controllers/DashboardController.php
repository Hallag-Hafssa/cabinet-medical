<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'medecin' => redirect()->route('medecin.planning'),
            'secretaire' => redirect()->route('secretaire.patients.index'),
            'patient' => redirect()->route('patient.rdv.index'),
        };
    }
}
