<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre facture {{ $facture->numero }}</title>
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
            background: linear-gradient(135deg, {{ setting('primary_color', '#3b82f6') }} 0%, {{ setting('secondary_color', '#1e40af') }} 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .logo-wrapper {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 20px;
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
        .facture-box {
            background-color: #f8f9fa;
            border-left: 4px solid {{ setting('primary_color', '#3b82f6') }};
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
        .total-box {
            background: linear-gradient(135deg, {{ setting('primary_color', '#3b82f6') }} 0%, {{ setting('secondary_color', '#1e40af') }} 100%);
            color: #ffffff;
            padding: 25px;
            margin: 30px 0;
            border-radius: 8px;
            text-align: center;
        }
        .total-box .label {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .total-box .amount {
            font-size: 36px;
            font-weight: bold;
            margin: 0;
        }
        .payment-box {
            background-color: #e8f5e9;
            border: 1px solid #4caf50;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .payment-box p {
            margin: 5px 0;
            color: #2e7d32;
        }
        .payment-box .rib {
            white-space: pre-line;
            color: #333;
            font-size: 14px;
            margin-top: 10px;
        }
        .attachment-notice {
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: center;
        }
        .attachment-notice p {
            margin: 0;
            color: #2e7d32;
            font-weight: 600;
            font-size: 16px;
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
        .footer a {
            color: {{ setting('primary_color', '#3b82f6') }};
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            @if(setting('company_logo'))
                <div class="logo-wrapper">
                    <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name', 'Votre Entreprise') }}" style="height: 60px; max-width: 200px;">
                </div>
            @endif
            <h1>📄 Votre Facture</h1>
            <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">Facture N° {{ $facture->numero }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Bonjour @if($facture->client){{ $facture->client->nom_complet ?? 'Client' }}@else Client @endif,
            </div>

            <div class="message">
                <p>Nous vous remercions de votre confiance.</p>
                <p>Vous trouverez ci-joint votre facture <strong>N° {{ $facture->numero }}</strong>.</p>
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

            <!-- Total Box -->
            <div class="total-box">
                <div class="label">Montant à régler</div>
                <div class="amount">{{ number_format($facture->montant_restant ?? $facture->prix_total_ttc, 2, ',', ' ') }} €</div>
                @if($facture->montant_paye > 0)
                <p style="margin: 15px 0 0; font-size: 14px; opacity: 0.9;">
                    Déjà payé : {{ number_format($facture->montant_paye, 2, ',', ' ') }} €
                </p>
                @endif
            </div>

            <!-- Payment Info -->
            @if(setting('company_rib'))
            <div class="payment-box">
                <p><strong>💳 Coordonnées bancaires pour le paiement :</strong></p>
                <div class="rib">{{ setting('company_rib') }}</div>
            </div>
            @endif

            <!-- Attachment Notice -->
            <div class="attachment-notice">
                <p>📎 Votre facture détaillée est disponible en pièce jointe</p>
            </div>

            <div class="message">
                <p>Nous vous remercions de régler cette facture dans les délais convenus.</p>
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
            <p>Email : <a href="mailto:{{ setting('company_email') }}">{{ setting('company_email') }}</a></p>
            @endif
            @if(setting('company_siret'))
            <p style="font-size: 12px; color: #999999; margin-top: 15px;">SIRET : {{ setting('company_siret') }}</p>
            @endif
            <p style="font-size: 12px; color: #999999; margin-top: 15px;">
                Cet email a été envoyé automatiquement. Merci de ne pas y répondre directement.
            </p>
        </div>
    </div>
</body>
</html>

