#!/bin/bash

# AirforceBES Events Deployment Script
# This script helps prepare the application for deployment

echo "🚀 Starting AirforceBES Events deployment preparation..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: This doesn't appear to be a Laravel project directory"
    exit 1
fi

# Create necessary directories if they don't exist
echo "📁 Creating necessary directories..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions
echo "🔐 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Clear and cache Laravel configurations
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ Caching Laravel configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate application key if not exists
if [ -z "$(grep '^APP_KEY=' .env | cut -d '=' -f2)" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --no-interaction
fi

# Build frontend assets if needed
if [ -f "package.json" ]; then
    echo "🎨 Building frontend assets..."
    npm ci --only=production
    npm run build
fi

echo "✅ Deployment preparation completed!"
echo ""
echo "📋 Next steps:"
echo "1. Ensure your .env file has all required production variables"
echo "2. Set up your database and update DB_* variables"
echo "3. Configure your mail settings"
echo "4. Deploy using Docker or your preferred method"
echo ""
echo "🐳 For Docker deployment:"
echo "   docker build -t airforcebes-events ."
echo "   docker run -p 80:80 --env-file .env airforcebes-events"
