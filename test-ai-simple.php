<?php

echo "=== Test de génération IA simple ===\n";

// Simuler les données
$serviceName = "Ravalement & façades";
$shortDescription = "Service professionnel de ravalement et façades";
$companyInfo = [
    'company_name' => 'JD RENOVATION SERVICE',
    'company_city' => 'Pontoise',
    'company_region' => 'Val-d\'Oise',
    'company_phone' => '0609372706',
    'company_email' => 'contact@jd-renovation-service.fr',
    'company_address' => '123 Rue de la Paix'
];

echo "Service: $serviceName\n";
echo "Description courte: $shortDescription\n";

// Construire le prompt comme dans le contrôleur
$prompt = "Tu es un expert en rédaction web et en marketing pour le secteur de la rénovation et du bâtiment.

CONTEXTE:
Entreprise: {$companyInfo['company_name']}
Localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
Service: {$serviceName}
Description courte: {$shortDescription}

MISSION:
Créez un contenu HTML professionnel, attractif et optimisé SEO pour une page de service de rénovation.

STRUCTURE HTML OBLIGATOIRE - EXACTEMENT COMME CET EXEMPLE:
<div class=\"grid md:grid-cols-2 gap-8\">
  <!-- Colonne gauche : description + engagement + prestations -->
  <div class=\"space-y-6\">
    <!-- Introduction générale -->
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">
        [ÉCRIVEZ UNE INTRODUCTION PERSONNALISÉE pour le service {$serviceName} à {$companyInfo['company_city']}, {$companyInfo['company_region']}. Adaptez le contenu selon le type de service : toiture, façade, isolation, etc.]
      </p>
      <p class=\"text-lg leading-relaxed\">
        [ÉCRIVEZ UN DEUXIÈME PARAGRAPHE sur l'expertise spécifique au service {$serviceName}]
      </p>
      <p class=\"text-lg leading-relaxed\">
        [ÉCRIVEZ UN TROISIÈME PARAGRAPHE sur l'approche personnalisée et la satisfaction client]
      </p>
    </div>

    <!-- Engagement qualité -->
    <div class=\"bg-blue-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Notre Engagement Qualité</h3>
      <p class=\"leading-relaxed mb-3\">
        Chez <strong>{$companyInfo['company_name']}</strong>, nous mettons un point d'honneur à garantir la satisfaction totale de nos clients. Chaque projet est unique et mérite une attention particulière.
      </p>
      <p class=\"leading-relaxed\">
        [ÉCRIVEZ UN PARAGRAPHE sur la qualité des matériaux et techniques spécifiques au service {$serviceName}]
      </p>
    </div>

    <!-- Prestations -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$serviceName}</h3>
    <ul class=\"space-y-3\">
      [LISTEZ 6-8 PRESTATIONS SPÉCIFIQUES au service {$serviceName}. Adaptez selon le type : toiture, façade, isolation, gouttières, etc.]
    </ul>

    <!-- Pourquoi choisir notre entreprise -->
    <div class=\"bg-green-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi Choisir Notre Entreprise</h3>
      <p class=\"leading-relaxed\">
        [ÉCRIVEZ UN PARAGRAPHE sur la réputation et l'expertise locale pour le service {$serviceName} à {$companyInfo['company_city']}, {$companyInfo['company_region']}]
      </p>
    </div>
  </div>

  <!-- Colonne droite : expertise locale + devis + infos pratiques -->
  <div class=\"space-y-6\">
    <!-- Expertise locale -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
    <p class=\"leading-relaxed\">
      [ÉCRIVEZ UN PARAGRAPHE sur l'expertise locale spécifique au service {$serviceName} dans la région {$companyInfo['company_region']}]
    </p>

    <!-- Devis -->
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un Devis ?</h4>
      <p class=\"mb-4\">
        Contactez-nous dès maintenant pour un devis personnalisé et gratuit pour vos {$serviceName}.
      </p>
      <a href=\"https://www.jd-renovation-service.fr/form/propertyType\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">
        Demande de devis
      </a>
    </div>

    <!-- Informations pratiques -->
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        [LISTEZ 4-6 INFORMATIONS PRATIQUES spécifiques au service {$serviceName} : délais, garanties, financement, etc.]
      </ul>
    </div>
  </div>
</div>

INSTRUCTIONS DÉTAILLÉES:
1. ADAPTEZ COMPLÈTEMENT le contenu au service spécifique: {$serviceName}
2. ÉCRIVEZ du contenu PERSONNALISÉ selon le type de service (toiture, façade, isolation, gouttières, etc.)
3. UTILISEZ les informations de l'entreprise: {$companyInfo['company_name']}
4. INTÉGREZ la localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
5. GARDEZ la structure HTML exacte de l'exemple ci-dessus
6. PERSONNALISEZ les prestations selon le service (pas de contenu générique)
7. ÉCRIVEZ du contenu UNIQUE et SPÉCIFIQUE au service
8. ADAPTEZ le vocabulaire et les formulations selon le service
9. INCLUEZ des informations sur le financement, les garanties, les délais
10. VARIEZ le contenu pour éviter les répétitions

FORMAT DE RÉPONSE (JSON):
{
  \"description\": \"<div class=\\\"grid md:grid-cols-2 gap-8\\\">...CONTENU HTML COMPLET EN 2 COLONNES...</div>\",
  \"short_description\": \"[Description courte et accrocheuse pour la page d'accueil - 200-300 caractères]\",
  \"icon\": \"fas fa-[icône appropriée au service]\",
  \"meta_title\": \"[Titre SEO optimisé avec ville/région - max 60 caractères]\",
  \"meta_description\": \"[Description SEO engageante avec localisation et CTA - 150-160 caractères]\",
  \"og_title\": \"[Titre optimisé pour Facebook/LinkedIn - max 60 caractères]\",
  \"og_description\": \"[Description engageante pour les réseaux sociaux - 150-160 caractères]\",
  \"meta_keywords\": \"[Mots-clés SEO séparés par virgules - max 255 caractères]\"
}

IMPORTANT:
- SUIVEZ EXACTEMENT la structure HTML de l'exemple
- ÉCRIVEZ du contenu PERSONNALISÉ pour le service {$serviceName}
- ADAPTEZ les prestations selon le type de service (toiture, façade, isolation, etc.)
- GARDEZ les classes CSS et la structure
- UTILISEZ les informations de l'entreprise et de la localisation
- Le contenu doit être professionnel et engageant
- ÉVITEZ la répétition de phrases identiques
- Variez le vocabulaire et les formulations
- INCLUEZ des informations sur le financement et les garanties
- ADAPTEZ le contenu selon le service spécifique

Répondez UNIQUEMENT avec le JSON valide, sans texte avant ou après.";

echo "\n=== Prompt généré ===\n";
echo "Longueur du prompt: " . strlen($prompt) . " caractères\n";
echo "Aperçu du prompt:\n";
echo substr($prompt, 0, 500) . "...\n";

echo "\n=== Fin du test ===\n";
