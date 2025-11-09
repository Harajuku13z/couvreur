<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre devis {{ $devis->numero }}</title>
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
        .devis-box {
            background-color: #f8f9fa;
            border-left: 4px solid {{ setting('primary_color', '#3b82f6') }};
            padding: 25px;
            margin: 30px 0;
            border-radius: 5px;
        }
        .devis-box h3 {
            margin: 0 0 15px;
            color: #333333;
            font-size: 20px;
            font-weight: bold;
        }
        .devis-info {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .devis-info:last-child {
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
        .contact-box {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: center;
        }
        .contact-box p {
            margin: 5px 0;
            color: #856404;
        }
        .contact-box .phone {
            font-size: 24px;
            font-weight: bold;
            color: {{ setting('primary_color', '#3b82f6') }};
            margin: 10px 0;
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
            <h1>📄 Votre Devis</h1>
            <p style="margin: 10px 0 0; font-size: 16px; opacity: 0.9;">Devis N° {{ $devis->numero }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Bonjour @if($devis->client){{ $devis->client->nom_complet ?? 'Client' }}@else Client @endif,
            </div>

            <div class="message">
                <p>Nous vous remercions de votre confiance et de l'intérêt que vous portez à nos services.</p>
                <p>Vous trouverez ci-joint votre devis détaillé <strong>N° {{ $devis->numero }}</strong>.</p>
            </div>

            <!-- Devis Info Box -->
            <div class="devis-box">
                <h3>📋 Informations du devis</h3>
                <div class="devis-info">
                    <span class="info-label">Numéro :</span>
                    <span class="info-value"><strong>{{ $devis->numero }}</strong></span>
                </div>
                <div class="devis-info">
                    <span class="info-label">Date d'émission :</span>
                    <span class="info-value">{{ $devis->date_emission ? $devis->date_emission->format('d/m/Y') : date('d/m/Y') }}</span>
                </div>
                @if($devis->date_validite)
                <div class="devis-info">
                    <span class="info-label">Valable jusqu'au :</span>
                    <span class="info-value">{{ $devis->date_validite->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>

            <!-- Total Box -->
            <div class="total-box">
                <div class="label">Montant Total TTC</div>
                <div class="amount">{{ number_format($devis->total_ttc ?? 0, 2, ',', ' ') }} €</div>
                <p style="margin: 15px 0 0; font-size: 14px; opacity: 0.9;">
                    HT : {{ number_format($devis->total_ht ?? 0, 2, ',', ' ') }} €
                    @if($devis->taux_tva)
                    | TVA ({{ $devis->taux_tva }}%) : {{ number_format(($devis->total_ttc ?? 0) - ($devis->total_ht ?? 0), 2, ',', ' ') }} €
                    @endif
                </p>
            </div>

            <!-- Attachment Notice -->
            <div class="attachment-notice">
                <p>📎 Votre devis détaillé est disponible en pièce jointe</p>
                <p style="margin-top: 15px;">
                    <a href="{{ $devis->getPublicPdfUrl() }}" 
                       style="display: inline-block; background-color: {{ setting('primary_color', '#3b82f6') }}; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: 600; margin-top: 10px;">
                        👁️ Voir le devis en ligne
                    </a>
                </p>
            </div>

            <!-- Contact Box -->
            <div class="contact-box">
                <p><strong>Des questions sur ce devis ?</strong></p>
                <p>N'hésitez pas à nous contacter :</p>
                @if(setting('company_phone'))
                <p class="phone">📞 {{ setting('company_phone') }}</p>
                @endif
                @if(setting('company_email'))
                <p>✉️ <a href="mailto:{{ setting('company_email') }}" style="color: {{ setting('primary_color', '#3b82f6') }};">{{ setting('company_email') }}</a></p>
                @endif
            </div>

            <div class="message">
                <p>Nous restons à votre disposition pour toute information complémentaire.</p>
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
            @if(setting('company_postal_code') || setting('company_city'))
            <p>{{ setting('company_postal_code') }} {{ setting('company_city') }}</p>
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

