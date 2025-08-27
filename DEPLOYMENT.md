# 🚀 AirforceBES Events - Deployment Guide

## 📋 Prerequisites

- Docker and Docker Compose installed
- MySQL/MariaDB database server
- SMTP server for email functionality
- Domain name (optional but recommended)

## 🐳 Docker Deployment

### 1. Environment Setup

Copy the environment template and configure it:

```bash
cp env.production.example .env
```

Edit `.env` with your production values:

```env
APP_NAME="AirforceBES Events"
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_smtp_user
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="AirforceBES Events"
```

### 2. Generate Application Key

```bash
php artisan key:generate
```

### 3. Build and Run

```bash
# Build the Docker image
docker build -t airforcebes-events .

# Run the container
docker run -d \
  --name airforcebes-events \
  -p 80:80 \
  --env-file .env \
  airforcebes-events
```

### 4. Using Docker Compose

```bash
docker-compose up -d
```

## 🌐 Coolify Deployment

### 1. Repository Setup

Ensure your repository contains:
- `Dockerfile`
- `coolify.yaml`
- `.dockerignore`
- `nginx/conf.d/default.conf`
- `docker/supervisor.conf`

### 2. Environment Variables

In Coolify, set these environment variables:
- `APP_KEY`: Your Laravel application key
- `APP_URL`: Your application URL
- `DB_HOST`: Database host
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password
- `MAIL_HOST`: SMTP host
- `MAIL_USERNAME`: SMTP username
- `MAIL_PASSWORD`: SMTP password

### 3. Deploy

1. Connect your repository to Coolify
2. Set the environment variables
3. Deploy the application

## 🔧 Manual Deployment

### 1. Prepare the Application

```bash
# Run the deployment script
./deploy.sh
```

### 2. Set Permissions

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

### 3. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm ci --only=production
npm run build
```

### 4. Laravel Setup

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

## 📊 Health Checks

The application includes a health check endpoint at `/health` that returns:
- Status: 200 OK
- Response: "healthy"

This is used by Docker for container health monitoring.

## 🚨 Troubleshooting

### Common Issues

1. **Permission Denied Errors**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

2. **Database Connection Issues**
   - Verify database credentials in `.env`
   - Ensure database server is accessible
   - Check firewall settings

3. **Storage Directory Issues**
   ```bash
   mkdir -p storage/framework/{cache,sessions,views}
   mkdir -p storage/logs
   ```

4. **Cache Issues**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   ```

### Logs

Check application logs:
```bash
tail -f storage/logs/laravel.log
```

Check Docker logs:
```bash
docker logs airforcebes-events
```

## 🔒 Security Considerations

1. **Environment Variables**: Never commit `.env` files
2. **File Permissions**: Restrict access to sensitive directories
3. **HTTPS**: Use SSL certificates in production
4. **Database**: Use strong passwords and restrict access
5. **Updates**: Keep dependencies updated

## 📈 Performance Optimization

1. **Caching**: Laravel caches are enabled by default
2. **Database**: Ensure proper indexing
3. **Static Files**: Nginx handles static file serving
4. **CDN**: Consider using a CDN for static assets

## 🆘 Support

For deployment issues:
1. Check the logs
2. Verify environment variables
3. Ensure all prerequisites are met
4. Check Docker and system resources
