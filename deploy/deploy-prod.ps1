# Deploy to Production Script
# Run this from the project root: .\deploy\deploy-prod.ps1

param(
    [string]$Server = "moov@10.80.16.51",
    [string]$ProjectPath = "/path/to/project",  # Update this to actual path on server
    [string]$DBUser = "DB_USER",  # Update with actual DB user
    [string]$DBPass = "PASSWORD"   # Update with actual DB password (or prompt)
)

Write-Host "Starting production deployment..." -ForegroundColor Green

# Step 1: Verify local git status
Write-Host "Checking local git status..." -ForegroundColor Yellow
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "Error: Local git has uncommitted changes. Commit and push first." -ForegroundColor Red
    exit 1
}
$ahead = git rev-list --count origin/main..HEAD
if ($ahead -gt 0) {
    Write-Host "Error: Local branch is ahead of origin/main. Push first." -ForegroundColor Red
    exit 1
}
Write-Host "Git is clean and up to date." -ForegroundColor Green

# Confirm deployment
$confirm = Read-Host "Proceed with deployment to $Server? (y/N)"
if ($confirm -ne 'y') {
    Write-Host "Deployment cancelled." -ForegroundColor Yellow
    exit 0
}

# Function to run SSH command
function Invoke-SSHCommand {
    param([string]$Command)
    ssh $Server $Command
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Error: SSH command failed: $Command" -ForegroundColor Red
        exit 1
    }
}

# Step 2: Take backups
Write-Host "Taking backups on production..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyyMMdd_HHmm"
Invoke-SSHCommand "cd $ProjectPath && TS=$timestamp && mysqldump -u $DBUser -p'$DBPass' moov_merchant > /tmp/moov_merchant_db_${TS}.sql"
Invoke-SSHCommand "cd $ProjectPath && TS=$timestamp && tar -czf /tmp/moov_release_${TS}.tar.gz . --exclude=node_modules --exclude=storage/logs"

# Step 3: Pull latest code
Write-Host "Pulling latest code..." -ForegroundColor Yellow
Invoke-SSHCommand "cd $ProjectPath && git fetch origin && git checkout main && git pull origin main"

# Step 4: Install dependencies
Write-Host "Installing dependencies..." -ForegroundColor Yellow
Invoke-SSHCommand "cd $ProjectPath/backend && composer install --no-dev --optimize-autoloader --no-interaction"

# Step 5: Run migrations and clear caches
Write-Host "Running migrations and clearing caches..." -ForegroundColor Yellow
Invoke-SSHCommand "cd $ProjectPath/backend && php artisan config:cache && php artisan route:cache && php artisan view:clear && php artisan migrate --force"

# Step 6: Build frontend
Write-Host "Building frontend..." -ForegroundColor Yellow
Invoke-SSHCommand "cd $ProjectPath/frontend && npm ci && npm run build && rm -rf ../backend/public/dist && cp -R dist ../backend/public/dist"

# Step 7: Restart services
Write-Host "Restarting services..." -ForegroundColor Yellow
Invoke-SSHCommand "cd $ProjectPath/backend && php artisan cache:clear && php artisan config:cache"
Invoke-SSHCommand "sudo systemctl restart php8.1-fpm"
Invoke-SSHCommand "sudo systemctl restart nginx"
Invoke-SSHCommand "php artisan queue:restart"

# Step 8: Smoke test
Write-Host "Running smoke tests..." -ForegroundColor Yellow
$healthCheck = Invoke-WebRequest -Uri "https://your.production.domain/api/health" -UseBasicParsing -TimeoutSec 10
if ($healthCheck.StatusCode -eq 200) {
    Write-Host "Health check passed." -ForegroundColor Green
} else {
    Write-Host "Warning: Health check failed. Check logs." -ForegroundColor Red
}

Write-Host "Deployment completed successfully!" -ForegroundColor Green
Write-Host "Rollback: If needed, restore DB from /tmp/moov_merchant_db_${timestamp}.sql and git reset --hard <previous-sha>" -ForegroundColor Yellow