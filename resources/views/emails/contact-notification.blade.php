<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header avec couleur principale -->
                    <tr>
                        <td style="background: linear-gradient(135deg, {{ setting('primary_color', '#3b82f6') }} 0%, {{ setting('secondary_color', '#1e40af') }} 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">
                                📧 Nouveau message de contact
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Contenu principal -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Bonjour,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 30px 0;">
                                Vous avez reçu un nouveau message via le formulaire de contact de votre site web.
                            </p>
                            
                            <div style="background-color: #f8f9fa; border-left: 4px solid {{ setting('primary_color', '#3b82f6') }}; padding: 20px; margin: 30px 0; border-radius: 5px;">
                                <h3 style="color: #333333; font-size: 18px; margin: 0 0 20px 0; font-weight: bold;">
                                    📋 Informations du contact :
                                </h3>
                                <table width="100%" cellpadding="8" cellspacing="0">
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 8px 0; width: 150px;"><strong>Nom :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $data['name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 8px 0;"><strong>Email :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 8px 0;">
                                            <a href="mailto:{{ $data['email'] ?? '' }}" style="color: {{ setting('primary_color', '#3b82f6') }}; text-decoration: none;">
                                                {{ $data['email'] ?? '' }}
                                            </a>
                                        </td>
                                    </tr>
                                    @if(isset($data['phone']) && $data['phone'])
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 8px 0;"><strong>Téléphone :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 8px 0;">
                                            <a href="tel:{{ $data['phone'] }}" style="color: {{ setting('primary_color', '#3b82f6') }}; text-decoration: none;">
                                                {{ $data['phone'] }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                    @if(isset($data['postal_code']) && $data['postal_code'])
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 8px 0;"><strong>Code postal :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $data['postal_code'] }}</td>
                                    </tr>
                                    @endif
                                    @if(isset($data['city']) && $data['city'])
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 8px 0;"><strong>Ville :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $data['city'] }}</td>
                                    </tr>
                                    @endif
                                    @if(isset($data['callback_time']) && $data['callback_time'])
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 8px 0;"><strong>Quand rappeler :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $data['callback_time'] }}</td>
                                    </tr>
                                    @endif
                                    @if(isset($data['service_interest']) && $data['service_interest'])
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 8px 0;"><strong>Service intéressé :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 8px 0;">{{ $data['service_interest'] }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 8px 0;"><strong>Sujet :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 8px 0; font-weight: bold;">{{ $data['subject'] ?? '' }}</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 5px; padding: 20px; margin: 30px 0;">
                                <h3 style="color: #333333; font-size: 18px; margin: 0 0 15px 0; font-weight: bold;">
                                    💬 Message :
                                </h3>
                                <p style="color: #333333; font-size: 14px; line-height: 1.8; margin: 0; white-space: pre-wrap;">
                                    {{ $data['message'] ?? '' }}
                                </p>
                            </div>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="mailto:{{ $data['email'] ?? '' }}?subject=Re: {{ $data['subject'] ?? '' }}" 
                                           style="display: inline-block; background: linear-gradient(135deg, {{ setting('primary_color', '#3b82f6') }} 0%, {{ setting('secondary_color', '#1e40af') }} 100%); color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 5px; font-weight: bold; font-size: 16px;">
                                            📧 Répondre à {{ $data['name'] ?? '' }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                {{ $companyName }}<br>
                                Message reçu le {{ now()->format('d/m/Y à H:i') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

