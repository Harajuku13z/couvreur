<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis {{ $devis->numero }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .devis-info {
            text-align: right;
        }
        .devis-number {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f5f5f5;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #333;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ $companySettings['name'] }}</div>
            @if($companySettings['address'])
            <div>{{ $companySettings['address'] }}</div>
            @endif
            @if($companySettings['postal_code'] || $companySettings['city'])
            <div>{{ $companySettings['postal_code'] }} {{ $companySettings['city'] }}</div>
            @endif
            @if($companySettings['phone'])
            <div>Tél: {{ $companySettings['phone'] }}</div>
            @endif
            @if($companySettings['email'])
            <div>Email: {{ $companySettings['email'] }}</div>
            @endif
            @if($companySettings['siret'])
            <div>SIRET: {{ $companySettings['siret'] }}</div>
            @endif
        </div>
        <div class="devis-info">
            <div class="devis-number">DEVIS N° {{ $devis->numero }}</div>
            <div>Date d'émission: {{ $devis->date_emission->format('d/m/Y') }}</div>
            @if($devis->date_validite)
            <div>Valable jusqu'au: {{ $devis->date_validite->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Client</div>
        <div><strong>{{ $devis->client->nom_complet }}</strong></div>
        @if($devis->client->adresse)
        <div>{{ $devis->client->adresse }}</div>
        @endif
        @if($devis->client->code_postal || $devis->client->ville)
        <div>{{ $devis->client->code_postal }} {{ $devis->client->ville }}</div>
        @endif
        @if($devis->client->email)
        <div>Email: {{ $devis->client->email }}</div>
        @endif
        @if($devis->client->telephone)
        <div>Tél: {{ $devis->client->telephone }}</div>
        @endif
    </div>

    @if($devis->description_globale)
    <div class="section">
        <div class="section-title">Description du projet</div>
        <div>{{ $devis->description_globale }}</div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Détail des prestations</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Quantité</th>
                    <th class="text-right">Prix unitaire</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($devis->lignesDevis as $ligne)
                <tr>
                    <td>{{ $ligne->description }}</td>
                    <td class="text-right">{{ number_format($ligne->quantite, 2, ',', ' ') }} {{ $ligne->unite }}</td>
                    <td class="text-right">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }} €</td>
                    <td class="text-right">{{ number_format($ligne->total_ligne, 2, ',', ' ') }} €</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total HT</td>
                    <td class="text-right">{{ number_format($devis->total_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">TVA ({{ $devis->taux_tva }}%)</td>
                    <td class="text-right">{{ number_format($devis->total_ttc - $devis->total_ht, 2, ',', ' ') }} €</td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>TOTAL TTC</strong></td>
                    <td class="text-right"><strong>{{ number_format($devis->total_ttc, 2, ',', ' ') }} €</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($devis->conditions_particulieres)
    <div class="section">
        <div class="section-title">Conditions particulières</div>
        <div style="white-space: pre-line;">{{ $devis->conditions_particulieres }}</div>
    </div>
    @endif

    <div class="footer">
        <div>Ce devis est établi à titre informatif et n'engage pas l'entreprise tant qu'il n'a pas été accepté par le client.</div>
        @if($companySettings['siret'])
        <div style="margin-top: 10px;">Assurance décennale en cours de validité - SIRET: {{ $companySettings['siret'] }}</div>
        @endif
    </div>
</body>
</html>

