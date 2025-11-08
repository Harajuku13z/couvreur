# 📍 Où placer le logo pour BIMI

## Emplacement du logo

Le logo doit être placé dans le dossier suivant :
```
public/logo/logo.svg
```

## Format requis

Le logo doit être au format **SVG** et respecter ces contraintes pour BIMI :

### ✅ Contraintes BIMI
- **Format** : SVG 1.1 ou SVG Tiny 1.2
- **Taille recommandée** : 200x200 pixels (viewBox)
- **Pas de scripts JavaScript**
- **Pas d'animations**
- **Pas de références externes** (images, fonts, etc.)
- **Couleurs simples recommandées**

### 📝 Exemple de structure SVG valide

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
  <!-- Votre logo ici -->
  <rect width="200" height="200" fill="#ffffff"/>
  <!-- Éléments de votre logo -->
</svg>
```

## Remplacement du logo actuel

1. **Ouvrez le fichier** : `public/logo/logo.svg`
2. **Remplacez le contenu** par votre logo SVG réel
3. **Vérifiez** que le logo est accessible via : `https://normesrenovationbretagne.fr/logo/logo.svg`

## Vérification

Pour vérifier que le logo est accessible :
1. Ouvrez dans votre navigateur : `https://normesrenovationbretagne.fr/logo/logo.svg`
2. Le logo doit s'afficher correctement

## Note importante

Le fichier actuel `public/logo/logo.svg` contient un logo de placeholder. Vous devez le remplacer par votre logo SVG réel pour que BIMI fonctionne correctement.

## Alternative PNG

Si vous n'avez pas de logo SVG, vous pouvez utiliser le PNG existant (`public/logo/logo.png`), mais BIMI nécessite un SVG pour fonctionner correctement. Le système utilisera automatiquement le PNG si le SVG n'existe pas, mais le logo ne s'affichera pas dans Gmail sans SVG + DNS BIMI configuré.

