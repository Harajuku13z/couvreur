-- =====================================================
-- VÉRIFICATION DU DÉPLOIEMENT DES TEMPLATES
-- =====================================================

-- 1. Vérifier que la table ad_templates existe et a la bonne structure
SELECT 
    'Table ad_templates' as table_name,
    CASE 
        WHEN COUNT(*) > 0 THEN 'EXISTE' 
        ELSE 'MANQUANTE' 
    END as status
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'ad_templates';

-- 2. Vérifier les colonnes de la table ad_templates
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'ad_templates'
ORDER BY ORDINAL_POSITION;

-- 3. Vérifier que la colonne template_id existe dans la table ads
SELECT 
    'Colonne template_id dans ads' as column_name,
    CASE 
        WHEN COUNT(*) > 0 THEN 'EXISTE' 
        ELSE 'MANQUANTE' 
    END as status
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'ads' 
AND COLUMN_NAME = 'template_id';

-- 4. Vérifier les contraintes de clé étrangère
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = DATABASE() 
AND CONSTRAINT_NAME = 'fk_ads_template_id';

-- 5. Vérifier les index
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE() 
AND (TABLE_NAME = 'ad_templates' OR TABLE_NAME = 'ads')
AND INDEX_NAME IN ('idx_service_slug_active', 'idx_service_name', 'idx_template_id')
ORDER BY TABLE_NAME, INDEX_NAME;

-- 6. Vérifier que les migrations sont marquées comme exécutées
SELECT 
    'Migrations templates' as type,
    CASE 
        WHEN COUNT(*) = 2 THEN 'TOUTES EXÉCUTÉES' 
        ELSE CONCAT(COUNT(*), '/2 EXÉCUTÉES') 
    END as status
FROM migrations 
WHERE migration LIKE '2025_10_27%';

-- 7. Test d'insertion d'un template de test (optionnel)
-- Décommentez les lignes suivantes pour tester l'insertion
/*
INSERT INTO ad_templates (
    name, service_name, service_slug, content_html, 
    short_description, long_description, icon,
    meta_title, meta_description, meta_keywords,
    og_title, og_description, twitter_title, twitter_description
) VALUES (
    'Test Template', 'Service Test', 'service-test',
    '<div>Contenu de test</div>', 'Description courte', 'Description longue',
    'fas fa-tools', 'Titre SEO', 'Description SEO', 'mots, clés',
    'Titre OG', 'Description OG', 'Titre Twitter', 'Description Twitter'
);

SELECT 'Template de test créé avec succès!' as message;
*/

-- 8. Résumé final
SELECT 
    '=== RÉSUMÉ DU DÉPLOIEMENT ===' as message
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
SELECT 
    CASE 
        WHEN (SELECT COUNT(*) FROM migrations WHERE migration LIKE '2025_10_27%') = 2
        THEN '✅ Migrations: OK'
        ELSE '❌ Migrations: INCOMPLÈTES'
    END
UNION ALL
SELECT '=== DÉPLOIEMENT TERMINÉ ===' as message;
