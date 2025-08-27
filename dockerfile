# Use PHP 8.3 FPM as base image
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy application code
COPY . .

# Copy nginx configuration
COPY nginx/conf.d/default.conf /etc/nginx/sites-available/default

# Copy supervisor configuration
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Create necessary directories
RUN mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views

# Generate Laravel application key if not exists
RUN php artisan key:generate --no-interaction || true

# Optimize Laravel for production
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port
EXPOSE 80

# Start supervisor to manage both nginx and php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]