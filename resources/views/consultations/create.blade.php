@extends('layouts.app')
@section('title', 'Nouvelle consultation')
@section('page-title', 'Consultation — ' . $patient->user->prenom . ' ' . $patient->user->nom)

@section('content')
<div class="grid grid-cols-3 gap-6">
    {{-- Patient info sidebar --}}
    <div class="col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-24">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-sm">
                    {{ strtoupper(substr($patient->user->prenom, 0, 1) . substr($patient->user->nom, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-gray-800">{{ $patient->user->prenom }} {{ $patient->user->nom }}</p>
                    <p class="text-xs text-gray-400">{{ $patient->age ?? '—' }} ans · {{ $patient->sexe ?? '' }}</p>
                </div>
            </div>
            @if($patient->allergies)
                <div class="p-2 bg-red-50 rounded text-xs text-red-600 mb-3"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $patient->allergies }}</div>
            @endif
            @if($patient->antecedents)
                <div class="p-2 bg-yellow-50 rounded text-xs text-yellow-600 mb-3"><i class="fas fa-notes-medical mr-1"></i>{{ $patient->antecedents }}</div>
            @endif

            <p class="text-xs font-semibold text-gray-400 uppercase mt-4 mb-2">Dernières consultations</p>
            @forelse($patient->consultations->sortByDesc('date_consultation')->take(3) as $c)
                <div class="py-2 border-b border-gray-50 last:border-0">
                    <p class="text-xs text-gray-500">{{ $c->date_consultation->format('d/m/Y') }} — Dr. {{ $c->medecin->user->nom }}</p>
                    <p class="text-xs text-gray-700">{{ Str::limit($c->diagnostic, 60) }}</p>
                </div>
            @empty
                <p class="text-xs text-gray-400">Première visite</p>
            @endforelse
        </div>
    </div>

    {{-- Formulaire consultation --}}
    <div class="col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <form method="POST" action="{{ route('medecin.consultation.store') }}">
                @csrf
                <input type="hidden" name="rendez_vous_id" value="{{ $rendezVous->id }}">

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motif de consultation</label>
                    <input type="text" name="motif" value="{{ old('motif', $rendezVous->motif) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Diagnostic *</label>
                    <textarea name="diagnostic" rows="3" required placeholder="Résultat de l'examen clinique..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('diagnostic') }}</textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Compte-rendu *</label>
                    <textarea name="compte_rendu" rows="5" required placeholder="Description détaillée de la consultation..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('compte_rendu') }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes internes</label>
                    <textarea name="notes" rows="2" placeholder="Notes privées (non visibles par le patient)..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700">
                        <i class="fas fa-save mr-1"></i>Enregistrer la consultation
                    </button>
                    <a href="{{ route('medecin.planning') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
