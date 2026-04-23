<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; padding: 40px; }

        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; color: #2563eb; margin-bottom: 5px; }
        .header p { font-size: 11px; color: #666; }

        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin: 0 5px; }
        .info-box h3 { font-size: 11px; color: #2563eb; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
        .info-box p { margin-bottom: 3px; font-size: 11px; }
        .info-box .label { color: #666; }
        .info-box .value { font-weight: bold; }

        .ordonnance-title { background: #2563eb; color: white; padding: 8px 15px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; }

        table.medicaments { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.medicaments th { background: #f1f5f9; padding: 8px 10px; text-align: left; font-size: 11px; color: #475569; border-bottom: 2px solid #e2e8f0; }
        table.medicaments td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        table.medicaments tr:nth-child(even) td { background: #fafafa; }

        .instructions { background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 12px; margin-bottom: 30px; }
        .instructions h3 { font-size: 11px; color: #92400e; margin-bottom: 5px; }
        .instructions p { font-size: 11px; color: #78350f; }

        .signature { text-align: right; margin-top: 40px; }
        .signature .line { border-top: 1px solid #333; width: 200px; display: inline-block; margin-top: 50px; }
        .signature p { font-size: 11px; color: #666; }

        .footer { text-align: center; margin-top: 40px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #999; }
    </style>
</head>
<body>

    {{-- En-tête du cabinet --}}
    <div class="header">
        <h1>Cabinet Médical</h1>
        <p>Adresse du cabinet | Tél : 05 XX XX XX XX | Email : contact@cabinet.ma</p>
    </div>

    {{-- Informations médecin et patient --}}
    <div class="info-grid">
        <div class="info-col">
            <div class="info-box">
                <h3>Médecin</h3>
                <p><span class="label">Dr.</span> <span class="value">{{ $ordonnance->medecin->user->prenom }} {{ $ordonnance->medecin->user->nom }}</span></p>
                <p><span class="label">Spécialité :</span> {{ $ordonnance->medecin->specialite->nom }}</p>
                <p><span class="label">Matricule :</span> {{ $ordonnance->medecin->matricule }}</p>
            </div>
        </div>
        <div class="info-col">
            <div class="info-box">
                <h3>Patient</h3>
                <p><span class="value">{{ $ordonnance->patient->user->prenom }} {{ $ordonnance->patient->user->nom }}</span></p>
                <p><span class="label">Né(e) le :</span> {{ $ordonnance->patient->date_naissance?->format('d/m/Y') ?? 'N/A' }}</p>
                <p><span class="label">Âge :</span> {{ $ordonnance->patient->age ?? 'N/A' }} ans</p>
            </div>
        </div>
    </div>

    {{-- Ordonnance --}}
    <div class="ordonnance-title">
        ORDONNANCE N° {{ $ordonnance->id }} — {{ $ordonnance->date_ordonnance->format('d/m/Y') }}
    </div>

    <table class="medicaments">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 25%">Médicament</th>
                <th style="width: 15%">Dosage</th>
                <th style="width: 10%">Forme</th>
                <th style="width: 25%">Posologie</th>
                <th style="width: 10%">Durée</th>
                <th style="width: 10%">Remarques</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ordonnance->medicaments as $index => $medicament)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $medicament->nom }}</strong></td>
                <td>{{ $medicament->dosage }}</td>
                <td>{{ $medicament->forme }}</td>
                <td>{{ $medicament->pivot->posologie }}</td>
                <td>{{ $medicament->pivot->duree }}</td>
                <td>{{ $medicament->pivot->remarques ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Instructions --}}
    @if($ordonnance->instructions)
    <div class="instructions">
        <h3>Instructions particulières</h3>
        <p>{{ $ordonnance->instructions }}</p>
    </div>
    @endif

    {{-- Signature --}}
    <div class="signature">
        <p>Fait à Casablanca, le {{ $ordonnance->date_ordonnance->format('d/m/Y') }}</p>
        <div class="line"></div>
        <p>Dr. {{ $ordonnance->medecin->user->prenom }} {{ $ordonnance->medecin->user->nom }}</p>
    </div>

    <div class="footer">
        Document généré automatiquement — Cabinet Médical © {{ date('Y') }}
    </div>

</body>
</html>
