<?php

echo "📝 Comparaison des prompts de génération d'articles\n";
echo "==================================================\n\n";

echo "🔴 ANCIEN PROMPT (basique) :\n";
echo "----------------------------\n";
echo "Crée un article HTML professionnel sur: [TITRE]\n\n";
echo "Instructions:\n";
echo "- Format HTML uniquement\n";
echo "- Commence par <article> et finit par </article>\n";
echo "- 1000-1500 mots\n";
echo "- Structure: introduction, 3 sections, FAQ, conclusion\n";
echo "- Utilise des emojis appropriés\n";
echo "- Inclut des listes\n\n";

echo "🟢 NOUVEAU PROMPT (professionnel) :\n";
echo "----------------------------------\n";
echo "Tu es un rédacteur web professionnel et expert en rénovation de bâtiments (toiture, isolation, plomberie, électricité, façade, etc.) et SEO.\n";
echo "À partir du titre fourni, rédige un article complet, structuré et optimisé SEO, sous format HTML prêt à publier, en utilisant Tailwind CSS pour que l'article soit agréable à lire.\n\n";

echo "Structure à respecter précisément :\n";
echo "• Container principal : max-w-7xl mx-auto px-4 sm:px-6 lg:px-8\n";
echo "• Titre principal (h1) : text-4xl font-bold text-gray-900 mb-6 text-center\n";
echo "• Sous-titres (h2) : text-2xl font-semibold text-gray-800 my-4\n";
echo "• Sections (div) : bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\n";
echo "• Paragraphes (p) : text-gray-700 text-base leading-relaxed mb-4\n";
echo "• Listes à puces (ul > li) : list-disc list-inside text-gray-700 mb-2\n";
echo "• Icônes / emojis : ajouter avant le texte ou dans les titres pour illustrer certaines sections\n";
echo "• FAQ : bg-green-50 p-4 rounded-lg mb-4, questions en gras et réponses normales\n";
echo "• Call-to-action : bouton bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300\n\n";

echo "Contenu à générer :\n";
echo "• Article original, informatif, entre 1 000 et 2 000 mots\n";
echo "• Introduction engageante\n";
echo "• Sections explicatives détaillées avec sous-titres et paragraphes\n";
echo "• Conseils pratiques pour les propriétaires ou professionnels\n";
echo "• FAQ pertinente sur le sujet\n";
echo "• Conclusion avec appel à l'action pour contacter l'entreprise ou découvrir ses services\n\n";

echo "SEO et mots-clés :\n";
echo "• Intégrer naturellement des mots-clés liés à la rénovation, toiture, façade, isolation, plomberie, électricité, énergie, maison, entretien, travaux…\n";
echo "• Optimiser les titres et sous-titres pour le référencement\n\n";

echo "Important :\n";
echo "• Générer directement un fichier HTML complet et propre\n";
echo "• Ne pas afficher le code HTML comme texte brut, mais un HTML prêt à publier\n";
echo "• Ajouter des icônes et emojis pour rendre la lecture agréable et visuelle\n\n";

echo "🎯 AVANTAGES DU NOUVEAU PROMPT :\n";
echo "================================\n";
echo "✅ Structure HTML avec Tailwind CSS pour un rendu professionnel\n";
echo "✅ Instructions détaillées pour chaque élément (titres, sections, FAQ, CTA)\n";
echo "✅ Optimisation SEO intégrée\n";
echo "✅ Emojis et icônes pour améliorer la lisibilité\n";
echo "✅ Call-to-action pour convertir les visiteurs\n";
echo "✅ Contenu de 1000-2000 mots pour un meilleur référencement\n";
echo "✅ Structure cohérente et professionnelle\n";
echo "✅ Focus sur la rénovation et les services de l'entreprise\n\n";

echo "📊 RÉSULTAT ATTENDU :\n";
echo "=====================\n";
echo "Les articles générés avec le nouveau prompt auront :\n";
echo "• Un design professionnel avec Tailwind CSS\n";
echo "• Une structure claire et engageante\n";
echo "• Un contenu optimisé pour le SEO\n";
echo "• Des éléments visuels (emojis, icônes)\n";
echo "• Des appels à l'action pour générer des leads\n";
echo "• Une qualité rédactionnelle supérieure\n\n";

echo "🚀 PROCHAINES ÉTAPES :\n";
echo "=====================\n";
echo "1. Configurez votre clé API OpenAI dans /admin/config\n";
echo "2. Testez la génération d'articles avec le nouveau prompt\n";
echo "3. Vérifiez la qualité et la structure des articles générés\n";
echo "4. Ajustez le prompt si nécessaire selon vos besoins\n\n";

echo "✨ Le nouveau prompt est maintenant actif et prêt à générer des articles de qualité professionnelle !\n";
