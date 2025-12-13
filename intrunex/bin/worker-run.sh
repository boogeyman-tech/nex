#!/bin/bash

# =============================================================================
# Intrunex Worker Setup and Runner Script
# =============================================================================
# This script handles complete environment setup and worker process management
# for the Intrunex Symfony application with React frontend
# =============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        warning "Running as root. Consider running as regular user for better security."
    fi
}

# =============================================================================
# ENVIRONMENT SETUP FUNCTIONS
# =============================================================================

# Update system packages
update_system() {
    log "Updating system packages..."
    sudo apt-get update -y
}

# Install system dependencies
install_system_deps() {
    log "Installing system dependencies..."
    
    # Essential PHP packages
    local php_packages="php php-cli php-common php-curl php-xml php-mbstring php-zip php-sqlite3 php-mysql php-intl php-gd php-bcmath php-imagick"
    
    # Other essential tools
    local other_packages="unzip git curl wget nmap supervisor nginx"
    
    sudo apt-get install -y $php_packages $other_packages
    
    # Install Node.js 18+ and npm if not present
    if ! command -v node &> /dev/null; then
        log "Installing Node.js..."
        curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
        sudo apt-get install -y nodejs
    fi
    
    # Install Yarn if not present
    if ! command -v yarn &> /dev/null; then
        log "Installing Yarn..."
        npm install -g yarn
    fi
    
    log "System dependencies installation completed."
}

# Setup PHP
setup_php() {
    log "Setting up PHP environment..."
    
    # Check PHP version
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
        info "PHP version: $PHP_VERSION"
        
        # Ensure PHP 8.1+ is available
        if [[ $(echo "$PHP_VERSION" | cut -d'.' -f1) -lt 8 || ($(echo "$PHP_VERSION" | cut -d'.' -f1) -eq 8 && $(echo "$PHP_VERSION" | cut -d'.' -f2) -lt 1) ]]; then
            error "PHP 8.1+ is required. Current version: $PHP_VERSION"
            exit 1
        fi
    else
        error "PHP is not installed"
        exit 1
    fi
    
    # Install Composer if not present
    if ! command -v composer &> /dev/null; then
        log "Installing Composer..."
        php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
        php composer-setup.php
        sudo mv composer.phar /usr/local/bin/composer
        php -r "unlink('composer-setup.php');"
        composer self-update
    else
        log "Composer already installed. Version: $(composer --version)"
    fi
    
    log "PHP setup completed."
}

# Setup Symfony CLI
setup_symfony() {
    log "Setting up Symfony CLI..."
    
    # Install Symfony CLI if not present
    if ! command -v symfony &> /dev/null; then
        log "Installing Symfony CLI..."
        curl -sS https://get.symfony.com/cli/installer | bash
        sudo mv ~/.symfony5/bin/symfony /usr/local/bin/symfony || sudo mv ~/.symfony*/bin/symfony /usr/local/bin/symfony
    else
        log "Symfony CLI already installed. Version: $(symfony version)"
    fi
    
    # Install Symfony CA certificates
    symfony server:ca:install || true
    
    log "Symfony CLI setup completed."
}

# =============================================================================
# PROJECT SETUP FUNCTIONS
# =============================================================================

# Setup project dependencies
setup_dependencies() {
    log "Setting up project dependencies..."
    
    cd "$(dirname "$0")/.."
    
    # Install PHP dependencies
    if [ -f "composer.json" ]; then
        log "Installing PHP dependencies with Composer..."
        composer install --no-interaction --prefer-dist --optimize-autoloader
    else
        error "composer.json not found"
        exit 1
    fi
    
    # Install Node.js dependencies
    if [ -f "package.json" ]; then
        log "Installing Node.js dependencies..."
        yarn install --frozen-lockfile || npm ci
    else
        warning "package.json not found, skipping Node.js dependencies"
    fi
    
    log "Dependencies setup completed."
}

