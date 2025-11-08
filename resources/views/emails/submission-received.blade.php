<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de devis reçue</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
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
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            opacity: 0.9;
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
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid {{ setting('primary_color', '#3b82f6') }};
            padding: 20px;
            margin: 30px 0;
            border-radius: 5px;
        }
        .info-box h3 {
            margin: 0 0 15px;
            color: #333333;
            font-size: 18px;
        }
        .info-item {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-item:last-child {
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
        .urgent-box {
            background: linear-gradient(135deg, {{ setting('primary_color', '#3b82f6') }} 0%, {{ setting('secondary_color', '#1e40af') }} 100%);
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
        .urgent-box .phone {
            font-size: 32px;
            font-weight: bold;
            margin: 15px 0;
            letter-spacing: 2px;
        }
        .urgent-box p {
            margin: 10px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .next-steps {
            background-color: #e8f5e9;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
        }
        .next-steps h3 {
            margin: 0 0 15px;
            color: #2e7d32;
            font-size: 18px;
        }
        .step {
            padding: 12px 0;
            color: #555555;
            display: flex;
            align-items: start;
        }
        .step-number {
            background-color: {{ setting('primary_color', '#3b82f6') }};
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .footer {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
        }
        .footer a {
            color: #ffffff;
            text-decoration: underline;
        }
        .social-links {
            margin: 20px 0 10px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #ffffff;
            text-decoration: none;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            @if(setting('company_logo'))
                <div class="logo-wrapper">
                    <img src="{{ asset(setting('company_logo')) }}" alt="{{ company('name') }}" style="height: 60px; max-width: 200px;">
                </div>
            @endif
            <h1>✅ Demande Reçue !</h1>
            <p>Nous avons bien reçu votre demande de devis</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Bonjour {{ $submission->first_name }} {{ $submission->last_name }},
            </div>

            <div class="message">
                <p>Nous vous remercions d'avoir choisi <strong>{{ company('name', 'notre entreprise') }}</strong> pour votre projet de rénovation.</p>
                <p>Votre demande de devis a été enregistrée avec succès et sera traitée dans les plus brefs délais par notre équipe d'experts.</p>
            </div>

            <!-- Urgent Box -->
            <div class="urgent-box">
                <h2>🚨 Vous avez une urgence ?</h2>
                <p>Appelez-nous immédiatement !</p>
                <div class="phone">{{ company('phone', '01 23 45 67 89') }}</div>
                <p>{{ company('hours', 'Du lundi au vendredi, de 9h à 18h') }}</p>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <h3>📋 Récapitulatif de votre demande</h3>
                <div class="info-item">
                    <span class="info-label">Type de bien :</span>
                    <span class="info-value">{{ ucfirst($submission->property_type) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Surface :</span>
                    <span class="info-value">{{ $submission->surface }} m²</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Téléphone :</span>
                    <span class="info-value">{{ $submission->phone }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email :</span>
                    <span class="info-value">{{ $submission->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Code postal :</span>
                    <span class="info-value">{{ $submission->postal_code }}</span>
                </div>
                
                @if($submission->work_types)
                <div class="info-item">
                    <span class="info-label">Types de travaux :</span>
                    <span class="info-value">
                        @php
                            $workTypes = json_decode($submission->work_types, true) ?? [];
                            $workTypeLabels = [
                                'roof' => 'Toiture',
                                'facade' => 'Façade', 
                                'isolation' => 'Isolation'
                            ];
                            $selectedTypes = [];
                            foreach($workTypes as $type) {
                                if(isset($workTypeLabels[$type])) {
                                    $selectedTypes[] = $workTypeLabels[$type];
                                }
                            }
                        @endphp
                        {{ implode(', ', $selectedTypes) }}
                    </span>
                </div>
                @endif
                
                @if($submission->roof_work_types)
                <div class="info-item">
                    <span class="info-label">Travaux de toiture :</span>
                    <span class="info-value">
                        @php
                            $roofTypes = json_decode($submission->roof_work_types, true) ?? [];
                            $roofLabels = [
                                'repair' => 'Réparation',
                                'replacement' => 'Remplacement',
                                'cleaning' => 'Nettoyage',
                                'insulation' => 'Isolation'
                            ];
                            $selectedRoof = [];
                            foreach($roofTypes as $type) {
                                if(isset($roofLabels[$type])) {
                                    $selectedRoof[] = $roofLabels[$type];
                                }
                            }
                        @endphp
                        {{ implode(', ', $selectedRoof) }}
                    </span>
                </div>
                @endif
                
                @if($submission->facade_work_types)
                <div class="info-item">
                    <span class="info-label">Travaux de façade :</span>
                    <span class="info-value">
                        @php
                            $facadeTypes = json_decode($submission->facade_work_types, true) ?? [];
                            $facadeLabels = [
                                'repair' => 'Réparation',
                                'painting' => 'Peinture',
                                'cleaning' => 'Nettoyage',
                                'insulation' => 'Isolation'
                            ];
                            $selectedFacade = [];
                            foreach($facadeTypes as $type) {
                                if(isset($facadeLabels[$type])) {
                                    $selectedFacade[] = $facadeLabels[$type];
                                }
                            }
                        @endphp
                        {{ implode(', ', $selectedFacade) }}
                    </span>
                </div>
                @endif
                
                @if($submission->isolation_work_types)
                <div class="info-item">
                    <span class="info-label">Travaux d'isolation :</span>
                    <span class="info-value">
                        @php
                            $isolationTypes = json_decode($submission->isolation_work_types, true) ?? [];
                            $isolationLabels = [
                                'walls' => 'Murs',
                                'roof' => 'Toiture',
                                'floor' => 'Sol',
                                'windows' => 'Fenêtres'
                            ];
                            $selectedIsolation = [];
                            foreach($isolationTypes as $type) {
                                if(isset($isolationLabels[$type])) {
                                    $selectedIsolation[] = $isolationLabels[$type];
                                }
                            }
                        @endphp
                        {{ implode(', ', $selectedIsolation) }}
                    </span>
                </div>
                @endif
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>📌 Prochaines étapes</h3>
                <div class="step">
                    <span class="step-number">1</span>
                    <span>Notre équipe analyse votre demande et votre projet</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span>Un conseiller vous contacte sous 24h pour affiner les détails</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span>Vous recevez votre devis personnalisé et détaillé</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span>Nous planifions ensemble la réalisation de vos travaux</span>
                </div>
            </div>

            <div class="message">
                <p><strong>À très bientôt,</strong></p>
                <p>L'équipe {{ company('name', 'Rénovation Expert') }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            @if(company('name'))
            <p><strong>{{ company('name') }}</strong></p>
            @endif
            @if(company('address'))
            <p>{{ company('address') }}@if(company('city')), {{ company('city') }}@endif</p>
            @endif
            @if(company('phone'))
            <p>Tél : {{ company('phone') }}</p>
            @endif
            @if(company('email'))
            <p>Email : <a href="mailto:{{ company('email') }}">{{ company('email') }}</a></p>
            @endif
            
            @if(setting('facebook_url') || setting('instagram_url') || setting('linkedin_url'))
            <div class="social-links">
                @if(setting('facebook_url'))
                <a href="{{ setting('facebook_url') }}" target="_blank">📘</a>
                @endif
                @if(setting('instagram_url'))
                <a href="{{ setting('instagram_url') }}" target="_blank">📷</a>
                @endif
                @if(setting('linkedin_url'))
                <a href="{{ setting('linkedin_url') }}" target="_blank">💼</a>
                @endif
            </div>
            @endif
            
            <p style="font-size: 12px; opacity: 0.7; margin-top: 20px;">
                Cet email a été envoyé automatiquement suite à votre demande de devis sur notre site internet.
            </p>
        </div>
    </div>
</body>
</html>


















