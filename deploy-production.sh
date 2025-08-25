#!/bin/bash

echo "🚀 Starting production deployment..."

# Clear all Laravel caches
echo "📦 Clearing Laravel caches..."
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Install/update npm dependencies
echo "📦 Installing npm dependencies..."
npm ci --production

# Build production assets
echo "🔨 Building production assets..."
npm run build

# Verify assets were built
echo "✅ Verifying built assets..."
if [ ! -f "public/build/manifest.json" ]; then
    echo "❌ Error: manifest.json not found!"
    exit 1
fi

if [ ! -f "public/build/assets/app-DtCVKgHt.js" ]; then
    echo "❌ Error: JavaScript assets not found!"
    exit 1
fi

if [ ! -f "public/build/assets/app-D-LJ-YKg.css" ]; then
    echo "❌ Error: CSS assets not found!"
    exit 1
fi

# Set proper permissions
echo "🔐 Setting proper permissions..."
chmod -R 755 public/build
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan optimize
php artisan view:cache
php artisan config:cache
php artisan route:cache

echo "🎉 Production deployment completed successfully!"
echo "📁 Built assets are in: public/build/"
echo "🔍 Check the settings page to verify tabs are working"