# Setup database
setup_database() {
    log "Setting up database..."
    
    # Check if DATABASE_URL is set
    if [ -z "$DATABASE_URL" ]; then
        warning "DATABASE_URL not set. Using default SQLite database."
        export DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
    fi
    
    # Create database if it doesn't exist
    log "Creating database..."
    php bin/console doctrine:database:create --if-not-exists || true
    
    # Run migrations
    log "Running database migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction
    
    # Load fixtures if in development
    if [ "${APP_ENV}" = "dev" ] && [ -f "src/DataFixtures/AppFixtures.php" ]; then
        log "Loading fixtures..."
        php bin/console doctrine:fixtures:load --no-interaction || true
    fi
    
    log "Database setup completed."
}

# Build frontend assets
build_assets() {
    log "Building frontend assets..."
    
    cd "$(dirname "$0")/.."
    
    if [ -f "webpack.config.js" ]; then
        log "Compiling assets with Webpack..."
        yarn build || npm run build
    else
        warning "webpack.config.js not found, skipping asset compilation"
    fi
    
    # Install assets
    if [ -d "public" ]; then
        php bin/console assets:install public --symlink || true
    fi
    
    log "Frontend assets build completed."
}

# =============================================================================
# WORKER FUNCTIONS
# =============================================================================

# Start the messenger worker
start_worker() {
    log "Starting messenger worker..."
    
    cd "$(dirname "$0")/.."
    
    # Set worker environment variables
    export APP_ENV=${APP_ENV:-prod}
    export MESSENGER_TRANSPORT_DSN=${MESSENGER_TRANSPORT_DSN:-async://}
    
    # Start worker with restart logic
    log "Starting worker process with automatic restart on crash..."
    
    while true; do
        # Run worker with memory and time limits
        php bin/console messenger:consume async \
            --memory-limit=128M \
            --time-limit=3600 \
            --transport=async \
            --verbose
        
        EXIT_CODE=$?
        
        if [ $EXIT_CODE -ne 0 ]; then
            error "Worker crashed with exit code $EXIT_CODE. Respawning in 5 seconds..."
            sleep 5
        else
            log "Worker stopped normally."
            break
        fi
    done
}

# Health check
health_check() {
    log "Performing health check..."
    
    cd "$(dirname "$0")/.."
    
    # Check PHP
    if command -v php &> /dev/null; then
        info "✓ PHP is available"
    else
        error "✗ PHP is not available"
        return 1
    fi
    
    # Check Composer
    if command -v composer &> /dev/null; then
        info "✓ Composer is available"
    else
        error "✗ Composer is not available"
        return 1
    fi
    
    # Check Symfony
    if command -v symfony &> /dev/null; then
        info "✓ Symfony CLI is available"
    else
        error "✗ Symfony CLI is not available"
        return 1
    fi
    
    # Check Node.js
    if command -v node &> /dev/null; then
        info "✓ Node.js is available"
    else
        error "✗ Node.js is not available"
        return 1
    fi
    
    # Check project structure
    if [ -f "composer.json" ]; then
        info "✓ Project structure is valid"
    else
        error "✗ Project structure is invalid"
        return 1
    fi
    
    log "Health check passed!"
    return 0
}

# =============================================================================
# MAIN EXECUTION
# =============================================================================

main() {
    log "Starting Intrunex Worker Setup..."
    
    check_root
    
    # Parse command line arguments
    SKIP_SETUP=false
    RUN_HEALTH_CHECK=false
    
    while [[ $# -gt 0 ]]; do
        case $1 in
            --skip-setup)
                SKIP_SETUP=true
                shift
                ;;
            --health-check)
                RUN_HEALTH_CHECK=true
                shift
                ;;
            --help)
                echo "Usage: $0 [OPTIONS]"
                echo "Options:"
                echo "  --skip-setup    Skip environment and dependency setup"
                echo "  --health-check  Run health check only"
                echo "  --help          Show this help message"
                exit 0
                ;;
            *)
                error "Unknown option: $1"
                exit 1
                ;;
        esac
    done
    
    if [ "$RUN_HEALTH_CHECK" = true ]; then
        health_check
        exit $?
    fi
    
    if [ "$SKIP_SETUP" = false ]; then
        # Perform full setup
        update_system
        install_system_deps
        setup_php
        setup_symfony
        setup_dependencies
        setup_database
        build_assets
        
        # Run health check after setup
        if health_check; then
            log "Setup completed successfully!"
        else
            error "Setup completed with warnings. Check the logs above."
        fi
    fi
    
    # Start the worker
    start_worker
}

# Run main function with all arguments
main "$@"
