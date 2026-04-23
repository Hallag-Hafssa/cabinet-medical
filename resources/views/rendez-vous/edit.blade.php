@extends('layouts.app')
@section('title', 'Modifier rendez-vous')
@section('page-title', 'Modifier le rendez-vous')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('secretaire.rendez-vous.update', $rendezVous) }}">
            @csrf @method('PUT')

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm text-gray-600">
                <p><strong>Patient :</strong> {{ $rendezVous->patient->user->prenom }} {{ $rendezVous->patient->user->nom }}</p>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Médecin *</label>
                <select name="medecin_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    @foreach($medecins as $medecin)
                        <option value="{{ $medecin->id }}" {{ $rendezVous->medecin_id == $medecin->id ? 'selected' : '' }}>
                            Dr. {{ $medecin->user->prenom }} {{ $medecin->user->nom }} — {{ $medecin->specialite->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Date et heure *</label>
                <input type="datetime-local" name="date_heure" required
                    value="{{ old('date_heure', $rendezVous->date_heure->format('Y-m-d\TH:i')) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut *</label>
                <select name="statut" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    @foreach(['en_attente' => 'En attente', 'confirme' => 'Confirmé', 'annule' => 'Annulé', 'termine' => 'Terminé'] as $val => $label)
                        <option value="{{ $val }}" {{ $rendezVous->statut === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Motif</label>
                <textarea name="motif" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">{{ old('motif', $rendezVous->motif) }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i>Mettre à jour
                </button>
                <a href="{{ route('secretaire.rendez-vous.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
