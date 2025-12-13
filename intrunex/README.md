
# IntruneX Project Setup

IntruneX is a comprehensive cybersecurity asset management and vulnerability detection platform built with Symfony 6.1, React, and modern web technologies.

## Prerequisites

Before you begin, ensure you have the following installed:


### System Requirements
- **Operating System**: Linux (Ubuntu/Debian recommended)
- **PHP 8.1+**: Core application runtime
- **Composer**: PHP dependency manager
- **Node.js 16+ and npm**: Frontend build tools
- **Symfony CLI**: Command-line tool for Symfony development
- **Git**: Version control


### Required PHP Extensions
The following PHP extensions are required and will be installed automatically:
- `php-cli` - Command-line interface
- `php-common` - Common PHP files
- `php-curl` - HTTP client functionality
- `php-xml` - XML processing
- `php-mbstring` - Multi-byte string handling
- `php-zip` - ZIP archive handling
- `php-sqlite3` - SQLite database support
- `php-intl` - Internationalization support
- `php-gd` - Image processing

### Additional Tools
- **Nmap**: Network discovery and security auditing
- **Unzip**: Archive extraction utility
- **Curl**: HTTP client for API calls

## Installation Guide

Follow these steps to get the IntruneX project running:

### 1. System Dependencies Installation

First, update your package manager and install all required system dependencies:

```bash
# Update package list
sudo apt update -y


# Install PHP and extensions
sudo apt install -y php php-cli php-common php-curl php-xml php-mbstring php-zip php-sqlite3 php-intl php-gd unzip git curl

# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"

# Install Symfony CLI
curl -sS https://get.symfony.com/cli/installer | bash
sudo mv ~/.symfony*/bin/symfony /usr/local/bin/symfony

# Install Nmap for network scanning capabilities
sudo apt install -y nmap

# Verify installations
php -v
composer -V
symfony -v
nmap -v
```

### 2. Environment Configuration

Navigate to the project directory and set up environment variables:

```bash
cd intrunex/

# Create local environment file
cp .env .env.local
```


Edit the `.env.local` file and configure the following variables:

```env
# Database Configuration (SQLite)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"

# Messenger Configuration (for async job processing)
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0

# Application Configuration
APP_ENV=dev
APP_SECRET=your_unique_secret_key_here
```


### 3. Database Setup

Create the SQLite database directory and set up the database:

```bash
# Create database directory if it doesn't exist
mkdir -p var

# Create and migrate the SQLite database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 4. Install PHP Dependencies

Install all required PHP packages:

```bash
composer install
```

### 5. Install Node.js Dependencies

Install frontend dependencies:

```bash
npm install
```

This will install the following key packages:
- React 19.2.0 for frontend UI components
- Webpack 5.102.0 for asset bundling
- Babel for JavaScript transpilation
- Three.js for 3D visualizations
- CSS and style loaders

### 6. Build Frontend Assets

Compile and bundle frontend assets:

```bash
npm run build
```

### 7. Database Setup

Run database migrations to create the schema:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Load sample data (optional):

```bash
php bin/console doctrine:fixtures:load
```

### 8. Start the Application

Start the Symfony development server:

```bash
symfony serve -d
```

Alternatively, use PHP's built-in server:

```bash
php -S 127.0.0.1:8000 -t public
```


### 9. Start Background Workers

For processing asynchronous jobs (vulnerability scanning, asset discovery):

```bash
php bin/console messenger:consume async -vv
```

Note: The file `bin/worker-run.sh` contains example commands for setting up the environment and running workers, but you should use the direct Symfony messenger command shown above.

## Project Architecture

### Core Modules
- **Asset Discovery**: Network scanning and asset identification
- **Vulnerability Detection**: Security vulnerability assessment
- **Scan Management**: Job queue and scan orchestration
- **User Management**: Authentication and authorization
- **Dashboard**: Real-time metrics and reporting
- **Audit Logging**: Activity tracking and compliance


### Technology Stack
- **Backend**: Symfony 6.1 (PHP 8.1+)
- **Frontend**: React 19.2.0 with Webpack
- **Database**: SQLite
- **Message Queue**: Symfony Messenger with Doctrine
- **Security**: Symfony Security component
- **Visualization**: Three.js for 3D graphics

## Development Commands

```bash
# Clear cache
php bin/console cache:clear

# Run tests
php bin/phpunit

# Generate code (entities, controllers, etc.)
php bin/console make:entity
php bin/console make:controller

# Asset compilation in development mode
npm run build

# Watch for asset changes
npx webpack --watch

# Start messenger worker
php bin/console messenger:consume async -vv

# Database operations
php bin/console doctrine:schema:update --dump-sql
php bin/console doctrine:migrations:diff
```

## Troubleshooting

### Common Issues

**1. PHP Extension Missing**
```bash
sudo apt install php-[extension-name]
sudo systemctl restart apache2  # or nginx
```


**2. Permission Denied**
```bash
sudo chown -R $USER:$USER .
chmod +x bin/worker-run.sh
```

**3. Database Connection Failed**
- Check SQLite database file permissions in the `var/` directory
- Verify database path in `.env.local` is correct
- Ensure the `var/` directory exists and is writable

**4. Asset Compilation Errors**
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

**5. Symfony CLI Not Found**
```bash
export PATH="$HOME/.symfony5/bin:$PATH"
echo 'export PATH="$HOME/.symfony5/bin:$PATH"' >> ~/.bashrc
```

## Production Deployment

For production deployment, additional steps are required:

1. Set `APP_ENV=prod` in environment variables
2. Configure proper database credentials
3. Set up SSL certificates
4. Configure web server (Nginx/Apache)
5. Set up process supervisor for workers
6. Configure log rotation
7. Set up monitoring and alerting

## Support

For issues and questions:
- Check the Symfony documentation: https://symfony.com/doc
- Review application logs: `var/log/`
- Check system requirements match the prerequisites listed above

## Security Notes

- Change default passwords in production
- Use strong APP_SECRET values
- Regularly update dependencies: `composer update` and `npm update`
- Monitor security advisories for PHP, Symfony, and npm packages
- Configure proper firewall rules for network scanning features
