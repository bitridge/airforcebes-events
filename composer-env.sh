#!/bin/bash

# Composer Environment Helper Script
# Usage: ./composer-env.sh [local|production] [install|update|require|remove]

set -e

ENV=${1:-local}
ACTION=${2:-install}

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check PHP version
check_php_version() {
    local php_version=$(php -r "echo PHP_VERSION;")
    print_status "Current PHP version: $php_version"
    
    # Check if PHP version meets minimum requirement
    if php -r "exit(version_compare(PHP_VERSION, '8.2.0', '<') ? 1 : 0);"; then
        print_success "PHP version meets minimum requirement (8.2.0+)"
    else
        print_error "PHP version $php_version is below minimum requirement (8.2.0+)"
        exit 1
    fi
}

# Function to setup local environment
setup_local() {
    print_status "Setting up LOCAL environment..."
    
    # Copy local composer config
    if [ -f "composer.local.json" ]; then
        cp composer.local.json composer.json
        print_success "Applied local composer configuration"
    else
        print_warning "composer.local.json not found, using default configuration"
    fi
    
    # Install dependencies with relaxed platform requirements
    print_status "Installing dependencies for local environment..."
    composer install --ignore-platform-reqs
    
    print_success "Local environment setup complete!"
}

# Function to setup production environment
setup_production() {
    print_status "Setting up PRODUCTION environment..."
    
    # Copy production composer config
    if [ -f "composer.production.json" ]; then
        cp composer.production.json composer.json
        print_success "Applied production composer configuration"
    else
        print_warning "composer.production.json not found, using default configuration"
    fi
    
    # Install dependencies with strict platform requirements
    print_status "Installing dependencies for production environment..."
    composer install --no-dev --optimize-autoloader
    
    print_success "Production environment setup complete!"
}

# Function to install dependencies
install_deps() {
    print_status "Installing dependencies for $ENV environment..."
    
    if [ "$ENV" = "local" ]; then
        composer install --ignore-platform-reqs
    else
        composer install --no-dev --optimize-autoloader
    fi
    
    print_success "Dependencies installed successfully!"
}

# Function to update dependencies
update_deps() {
    print_status "Updating dependencies for $ENV environment..."
    
    if [ "$ENV" = "local" ]; then
        composer update --ignore-platform-reqs
    else
        composer update --no-dev --optimize-autoloader
    fi
    
    print_success "Dependencies updated successfully!"
}

# Function to add a package
add_package() {
    local package=${3:-}
    
    if [ -z "$package" ]; then
        print_error "Package name is required for 'require' action"
        echo "Usage: ./composer-env.sh [local|production] require [package-name]"
        exit 1
    fi
    
    print_status "Adding package: $package to $ENV environment..."
    
    if [ "$ENV" = "local" ]; then
        composer require "$package" --ignore-platform-reqs
    else
        composer require "$package" --no-dev --optimize-autoloader
    fi
    
    print_success "Package $package added successfully!"
}

# Function to remove a package
remove_package() {
    local package=${3:-}
    
    if [ -z "$package" ]; then
        print_error "Package name is required for 'remove' action"
        echo "Usage: ./composer-env.sh [local|production] remove [package-name]"
        exit 1
    fi
    
    print_status "Removing package: $package from $ENV environment..."
    
    if [ "$ENV" = "local" ]; then
        composer remove "$package" --ignore-platform-reqs
    else
        composer remove "$package" --no-dev --optimize-autoloader
    fi
    
    print_success "Package $package removed successfully!"
}

# Function to show help
show_help() {
    echo "Composer Environment Helper Script"
    echo ""
    echo "Usage: ./composer-env.sh [local|production] [install|update|require|remove] [package-name]"
    echo ""
    echo "Environments:"
    echo "  local       - Local development environment (relaxed platform requirements)"
    echo "  production  - Production environment (strict platform requirements)"
    echo ""
    echo "Actions:"
    echo "  install     - Install dependencies"
    echo "  update      - Update dependencies"
    echo "  require     - Add a package (requires package name)"
    echo "  remove      - Remove a package (requires package name)"
    echo ""
    echo "Examples:"
    echo "  ./composer-env.sh local install"
    echo "  ./composer-env.sh production update"
    echo "  ./composer-env.sh local require laravel/sanctum"
    echo "  ./composer-env.sh production remove fakerphp/faker"
    echo ""
    echo "Environment-specific configurations:"
    echo "  - composer.local.json      - Local development settings"
    echo "  - composer.production.json - Production settings"
    echo "  - .composerrc              - Global composer settings"
}

# Main script logic
main() {
    print_status "Composer Environment Helper Script"
    print_status "Environment: $ENV"
    print_status "Action: $ACTION"
    echo ""
    
    # Check PHP version first
    check_php_version
    echo ""
    
    case "$ACTION" in
        "install")
            install_deps
            ;;
        "update")
            update_deps
            ;;
        "require")
            add_package "$@"
            ;;
        "remove")
            remove_package "$@"
            ;;
        "setup")
            if [ "$ENV" = "local" ]; then
                setup_local
            elif [ "$ENV" = "production" ]; then
                setup_production
            else
                print_error "Invalid environment: $ENV"
                exit 1
            fi
            ;;
        "help"|"--help"|"-h")
            show_help
            exit 0
            ;;
        *)
            print_error "Invalid action: $ACTION"
            echo ""
            show_help
            exit 1
            ;;
    esac
    
    # Run post-install/update commands if available
    if [ -f "composer.json" ] && [ "$ACTION" = "install" ] || [ "$ACTION" = "update" ]; then
        if composer run-script --list | grep -q "post-$ACTION-cmd"; then
            print_status "Running post-$ACTION commands..."
            composer run-script "post-$ACTION-cmd"
        fi
    fi
    
    print_success "Operation completed successfully!"
}

# Check if composer is available
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed or not in PATH"
    exit 1
fi

# Run main function with all arguments
main "$@"
