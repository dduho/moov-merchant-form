# Script de configuration de l'environnement Docker local pour Windows

Write-Host "🚀 Configuration de l'environnement Docker local..." -ForegroundColor Green

# Copier les fichiers .env pour Docker
Write-Host "📄 Copie des fichiers .env..." -ForegroundColor Yellow
Copy-Item "backend\.env.local" "backend\.env" -Force
Copy-Item "frontend\.env.local" "frontend\.env" -Force

# Arrêter les conteneurs existants
Write-Host "🛑 Arrêt des conteneurs existants..." -ForegroundColor Yellow
docker-compose down -v

# Démarrer les conteneurs
Write-Host "🐳 Démarrage des conteneurs Docker..." -ForegroundColor Yellow
docker-compose up -d

# Attendre que la base de données soit prête
Write-Host "⏳ Attente du démarrage de MySQL (30 secondes)..." -ForegroundColor Yellow
Start-Sleep -Seconds 30

# Exécuter les migrations et seeders
Write-Host "🗃️  Exécution des migrations et seeders..." -ForegroundColor Yellow
docker-compose exec -T backend php artisan migrate:fresh --seed --force

Write-Host ""
Write-Host "✅ Configuration terminée !" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 URLs d'accès :" -ForegroundColor Cyan
Write-Host "   - Frontend: http://localhost:3000" -ForegroundColor White
Write-Host "   - Backend API: http://localhost:8000" -ForegroundColor White
Write-Host "   - MailHog: http://localhost:8025" -ForegroundColor White
Write-Host ""
Write-Host "🔐 Identifiants de connexion disponibles :" -ForegroundColor Cyan
Write-Host ""
Write-Host "   Compte Admin Principal:" -ForegroundColor Yellow
Write-Host "   - Username: admin" -ForegroundColor White
Write-Host "   - Password: password" -ForegroundColor White
Write-Host ""
Write-Host "   Compte Flooz Admin:" -ForegroundColor Yellow
Write-Host "   - Username: floozadmin" -ForegroundColor White
Write-Host "   - Password: 1210" -ForegroundColor White
Write-Host ""
Write-Host "   Compte Test Admin:" -ForegroundColor Yellow
Write-Host "   - Username: testadmin" -ForegroundColor White
Write-Host "   - Password: password" -ForegroundColor White
Write-Host ""
Write-Host "   Compte Commercial (test):" -ForegroundColor Yellow
Write-Host "   - Username: commercial" -ForegroundColor White
Write-Host "   - Password: password" -ForegroundColor White
Write-Host ""
Write-Host "📊 Pour voir les logs :" -ForegroundColor Cyan
Write-Host "   docker-compose logs -f" -ForegroundColor White
