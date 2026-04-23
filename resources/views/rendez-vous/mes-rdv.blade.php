@extends('layouts.app')
@section('title', 'Mes rendez-vous')
@section('page-title', 'Mes rendez-vous')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('patient.rdv.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        <i class="fas fa-plus mr-1"></i>Prendre un rendez-vous
    </a>
</div>

<div class="space-y-3">
    @forelse($rdvs as $rdv)
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg flex flex-col items-center justify-center
                {{ $rdv->date_heure->isFuture() ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-400' }}">
                <span class="text-xs font-medium">{{ $rdv->date_heure->format('M') }}</span>
                <span class="text-lg font-bold leading-none">{{ $rdv->date_heure->format('d') }}</span>
            </div>
            <div>
                <p class="font-medium text-gray-800">
                    Dr. {{ $rdv->medecin->user->prenom }} {{ $rdv->medecin->user->nom }}
                    <span class="text-gray-400 font-normal">— {{ $rdv->medecin->specialite->nom }}</span>
                </p>
                <p class="text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>{{ $rdv->date_heure->format('H:i') }} ({{ $rdv->duree_minutes }} min)
                    @if($rdv->motif) · {{ $rdv->motif }} @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-medium
                @switch($rdv->statut)
                    @case('en_attente') bg-yellow-50 text-yellow-600 @break
                    @case('confirme') bg-green-50 text-green-600 @break
                    @case('annule') bg-red-50 text-red-600 @break
                    @case('termine') bg-gray-100 text-gray-500 @break
                @endswitch">
                {{ ucfirst(str_replace('_', ' ', $rdv->statut)) }}
            </span>
            @if(in_array($rdv->statut, ['en_attente', 'confirme']) && $rdv->date_heure->isFuture())
                <form action="{{ route('patient.rdv.annuler', $rdv) }}" method="POST" onsubmit="return confirm('Annuler ce rendez-vous ?')">
                    @csrf @method('PUT')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm"><i class="fas fa-times mr-1"></i>Annuler</button>
                </form>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <i class="fas fa-calendar text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-400 mb-4">Vous n'avez aucun rendez-vous.</p>
        <a href="{{ route('patient.rdv.create') }}" class="text-blue-600 hover:underline text-sm">Prendre un rendez-vous</a>
    </div>
    @endforelse
</div>

<div class="mt-4">{{ $rdvs->links() }}</div>
@endsection
