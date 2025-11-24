# 🐳 Guide Complet - Docker Local

## 🚀 Démarrage Ultra-Rapide (2 minutes)

```powershell
# Windows PowerShell
.\setup-local.ps1
```

```bash
# Linux/Mac
chmod +x setup-local.sh
./setup-local.sh
```

Puis ouvrez http://localhost:3000 et connectez-vous avec :
- **Username** : `admin`
- **Password** : `password`

**C'est tout ! 🎉**

---

## 📚 Documentation complète

### Guides disponibles

1. **[DEMARRAGE-RAPIDE.md](DEMARRAGE-RAPIDE.md)** - Guide de démarrage pas à pas
2. **[CREDENTIALS.md](CREDENTIALS.md)** - Tous les identifiants de connexion
3. **[SETUP-LOCAL-DOCKER.md](SETUP-LOCAL-DOCKER.md)** - Documentation technique détaillée

### Fichiers de configuration

- **`backend/.env.local`** - Configuration backend pour Docker
- **`frontend/.env.local`** - Configuration frontend pour Docker
- **`setup-local.ps1`** - Script automatique Windows
- **`setup-local.sh`** - Script automatique Linux/Mac
- **`docker-compose.yml`** - Configuration des conteneurs

---

## 🌐 URLs d'accès

Une fois Docker démarré :

| Service | URL | Description |
|---------|-----|-------------|
| Frontend | http://localhost:3000 | Application Vue.js |
| Backend API | http://localhost:8000 | API Laravel |
| MailHog | http://localhost:8025 | Interface emails de test |
| MySQL | localhost:3306 | Base de données |
| Redis | localhost:6379 | Cache |

---

## 🔐 Comptes disponibles

### Admin Principal (Recommandé)
```
Username: admin
Password: password
```

### Autres comptes

Voir [CREDENTIALS.md](CREDENTIALS.md) pour la liste complète.

---

## 📊 Commandes Docker utiles

### Démarrer
```bash
docker-compose up -d
```

### Arrêter
```bash
docker-compose down
```

### Voir les logs
```bash
docker-compose logs -f
```

### Logs d'un service spécifique
```bash
docker-compose logs -f backend
docker-compose logs -f frontend
```

### Redémarrer
```bash
docker-compose restart
```

### Réinitialiser complètement
```bash
docker-compose down -v
.\setup-local.ps1
```

### Entrer dans un conteneur
```bash
docker-compose exec backend sh
docker-compose exec frontend sh
```

---

## 🔧 Résolution de problèmes

### Erreur : "Les identifiants fournis sont incorrects"

➡️ Exécutez : `.\setup-local.ps1`

### Le backend ne démarre pas

➡️ Vérifiez que MySQL est bien démarré :
```bash
docker-compose logs database | grep "ready for connections"
```

### Le frontend ne se connecte pas au backend

➡️ Vérifiez que `frontend/.env` contient :
```
VITE_API_URL=http://localhost:8000
```

### Erreur de base de données

➡️ Réinitialisez tout :
```bash
docker-compose down -v
.\setup-local.ps1
```

### Port déjà utilisé

Si un port est déjà occupé, modifiez `docker-compose.yml` :
```yaml
ports:
  - "3001:3000"  # Frontend sur 3001 au lieu de 3000
```

---

## ⚠️ Important

- ✅ Ces fichiers sont **uniquement pour le développement local**
- ✅ Les fichiers `.env.local` sont **ignorés par Git**
- ✅ La configuration de **production reste inchangée**
- ❌ Ne **JAMAIS** utiliser ces mots de passe en production

---

## 🎯 Ce qui a été configuré

Le script `setup-local.ps1` fait automatiquement :

1. ✅ Copie les fichiers `.env.local` → `.env`
2. ✅ Démarre tous les conteneurs Docker
3. ✅ Attend que MySQL soit prêt (30 secondes)
4. ✅ Exécute les migrations de base de données
5. ✅ Crée 4 utilisateurs de test (3 admins + 1 commercial)
6. ✅ Affiche tous les credentials disponibles

---

## 📝 Structure Docker

```
moov-merchant-form/
├── frontend/          # Application Vue.js
│   ├── Dockerfile
│   └── .env.local     # Config Docker
├── backend/           # API Laravel
│   ├── Dockerfile
│   ├── .env.local     # Config Docker
│   └── check-users.php # Script de création utilisateurs
├── docker-compose.yml # Orchestration
├── setup-local.ps1    # Script Windows
└── setup-local.sh     # Script Linux/Mac
```

---

## 🚀 Prochaines étapes

1. Connectez-vous avec `admin` / `password`
2. Explorez l'application
3. Créez des candidatures de test
4. Testez les exports SP
5. Vérifiez les emails dans MailHog (http://localhost:8025)

---

## 📧 Support

Pour toute question ou problème :
1. Vérifiez [DEMARRAGE-RAPIDE.md](DEMARRAGE-RAPIDE.md)
2. Consultez [SETUP-LOCAL-DOCKER.md](SETUP-LOCAL-DOCKER.md)
3. Relancez `.\setup-local.ps1` pour réinitialiser

**Bon développement ! 🎉**
