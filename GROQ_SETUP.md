# Configuration de l'API Groq pour la génération IA

## 🚀 Installation

### 1. Obtenir une clé API Groq

1. Rendez-vous sur [https://console.groq.com/](https://console.groq.com/)
2. Créez un compte ou connectez-vous
3. Allez dans la section "API Keys"
4. Créez une nouvelle clé API
5. Copiez la clé générée

### 2. Configuration dans le projet

Ajoutez la clé API dans votre fichier `.env` :

```bash
# Configuration Groq pour l'IA
GROQ_API_KEY=gsk_your-actual-api-key-here
```

### 3. Modèles disponibles

Le système utilise par défaut le modèle `llama-3.1-8b-instant` qui est :
- ✅ **Rapide** : Réponse en quelques secondes
- ✅ **Efficace** : Optimisé pour la génération de contenu
- ✅ **Gratuit** : Dans les limites de quota

**Modèles alternatifs disponibles :**
- `llama-3.1-70b-versatile` : Plus puissant, plus lent
- `mixtral-8x7b-32768` : Très rapide, bon pour le contenu court

### 4. Utilisation

1. Allez dans l'admin : `/admin/services`
2. Cliquez sur "Génération IA"
3. Saisissez les noms de services (un par ligne)
4. Cliquez sur "Générer les Services"

### 5. Fonctionnalités

Le système génère automatiquement :
- ✅ **Titre SEO optimisé**
- ✅ **Description courte**
- ✅ **Contenu HTML structuré** (800-1200 mots)
- ✅ **Mots-clés intégrés**
- ✅ **CTA pour la conversion**
- ✅ **Meta descriptions**
- ✅ **Slug unique**

### 6. Exemples de services à générer

```
Rénovation de toiture
Réparation de gouttières
Isolation des combles
Ravalement de façade
Charpente traditionnelle
Couverture en ardoise
Couverture en tuiles
Zinguerie et étanchéité
```

### 7. Personnalisation

Vous pouvez ajouter des instructions personnalisées :
- Ton spécifique
- Mots-clés particuliers
- Structure de contenu
- CTA personnalisés

### 8. Limites et quotas

- **Gratuit** : 14,400 requêtes/jour
- **Rapide** : ~30 requêtes/minute
- **Concurrent** : Jusqu'à 30 requêtes simultanées

### 9. Dépannage

**Erreur "GROQ_API_KEY manquant" :**
- Vérifiez que la clé est bien dans le fichier `.env`
- Redémarrez le serveur après modification

**Erreur de connexion :**
- Vérifiez votre connexion internet
- Vérifiez que la clé API est valide
- Utilisez le bouton "Tester la Connexion"

**Contenu de mauvaise qualité :**
- Ajustez les instructions personnalisées
- Essayez un autre modèle
- Vérifiez que les noms de services sont clairs

### 10. Support

Pour toute question sur l'API Groq :
- Documentation : [https://console.groq.com/docs](https://console.groq.com/docs)
- Support : [https://console.groq.com/support](https://console.groq.com/support)
