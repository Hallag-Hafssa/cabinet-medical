@extends('layouts.app')
@section('title', isset($disponibilite) ? 'Modifier créneau' : 'Nouveau créneau')
@section('page-title', isset($disponibilite) ? 'Modifier le créneau' : 'Ajouter un créneau')

@section('content')
<div class="max-w-md">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ isset($disponibilite) ? route('medecin.disponibilites.update', $disponibilite) : route('medecin.disponibilites.store') }}">
            @csrf
            @if(isset($disponibilite)) @method('PUT') @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jour de la semaine *</label>
                <select name="jour_semaine" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    @foreach(['lundi','mardi','mercredi','jeudi','vendredi','samedi'] as $jour)
                        <option value="{{ $jour }}" {{ old('jour_semaine', $disponibilite->jour_semaine ?? '') === $jour ? 'selected' : '' }}>{{ ucfirst($jour) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Heure début *</label>
                    <input type="time" name="heure_debut" required value="{{ old('heure_debut', isset($disponibilite) ? substr($disponibilite->heure_debut, 0, 5) : '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Heure fin *</label>
                    <input type="time" name="heure_fin" required value="{{ old('heure_fin', isset($disponibilite) ? substr($disponibilite->heure_fin, 0, 5) : '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700"><i class="fas fa-save mr-1"></i>Enregistrer</button>
                <a href="{{ route('medecin.disponibilites.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
