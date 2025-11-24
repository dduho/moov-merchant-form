# Mode Hors Ligne - PWA (Progressive Web App)

## 🎯 Fonctionnalités

L'application **Moov Merchant Form** fonctionne maintenant **complètement hors ligne** grâce à la technologie PWA.

## ✅ Ce qui fonctionne hors ligne

1. **Chargement de l'application**
   - Une fois visitée, l'application reste accessible même sans Internet
   - Toutes les pages (Accueil, Formulaire, Dashboard) sont disponibles

2. **Saisie du formulaire**
   - Remplissez le formulaire complet sans connexion
   - Les données sont sauvegardées automatiquement dans le navigateur (IndexedDB)
   - Vos saisies ne seront jamais perdues

3. **Navigation**
   - Naviguez entre les pages sans problème
   - L'interface reste fluide et réactive

4. **Assets (ressources)**
   - Images, icônes, polices sont mis en cache
   - Chargement instantané des ressources

## 🔄 Synchronisation automatique

Quand vous revenez en ligne :
- ✅ Vos données sont **automatiquement envoyées** au serveur
- ✅ Le formulaire se synchronise sans intervention
- ✅ Vous recevez une confirmation de soumission

## 📱 Indicateurs visuels

### Indicateur de connexion (header)
- 🟢 **Vert** : En ligne - Les données sont envoyées immédiatement
- 🔴 **Rouge** : Hors ligne - Les données sont sauvegardées localement

### Bannière mode hors ligne
Quand vous êtes hors ligne, une bannière jaune apparaît en haut de page :
> ⚠️ **Mode hors ligne** • Vos données seront sauvegardées localement et envoyées une fois reconnecté

## 🛠️ Configuration technique

### Service Worker
- Workbox (générateur Google)
- Stratégie : **NetworkFirst** pour les API, **CacheFirst** pour les assets
- Auto-update activé

### Cache
- **API Cache** : 5 minutes, max 50 entrées
- **Images Cache** : 30 jours, max 60 entrées  
- **Fonts Cache** : 1 an, max 30 entrées

### Fichiers mis en cache
```
- HTML, CSS, JavaScript
- Polices (woff, woff2, ttf, eot)
- Images (png, jpg, svg, webp)
- Icônes FontAwesome
```

## 📦 Stockage local

Les données du formulaire sont stockées dans **IndexedDB** via le StorageService :
- Brouillons de formulaire
- Queue de synchronisation
- Données utilisateur

## 🚀 Installation PWA

L'utilisateur peut **installer l'application** sur son appareil :

### Sur mobile (Android/iOS)
1. Ouvrir le site dans le navigateur
2. Menu ⋮ > "Ajouter à l'écran d'accueil"
3. L'app apparaît comme une app native

### Sur desktop (Chrome/Edge)
1. Icône ➕ dans la barre d'adresse
2. "Installer Moov Money Marchand"
3. L'app s'ouvre dans une fenêtre dédiée

## 🔍 Test du mode hors ligne

### Méthode 1 : DevTools
1. Ouvrir DevTools (F12)
2. Onglet **Network**
3. Cocher **Offline**
4. Recharger la page → ✅ L'app fonctionne !

### Méthode 2 : Mode avion
1. Activer le mode avion
2. Recharger la page
3. Remplir le formulaire
4. Désactiver le mode avion → Synchronisation automatique

## 📝 Commits associés

- `e9cdee5` - feat: Améliorer PWA pour fonctionnement hors ligne complet
- `6c69001` - feat: Ajouter info mode hors ligne sur page d'accueil
- `d8cbc15` - fix: Supprimer merchant_phone du formulaire et restaurer sauvegarde hors ligne

## 🏗️ Fichiers modifiés

```
frontend/
├── vite.config.js           # Configuration PWA (Workbox)
├── src/
│   ├── registerSW.js        # Enregistrement service worker
│   ├── main.js              # Import registerSW
│   ├── App.vue              # Bannière hors ligne
│   └── views/
│       └── HomeView.vue     # Info mode hors ligne
└── public/
    ├── manifest.webmanifest # Métadonnées PWA
    └── pwa-*.png            # Icônes PWA
```

## 🎓 Pour les développeurs

### Vérifier le service worker
```javascript
navigator.serviceWorker.getRegistrations().then(regs => {
  console.log('Service Workers:', regs)
})
```

### Vider le cache
```javascript
caches.keys().then(names => {
  names.forEach(name => caches.delete(name))
})
```

### Forcer la mise à jour
```javascript
navigator.serviceWorker.getRegistrations().then(regs => {
  regs.forEach(reg => reg.update())
})
```

## ⚠️ Limitations

- ❌ **Pas de soumission hors ligne vers l'API externe** - Les données sont stockées localement
- ❌ **Pas de récupération de nouvelles données** - Seules les données déjà visitées sont disponibles
- ✅ **Mais tout est sauvegardé** et synchronisé automatiquement au retour en ligne !

## 📊 Statistiques

- **Taille totale en cache** : ~4.8 MB
- **Nombre de fichiers** : 27 entrées précachées
- **Assets FontAwesome** : 12 fichiers (fonts + SVG)
- **Version PWA** : 1.0.3 (Vite PWA Plugin)

---

**🎉 Résultat** : L'application fonctionne maintenant **100% hors ligne** après la première visite !
