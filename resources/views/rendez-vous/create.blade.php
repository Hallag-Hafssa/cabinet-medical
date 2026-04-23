@extends('layouts.app')
@section('title', 'Prendre rendez-vous')
@section('page-title', 'Prendre un rendez-vous')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('patient.rdv.store') }}">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Choisir un médecin *</label>
                <select name="medecin_id" id="medecin_id" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Sélectionner un médecin —</option>
                    @foreach($medecins as $medecin)
                        <option value="{{ $medecin->id }}" {{ old('medecin_id') == $medecin->id ? 'selected' : '' }}>
                            Dr. {{ $medecin->user->prenom }} {{ $medecin->user->nom }} — {{ $medecin->specialite->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Disponibilités du médecin sélectionné --}}
            <div id="disponibilites-info" class="mb-5 hidden">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm font-medium text-blue-700 mb-2"><i class="fas fa-info-circle mr-1"></i>Disponibilités</p>
                    <div id="dispo-list" class="text-sm text-blue-600"></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" name="date" id="rdv-date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                        value="{{ old('date') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Heure *</label>
                    <select name="heure" id="rdv-heure" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">— Heure —</option>
                        @for($h = 8; $h <= 17; $h++)
                            @foreach(['00', '30'] as $m)
                                <option value="{{ sprintf('%02d:%s', $h, $m) }}">{{ sprintf('%02d:%s', $h, $m) }}</option>
                            @endforeach
                        @endfor
                    </select>
                </div>
            </div>

            {{-- Champ caché pour date_heure combinée --}}
            <input type="hidden" name="date_heure" id="date_heure">

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Motif de la consultation</label>
                <textarea name="motif" rows="3" placeholder="Décrivez brièvement la raison de votre visite..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('motif') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-calendar-check mr-1"></i>Confirmer le rendez-vous
                </button>
                <a href="{{ route('patient.rdv.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Afficher les disponibilités du médecin sélectionné
    const medecins = @json($medecins);
    const jours = {'lundi':'Lundi','mardi':'Mardi','mercredi':'Mercredi','jeudi':'Jeudi','vendredi':'Vendredi','samedi':'Samedi'};

    document.getElementById('medecin_id').addEventListener('change', function() {
        const med = medecins.find(m => m.id == this.value);
        const box = document.getElementById('disponibilites-info');
        const list = document.getElementById('dispo-list');
        if (med && med.disponibilites.length) {
            box.classList.remove('hidden');
            list.innerHTML = med.disponibilites.map(d =>
                `<span class="inline-block mr-3">${jours[d.jour_semaine]}: ${d.heure_debut.slice(0,5)}–${d.heure_fin.slice(0,5)}</span>`
            ).join('');
        } else {
            box.classList.add('hidden');
        }
    });

    // Combiner date + heure dans le champ caché
    document.querySelector('form').addEventListener('submit', function(e) {
        const date = document.getElementById('rdv-date').value;
        const heure = document.getElementById('rdv-heure').value;
        if (date && heure) {
            document.getElementById('date_heure').value = date + ' ' + heure + ':00';
        }
    });
</script>
@endpush
@endsection
