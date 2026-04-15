@extends('layouts.app')
@section('title', isset($patient) ? 'Modifier patient' : 'Nouveau patient')
@section('page-title', isset($patient) ? 'Modifier la fiche patient' : 'Nouveau patient')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ isset($patient) ? route('secretaire.patients.update', $patient) : route('secretaire.patients.store') }}">
            @csrf
            @if(isset($patient)) @method('PUT') @endif

            <h3 class="text-sm font-semibold text-gray-400 uppercase mb-4">Informations personnelles</h3>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="nom" value="{{ old('nom', isset($patient) ? $patient->user->nom : '') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                    <input type="text" name="prenom" value="{{ old('prenom', isset($patient) ? $patient->user->prenom : '') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            @if(!isset($patient))
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            @endif

            <div class="grid grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone', isset($patient) ? $patient->user->telephone : '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                    <input type="date" name="date_naissance" value="{{ old('date_naissance', isset($patient) ? $patient->date_naissance?->format('Y-m-d') : '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sexe</label>
                    <select name="sexe" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">—</option>
                        <option value="homme" {{ old('sexe', $patient->sexe ?? '') === 'homme' ? 'selected' : '' }}>Homme</option>
                        <option value="femme" {{ old('sexe', $patient->sexe ?? '') === 'femme' ? 'selected' : '' }}>Femme</option>
                    </select>
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-400 uppercase mb-4">Informations médicales</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Groupe sanguin</label>
                    <select name="groupe_sanguin" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">—</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gs)
                            <option value="{{ $gs }}" {{ old('groupe_sanguin', $patient->groupe_sanguin ?? '') === $gs ? 'selected' : '' }}>{{ $gs }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', $patient->adresse ?? '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Allergies</label>
                <textarea name="allergies" rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('allergies', $patient->allergies ?? '') }}</textarea>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Antécédents médicaux</label>
                <textarea name="antecedents" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('antecedents', $patient->antecedents ?? '') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i>{{ isset($patient) ? 'Mettre à jour' : 'Créer le patient' }}
                </button>
                <a href="{{ route('secretaire.patients.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
