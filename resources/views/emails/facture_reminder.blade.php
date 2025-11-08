<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel - Facture {{ $facture->numero }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333333;
            margin-bottom: 20px;
        }
        .message {
            color: #666666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .urgent-box {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            padding: 30px;
            margin: 30px 0;
            border-radius: 10px;
            text-align: center;
        }
        .urgent-box h2 {
            margin: 0 0 15px;
            font-size: 24px;
        }
        .urgent-box .amount {
            font-size: 36px;
            font-weight: bold;
            margin: 15px 0;
        }
        .facture-box {
            background-color: #f8f9fa;
            border-left: 4px solid #ef4444;
            padding: 25px;
            margin: 30px 0;
            border-radius: 5px;
        }
        .facture-box h3 {
            margin: 0 0 15px;
            color: #333333;
            font-size: 20px;
            font-weight: bold;
        }
        .facture-info {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .facture-info:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #555555;
            display: inline-block;
            width: 150px;
        }
        .info-value {
            color: #333333;
        }
        .payment-info {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .payment-info p {
            margin: 5px 0;
            color: #856404;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #666666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            @if(setting('company_logo'))
                <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name', 'Votre Entreprise') }}" style="height: 60px; margin-bottom: 20px;">
            @endif
            <h1>⚠️ Rappel de Paiement</h1>
            <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">Facture N° {{ $facture->numero }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Bonjour @if($facture->client){{ $facture->client->nom_complet ?? 'Client' }}@else Client @endif,
            </div>

            <div class="message">
                <p>Nous vous rappelons que votre facture <strong>N° {{ $facture->numero }}</strong> est toujours en attente de paiement.</p>
                @if($facture->date_echeance && $facture->date_echeance->isPast())
                <p><strong>⚠️ Cette facture est en retard depuis le {{ $facture->date_echeance->format('d/m/Y') }}.</strong></p>
                @elseif($facture->date_echeance)
                <p>La date d'échéance était le <strong>{{ $facture->date_echeance->format('d/m/Y') }}</strong>.</p>
                @endif
            </div>

            <!-- Urgent Box -->
            <div class="urgent-box">
                <h2>Montant à régler</h2>
                <div class="amount">{{ number_format($facture->montant_restant ?? $facture->prix_total_ttc, 2, ',', ' ') }} €</div>
                @if($facture->montant_paye > 0)
                <p style="margin: 10px 0 0; font-size: 14px; opacity: 0.9;">
                    Déjà payé : {{ number_format($facture->montant_paye, 2, ',', ' ') }} €
                </p>
                @endif
            </div>

            <!-- Facture Info Box -->
            <div class="facture-box">
                <h3>📋 Informations de la facture</h3>
                <div class="facture-info">
                    <span class="info-label">Numéro :</span>
                    <span class="info-value"><strong>{{ $facture->numero }}</strong></span>
                </div>
                <div class="facture-info">
                    <span class="info-label">Date d'émission :</span>
                    <span class="info-value">{{ $facture->date_emission->format('d/m/Y') }}</span>
                </div>
                @if($facture->date_echeance)
                <div class="facture-info">
                    <span class="info-label">Date d'échéance :</span>
                    <span class="info-value">{{ $facture->date_echeance->format('d/m/Y') }}</span>
                </div>
                @endif
                <div class="facture-info">
                    <span class="info-label">Total TTC :</span>
                    <span class="info-value"><strong>{{ number_format($facture->prix_total_ttc, 2, ',', ' ') }} €</strong></span>
                </div>
                @if($facture->montant_paye > 0)
                <div class="facture-info">
                    <span class="info-label">Montant payé :</span>
                    <span class="info-value">{{ number_format($facture->montant_paye, 2, ',', ' ') }} €</span>
                </div>
                <div class="facture-info">
                    <span class="info-label">Reste à payer :</span>
                    <span class="info-value"><strong>{{ number_format($facture->montant_restant, 2, ',', ' ') }} €</strong></span>
                </div>
                @endif
            </div>

            <!-- Payment Info -->
            <div class="payment-info">
                <p><strong>📎 Votre facture est disponible en pièce jointe</strong></p>
                @if(setting('company_rib'))
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ffc107;">
                    <p style="color: #333; font-weight: 600; margin-bottom: 10px;">Coordonnées bancaires :</p>
                    <div style="white-space: pre-line; color: #333; font-size: 14px;">{{ setting('company_rib') }}</div>
                </div>
                @endif
            </div>

            <div class="message">
                <p>Nous vous remercions de régler cette facture dans les plus brefs délais.</p>
                <p><strong>Cordialement,</strong><br>
                L'équipe de <strong>{{ setting('company_name', 'Votre Entreprise') }}</strong></p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            @if(setting('company_name'))
            <p><strong>{{ setting('company_name') }}</strong></p>
            @endif
            @if(setting('company_address'))
            <p>{{ setting('company_address') }}</p>
            @endif
            @if(setting('company_phone'))
            <p>Tél : {{ setting('company_phone') }}</p>
            @endif
            @if(setting('company_email'))
            <p>Email : <a href="mailto:{{ setting('company_email') }}" style="color: #3b82f6;">{{ setting('company_email') }}</a></p>
            @endif
            <p style="font-size: 12px; color: #999999; margin-top: 15px;">
                Cet email est un rappel automatique concernant votre facture impayée.
            </p>
        </div>
    </div>
</body>
</html>

