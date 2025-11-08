<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Devis {{ $devis->numero }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid {{ $companySettings['primary_color'] ?? '#3b82f6' }};
        }
        .logo-container img {
            max-height: 60px;
            max-width: 200px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid {{ $companySettings['primary_color'] ?? '#3b82f6' }};
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: {{ $companySettings['primary_color'] ?? '#3b82f6' }};
        }
        .devis-info {
            text-align: right;
        }
        .devis-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            color: {{ $companySettings['primary_color'] ?? '#3b82f6' }};
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid {{ $companySettings['primary_color'] ?? '#3b82f6' }};
            color: {{ $companySettings['primary_color'] ?? '#3b82f6' }};
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: {{ $companySettings['primary_color'] ?? '#3b82f6' }};
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .total-ttc {
            background-color: {{ $companySettings['primary_color'] ?? '#3b82f6' }};
            color: white;
        }
        .total-ttc td {
            color: white;
            font-size: 13px;
            padding: 10px 8px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid {{ $companySettings['primary_color'] ?? '#3b82f6' }};
            font-size: 9px;
            color: #666;
            line-height: 1.6;
        }
        .footer-title {
            font-weight: bold;
            color: {{ $companySettings['primary_color'] ?? '#3b82f6' }};
            margin-bottom: 8px;
            font-size: 10px;
        }
        .footer-info {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Logo en haut -->
    @if($companySettings['logo_base64'])
    <div class="logo-container">
        <img src="{{ $companySettings['logo_base64'] }}" alt="{{ $companySettings['name'] }}">
    </div>
    @endif

    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ $companySettings['name'] }}</div>
            @if($companySettings['address'])
            <div>{{ $companySettings['address'] }}</div>
            @elseif($companySettings['postal_code'] || $companySettings['city'])
            <div>{{ trim(($companySettings['postal_code'] ?? '') . ' ' . ($companySettings['city'] ?? '')) }}</div>
            @endif
            @if($companySettings['phone'])
            <div>Tél: {{ $companySettings['phone'] }}</div>
            @endif
            @if($companySettings['email'])
            <div>Email: {{ $companySettings['email'] }}</div>
            @endif
        </div>
        <div class="devis-info">
            <div class="devis-number">DEVIS N° {{ $devis->numero ?? 'N/A' }}</div>
            <div>Date d'émission: {{ $devis->date_emission ? $devis->date_emission->format('d/m/Y') : date('d/m/Y') }}</div>
            @if($devis->date_validite)
            <div>Valable jusqu'au: {{ $devis->date_validite->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Client</div>
        @if($devis->client)
        <div><strong>{{ $devis->client->nom_complet ?? 'Client non renseigné' }}</strong></div>
        @if($devis->client->adresse)
        <div>{{ $devis->client->adresse }}</div>
        @endif
        @if($devis->client->code_postal || $devis->client->ville)
        <div>{{ trim(($devis->client->code_postal ?? '') . ' ' . ($devis->client->ville ?? '')) }}</div>
        @endif
        @if($devis->client->email)
        <div>Email: {{ $devis->client->email }}</div>
        @endif
        @if($devis->client->telephone)
        <div>Tél: {{ $devis->client->telephone }}</div>
        @endif
        @else
        <div><strong>Client non renseigné</strong></div>
        @endif
    </div>

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
                    <td class="text-right">{{ number_format($devis->total_ht ?? 0, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">TVA ({{ $devis->taux_tva ?? 20 }}%)</td>
                    <td class="text-right">{{ number_format(($devis->total_ttc ?? 0) - ($devis->total_ht ?? 0), 2, ',', ' ') }} €</td>
                </tr>
                <tr class="total-ttc">
                    <td colspan="3" class="text-right"><strong>TOTAL TTC</strong></td>
                    <td class="text-right"><strong>{{ number_format($devis->total_ttc ?? 0, 2, ',', ' ') }} €</strong></td>
                </tr>
                @if($devis->acompte_pourcentage && $devis->acompte_pourcentage > 0)
                <tr style="background-color: #e3f2fd;">
                    <td colspan="3" class="text-right">Acompte ({{ $devis->acompte_pourcentage }}%)</td>
                    <td class="text-right"><strong>{{ number_format($devis->acompte_montant ?? 0, 2, ',', ' ') }} €</strong></td>
                </tr>
                <tr style="background-color: #fff3e0; font-weight: bold;">
                    <td colspan="3" class="text-right">Reste à payer</td>
                    <td class="text-right"><strong>{{ number_format($devis->reste_a_payer ?? $devis->total_ttc, 2, ',', ' ') }} €</strong></td>
                </tr>
                @endif
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
        @if($devis->acompte_pourcentage && $devis->acompte_pourcentage > 0)
        <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
            <div class="footer-title">Conditions de Paiement</div>
            <div style="color: #333; font-size: 10px; line-height: 1.6;">
                <div style="margin-bottom: 8px;">
                    <strong>Acompte ({{ number_format($devis->acompte_pourcentage, 2, ',', ' ') }}% - {{ number_format($devis->acompte_montant ?? 0, 2, ',', ' ') }} €) :</strong> Condition de lancement du chantier
                </div>
                <div>
                    <strong>Reste à payer ({{ number_format($devis->reste_a_payer ?? $devis->total_ttc, 2, ',', ' ') }} €) :</strong> Livraison des travaux
                </div>
            </div>
        </div>
        @endif
        
        @if($companySettings['rib'])
        <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
            <div class="footer-title">Coordonnées Bancaires</div>
            <div style="white-space: pre-line; color: #333; font-size: 10px; line-height: 1.6;">{{ $companySettings['rib'] }}</div>
        </div>
        @endif
        
        <div class="footer-title">Informations Légales</div>
        <div class="footer-info">
            <strong>{{ $companySettings['name'] }}</strong>
        </div>
        @if($companySettings['address'])
        <div class="footer-info">{{ $companySettings['address'] }}</div>
        @elseif($companySettings['postal_code'] || $companySettings['city'])
        <div class="footer-info">{{ trim(($companySettings['postal_code'] ?? '') . ' ' . ($companySettings['city'] ?? '')) }}</div>
        @endif
        @if($companySettings['phone'])
        <div class="footer-info">Tél : {{ $companySettings['phone'] }}</div>
        @endif
        @if($companySettings['email'])
        <div class="footer-info">Email : {{ $companySettings['email'] }}</div>
        @endif
        @if($companySettings['siret'])
        <div class="footer-info">SIRET : {{ $companySettings['siret'] }}</div>
        @endif
        @if($companySettings['rcs'])
        <div class="footer-info">RCS : {{ $companySettings['rcs'] }}</div>
        @endif
        @if($companySettings['capital'])
        <div class="footer-info">Capital social : {{ $companySettings['capital'] }}</div>
        @endif
        @if($companySettings['tva'])
        <div class="footer-info">TVA intracommunautaire : {{ $companySettings['tva'] }}</div>
        @endif
        @if($companySettings['hosting_provider'])
        <div class="footer-info">Hébergeur : {{ $companySettings['hosting_provider'] }}</div>
        @endif
        <div style="margin-top: 10px; font-style: italic;">
            Ce devis est établi à titre informatif et n'engage pas l'entreprise tant qu'il n'a pas été accepté par le client.
        </div>
    </div>
</body>
</html>
