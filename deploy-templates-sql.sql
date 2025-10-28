-- =====================================================
-- DÉPLOIEMENT DU SYSTÈME DE TEMPLATES D'ANNONCES
-- =====================================================
-- Exécuter ces requêtes directement sur votre base de données MySQL

-- 1. Créer la table ad_templates
CREATE TABLE IF NOT EXISTS ad_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    service_slug VARCHAR(255) NOT NULL,
    content_html LONGTEXT NOT NULL,
    short_description TEXT NOT NULL,
    long_description TEXT NOT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-tools',
    meta_title VARCHAR(160) NOT NULL,
    meta_description TEXT NOT NULL,
    meta_keywords TEXT NOT NULL,
    og_title VARCHAR(160) NOT NULL,
    og_description TEXT NOT NULL,
    twitter_title VARCHAR(160) NOT NULL,
    twitter_description TEXT NOT NULL,
    ai_prompt_used JSON NULL,
    ai_response_data JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_service_slug_active (service_slug, is_active),
    INDEX idx_service_name (service_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Ajouter la colonne template_id à la table ads
ALTER TABLE ads 
ADD COLUMN template_id BIGINT UNSIGNED NULL AFTER city_id,
ADD INDEX idx_template_id (template_id),
ADD CONSTRAINT fk_ads_template_id 
    FOREIGN KEY (template_id) 
    REFERENCES ad_templates(id) 
    ON DELETE SET NULL;

-- 3. Vérifier que les tables ont été créées correctement
SELECT 'Table ad_templates créée avec succès' as status;
SELECT COUNT(*) as nombre_templates FROM ad_templates;

SELECT 'Colonne template_id ajoutée avec succès' as status;
DESCRIBE ads;

-- =====================================================
-- VÉRIFICATIONS FINALES
-- =====================================================
-- Vérifier que la table ad_templates existe
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'ad_templates';

-- Vérifier que la colonne template_id existe dans ads
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'ads' 
AND COLUMN_NAME = 'template_id';

-- =====================================================
-- INSTRUCTIONS POST-DÉPLOIEMENT
-- =====================================================
-- 1. Aller sur https://votre-site.com/admin/ads/templates
-- 2. Vérifier que la page se charge sans erreur
-- 3. Cliquer sur "Créer un Template"
-- 4. Sélectionner un service et créer un template
-- 5. Tester la génération d'annonces pour plusieurs villes

SELECT 'Déploiement terminé avec succès!' as message;
