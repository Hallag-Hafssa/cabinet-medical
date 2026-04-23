<?php

namespace App\Http\Controllers;

use App\Models\Disponibilite;
use Illuminate\Http\Request;

class DisponibiliteController extends Controller
{
    public function index()
    {
        $disponibilites = auth()->user()->medecin->disponibilites()->orderByRaw("
            FIELD(jour_semaine, 'lundi','mardi','mercredi','jeudi','vendredi','samedi')
        ")->get();

        return view('disponibilites.index', compact('disponibilites'));
    }

    public function create()
    {
        return view('disponibilites.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jour_semaine' => 'required|in:lundi,mardi,mercredi,jeudi,vendredi,samedi',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
        ]);

        auth()->user()->medecin->disponibilites()->create($validated);

        return redirect()->route('medecin.disponibilites.index')
                         ->with('success', 'Disponibilité ajoutée.');
    }

    public function edit(Disponibilite $disponibilite)
    {
        return view('disponibilites.edit', compact('disponibilite'));
    }

    public function update(Request $request, Disponibilite $disponibilite)
    {
        $validated = $request->validate([
            'jour_semaine' => 'required|in:lundi,mardi,mercredi,jeudi,vendredi,samedi',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
        ]);

        $disponibilite->update($validated);

        return redirect()->route('medecin.disponibilites.index')
                         ->with('success', 'Disponibilité modifiée.');
    }

    public function destroy(Disponibilite $disponibilite)
    {
        $disponibilite->delete();

        return back()->with('success', 'Disponibilité supprimée.');
    }
}
