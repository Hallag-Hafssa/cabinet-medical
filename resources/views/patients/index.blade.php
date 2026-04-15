@extends('layouts.app')
@section('title', 'Gestion des patients')
@section('page-title', 'Gestion des patients')

@section('content')
<div class="flex items-center justify-between mb-6">
    <form action="{{ route('secretaire.patients.search') }}" method="GET" class="flex gap-2">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-search"></i></span>
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Rechercher un patient..."
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm w-72 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <button type="submit" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200">Rechercher</button>
    </form>
    <a href="{{ route('secretaire.patients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        <i class="fas fa-plus mr-1"></i>Nouveau patient
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Patient</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Téléphone</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Âge</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Sexe</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Groupe sanguin</th>
                <th class="text-right px-6 py-3 font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($patients as $patient)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-xs">
                            {{ strtoupper(substr($patient->user->prenom, 0, 1) . substr($patient->user->nom, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $patient->user->prenom }} {{ $patient->user->nom }}</p>
                            <p class="text-xs text-gray-400">{{ $patient->user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $patient->user->telephone ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $patient->age ?? '—' }} ans</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $patient->sexe === 'homme' ? 'bg-blue-50 text-blue-600' : 'bg-pink-50 text-pink-600' }}">
                        {{ ucfirst($patient->sexe ?? '—') }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $patient->groupe_sanguin ?? '—' }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('secretaire.patients.show', $patient) }}" class="text-blue-600 hover:text-blue-800 mr-3" title="Voir"><i class="fas fa-eye"></i></a>
                    <a href="{{ route('secretaire.patients.edit', $patient) }}" class="text-yellow-600 hover:text-yellow-800 mr-3" title="Modifier"><i class="fas fa-edit"></i></a>
                    <form action="{{ route('secretaire.patients.destroy', $patient) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce patient ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Aucun patient trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $patients->links() }}</div>
@endsection
