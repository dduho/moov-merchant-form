# Simple Deploy Script
# Update the variables below with actual values

$Server = "moov@10.80.16.51"
$ProjectPath = "/home/moov/moov-merchant-form"  # Replace with actual path

# Git pull
ssh $Server "cd $ProjectPath && git pull origin main"

# Migrations (if needed)
ssh $Server "cd $ProjectPath/backend && php artisan migrate --force"

# Clear cache
ssh $Server "cd $ProjectPath/backend && php artisan cache:clear && php artisan config:cache"

# Build frontend
ssh $Server "cd $ProjectPath/frontend && npm ci && npm run build"

# Restart services
ssh $Server "sudo systemctl restart php8.1-fpm && sudo systemctl restart nginx"