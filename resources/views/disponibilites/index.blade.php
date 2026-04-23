@extends('layouts.app')
@section('title', 'Mes disponibilités')
@section('page-title', 'Gérer mes disponibilités')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('medecin.disponibilites.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        <i class="fas fa-plus mr-1"></i>Ajouter un créneau
    </a>
</div>

<div class="grid grid-cols-2 gap-4">
    @php
        $joursOrdre = ['lundi','mardi','mercredi','jeudi','vendredi','samedi'];
        $grouped = $disponibilites->groupBy('jour_semaine');
    @endphp
    @foreach($joursOrdre as $jour)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 capitalize mb-3">{{ $jour }}</h3>
            @if(isset($grouped[$jour]))
                @foreach($grouped[$jour] as $dispo)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-clock text-blue-400 mr-1"></i>
                        {{ substr($dispo->heure_debut, 0, 5) }} — {{ substr($dispo->heure_fin, 0, 5) }}
                    </span>
                    <div class="flex gap-2">
                        <a href="{{ route('medecin.disponibilites.edit', $dispo) }}" class="text-yellow-500 hover:text-yellow-700 text-xs"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('medecin.disponibilites.destroy', $dispo) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 text-xs"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-sm text-gray-400">Aucun créneau</p>
            @endif
        </div>
    @endforeach
</div>
@endsection
