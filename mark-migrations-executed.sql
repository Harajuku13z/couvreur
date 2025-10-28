-- =====================================================
-- MARQUER LES MIGRATIONS COMME EXÉCUTÉES
-- =====================================================
-- Exécuter après avoir créé les tables manuellement

-- Marquer la migration de création de la table ad_templates comme exécutée
INSERT INTO migrations (migration, batch) 
VALUES ('2025_10_27_224825_create_ad_templates_table', 
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations m));

-- Marquer la migration d'ajout de la colonne template_id comme exécutée
INSERT INTO migrations (migration, batch) 
VALUES ('2025_10_27_224854_add_template_id_to_ads_table', 
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations m));

-- Vérifier que les migrations ont été marquées
SELECT migration, batch 
FROM migrations 
WHERE migration LIKE '2025_10_27%' 
ORDER BY batch DESC;

SELECT 'Migrations marquées comme exécutées!' as status;
