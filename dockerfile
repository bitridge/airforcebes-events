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

# Copy application code first (including app/helpers.php)
COPY . .

# Create nginx configuration directory and copy config
RUN mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
COPY nginx/conf.d/default.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copy supervisor configuration
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Create supervisor log directories
RUN mkdir -p /var/log/supervisor

# Install PHP dependencies (after copying code so app/helpers.php is available)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Create necessary directories
RUN mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views

# Generate Laravel application key if not exists (skip if no .env)
RUN if [ -f .env ]; then php artisan key:generate --no-interaction || true; fi

# Optimize Laravel for production (skip if no .env)
RUN if [ -f .env ]; then \
        php artisan config:cache || true; \
        php artisan route:cache || true; \
        php artisan view:cache || true; \
    fi

# Expose port
EXPOSE 80

# Start supervisor to manage both nginx and php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]