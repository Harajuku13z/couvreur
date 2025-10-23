<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Models\Setting;
use App\Models\Submission;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer(): void
    {
        try {
            // Configuration SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = Setting::get('mail_host', 'smtp.hostinger.com');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = Setting::get('mail_username', 'contact@sausercouverture.fr');
            $this->mailer->Password = Setting::get('mail_password', 'Harajuku1993@');
            $this->mailer->SMTPSecure = Setting::get('mail_encryption', 'tls');
            $this->mailer->Port = Setting::get('mail_port', 587);

            // Configuration de l'expéditeur
            $this->mailer->setFrom(
                Setting::get('mail_from_address', 'contact@sausercouverture.fr'),
                Setting::get('mail_from_name', 'SA User Couverture')
            );

            // Configuration des caractères
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->Encoding = 'base64';

        } catch (Exception $e) {
            \Log::error('Erreur configuration PHPMailer: ' . $e->getMessage());
        }
    }

    public function sendSubmissionReceived(Submission $submission): bool
    {
        try {
            if (!Setting::get('email_enabled', false)) {
                \Log::info('Email désactivé, pas d\'envoi');
                return false;
            }

            if (!$submission->email) {
                \Log::warning('Pas d\'email pour la soumission ' . $submission->id);
                return false;
            }

            $this->mailer->clearAddresses();
            $this->mailer->addAddress($submission->email);
            // Utiliser le sujet personnalisé ou le défaut
            $customSubject = setting('email_client_subject', '');
            $this->mailer->Subject = !empty($customSubject) ? $customSubject : '✅ Votre demande de devis a été reçue - ' . setting('company_name', 'Simulateur');
            
            // Contenu HTML de l'email
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->generateSubmissionEmailBody($submission);

            $this->mailer->send();
            \Log::info('Email envoyé avec succès à ' . $submission->email);
            return true;

        } catch (Exception $e) {
            \Log::error('Erreur envoi email: ' . $e->getMessage());
            return false;
        }
    }

    public function sendSubmissionNotification(Submission $submission): bool
    {
        try {
            if (!Setting::get('email_enabled', false)) {
                return false;
            }

            $adminEmail = Setting::get('admin_notification_email') ?? Setting::get('company_email') ?? Setting::get('mail_from_address');
            if (!$adminEmail) {
                \Log::warning('Pas d\'email admin configuré');
                return false;
            }

            $this->mailer->clearAddresses();
            $this->mailer->addAddress($adminEmail);
            // Utiliser le sujet personnalisé ou le défaut
            $customSubject = setting('email_admin_subject', '');
            $this->mailer->Subject = !empty($customSubject) ? $customSubject : '🔔 Nouvelle demande de devis - ' . $submission->first_name . ' ' . $submission->last_name;
            
            // Contenu HTML de l'email admin
            $this->mailer->isHTML(true);
            $this->mailer->Body = $this->generateAdminEmailBody($submission);

            $this->mailer->send();
            \Log::info('Email admin envoyé avec succès à ' . $adminEmail);
            return true;

        } catch (Exception $e) {
            \Log::error('Erreur envoi email admin: ' . $e->getMessage());
            return false;
        }
    }

    private function generateSubmissionEmailBody(Submission $submission): string
    {
        // Vérifier s'il y a un template personnalisé
        $customTemplate = setting('email_client_template', '');
        if (!empty($customTemplate)) {
            return $this->processCustomTemplate($customTemplate, $submission, 'client');
        }
        
        $workTypes = is_string($submission->work_types) ? json_decode($submission->work_types, true) : ($submission->work_types ?? []);
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
        
        // Debug: log les types de travaux
        \Log::info('Email submission - work types debug', [
            'work_types_raw' => $submission->work_types,
            'work_types_decoded' => $workTypes,
            'selected_types' => $selectedTypes,
            'submission_id' => $submission->id
        ]);
        
        // Traduction du type de bien
        $propertyTypeLabels = [
            'house' => 'Maison',
            'apartment' => 'Appartement',
            'commercial' => 'Commercial',
            'other' => 'Autre'
        ];
        $propertyTypeFrench = $propertyTypeLabels[$submission->property_type] ?? ucfirst($submission->property_type);

        // Détails des travaux de toiture
        $roofDetails = '';
        if ($submission->roof_work_types) {
            $roofTypes = is_string($submission->roof_work_types) ? json_decode($submission->roof_work_types, true) : ($submission->roof_work_types ?? []);
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
            if (!empty($selectedRoof)) {
                $roofDetails = "<p><strong>Travaux de toiture :</strong> " . implode(', ', $selectedRoof) . "</p>";
            }
        }

        // Détails des travaux de façade
        $facadeDetails = '';
        if ($submission->facade_work_types) {
            $facadeTypes = is_string($submission->facade_work_types) ? json_decode($submission->facade_work_types, true) : ($submission->facade_work_types ?? []);
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
            if (!empty($selectedFacade)) {
                $facadeDetails = "<p><strong>Travaux de façade :</strong> " . implode(', ', $selectedFacade) . "</p>";
            }
        }

        // Détails des travaux d'isolation
        $isolationDetails = '';
        if ($submission->isolation_work_types) {
            $isolationTypes = is_string($submission->isolation_work_types) ? json_decode($submission->isolation_work_types, true) : ($submission->isolation_work_types ?? []);
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
            if (!empty($selectedIsolation)) {
                $isolationDetails = "<p><strong>Travaux d'isolation :</strong> " . implode(', ', $selectedIsolation) . "</p>";
            }
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Demande de devis reçue</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <!-- Header avec logo entreprise -->
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px;'>✅ Demande Reçue !</h1>
                    <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>" . setting('company_name', 'Rénovation Expert') . "</p>
                </div>
                
                <!-- Contenu principal -->
                <div style='padding: 30px;'>
                    <p style='font-size: 16px; margin-bottom: 20px;'>Bonjour <strong>{$submission->first_name} {$submission->last_name}</strong>,</p>
                    
                    <p style='font-size: 16px; margin-bottom: 25px;'>Nous vous remercions d'avoir choisi <strong>" . setting('company_name', 'notre entreprise') . "</strong> pour votre projet de rénovation.</p>
                
                <div style='background: #f8f9fa; padding: 25px; border-left: 5px solid #007bff; margin: 25px 0; border-radius: 0 8px 8px 0;'>
                    <h3 style='color: #007bff; margin-top: 0; font-size: 20px;'>📋 Récapitulatif de votre demande</h3>
                    <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                        <div>
                            <p style='margin: 8px 0;'><strong>Type de bien :</strong> " . $propertyTypeFrench . "</p>
                            <p style='margin: 8px 0;'><strong>Surface :</strong> {$submission->surface} m²</p>
                            <p style='margin: 8px 0;'><strong>Code postal :</strong> {$submission->postal_code}</p>
                        </div>
                        <div>
                            <p style='margin: 8px 0;'><strong>Téléphone :</strong> {$submission->phone}</p>
                            <p style='margin: 8px 0;'><strong>Email :</strong> {$submission->email}</p>
                        </div>
                    </div>";
        
        // AFFICHAGE SIMPLIFIÉ des types de travaux
        $workTypesDisplay = '';
        if (!empty($selectedTypes)) {
            $workTypesDisplay = "<p><strong>Types de travaux :</strong> " . implode(', ', $selectedTypes) . "</p>";
        } else {
            // Fallback simple: afficher les types de travaux bruts
            $workTypes = is_string($submission->work_types) ? json_decode($submission->work_types, true) : ($submission->work_types ?? []);
            if (!empty($workTypes)) {
                $workTypesDisplay = "<p><strong>Types de travaux :</strong> " . implode(', ', $workTypes) . "</p>";
            }
        }
        
        // TOUJOURS afficher les types de travaux
        $html .= $workTypesDisplay;
        
        $html .= $roofDetails . $facadeDetails . $isolationDetails;
        
        $html .= "
                </div>
                
                <div style='background: #e8f5e9; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 5px solid #28a745;'>
                    <h3 style='color: #28a745; margin-top: 0; font-size: 20px;'>📌 Prochaines étapes</h3>
                    <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                        <div>
                            <p style='margin: 8px 0; font-size: 14px;'>1. Notre équipe analyse votre demande et votre projet</p>
                            <p style='margin: 8px 0; font-size: 14px;'>2. Un conseiller vous contacte sous 24h pour affiner les détails</p>
                        </div>
                        <div>
                            <p style='margin: 8px 0; font-size: 14px;'>3. Vous recevez votre devis personnalisé et détaillé</p>
                            <p style='margin: 8px 0; font-size: 14px;'>4. Nous planifions ensemble la réalisation de vos travaux</p>
                        </div>
                    </div>
                </div>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 25px 0; text-align: center;'>
                    <p style='margin: 0; font-size: 16px;'><strong>À très bientôt,</strong></p>
                    <p style='margin: 5px 0 0 0; font-size: 14px; color: #666;'>L'équipe " . setting('company_name', 'Rénovation Expert') . "</p>
                </div>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    private function generateAdminEmailBody(Submission $submission): string
    {
        // Vérifier s'il y a un template personnalisé
        $customTemplate = setting('email_admin_template', '');
        if (!empty($customTemplate)) {
            return $this->processCustomTemplate($customTemplate, $submission, 'admin');
        }
        
        $workTypes = is_string($submission->work_types) ? json_decode($submission->work_types, true) : ($submission->work_types ?? []);
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
        
        // Debug: log les types de travaux pour l'email admin
        \Log::info('Email admin - work types debug', [
            'work_types_raw' => $submission->work_types,
            'work_types_decoded' => $workTypes,
            'selected_types' => $selectedTypes,
            'submission_id' => $submission->id
        ]);

        // Détails des travaux de toiture
        $roofDetails = '';
        if ($submission->roof_work_types) {
            $roofTypes = is_string($submission->roof_work_types) ? json_decode($submission->roof_work_types, true) : ($submission->roof_work_types ?? []);
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
            if (!empty($selectedRoof)) {
                $roofDetails = "<p><strong>Travaux de toiture :</strong> " . implode(', ', $selectedRoof) . "</p>";
            }
        }

        // Détails des travaux de façade
        $facadeDetails = '';
        if ($submission->facade_work_types) {
            $facadeTypes = is_string($submission->facade_work_types) ? json_decode($submission->facade_work_types, true) : ($submission->facade_work_types ?? []);
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
            if (!empty($selectedFacade)) {
                $facadeDetails = "<p><strong>Travaux de façade :</strong> " . implode(', ', $selectedFacade) . "</p>";
            }
        }

        // Détails des travaux d'isolation
        $isolationDetails = '';
        if ($submission->isolation_work_types) {
            $isolationTypes = is_string($submission->isolation_work_types) ? json_decode($submission->isolation_work_types, true) : ($submission->isolation_work_types ?? []);
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
            if (!empty($selectedIsolation)) {
                $isolationDetails = "<p><strong>Travaux d'isolation :</strong> " . implode(', ', $selectedIsolation) . "</p>";
            }
        }

        // Informations de l'entreprise
        $companyName = setting('company_name', 'Rénovation Expert');
        $companyPhone = setting('company_phone', '01 23 45 67 89');
        $companyEmail = setting('company_email', 'contact@entreprise.com');
        $companyAddress = setting('company_address', '123 Rue de la Paix, 75001 Paris');

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Nouvelle demande de devis</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa;'>
            <div style='max-width: 800px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                <!-- Header avec logo entreprise -->
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px;'>🔔 Nouvelle Demande de Devis</h1>
                    <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>" . $companyName . "</p>
                </div>
                
                <!-- Informations du client -->
                <div style='padding: 30px;'>
                    <div style='background: #f8f9fa; padding: 25px; border-left: 5px solid #dc3545; margin-bottom: 25px; border-radius: 0 8px 8px 0;'>
                        <h2 style='color: #dc3545; margin-top: 0; font-size: 22px;'>👤 Informations du Client</h2>
                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                            <div>
                                <p style='margin: 8px 0;'><strong>Nom complet :</strong> {$submission->first_name} {$submission->last_name}</p>
                                <p style='margin: 8px 0;'><strong>Email :</strong> <a href='mailto:{$submission->email}' style='color: #007bff;'>{$submission->email}</a></p>
                            </div>
                            <div>
                                <p style='margin: 8px 0;'><strong>Téléphone :</strong> <a href='tel:{$submission->phone}' style='color: #007bff;'>{$submission->phone}</a></p>
                                <p style='margin: 8px 0;'><strong>Code postal :</strong> {$submission->postal_code}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Détails du projet -->
                    <div style='background: #e8f5e9; padding: 25px; border-radius: 8px; margin-bottom: 25px;'>
                        <h2 style='color: #28a745; margin-top: 0; font-size: 22px;'>🏠 Détails du Projet</h2>
                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                            <div>
                                <p style='margin: 8px 0;'><strong>Type de bien :</strong> " . ucfirst($submission->property_type) . "</p>
                                <p style='margin: 8px 0;'><strong>Surface :</strong> {$submission->surface} m²</p>
                            </div>
                            <div>";
        
        // AFFICHAGE FORCÉ des types de travaux dans l'email admin
        $workTypesDisplay = '';
        if (!empty($selectedTypes)) {
            $workTypesDisplay = "<p style='margin: 8px 0;'><strong>Types de travaux :</strong><br>" . implode('<br>', $selectedTypes) . "</p>";
        } else {
            // Fallback: afficher les types de travaux même si la logique principale échoue
            $workTypes = is_string($submission->work_types) ? json_decode($submission->work_types, true) : ($submission->work_types ?? []);
            if (!empty($workTypes)) {
                $workTypesDisplay = "<p style='margin: 8px 0;'><strong>Types de travaux :</strong><br>" . implode('<br>', $workTypes) . "</p>";
            }
        }
        
        // TOUJOURS afficher les types de travaux
        $html .= $workTypesDisplay;
        
        $html .= $roofDetails . $facadeDetails . $isolationDetails;
        
        $html .= "
                </div>
                        </div>
                    </div>
                    
                    <!-- Action requise -->
                    <div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 5px solid #ffc107; margin-bottom: 25px;'>
                        <h3 style='color: #856404; margin-top: 0;'>⚠️ Action Requise</h3>
                        <p style='margin: 8px 0; font-size: 16px;'><strong>Contacter le client sous 24h</strong></p>
                        <div style='margin-top: 15px;'>
                            <a href='mailto:{$submission->email}?subject=Re: Votre demande de devis' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px; display: inline-block;'>📧 Répondre par email</a>
                            <a href='tel:{$submission->phone}' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>📞 Appeler</a>
                        </div>
                    </div>
                    
                    <!-- Informations entreprise -->
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; border-top: 3px solid #6c757d;'>
                        <h3 style='color: #495057; margin-top: 0;'>🏢 " . $companyName . "</h3>";
        
        if ($companyPhone) {
            $html .= "<p style='margin: 5px 0;'><strong>📞 Téléphone :</strong> " . $companyPhone . "</p>";
        }
        if ($companyEmail) {
            $html .= "<p style='margin: 5px 0;'><strong>📧 Email :</strong> " . $companyEmail . "</p>";
        }
        if ($companyAddress) {
            $html .= "<p style='margin: 5px 0;'><strong>📍 Adresse :</strong> " . $companyAddress . "</p>";
        }
        
        $html .= "
                        <p style='margin: 15px 0 0 0; font-size: 14px; color: #6c757d;'>
                            Email automatique généré le " . date('d/m/Y à H:i') . "
                        </p>
                    </div>
                </div>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Process custom email template
     */
    private function processCustomTemplate($template, $submission, $type)
    {
        // Variables disponibles
        $variables = [
            '{first_name}' => $submission->first_name ?? '',
            '{last_name}' => $submission->last_name ?? '',
            '{company_name}' => setting('company_name', 'Rénovation Expert'),
            '{company_phone}' => setting('company_phone', ''),
            '{company_email}' => setting('company_email', ''),
            '{company_address}' => setting('company_address', ''),
            '{work_types}' => $this->getWorkTypesString($submission),
            '{property_type}' => $this->getPropertyTypeString($submission->property_type ?? ''),
            '{surface}' => $submission->surface ?? '',
            '{phone}' => $submission->phone ?? '',
            '{email}' => $submission->email ?? '',
            '{postal_code}' => $submission->postal_code ?? '',
            '{date}' => date('d/m/Y à H:i')
        ];

        // Remplacer les variables dans le template
        $processedTemplate = str_replace(array_keys($variables), array_values($variables), $template);

        return $processedTemplate;
    }

    /**
     * Get work types as string
     */
    private function getWorkTypesString($submission)
    {
        $workTypes = is_string($submission->work_types) ? json_decode($submission->work_types, true) : ($submission->work_types ?? []);
        
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
        
        // Debug: log les types de travaux pour les templates
        \Log::info('Template work types debug', [
            'work_types_raw' => $submission->work_types,
            'work_types_decoded' => $workTypes,
            'selected_types' => $selectedTypes,
            'submission_id' => $submission->id
        ]);
        
        // Si aucun type traduit trouvé, retourner les types bruts
        if (empty($selectedTypes) && !empty($workTypes)) {
            return implode(', ', $workTypes);
        }
        
        return implode(', ', $selectedTypes);
    }

    /**
     * Get property type as string
     */
    private function getPropertyTypeString($propertyType)
    {
        $propertyTypeLabels = [
            'house' => 'Maison',
            'apartment' => 'Appartement',
            'commercial' => 'Commercial',
            'other' => 'Autre'
        ];
        
        return $propertyTypeLabels[$propertyType] ?? ucfirst($propertyType);
    }

    /**
     * Send test email template
     */
    public function sendTestEmailTemplate($email, $type)
    {
        // Créer un objet submission factice pour le test
        $testSubmission = (object) [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'work_types' => json_encode(['roof', 'facade']),
            'property_type' => 'house',
            'surface' => '120',
            'phone' => '01 23 45 67 89',
            'email' => $email,
            'postal_code' => '75001'
        ];

        if ($type === 'client') {
            $template = setting('email_client_template', '');
            $subject = setting('email_client_subject', '✅ Demande de devis reçue - Rénovation Expert');
        } else {
            $template = setting('email_admin_template', '');
            $subject = setting('email_admin_subject', '🚨 Nouvelle demande de devis - Action requise');
        }

        if (empty($template)) {
            throw new \Exception('Template email non configuré');
        }

        $htmlContent = $this->processCustomTemplate($template, $testSubmission, $type);

        $this->mailer->isHTML(true);
        $this->mailer->setFrom(setting('email_client_from_email', 'contact@entreprise.com'), setting('email_client_from_name', 'Rénovation Expert'));
        $this->mailer->addAddress($email);
        $this->mailer->Subject = $subject;
        $this->mailer->Body = $htmlContent;

        return $this->mailer->send();
    }
}








