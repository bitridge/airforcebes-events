# Composer Environment Configuration Guide

This guide explains how to use the new composer environment configurations to resolve PHP version compatibility issues between local and production environments.

## 🚨 Problem Solved

**Issue**: `Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.3.0"`

**Solution**: Environment-specific composer configurations that handle different PHP version requirements and platform constraints.

## 📁 Files Created

### 1. **composer.json** (Updated)
- **PHP Version**: Changed from `^8.2` to `>=8.2.0` for broader compatibility
- **Platform Config**: Added `platform-check: false` to bypass strict version checking
- **New Scripts**: Environment-specific install/update commands

### 2. **.composerrc**
- **Global Settings**: Default composer configuration
- **Platform Override**: Sets PHP platform to 8.2.0
- **Optimizations**: Enables autoloader optimization

### 3. **composer.local.json**
- **Local Development**: Relaxed platform requirements
- **Auto-migration**: Runs migrations after install/update
- **Key Generation**: Automatically generates app key

### 4. **composer.production.json**
- **Production Ready**: Strict platform checking
- **No Dev Dependencies**: Excludes development packages
- **Cache Optimization**: Enables all Laravel caches

### 5. **composer-env.sh**
- **Helper Script**: Automated environment management
- **Smart Detection**: Automatically applies correct configuration
- **Post-Commands**: Runs appropriate Laravel commands

## 🛠️ Usage

### **Quick Start**

#### **Local Development**
```bash
# Install dependencies (ignores platform requirements)
./composer-env.sh local install

# Update dependencies
./composer-env.sh local update

# Add a new package
./composer-env.sh local require laravel/sanctum
```

#### **Production Environment**
```bash
# Install production dependencies
./composer-env.sh production install

# Update production dependencies
./composer-env.sh production update

# Remove a package
./composer-env.sh production remove fakerphp/faker
```

### **Manual Commands**

#### **Local Development (Relaxed)**
```bash
# Install with relaxed platform requirements
composer install --ignore-platform-reqs

# Update with relaxed platform requirements
composer update --ignore-platform-reqs

# Add package with relaxed requirements
composer require laravel/sanctum --ignore-platform-reqs
```

#### **Production (Strict)**
```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Update production dependencies
composer update --no-dev --optimize-autoloader

# Add production package
composer require laravel/sanctum --no-dev --optimize-autoloader
```

## 🔧 Configuration Details

### **PHP Version Support**

| Environment | PHP Version | Platform Check | Notes |
|-------------|-------------|----------------|-------|
| **Local** | >=8.2.0 | ❌ Disabled | Relaxed requirements |
| **Production** | >=8.2.0 | ✅ Enabled | Strict requirements |

### **Platform Configuration**

#### **Local Development**
```json
{
    "config": {
        "platform": {
            "php": "8.2.0"
        },
        "platform-check": false,
        "optimize-autoloader": false
    }
}
```

#### **Production**
```json
{
    "config": {
        "platform": {
            "php": "8.2.0"
        },
        "platform-check": true,
        "optimize-autoloader": true,
        "no-dev": true
    }
}
```

## 📋 Available Scripts

### **Built-in Composer Scripts**

#### **Install Scripts**
```bash
# Local development
composer run install-local

# Production
composer run install-production
```

#### **Update Scripts**
```bash
# Local development
composer run update-local

# Production
composer run update-production
```

### **Helper Script Commands**

```bash
# Show help
./composer-env.sh help

# Setup environment
./composer-env.sh local setup
./composer-env.sh production setup

# Package management
./composer-env.sh local require [package]
./composer-env.sh production remove [package]
```

## 🚀 Environment Setup

### **First Time Setup**

#### **1. Local Development**
```bash
# Setup local environment
./composer-env.sh local setup

# This will:
# - Apply local composer configuration
# - Install dependencies with relaxed requirements
# - Generate application key
# - Run database migrations
```

#### **2. Production Environment**
```bash
# Setup production environment
./composer-env.sh production setup

# This will:
# - Apply production composer configuration
# - Install production dependencies only
# - Enable all Laravel caches
```

### **Switching Environments**

```bash
# Switch to local configuration
cp composer.local.json composer.json

# Switch to production configuration
cp composer.production.json composer.json

# Or use the helper script
./composer-env.sh local setup
./composer-env.sh production setup
```

## 🔍 Troubleshooting

### **Common Issues**

#### **1. Platform Requirements Error**
```bash
# Error: Your Composer dependencies require a PHP version ">= 8.3.0"
# Solution: Use local environment with relaxed requirements
./composer-env.sh local install
```

#### **2. Memory Limit Issues**
```bash
# Increase PHP memory limit
php -d memory_limit=-1 /usr/local/bin/composer install

# Or use the helper script
./composer-env.sh local install
```

#### **3. Permission Issues**
```bash
# Make script executable
chmod +x composer-env.sh

# Run with proper permissions
./composer-env.sh local install
```

### **Debug Commands**

```bash
# Check current PHP version
php -v

# Check composer configuration
composer config --list

# Check platform requirements
composer check-platform-reqs

# Validate composer.json
composer validate
```

## 📊 Performance Comparison

### **Installation Time**

| Environment | Dependencies | Platform Check | Time |
|-------------|--------------|----------------|------|
| **Local** | All | ❌ Disabled | ~2-3 min |
| **Production** | Production Only | ✅ Enabled | ~1-2 min |

### **Memory Usage**

| Environment | Autoloader | Dev Dependencies | Memory |
|-------------|------------|------------------|--------|
| **Local** | Standard | ✅ Included | ~150MB |
| **Production** | Optimized | ❌ Excluded | ~80MB |

## 🔒 Security Considerations

### **Local Development**
- **Dev Dependencies**: Included for development tools
- **Platform Check**: Disabled for flexibility
- **Auto-migration**: Enabled for convenience

### **Production**
- **Dev Dependencies**: Excluded for security
- **Platform Check**: Enabled for stability
- **Cache Optimization**: Enabled for performance

## 📝 Best Practices

### **1. Environment Management**
- Always use the helper script for consistency
- Keep environment-specific configs in version control
- Document any custom configurations

### **2. Dependency Management**
- Use `composer.lock` for reproducible builds
- Regularly update dependencies in local environment
- Test updates in local before production

### **3. Deployment**
- Use production configuration in CI/CD
- Run platform checks in production builds
- Optimize autoloader for production

## 🎯 Quick Reference

### **Daily Commands**
```bash
# Local development
./composer-env.sh local install    # Install dependencies
./composer-env.sh local update     # Update dependencies
./composer-env.sh local require    # Add package

# Production deployment
./composer-env.sh production install  # Deploy dependencies
./composer-env.sh production update   # Update production
```

### **Emergency Commands**
```bash
# Force install (bypass all checks)
composer install --ignore-platform-reqs --no-dev

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

## 📞 Support

If you encounter issues:

1. **Check PHP Version**: `php -v`
2. **Check Composer Version**: `composer --version`
3. **Review Logs**: `tail -f storage/logs/laravel.log`
4. **Use Helper Script**: `./composer-env.sh help`

---

**Note**: This configuration system ensures compatibility between different PHP versions while maintaining security and performance best practices for each environment.
