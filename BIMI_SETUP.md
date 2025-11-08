# Configuration BIMI pour Gmail

## 📧 Qu'est-ce que BIMI ?

BIMI (Brand Indicators for Message Identification) est un protocole qui permet d'afficher le logo de votre entreprise à côté de vos emails dans Gmail et d'autres clients de messagerie.

## ✅ Ce qui a été fait

1. **Headers BIMI ajoutés** dans les Mailables (`ContactNotification` et `ContactConfirmation`)
2. **Fichier SVG créé** : `public/logo/logo.svg`
3. **Configuration email corrigée** : Le système utilise maintenant SMTP depuis les settings au lieu de 'log'

## 🔧 Configuration DNS BIMI

Pour que le logo s'affiche dans Gmail, vous devez ajouter un enregistrement DNS TXT.

### 1. Créer le fichier SVG du logo

**Important** : Le logo SVG doit respecter ces contraintes :
- Format SVG 1.1 ou SVG Tiny 1.2
- Taille recommandée : 200x200 pixels (viewBox)
- Pas de scripts JavaScript
- Pas d'animations
- Pas de références externes (images, fonts, etc.)
- Couleurs simples recommandées

Remplacez le contenu de `public/logo/logo.svg` par votre logo SVG réel.

### 2. Vérifier que le logo est accessible

Le logo doit être accessible publiquement via HTTPS :
```
https://normesrenovationbretagne.fr/logo/logo.svg
```

### 3. Ajouter l'enregistrement DNS TXT

Ajoutez un enregistrement DNS TXT pour votre domaine :

**Type** : TXT  
**Nom/Hôte** : `default._bimi.normesrenovationbretagne.fr`  
**Valeur** : 
```
v=BIMI1; l=https://normesrenovationbretagne.fr/logo/logo.svg;
```

**Exemple complet** :
```
default._bimi.normesrenovationbretagne.fr. 3600 IN TXT "v=BIMI1; l=https://normesrenovationbretagne.fr/logo/logo.svg;"
```

### 4. (Optionnel) Certificat VMC pour Gmail

Pour que Gmail affiche le logo, vous devez également configurer un certificat VMC (Verified Mark Certificate). C'est un certificat qui prouve que vous êtes le propriétaire de la marque.

**Sans VMC** : Le logo peut s'afficher dans certains clients de messagerie, mais pas dans Gmail.

**Avec VMC** : Le logo s'affichera dans Gmail.

Pour obtenir un VMC :
1. Contactez un fournisseur de certificats VMC (comme DigiCert, Entrust, etc.)
2. Obtenez le certificat VMC
3. Ajoutez-le à votre enregistrement DNS :

```
v=BIMI1; l=https://normesrenovationbretagne.fr/logo/logo.svg; a=https://normesrenovationbretagne.fr/.well-known/bimi-selector.pem;
```

### 5. Vérifier la configuration

Utilisez un outil de vérification BIMI :
- https://bimigroup.org/bimi-checker/
- https://www.dmarcanalyzer.com/bimi/

## 📝 Notes importantes

1. **Propagation DNS** : Les changements DNS peuvent prendre jusqu'à 48h pour se propager.

2. **Gmail** : Gmail nécessite un certificat VMC pour afficher le logo. Sans VMC, le logo ne s'affichera pas dans Gmail, mais peut s'afficher dans d'autres clients.

3. **Autres clients** : Yahoo, Apple Mail et d'autres clients peuvent afficher le logo sans VMC.

4. **SPF, DKIM, DMARC** : BIMI nécessite que SPF, DKIM et DMARC soient correctement configurés.

## 🔍 Vérification

Après configuration, testez en envoyant un email depuis votre application. Le logo devrait apparaître à côté de l'expéditeur dans les clients de messagerie compatibles.

## 📚 Ressources

- Documentation BIMI : https://bimigroup.org/
- Guide Gmail BIMI : https://support.google.com/a/answer/10684723
- Validateur BIMI : https://bimigroup.org/bimi-checker/

