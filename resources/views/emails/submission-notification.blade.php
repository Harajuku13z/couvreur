<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle demande de devis</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 700px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 600;
        }
        .header .badge {
            display: inline-block;
            background-color: #fbbf24;
            color: #1e3a8a;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 15px;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .alert-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .alert-box p {
            margin: 0;
            color: #92400e;
            font-weight: 600;
        }
        .client-info {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
        }
        .client-info h2 {
            margin: 0 0 20px;
            color: #1e293b;
            font-size: 20px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: 600;
            color: #475569;
            padding: 12px 15px 12px 0;
            width: 180px;
            vertical-align: top;
        }
        .info-value {
            display: table-cell;
            color: #1e293b;
            padding: 12px 0;
            font-weight: 500;
        }
        .highlight {
            background-color: #dbeafe;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .action-buttons {
            margin: 30px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #3b82f6;
            color: #ffffff;
        }
        .btn-success {
            background-color: #10b981;
            color: #ffffff;
        }
        .project-details {
            background-color: #f0fdf4;
            border: 2px solid #86efac;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .project-details h3 {
            margin: 0 0 15px;
            color: #166534;
            font-size: 18px;
        }
        .detail-item {
            padding: 10px 0;
            border-bottom: 1px dashed #d1fae5;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #166534;
        }
        .footer {
            background-color: #f8fafc;
            padding: 25px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
            color: #64748b;
            font-size: 13px;
        }
        .timestamp {
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🔔 Nouvelle Demande de Devis</h1>
            <div class="badge">ACTION REQUISE</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Alert -->
            <div class="alert-box">
                <p>⚡ Une nouvelle demande de devis vient d'être soumise sur votre site internet !</p>
            </div>

            <!-- Timestamp -->
            <div class="timestamp">
                📅 Reçue le {{ $submission->created_at->format('d/m/Y à H:i') }}
            </div>

            <!-- Client Info -->
            <div class="client-info">
                <h2>👤 Informations du Client</h2>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Nom complet :</div>
                        <div class="info-value"><strong>{{ $submission->gender === 'Mme' ? 'Madame' : 'Monsieur' }} {{ $submission->first_name }} {{ $submission->last_name }}</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">📞 Téléphone :</div>
                        <div class="info-value"><span class="highlight">{{ $submission->phone }}</span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">📧 Email :</div>
                        <div class="info-value"><span class="highlight">{{ $submission->email }}</span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">📍 Localisation :</div>
                        <div class="info-value">{{ $submission->postal_code }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">🏠 Statut :</div>
                        <div class="info-value">{{ $submission->ownership_status === 'owner' ? 'Propriétaire' : 'Locataire' }}</div>
                    </div>
                </div>
            </div>

            <!-- Project Details -->
            <div class="project-details">
                <h3>🏗️ Détails du Projet</h3>
                <div class="detail-item">
                    <span class="detail-label">Type de bien :</span>
                    {{ ucfirst($submission->property_type) }}
                </div>
                <div class="detail-item">
                    <span class="detail-label">Surface :</span>
                    {{ $submission->surface }} m²
                </div>
                @if($submission->work_types)
                <div class="detail-item">
                    <span class="detail-label">Types de travaux :</span>
                    @if(is_array($submission->work_types))
                        {{ implode(', ', array_map('ucfirst', $submission->work_types)) }}
                    @else
                        {{ $submission->work_types }}
                    @endif
                </div>
                @endif
                @if($submission->roof_work_types)
                <div class="detail-item">
                    <span class="detail-label">Travaux toiture :</span>
                    @if(is_array($submission->roof_work_types))
                        {{ implode(', ', $submission->roof_work_types) }}
                    @else
                        {{ $submission->roof_work_types }}
                    @endif
                </div>
                @endif
                @if($submission->facade_work_types)
                <div class="detail-item">
                    <span class="detail-label">Travaux façade :</span>
                    @if(is_array($submission->facade_work_types))
                        {{ implode(', ', $submission->facade_work_types) }}
                    @else
                        {{ $submission->facade_work_types }}
                    @endif
                </div>
                @endif
                @if($submission->isolation_work_types)
                <div class="detail-item">
                    <span class="detail-label">Travaux isolation :</span>
                    @if(is_array($submission->isolation_work_types))
                        {{ implode(', ', $submission->isolation_work_types) }}
                    @else
                        {{ $submission->isolation_work_types }}
                    @endif
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ url('/admin/submissions/' . $submission->id) }}" class="btn btn-primary">
                    📊 Voir les Détails Complets
                </a>
                <a href="tel:{{ $submission->phone }}" class="btn btn-success">
                    📞 Appeler le Client
                </a>
            </div>

            <!-- Quick Stats -->
            <div style="text-align: center; margin-top: 30px; padding: 20px; background-color: #fef3c7; border-radius: 8px;">
                <p style="margin: 0; color: #92400e; font-weight: 600;">
                    ⚡ <strong>Action recommandée :</strong> Contactez le client dans les 2 heures pour maximiser vos chances de conversion !
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ company('name', 'Rénovation Expert') }}</strong></p>
            <p>Dashboard Admin : <a href="{{ url('/admin') }}" style="color: #3b82f6;">{{ url('/admin') }}</a></p>
            <p style="margin-top: 15px;">Cet email a été généré automatiquement par votre système de gestion de devis.</p>
        </div>
    </div>
</body>
</html>
























