@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl">
                {{ strtoupper(substr($user->prenom, 0, 1) . substr($user->nom, 0, 1)) }}
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-800">{{ $user->prenom }} {{ $user->nom }}</p>
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    @switch($user->role)
                        @case('admin') bg-purple-50 text-purple-600 @break
                        @case('medecin') bg-green-50 text-green-600 @break
                        @case('secretaire') bg-yellow-50 text-yellow-600 @break
                        @default bg-blue-50 text-blue-600
                    @endswitch">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('profil.update') }}">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" name="nom" value="{{ old('nom', $user->nom) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <input type="text" name="telephone" value="{{ old('telephone', $user->telephone) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700">
                <i class="fas fa-save mr-1"></i>Mettre à jour
            </button>
        </form>
    </div>
</div>
@endsection
