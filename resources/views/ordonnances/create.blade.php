@extends('layouts.app')
@section('title', 'Rédiger ordonnance')
@section('page-title', 'Rédiger une ordonnance')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('medecin.ordonnance.store') }}" id="ordonnance-form">
            @csrf
            <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm text-gray-600">
                <p><strong>Patient :</strong> {{ $consultation->patient->user->prenom }} {{ $consultation->patient->user->nom }}</p>
                <p><strong>Consultation du :</strong> {{ $consultation->date_consultation->format('d/m/Y') }}</p>
                <p><strong>Diagnostic :</strong> {{ $consultation->diagnostic }}</p>
            </div>

            <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">Médicaments prescrits</h3>
            <div id="medicaments-container">
                <div class="medicament-row border border-gray-200 rounded-lg p-4 mb-3">
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Médicament *</label>
                            <select name="medicaments[0][id]" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">— Choisir —</option>
                                @foreach($medicaments as $med)
                                    <option value="{{ $med->id }}">{{ $med->nom }} ({{ $med->dosage }} — {{ $med->forme }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Posologie *</label>
                            <input type="text" name="medicaments[0][posologie]" required placeholder="ex: 1 cp matin et soir"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Durée *</label>
                            <input type="text" name="medicaments[0][duree]" required placeholder="ex: 7 jours"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Remarques</label>
                            <input type="text" name="medicaments[0][remarques]" placeholder="Optionnel"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" onclick="ajouterMedicament()" class="text-blue-600 hover:text-blue-800 text-sm mb-6 block">
                <i class="fas fa-plus mr-1"></i>Ajouter un médicament
            </button>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Instructions particulières</label>
                <textarea name="instructions" rows="3" placeholder="Instructions générales pour le patient..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">{{ old('instructions') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-save mr-1"></i>Enregistrer l'ordonnance
                </button>
                <a href="{{ route('medecin.consultation.show', $consultation) }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let medCount = 1;
function ajouterMedicament() {
    const container = document.getElementById('medicaments-container');
    const template = container.querySelector('.medicament-row').cloneNode(true);
    template.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${medCount}]`);
        el.value = '';
    });
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'text-red-500 hover:text-red-700 text-xs mt-2';
    removeBtn.innerHTML = '<i class="fas fa-trash mr-1"></i>Retirer';
    removeBtn.onclick = function() { template.remove(); };
    template.appendChild(removeBtn);
    container.appendChild(template);
    medCount++;
}
</script>
@endpush
@endsection
