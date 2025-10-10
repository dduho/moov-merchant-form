@echo off
echo ========================================
echo   DÉMARRAGE DES SERVEURS RÉSEAU LOCAL
echo ========================================
echo.
echo Votre adresse IP locale : 10.80.3.159
echo.
echo Démarrage du backend Laravel...
start "Backend Laravel" cmd /k "cd /d %~dp0\backend && php artisan serve --host=0.0.0.0 --port=8000"
echo.
echo Attendre 3 secondes avant de démarrer le frontend...
timeout /t 3 /nobreak > nul
echo.
echo Démarrage du frontend Vite...
start "Frontend Vite" cmd /k "cd /d %~dp0\frontend && npx vite --host 0.0.0.0 --port 3000"
echo.
echo ========================================
echo   SERVEURS EN COURS DE DÉMARRAGE
echo ========================================
echo.
echo Backend (API) : http://10.80.3.159:8000
echo Frontend (App): http://10.80.3.159:3000
echo.
echo Les autres ordinateurs du réseau peuvent
echo accéder à l'application via ces URLs.
echo.
echo Appuyez sur une touche pour continuer...
pause > nul