-- =====================================================
-- SCRIPT SQL DE CORRECTION RAPIDE
-- =====================================================
-- À exécuter directement sur la base de données MySQL

-- 1. Vérifier si la table ad_templates existe
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Table ad_templates existe' 
        ELSE 'Table ad_templates manquante' 
    END as status
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'ad_templates';

-- 2. Créer la table ad_templates si elle n'existe pas
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

-- 3. Vérifier si la colonne template_id existe dans ads
SELECT 
    CASE 
        WHEN COUNT(*) > 0 THEN 'Colonne template_id existe' 
        ELSE 'Colonne template_id manquante' 
    END as status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'ads' 
AND COLUMN_NAME = 'template_id';

-- 4. Ajouter la colonne template_id si elle n'existe pas
ALTER TABLE ads 
ADD COLUMN template_id BIGINT UNSIGNED NULL AFTER city_id,
ADD INDEX idx_template_id (template_id),
ADD CONSTRAINT fk_ads_template_id 
    FOREIGN KEY (template_id) 
    REFERENCES ad_templates(id) 
    ON DELETE SET NULL;

-- 5. Marquer les migrations comme exécutées
INSERT IGNORE INTO migrations (migration, batch) 
VALUES 
    ('2025_10_27_224825_create_ad_templates_table', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations m)),
    ('2025_10_27_224854_add_template_id_to_ads_table', (SELECT COALESCE(MAX(batch), 0) + 2 FROM migrations m));

-- 6. Vérification finale
SELECT '=== VÉRIFICATION FINALE ===' as message
UNION ALL
SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ad_templates') > 0
        THEN '✅ Table ad_templates: OK'
        ELSE '❌ Table ad_templates: MANQUANTE'
    END
UNION ALL
SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ads' AND COLUMN_NAME = 'template_id') > 0
        THEN '✅ Colonne template_id: OK'
        ELSE '❌ Colonne template_id: MANQUANTE'
    END
UNION ALL
SELECT '=== CORRECTION TERMINÉE ===' as message;
