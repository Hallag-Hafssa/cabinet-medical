@extends('layouts.app')
@section('title', 'Gestion des utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="flex justify-end mb-6">
    <a href="{{ route('admin.utilisateurs.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
        <i class="fas fa-plus mr-1"></i>Nouvel utilisateur
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Utilisateur</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Email</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Rôle</th>
                <th class="text-left px-6 py-3 font-medium text-gray-500">Inscrit le</th>
                <th class="text-right px-6 py-3 font-medium text-gray-500">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($utilisateurs as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-800">{{ $user->prenom }} {{ $user->nom }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @switch($user->role)
                            @case('admin') bg-purple-50 text-purple-600 @break
                            @case('medecin') bg-green-50 text-green-600 @break
                            @case('secretaire') bg-yellow-50 text-yellow-600 @break
                            @default bg-blue-50 text-blue-600
                        @endswitch">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('admin.utilisateurs.edit', $user) }}" class="text-yellow-600 hover:text-yellow-800 mr-3"><i class="fas fa-edit"></i></a>
                    @if($user->id !== auth()->id())
                    <form action="{{ route('admin.utilisateurs.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $utilisateurs->links() }}</div>
@endsection
