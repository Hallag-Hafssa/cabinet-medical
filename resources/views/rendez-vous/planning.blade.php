@extends('layouts.app')
@section('title', 'Mon planning')
@section('page-title', 'Mon planning')

@section('content')
{{-- Stats du jour --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500">RDV aujourd'hui</p>
        <p class="text-2xl font-bold text-gray-800">{{ $rdvsAujourdhui->count() }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500">En attente</p>
        <p class="text-2xl font-bold text-yellow-600">{{ $rdvsAujourdhui->where('statut', 'en_attente')->count() }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-sm text-gray-500">Terminés</p>
        <p class="text-2xl font-bold text-green-600">{{ $rdvsAujourdhui->where('statut', 'termine')->count() }}</p>
    </div>
</div>

{{-- RDV du jour --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-calendar-day text-blue-500 mr-2"></i>Aujourd'hui — {{ now()->translatedFormat('l d F Y') }}</h3>
    @forelse($rdvsAujourdhui as $rdv)
    <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
        <div class="flex items-center gap-4">
            <div class="text-center w-16">
                <p class="text-lg font-bold text-gray-800">{{ $rdv->date_heure->format('H:i') }}</p>
                <p class="text-xs text-gray-400">{{ $rdv->duree_minutes }} min</p>
            </div>
            <div>
                <p class="font-medium text-gray-800">{{ $rdv->patient->user->prenom }} {{ $rdv->patient->user->nom }}</p>
                <p class="text-sm text-gray-400">{{ $rdv->motif ?? 'Sans motif' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded text-xs font-medium
                @switch($rdv->statut)
                    @case('en_attente') bg-yellow-50 text-yellow-600 @break
                    @case('confirme') bg-green-50 text-green-600 @break
                    @case('termine') bg-gray-100 text-gray-500 @break
                    @default bg-red-50 text-red-600
                @endswitch">
                {{ ucfirst(str_replace('_', ' ', $rdv->statut)) }}
            </span>
            @if($rdv->statut === 'en_attente')
                <form action="{{ route('medecin.rdv.confirmer', $rdv) }}" method="POST" class="inline">
                    @csrf @method('PUT')
                    <button class="text-green-600 hover:text-green-800 text-xs"><i class="fas fa-check"></i></button>
                </form>
            @endif
            @if(in_array($rdv->statut, ['confirme', 'en_attente']) && !$rdv->consultation)
                <a href="{{ route('medecin.consultation.create', $rdv) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">
                    <i class="fas fa-stethoscope mr-1"></i>Consulter
                </a>
            @endif
        </div>
    </div>
    @empty
    <p class="text-sm text-gray-400 py-4 text-center">Aucun rendez-vous aujourd'hui.</p>
    @endforelse
</div>

{{-- RDV à venir --}}
<div class="bg-white rounded-xl border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-calendar-alt text-gray-400 mr-2"></i>Prochains rendez-vous</h3>
    @forelse($rdvsAVenir as $rdv)
    <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-gray-50 flex flex-col items-center justify-center text-gray-600">
                <span class="text-xs">{{ $rdv->date_heure->format('M') }}</span>
                <span class="text-lg font-bold leading-none">{{ $rdv->date_heure->format('d') }}</span>
            </div>
            <div>
                <p class="font-medium text-gray-800">{{ $rdv->patient->user->prenom }} {{ $rdv->patient->user->nom }}</p>
                <p class="text-sm text-gray-400">{{ $rdv->date_heure->format('H:i') }} · {{ $rdv->motif ?? 'Sans motif' }}</p>
            </div>
        </div>
        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $rdv->statut === 'confirme' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }}">
            {{ ucfirst(str_replace('_', ' ', $rdv->statut)) }}
        </span>
    </div>
    @empty
    <p class="text-sm text-gray-400 py-4 text-center">Aucun rendez-vous à venir.</p>
    @endforelse
</div>
@endsection
