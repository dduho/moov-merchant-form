#!/bin/bash

echo "🚀 Configuration de l'environnement Docker local..."

# Copier les fichiers .env pour Docker
echo "📄 Copie des fichiers .env..."
cp backend/.env.local backend/.env
cp frontend/.env.local frontend/.env

# Arrêter les conteneurs existants
echo "🛑 Arrêt des conteneurs existants..."
docker-compose down -v

# Démarrer les conteneurs
echo "🐳 Démarrage des conteneurs Docker..."
docker-compose up -d

# Attendre que la base de données soit prête
echo "⏳ Attente du démarrage de MySQL..."
sleep 30

# Exécuter les migrations et seeders
echo "🗃️  Exécution des migrations et seeders..."
docker-compose exec -T backend php artisan migrate:fresh --seed --force

echo ""
echo "✅ Configuration terminée !"
echo ""
echo "🌐 URLs d'accès :"
echo "   - Frontend: http://localhost:3000"
echo "   - Backend API: http://localhost:8000"
echo "   - MailHog: http://localhost:8025"
echo ""
echo "🔐 Identifiants de connexion disponibles :"
echo ""
echo "   Compte Admin Principal:"
echo "   - Username: admin"
echo "   - Password: password"
echo ""
echo "   Compte Flooz Admin:"
echo "   - Username: floozadmin"
echo "   - Password: 1210"
echo ""
echo "   Compte Test Admin:"
echo "   - Username: testadmin"
echo "   - Password: password"
echo ""
echo "   Compte Commercial (test):"
echo "   - Username: commercial"
echo "   - Password: password"
echo ""
echo "📊 Pour voir les logs :"
echo "   docker-compose logs -f"
