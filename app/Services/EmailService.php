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

            // Joindre les photos (si présentes)
            $tracking = $submission->tracking_data ?? [];
            if (is_array($tracking) && !empty($tracking['photos']) && is_array($tracking['photos'])) {
                $photos = array_slice($tracking['photos'], 0, 5);
                foreach ($photos as $photo) {
                    $path = public_path(ltrim($photo, '/'));
                    if (file_exists($path)) {
                        try { $this->mailer->addAttachment($path); } catch (\Throwable $e) { \Log::warning('Attachment error: '.$e->getMessage()); }
                    }
                }
            }

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
        
        // Utiliser le template par défaut avec types de travaux
        return $this->generateDefaultTemplate($submission, 'client');
    }

    private function generateAdminEmailBody(Submission $submission): string
    {
        // Vérifier s'il y a un template personnalisé
        $customTemplate = setting('email_admin_template', '');
        if (!empty($customTemplate)) {
            return $this->processCustomTemplate($customTemplate, $submission, 'admin');
        }
        
        // Utiliser le template par défaut avec types de travaux
        return $this->generateDefaultTemplate($submission, 'admin');
    }

    /**
     * Générer un template d'email par défaut avec types de travaux
     */
    private function generateDefaultTemplate($submission, $type)
    {
        $workTypesString = $this->getWorkTypesString($submission);
        $companyName = setting('company_name', 'Rénovation Expert');
        $propertyLabel = $this->getPropertyTypeString($submission->property_type ?? '');
        $photosHtml = $this->generatePhotosHtml($submission);
        
        if ($type === 'client') {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Demande de devis reçue</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center;'>
                        <h1 style='margin: 0; font-size: 28px;'>✅ Demande Reçue !</h1>
                        <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>" . $companyName . "</p>
                    </div>
                    
                    <div style='padding: 30px;'>
                        <p style='font-size: 16px; margin-bottom: 20px;'>Bonjour <strong>{$submission->first_name} {$submission->last_name}</strong>,</p>
                        
                        <p style='font-size: 16px; margin-bottom: 25px;'>Nous vous remercions d'avoir choisi <strong>" . $companyName . "</strong> pour votre projet de rénovation.</p>
                    
                        <div style='background: #f8f9fa; padding: 25px; border-left: 5px solid #007bff; margin: 25px 0; border-radius: 0 8px 8px 0;'>
                            <h3 style='color: #007bff; margin-top: 0; font-size: 20px;'>📋 Récapitulatif de votre demande</h3>
                            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                                <div>
                                    <p style='margin: 8px 0;'><strong>Type de bien :</strong> " . $propertyLabel . "</p>
                                    <p style='margin: 8px 0;'><strong>Surface :</strong> {$submission->surface} m²</p>
                                    <p style='margin: 8px 0;'><strong>Code postal :</strong> {$submission->postal_code}</p>
                                </div>
                                <div>
                                    <p style='margin: 8px 0;'><strong>Téléphone :</strong> {$submission->phone}</p>
                                    <p style='margin: 8px 0;'><strong>Email :</strong> {$submission->email}</p>
                                </div>
                            </div>
                            
                            <p style='margin: 15px 0 0 0;'><strong>Types de travaux souhaités :</strong> " . $workTypesString . "</p>
                        </div>
                        " . ($photosHtml ? "<div style='margin: 15px 0;'>{$photosHtml}</div>" : "") . "
                        
                        <div style='background: #e8f5e9; padding: 25px; border-radius: 8px; margin: 25px 0; border-left: 5px solid #28a745;'>
                            <h3 style='color: #28a745; margin-top: 0; font-size: 20px;'>📌 Prochaines étapes</h3>
                            <p style='margin: 8px 0; font-size: 14px;'>1. Notre équipe analyse votre demande et votre projet</p>
                            <p style='margin: 8px 0; font-size: 14px;'>2. Un conseiller vous contacte sous 24h pour affiner les détails</p>
                            <p style='margin: 8px 0; font-size: 14px;'>3. Vous recevez votre devis personnalisé et détaillé</p>
                            <p style='margin: 8px 0; font-size: 14px;'>4. Nous planifions ensemble la réalisation de vos travaux</p>
                        </div>
                        
                        <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 25px 0; text-align: center;'>
                            <p style='margin: 0; font-size: 16px;'><strong>À très bientôt,</strong></p>
                            <p style='margin: 5px 0 0 0; font-size: 14px; color: #666;'>L'équipe " . $companyName . "</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>";
        } else {
            return "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Nouvelle demande de devis</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa;'>
                <div style='max-width: 800px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                    <div style='background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 30px; text-align: center;'>
                        <h1 style='margin: 0; font-size: 28px;'>🔔 Nouvelle Demande</h1>
                        <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Demande de devis reçue</p>
                    </div>
                    
                    <div style='padding: 30px;'>
                        <div style='background: #f8f9fa; padding: 25px; border-left: 5px solid #007bff; margin: 25px 0; border-radius: 0 8px 8px 0;'>
                            <h3 style='color: #007bff; margin-top: 0; font-size: 20px;'>👤 Informations Client</h3>
                            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                                <div>
                                    <p style='margin: 8px 0;'><strong>Nom :</strong> {$submission->first_name} {$submission->last_name}</p>
                                    <p style='margin: 8px 0;'><strong>Type de bien :</strong> " . $propertyLabel . "</p>
                                    <p style='margin: 8px 0;'><strong>Surface :</strong> {$submission->surface} m²</p>
                                </div>
                                <div>
                                    <p style='margin: 8px 0;'><strong>Téléphone :</strong> {$submission->phone}</p>
                                    <p style='margin: 8px 0;'><strong>Email :</strong> {$submission->email}</p>
                                    <p style='margin: 8px 0;'><strong>Code postal :</strong> {$submission->postal_code}</p>
                                </div>
                            </div>
                            
                            <p style='margin: 15px 0 0 0;'><strong>Types de travaux souhaités :</strong> " . $workTypesString . "</p>
                        </div>
                        " . ($photosHtml ? "<div style='margin: 15px 0;'>{$photosHtml}</div>" : "") . "
                        
                        <div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 5px solid #ffc107; margin-bottom: 25px;'>
                            <h3 style='color: #856404; margin-top: 0;'>⚠️ Action Requise</h3>
                            <p style='margin: 8px 0; font-size: 16px;'><strong>Contacter le client sous 24h</strong></p>
                            <div style='margin-top: 15px;'>
                                <a href='mailto:{$submission->email}?subject=Re: Votre demande de devis' style='background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-right: 10px; display: inline-block;'>📧 Répondre par email</a>
                                <a href='tel:{$submission->phone}' style='background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>📞 Appeler</a>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>";
        }
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

        // Ajouter placeholder {photos} si demandé
        $variables['{photos}'] = $this->generatePhotosHtml($submission);

        // Remplacer les variables dans le template
        $processedTemplate = str_replace(array_keys($variables), array_values($variables), $template);
        
        // FORCER l'ajout des types de travaux si pas présents dans le template
        $workTypesString = $this->getWorkTypesString($submission);
        if (!empty($workTypesString) && strpos($template, '{work_types}') === false) {
            // Ajouter les types de travaux à la fin du template si pas déjà présents
            $workTypesHtml = "<p><strong>Types de travaux souhaités :</strong> " . $workTypesString . "</p>";
            $processedTemplate = str_replace('</body>', $workTypesHtml . '</body>', $processedTemplate);
        }

        // Si le template ne contient pas {photos}, ajouter les photos automatiquement
        $photosHtml = $this->generatePhotosHtml($submission);
        if (!empty($photosHtml) && strpos($template, '{photos}') === false) {
            $processedTemplate = str_replace('</body>', $photosHtml . '</body>', $processedTemplate);
        }

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
        $key = strtoupper(trim((string)$propertyType));
        $map = [
            'HOUSE' => 'Maison',
            'APARTMENT' => 'Appartement',
            'COMMERCIAL' => 'Commercial',
            'OTHER' => 'Autre',
        ];
        if (isset($map[$key])) return $map[$key];
        return ucfirst(strtolower((string)$propertyType));
    }

    /**
     * Générer le HTML des photos si disponibles
     */
    private function generatePhotosHtml(Submission $submission): string
    {
        $tracking = $submission->tracking_data ?? [];
        $photos = [];
        if (is_array($tracking) && isset($tracking['photos']) && is_array($tracking['photos'])) {
            $photos = array_slice($tracking['photos'], 0, 5);
        }
        if (empty($photos)) return '';
        $items = '';
        foreach ($photos as $photo) {
            $url = $this->publicPhotoUrl($submission, $photo);
            $items .= "<a href='{$url}' target='_blank' style='display:inline-block;margin:4px;border:1px solid #eee;border-radius:6px;overflow:hidden;'>"
                   . "<img src='{$url}' alt='Photo' style='width:120px;height:120px;object-fit:cover;display:block;'>"
                   . "</a>";
        }
        return "<div style='padding:16px;background:#f8f9fa;border-radius:8px;'><h3 style='margin:0 0 10px 0;'>📷 Photos du projet</h3><div>{$items}</div></div>";
    }

    /**
     * Construire une URL absolue HTTPS vers une ressource publique
     */
    private function absoluteUrl(string $path): string
    {
        // Si déjà une URL absolue
        if (preg_match('/^https?:\/\//i', $path)) {
            // Forcer https
            return preg_replace('/^http:\\/\\//i', 'https://', $path);
        }
        $siteUrl = Setting::get('site_url', config('app.url'));
        if (empty($siteUrl)) {
            $siteUrl = config('app.url', 'https://'.parse_url(url('/'), PHP_URL_HOST));
        }
        if (strpos($siteUrl, 'http') !== 0) {
            $siteUrl = 'https://' . ltrim($siteUrl, '/');
        }
        // Normaliser
        $siteUrl = rtrim($siteUrl, '/');
        $normalizedPath = '/' . ltrim($path, '/');
        $full = $siteUrl . $normalizedPath;
        // Forcer https
        return preg_replace('/^http:\\/\\//i', 'https://', $full);
    }

    /**
     * Construire l'URL publique sûre d'une photo de soumission via route (évite 403/hotlinking)
     */
    private function publicPhotoUrl(Submission $submission, string $photo): string
    {
        // Toujours exposer via la route media
        $file = basename($photo);
        $url = route('media.submission.photo', ['id' => $submission->id, 'file' => $file]);
        // Forcer https si nécessaire
        return preg_replace('/^http:\\/\\//i', 'https://', $url);
    }
}