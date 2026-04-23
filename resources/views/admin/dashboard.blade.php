@extends('layouts.app')
@section('title', 'Dashboard Administration')
@section('page-title', 'Tableau de bord — Statistiques')

@section('content')
{{-- Stats cards --}}
<div class="grid grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total patients</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_patients'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Médecins</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_medecins'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-500"><i class="fas fa-user-md"></i></div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">RDV aujourd'hui</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['rdv_aujourdhui'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center text-yellow-500"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Consultations ce mois</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['consultations_mois'] }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-500"><i class="fas fa-stethoscope"></i></div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Rendez-vous par mois</h3>
        <canvas id="chartRdvMois" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Patients par spécialité</h3>
        <canvas id="chartSpecialites" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">Taux d'annulation mensuel</h3>
        <canvas id="chartAnnulation" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-4">RDV par jour de la semaine</h3>
        <canvas id="chartJours" height="200"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const moisLabels = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
const joursLabels = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];

// 1. RDV par mois (Line Chart)
fetch('{{ route("admin.stats.rdv") }}')
    .then(r => r.json())
    .then(data => {
        const labels = data.map(d => moisLabels[d.mois - 1]);
        const values = data.map(d => d.total);
        new Chart(document.getElementById('chartRdvMois'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Rendez-vous',
                    data: values,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    });

// 2. Patients par spécialité (Doughnut)
fetch('{{ route("admin.stats.specialites") }}')
    .then(r => r.json())
    .then(data => {
        new Chart(document.getElementById('chartSpecialites'), {
            type: 'doughnut',
            data: {
                labels: data.map(d => d.nom),
                datasets: [{
                    data: data.map(d => d.total),
                    backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'],
                }]
            },
            options: { responsive: true }
        });
    });

// 3. Taux d'annulation (Bar Chart)
fetch('{{ route("admin.stats.annulation") }}')
    .then(r => r.json())
    .then(data => {
        new Chart(document.getElementById('chartAnnulation'), {
            type: 'bar',
            data: {
                labels: data.map(d => moisLabels[d.mois - 1]),
                datasets: [{
                    label: 'Taux d\'annulation (%)',
                    data: data.map(d => d.taux),
                    backgroundColor: 'rgba(239,68,68,0.7)',
                    borderRadius: 6,
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
        });
    });

// 4. RDV par jour (Bar Chart)
fetch('{{ route("admin.stats.rdv-jour") }}')
    .then(r => r.json())
    .then(data => {
        const values = new Array(7).fill(0);
        data.forEach(d => values[d.jour - 1] = d.total);
        new Chart(document.getElementById('chartJours'), {
            type: 'bar',
            data: {
                labels: joursLabels,
                datasets: [{
                    label: 'Rendez-vous',
                    data: values,
                    backgroundColor: 'rgba(16,185,129,0.7)',
                    borderRadius: 6,
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    });
</script>
@endpush
@endsection
