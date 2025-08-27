# Use PHP 8.3 FPM as base image
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    nginx \
    supervisor \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 20.x and npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . .

# Create all necessary directories BEFORE composer install
RUN mkdir -p /var/www/storage/logs \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /etc/nginx/sites-available \
    && mkdir -p /etc/nginx/sites-enabled

# Set proper permissions BEFORE composer install
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Create a temporary .env file for the build process with properly escaped values
RUN echo 'APP_NAME="Meet Ups Pro"' > .env \
    && echo 'APP_ENV=local' >> .env \
    && echo 'APP_DEBUG=true' >> .env \
    && echo 'APP_KEY=base64:tempkeyforbuildonly' >> .env \
    && echo 'APP_URL=http://localhost' >> .env \
    && echo 'DB_CONNECTION=sqlite' >> .env \
    && echo 'DB_DATABASE=:memory:' >> .env

# Copy configurations
COPY nginx/conf.d/default.conf /etc/nginx/sites-available/default
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Configure nginx
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Clean npm cache and install dependencies fresh, then build frontend assets
RUN rm -rf node_modules package-lock.json \
    && npm cache clean --force \
    && npm install \
    && npm run build

# Remove temporary .env file
RUN rm -f .env

# Expose port
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]