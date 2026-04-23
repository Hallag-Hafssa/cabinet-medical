@extends('layouts.app')
@section('title', isset($utilisateur) ? 'Modifier utilisateur' : 'Nouvel utilisateur')
@section('page-title', isset($utilisateur) ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ isset($utilisateur) ? route('admin.utilisateurs.update', $utilisateur) : route('admin.utilisateurs.store') }}">
            @csrf
            @if(isset($utilisateur)) @method('PUT') @endif

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                    <input type="text" name="nom" value="{{ old('nom', $utilisateur->nom ?? '') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prénom *</label>
                    <input type="text" name="prenom" value="{{ old('prenom', $utilisateur->prenom ?? '') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                <input type="email" name="email" value="{{ old('email', $utilisateur->email ?? '') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rôle *</label>
                <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    @foreach(['admin','medecin','secretaire','patient'] as $role)
                        <option value="{{ $role }}" {{ old('role', $utilisateur->role ?? '') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>

            @if(!isset($utilisateur))
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe *</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            </div>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i>{{ isset($utilisateur) ? 'Mettre à jour' : 'Créer' }}
                </button>
                <a href="{{ route('admin.utilisateurs.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
