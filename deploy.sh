#!/usr/bin/env bash
set -e

# Deploy script for VPS
# Usage: ./deploy.sh

echo "🚀 Starting deployment..."

# Pull latest code
echo "📥 Pulling latest code from main..."
git pull origin main

# Build images
echo "🔨 Building Docker images..."
docker-compose build --no-cache

# Stop old containers
echo "🛑 Stopping old containers..."
docker-compose down

# Start new containers
echo "▶️ Starting containers..."
docker-compose up -d

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec -T backend php artisan migrate --force

# Run seeders (optional - uncomment if needed)
# echo "🌱 Running seeders..."
# docker-compose exec -T backend php artisan db:seed --force

# Clear caches
echo "🧹 Clearing caches..."
docker-compose exec -T backend php artisan config:cache
docker-compose exec -T backend php artisan route:cache
docker-compose exec -T backend php artisan view:cache

echo "✅ Deployment completed successfully!"
