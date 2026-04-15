@extends('layouts.app')
@section('title', 'Mon historique médical')
@section('page-title', 'Mon historique médical')

@section('content')
@forelse($consultations as $consultation)
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                <i class="fas fa-notes-medical"></i>
            </div>
            <div>
                <p class="font-medium text-gray-800">Consultation du {{ $consultation->date_consultation->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-400">Dr. {{ $consultation->medecin->user->prenom }} {{ $consultation->medecin->user->nom }}</p>
            </div>
        </div>
    </div>
    <div class="ml-13 space-y-2 text-sm">
        <p><span class="font-medium text-gray-600">Motif :</span> {{ $consultation->motif ?? '—' }}</p>
        <p><span class="font-medium text-gray-600">Diagnostic :</span> {{ $consultation->diagnostic }}</p>
        <p><span class="font-medium text-gray-600">Compte-rendu :</span> {{ $consultation->compte_rendu }}</p>
    </div>
    @if($consultation->ordonnance)
    <div class="mt-4 p-3 bg-blue-50 rounded-lg flex items-center justify-between">
        <span class="text-sm text-blue-700"><i class="fas fa-prescription mr-2"></i>Ordonnance disponible</span>
    </div>
    @endif
</div>
@empty
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
    <i class="fas fa-file-medical text-4xl text-gray-300 mb-3"></i>
    <p class="text-gray-400">Aucune consultation enregistrée.</p>
</div>
@endforelse
@endsection
