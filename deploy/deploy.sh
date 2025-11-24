#!/bin/bash

# Deploy script for production server
# Run this on the server after git pull

echo "Starting deployment..."

# Go to project directory (update if different)
cd /home/moov/moov-merchant-form || exit 1

# Git pull
echo "Pulling latest code..."
git pull origin main

# Backend: migrations
echo "Running migrations..."
cd backend
php artisan migrate --force

# Clear cache
echo "Clearing cache..."
php artisan cache:clear
php artisan config:cache

# Frontend: build
echo "Building frontend..."
cd ../frontend
npm ci
npm run build

# Restart services
echo "Restarting services..."
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

echo "Deployment completed!"