@extends('layouts.app')
@section('title', 'Détail consultation')
@section('page-title', 'Consultation du ' . $consultation->date_consultation->format('d/m/Y'))

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600"><i class="fas fa-notes-medical"></i></div>
                <div>
                    <p class="font-medium text-gray-800">{{ $consultation->patient->user->prenom }} {{ $consultation->patient->user->nom }}</p>
                    <p class="text-sm text-gray-400">{{ $consultation->date_consultation->format('d/m/Y à H:i') }}</p>
                </div>
            </div>
            @if(!$consultation->ordonnance)
                <a href="{{ route('medecin.ordonnance.create', $consultation) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-prescription mr-1"></i>Rédiger une ordonnance
                </a>
            @endif
        </div>

        <div class="space-y-4">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Motif</p>
                <p class="text-sm text-gray-700">{{ $consultation->motif ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Diagnostic</p>
                <p class="text-sm text-gray-700">{{ $consultation->diagnostic }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Compte-rendu</p>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $consultation->compte_rendu }}</p>
            </div>
            @if($consultation->notes)
            <div class="p-3 bg-yellow-50 rounded-lg">
                <p class="text-xs font-semibold text-yellow-600 mb-1">Notes internes</p>
                <p class="text-sm text-yellow-700">{{ $consultation->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    @if($consultation->ordonnance)
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-prescription text-blue-500 mr-2"></i>Ordonnance #{{ $consultation->ordonnance->id }}</h3>
            <a href="{{ route('medecin.ordonnance.pdf', $consultation->ordonnance) }}" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">
                <i class="fas fa-file-pdf mr-1"></i>Télécharger PDF
            </a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-2 font-medium text-gray-500">Médicament</th>
                    <th class="text-left px-4 py-2 font-medium text-gray-500">Dosage</th>
                    <th class="text-left px-4 py-2 font-medium text-gray-500">Posologie</th>
                    <th class="text-left px-4 py-2 font-medium text-gray-500">Durée</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($consultation->ordonnance->medicaments as $med)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-700">{{ $med->nom }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $med->dosage }} — {{ $med->forme }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $med->pivot->posologie }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $med->pivot->duree }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($consultation->ordonnance->instructions)
            <div class="mt-4 p-3 bg-blue-50 rounded-lg text-sm text-blue-700">
                <strong>Instructions :</strong> {{ $consultation->ordonnance->instructions }}
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
