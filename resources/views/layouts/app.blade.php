<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cabinet Médical')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col fixed h-full z-10">
            {{-- Logo --}}
            <div class="p-6 border-b border-gray-100">
                <h1 class="text-xl font-bold text-blue-600">
                    <i class="fas fa-stethoscope mr-2"></i>Cabinet Médical
                </h1>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                {{-- Commun --}}
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    <i class="fas fa-home w-5 mr-3"></i>Tableau de bord
                </a>

                @if(auth()->user()->role === 'patient')
                    <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase">Patient</div>
                    <a href="{{ route('patient.rdv.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('patient.rdv.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-calendar-check w-5 mr-3"></i>Mes rendez-vous
                    </a>
                    <a href="{{ route('patient.rdv.create') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('patient.rdv.create') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-plus-circle w-5 mr-3"></i>Prendre RDV
                    </a>
                    <a href="{{ route('patient.historique') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('patient.historique') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-file-medical w-5 mr-3"></i>Mon historique
                    </a>
                @endif

                @if(auth()->user()->role === 'medecin')
                    <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase">Médecin</div>
                    <a href="{{ route('medecin.planning') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('medecin.planning') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-calendar-alt w-5 mr-3"></i>Mon planning
                    </a>
                    <a href="{{ route('medecin.rdv.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('medecin.rdv.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-user-injured w-5 mr-3"></i>Patients du jour
                    </a>
                    <a href="{{ route('medecin.disponibilites.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('medecin.disponibilites.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-clock w-5 mr-3"></i>Disponibilités
                    </a>
                @endif

                @if(in_array(auth()->user()->role, ['secretaire', 'admin']))
                    <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase">Secrétariat</div>
                    <a href="{{ route('secretaire.patients.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('secretaire.patients.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-users w-5 mr-3"></i>Patients
                    </a>
                    <a href="{{ route('secretaire.rendez-vous.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('secretaire.rendez-vous.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-calendar w-5 mr-3"></i>Rendez-vous
                    </a>
                @endif

                @if(auth()->user()->role === 'admin')
                    <div class="pt-4 pb-2 px-3 text-xs font-semibold text-gray-400 uppercase">Administration</div>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-chart-bar w-5 mr-3"></i>Statistiques
                    </a>
                    <a href="{{ route('admin.utilisateurs.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin.utilisateurs.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="fas fa-user-cog w-5 mr-3"></i>Utilisateurs
                    </a>
                @endif
            </nav>

            {{-- User info --}}
            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-semibold text-sm">
                        {{ strtoupper(substr(auth()->user()->prenom, 0, 1) . substr(auth()->user()->nom, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 ml-64">
            {{-- Top bar --}}
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between sticky top-0 z-0">
                <h2 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Accueil')</h2>
                <div class="flex items-center gap-4">
                    <a href="{{ route('profil') }}" class="text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-user mr-1"></i>Profil
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">
                            <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
                        </button>
                    </form>
                </div>
            </header>

            {{-- Flash messages --}}
            <div class="px-8 pt-4">
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Page content --}}
            <div class="p-8">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
