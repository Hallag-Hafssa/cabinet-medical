@extends('layouts.app')
@section('title', 'Patients du jour')
@section('page-title', 'Patients du jour — ' . now()->translatedFormat('l d F Y'))

@section('content')
<div class="space-y-3">
    @forelse($rdvs as $rdv)
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="text-center w-16">
                <p class="text-xl font-bold text-gray-800">{{ $rdv->date_heure->format('H:i') }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-sm">
                {{ strtoupper(substr($rdv->patient->user->prenom, 0, 1) . substr($rdv->patient->user->nom, 0, 1)) }}
            </div>
            <div>
                <p class="font-medium text-gray-800">{{ $rdv->patient->user->prenom }} {{ $rdv->patient->user->nom }}</p>
                <p class="text-sm text-gray-400">{{ $rdv->motif ?? 'Sans motif' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-medium
                @switch($rdv->statut)
                    @case('en_attente') bg-yellow-50 text-yellow-600 @break
                    @case('confirme') bg-green-50 text-green-600 @break
                    @case('termine') bg-gray-100 text-gray-500 @break
                    @default bg-red-50 text-red-600
                @endswitch">
                {{ ucfirst(str_replace('_', ' ', $rdv->statut)) }}
            </span>

            @if($rdv->consultation)
                <a href="{{ route('medecin.consultation.show', $rdv->consultation) }}" class="bg-gray-100 text-gray-600 px-3 py-1 rounded text-xs hover:bg-gray-200">
                    <i class="fas fa-eye mr-1"></i>Voir consultation
                </a>
            @elseif(in_array($rdv->statut, ['confirme', 'en_attente']))
                <a href="{{ route('medecin.consultation.create', $rdv) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                    <i class="fas fa-stethoscope mr-1"></i>Consulter
                </a>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <i class="fas fa-calendar-check text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-400">Aucun patient prévu aujourd'hui.</p>
    </div>
    @endforelse
</div>
@endsection
