<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 150px;
            height: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid {{ $companySettings['primary_color'] ?? '#333' }};
            padding-bottom: 20px;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: {{ $companySettings['primary_color'] ?? '#333' }};
        }
        .facture-info {
            text-align: right;
        }
        .facture-number {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: {{ $companySettings['secondary_color'] ?? '#333' }};
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
            color: {{ $companySettings['primary_color'] ?? '#333' }};
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
            border-bottom: 2px solid {{ $companySettings['primary_color'] ?? '#333' }};
            font-weight: bold;
            color: {{ $companySettings['primary_color'] ?? '#333' }};
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
        .total-ttc {
            background-color: {{ $companySettings['secondary_color'] ?? '#f9f9f9' }};
            color: #fff;
        }
        .total-ttc td {
            color: #fff;
            border-bottom: none;
        }
        .acompte-row {
            background-color: #e3f2fd;
        }
        .reste-row {
            background-color: #fff3e0;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            color: #666;
        }
        .footer-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: {{ $companySettings['primary_color'] ?? '#333' }};
        }
        .footer-info {
            margin-bottom: 3px;
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
        <div class="facture-info">
            <div class="facture-number">FACTURE N° {{ $facture->numero ?? 'N/A' }}</div>
            <div>Date d'émission: {{ $facture->date_emission ? $facture->date_emission->format('d/m/Y') : date('d/m/Y') }}</div>
            @if($facture->date_echeance)
            <div>Date d'échéance: {{ $facture->date_echeance->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Client</div>
        @if($facture->client)
        <div><strong>{{ $facture->client->nom_complet ?? 'Client non renseigné' }}</strong></div>
        @if($facture->client->adresse)
        <div>{{ $facture->client->adresse }}</div>
        @endif
        @if($facture->client->code_postal || $facture->client->ville)
        <div>{{ trim(($facture->client->code_postal ?? '') . ' ' . ($facture->client->ville ?? '')) }}</div>
        @endif
        @if($facture->client->email)
        <div>Email: {{ $facture->client->email }}</div>
        @endif
        @if($facture->client->telephone)
        <div>Tél: {{ $facture->client->telephone }}</div>
        @endif
        @else
        <div><strong>Client non renseigné</strong></div>
        @endif
    </div>

    @if($facture->devis && $facture->devis->acompte_pourcentage && $facture->devis->acompte_pourcentage > 0)
    <div class="section">
        <div class="section-title">Acompte</div>
        <table>
            <tr class="acompte-row">
                <td colspan="3" class="text-right">Acompte payé ({{ $facture->devis->acompte_pourcentage }}%)</td>
                <td class="text-right"><strong>{{ number_format($facture->devis->acompte_montant ?? 0, 2, ',', ' ') }} €</strong></td>
            </tr>
        </table>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Détail de la facture</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Total HT</th>
                    <th class="text-right">TVA</th>
                    <th class="text-right">Total TTC</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Facture pour devis {{ $facture->devis->numero ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($facture->prix_total_ht ?? 0, 2, ',', ' ') }} €</td>
                    <td class="text-right">{{ number_format(($facture->prix_total_ttc ?? 0) - ($facture->prix_total_ht ?? 0), 2, ',', ' ') }} €</td>
                    <td class="text-right">{{ number_format($facture->prix_total_ttc ?? 0, 2, ',', ' ') }} €</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total HT</td>
                    <td class="text-right">{{ number_format($facture->prix_total_ht ?? 0, 2, ',', ' ') }} €</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">TVA ({{ $facture->taux_tva ?? 20 }}%)</td>
                    <td class="text-right">{{ number_format(($facture->prix_total_ttc ?? 0) - ($facture->prix_total_ht ?? 0), 2, ',', ' ') }} €</td>
                </tr>
                <tr class="total-ttc">
                    <td colspan="3" class="text-right"><strong>TOTAL TTC</strong></td>
                    <td class="text-right"><strong>{{ number_format($facture->prix_total_ttc ?? 0, 2, ',', ' ') }} €</strong></td>
                </tr>
                @if($facture->montant_paye > 0)
                <tr class="acompte-row">
                    <td colspan="3" class="text-right">Montant déjà payé</td>
                    <td class="text-right"><strong>{{ number_format($facture->montant_paye, 2, ',', ' ') }} €</strong></td>
                </tr>
                <tr class="reste-row">
                    <td colspan="3" class="text-right"><strong>RESTE À PAYER</strong></td>
                    <td class="text-right"><strong>{{ number_format($facture->montant_restant, 2, ',', ' ') }} €</strong></td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>

    @if($facture->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <div style="white-space: pre-line;">{{ $facture->notes }}</div>
    </div>
    @endif

    <div class="footer">
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
            Cette facture est établie conformément aux conditions générales de vente.
        </div>
    </div>
</body>
</html>

