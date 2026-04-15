@extends('layouts.app')
@section('title', 'Dossier patient')
@section('page-title', 'Dossier de ' . $patient->user->prenom . ' ' . $patient->user->nom)

@section('content')
<div class="grid grid-cols-3 gap-6">
    {{-- Infos patient --}}
    <div class="col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="text-center mb-4">
                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl mx-auto mb-3">
                    {{ strtoupper(substr($patient->user->prenom, 0, 1) . substr($patient->user->nom, 0, 1)) }}
                </div>
                <h3 class="font-semibold text-gray-800">{{ $patient->user->prenom }} {{ $patient->user->nom }}</h3>
                <p class="text-sm text-gray-400">Patient #{{ $patient->id }}</p>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="text-gray-700">{{ $patient->user->email }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Téléphone</span><span class="text-gray-700">{{ $patient->user->telephone ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Âge</span><span class="text-gray-700">{{ $patient->age ?? '—' }} ans</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Sexe</span><span class="text-gray-700">{{ ucfirst($patient->sexe ?? '—') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Groupe sanguin</span><span class="font-medium text-red-600">{{ $patient->groupe_sanguin ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Adresse</span><span class="text-gray-700">{{ $patient->adresse ?? '—' }}</span></div>
            </div>
            @if($patient->allergies)
                <div class="mt-4 p-3 bg-red-50 rounded-lg">
                    <p class="text-xs font-semibold text-red-600 mb-1"><i class="fas fa-exclamation-triangle mr-1"></i>Allergies</p>
                    <p class="text-sm text-red-700">{{ $patient->allergies }}</p>
                </div>
            @endif
            @if($patient->antecedents)
                <div class="mt-3 p-3 bg-yellow-50 rounded-lg">
                    <p class="text-xs font-semibold text-yellow-600 mb-1"><i class="fas fa-notes-medical mr-1"></i>Antécédents</p>
                    <p class="text-sm text-yellow-700">{{ $patient->antecedents }}</p>
                </div>
            @endif
            <a href="{{ route('secretaire.patients.edit', $patient) }}" class="block mt-4 text-center bg-gray-100 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-200">
                <i class="fas fa-edit mr-1"></i>Modifier la fiche
            </a>
        </div>
    </div>

    {{-- Historique --}}
    <div class="col-span-2 space-y-6">
        {{-- RDV à venir --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-calendar-check text-blue-500 mr-2"></i>Rendez-vous à venir</h3>
            @forelse($patient->rendezVous->where('statut', '!=', 'termine')->where('statut', '!=', 'annule') as $rdv)
                <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ $rdv->date_heure->format('d/m/Y à H:i') }}</p>
                        <p class="text-xs text-gray-400">Dr. {{ $rdv->medecin->user->nom }} — {{ $rdv->motif ?? 'Sans motif' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        {{ $rdv->statut === 'confirme' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }}">
                        {{ ucfirst(str_replace('_', ' ', $rdv->statut)) }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400">Aucun rendez-vous à venir.</p>
            @endforelse
        </div>

        {{-- Historique des consultations --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-file-medical text-green-500 mr-2"></i>Historique des consultations</h3>
            @forelse($patient->consultations->sortByDesc('date_consultation') as $consultation)
                <div class="py-4 border-b border-gray-50 last:border-0">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-700">{{ $consultation->date_consultation->format('d/m/Y') }}</p>
                        <span class="text-xs text-gray-400">Dr. {{ $consultation->medecin->user->nom }}</span>
                    </div>
                    <p class="text-sm text-gray-600"><span class="font-medium">Diagnostic :</span> {{ $consultation->diagnostic }}</p>
                    @if($consultation->ordonnance)
                        <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-600">
                            <i class="fas fa-prescription mr-1"></i>Ordonnance #{{ $consultation->ordonnance->id }}
                        </span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-400">Aucune consultation enregistrée.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
