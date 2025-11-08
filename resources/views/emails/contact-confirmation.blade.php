<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de réception</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header avec couleur principale -->
                    <tr>
                        <td style="background: linear-gradient(135deg, {{ setting('primary_color', '#3b82f6') }} 0%, {{ setting('secondary_color', '#1e40af') }} 100%); padding: 40px 30px; text-align: center;">
                            @if(setting('company_logo'))
                            <div style="background-color: #ffffff; padding: 15px; border-radius: 8px; display: inline-block; margin-bottom: 20px;">
                                <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name', 'Votre Entreprise') }}" style="height: 60px; max-width: 200px;">
                            </div>
                            @endif
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold;">
                                ✅ Message reçu !
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Contenu principal -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Bonjour <strong>{{ $data['name'] ?? '' }}</strong>,
                            </p>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Nous avons bien reçu votre message et nous vous en remercions. Notre équipe va l'examiner et vous répondre dans les plus brefs délais.
                            </p>
                            
                            <div style="background-color: #f8f9fa; border-left: 4px solid {{ setting('primary_color', '#3b82f6') }}; padding: 20px; margin: 30px 0; border-radius: 5px;">
                                <h3 style="color: #333333; font-size: 18px; margin: 0 0 15px 0; font-weight: bold;">
                                    📋 Récapitulatif de votre message :
                                </h3>
                                <table width="100%" cellpadding="5" cellspacing="0">
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 5px 0;"><strong>Sujet :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 5px 0;">{{ $data['subject'] ?? '' }}</td>
                                    </tr>
                                    @if(isset($data['service_interest']) && $data['service_interest'])
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 5px 0;"><strong>Service :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 5px 0;">{{ $data['service_interest'] }}</td>
                                    </tr>
                                    @endif
                                    @if(isset($data['callback_time']) && $data['callback_time'])
                                    <tr>
                                        <td style="color: #666666; font-size: 14px; padding: 5px 0;"><strong>Rappel :</strong></td>
                                        <td style="color: #333333; font-size: 14px; padding: 5px 0;">{{ $data['callback_time'] }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 20px 0;">
                                En attendant notre réponse, n'hésitez pas à nous contacter directement :
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 30px 0;">
                                <tr>
                                    <td style="padding: 15px; background-color: #f8f9fa; border-radius: 5px; text-align: center;">
                                        <a href="tel:{{ $companyPhone }}" style="color: {{ setting('primary_color', '#3b82f6') }}; text-decoration: none; font-weight: bold; font-size: 16px;">
                                            📞 {{ $companyPhone }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #666666; font-size: 14px; line-height: 1.6; margin: 30px 0 0 0;">
                                Cordialement,<br>
                                <strong style="color: #333333;">{{ $companyName }}</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.<br>
                                Pour nous contacter, utilisez le formulaire sur notre site ou appelez-nous au {{ $companyPhone }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

