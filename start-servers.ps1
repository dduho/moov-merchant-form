# Script PowerShell pour démarrer les serveurs en mode réseau local
Write-Host "========================================"
Write-Host "  DÉMARRAGE DES SERVEURS RÉSEAU LOCAL"
Write-Host "========================================"
Write-Host ""
Write-Host "Votre adresse IP locale : 10.80.3.159"
Write-Host ""

# Démarrage du backend Laravel
Write-Host "Démarrage du backend Laravel..."
Start-Process powershell -ArgumentList "-NoExit", "-Command", "Set-Location 'backend'; php artisan serve --host=0.0.0.0 --port=8000"

# Attendre 3 secondes
Start-Sleep -Seconds 3

# Démarrage du frontend Vite
Write-Host "Démarrage du frontend Vite..."
Start-Process powershell -ArgumentList "-NoExit", "-Command", "Set-Location 'frontend'; npx vite --host 0.0.0.0 --port 3000"

Write-Host ""
Write-Host "========================================"
Write-Host "   SERVEURS EN COURS DE DÉMARRAGE"
Write-Host "========================================"
Write-Host ""
Write-Host "Backend (API) : http://10.80.3.159:8000"
Write-Host "Frontend (App): http://10.80.3.159:3000"
Write-Host ""
Write-Host "Les autres ordinateurs du réseau peuvent"
Write-Host "accéder à l'application via ces URLs."
Write-Host ""
Write-Host "Appuyez sur Entrée pour continuer..."
Read-Host