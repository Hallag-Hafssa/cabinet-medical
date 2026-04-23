@extends('layouts.app')
@section('title', 'Gestion des rendez-vous')
@section('page-title', 'Tous les rendez-vous')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Date / Heure</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Patient</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Médecin</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Motif</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Statut</th>
                <th class="text-right px-6 py-3 font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rdvs as $rdv)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $rdv->date_heure->format('d/m/Y') }}</p>
                    <p class="text-xs text-gray-400">{{ $rdv->date_heure->format('H:i') }} ({{ $rdv->duree_minutes }} min)</p>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ $rdv->patient->user->prenom }} {{ $rdv->patient->user->nom }}</td>
                <td class="px-6 py-4 text-gray-600">Dr. {{ $rdv->medecin->user->nom }}</td>
                <td class="px-6 py-4 text-gray-500">{{ Str::limit($rdv->motif ?? '—', 30) }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @switch($rdv->statut)
                            @case('en_attente') bg-yellow-50 text-yellow-600 @break
                            @case('confirme') bg-green-50 text-green-600 @break
                            @case('annule') bg-red-50 text-red-600 @break
                            @case('termine') bg-gray-100 text-gray-500 @break
                        @endswitch">
                        {{ ucfirst(str_replace('_', ' ', $rdv->statut)) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    @if($rdv->statut === 'en_attente')
                        <form action="{{ route('secretaire.rdv.confirmer', $rdv) }}" method="POST" class="inline">
                            @csrf @method('PUT')
                            <button class="text-green-600 hover:text-green-800 mr-2" title="Confirmer"><i class="fas fa-check"></i></button>
                        </form>
                    @endif
                    <a href="{{ route('secretaire.rendez-vous.edit', $rdv) }}" class="text-yellow-600 hover:text-yellow-800 mr-2"><i class="fas fa-edit"></i></a>
                    @if(in_array($rdv->statut, ['en_attente', 'confirme']))
                        <form action="{{ route('secretaire.rdv.annuler', $rdv) }}" method="POST" class="inline" onsubmit="return confirm('Annuler ce RDV ?')">
                            @csrf @method('PUT')
                            <button class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Aucun rendez-vous.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $rdvs->links() }}</div>
@endsection
