INTRUNEX

IntruNex is an advanced web vulnerability scanning and management system developed using the Symfony Framework within GitHub Codespaces. It enables users to securely add and profile assets such as websites, web applications, and IP addresses, then perform automated vulnerability scans to detect potential security weaknesses. The platform generates detailed scan reports, highlights identified issues, and provides actionable insights to help users strengthen their asset security. By continuously monitoring and analyzing vulnerabilities, IntruNex empowers developers, security analysts, and organizations to proactively safeguard their digital infrastructure and maintain a robust security posture.


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
- `php-sqlite3` - SQLite database support (default)
- `php-intl` - Internationalization support
- `php-gd` - Image processing

**Note**: The database-specific extension (php-sqlite3, php-mysql, php-pgsql, etc.) depends on your chosen database.

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



### 3. Database Configuration

IntruneX uses SQLite by default for simplicity, but you can easily configure it to use other database systems. The application is compatible with MySQL, PostgreSQL, and other Doctrine-supported databases.

#### 3.1 SQLite (Default)

SQLite is perfect for development and small deployments:

```bash
# Create database directory if it doesn't exist
mkdir -p var

# Create and migrate the SQLite database
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

**Configuration in `.env.local`:**
```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

#### 3.2 MySQL/MariaDB

For production environments or when you need more robust database features:

**Install MySQL extension:**
```bash
sudo apt install -y php-mysql
```

**Configuration in `.env.local`:**
```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/intrunex_db?serverVersion=8.0&charset=utf8mb4"
```

**Setup MySQL database:**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE intrunex_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Create user and grant privileges
mysql -u root -p -e "CREATE USER 'intrunex_user'@'localhost' IDENTIFIED BY 'your_secure_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON intrunex_db.* TO 'intrunex_user'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"

# Run migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

#### 3.3 PostgreSQL

For enterprise-grade deployments:

**Install PostgreSQL extension:**
```bash
sudo apt install -y php-pgsql
```

**Configuration in `.env.local`:**
```env
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/intrunex_db?serverVersion=13&charset=utf8"
```

**Setup PostgreSQL database:**
```bash
# Create database
sudo -u postgres psql -c "CREATE DATABASE intrunex_db;"

# Create user and grant privileges
sudo -u postgres psql -c "CREATE USER intrunex_user WITH PASSWORD 'your_secure_password';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE intrunex_db TO intrunex_user;"

# Run migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

#### 3.4 Database Configuration Options

You can also configure additional database options in `config/packages/doctrine.yaml`:

```yaml
doctrine:
    dbal:
        driver: 'pdo_mysql'  # or 'pdo_pgsql', 'pdo_sqlite'
        url: '%env(resolve:DATABASE_URL)%'
        options:
            1002: true  # PDO::SQLITE_ATTR_ENABLE_FKEYS (for SQLite)
        # Additional options for production
        # charset: 'UTF8MB4'
        # server_version: '8.0'
        # logging: '%kernel.debug%'
        # pool_size: 20
        # connect_timeout: 10
        # read_timeout: 10
```

#### 3.5 Switching Databases

To migrate from SQLite to another database:

1. **Backup existing data (if any):**
```bash
php bin/console doctrine:query:sql "SELECT * FROM user" --dump-sql > backup_users.sql
```

2. **Update `.env.local` with new database URL**

3. **Create new database and run migrations:**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4. **Import data if needed:**
```bash
# For MySQL
mysql -u username -p intrunex_db < backup_data.sql

# For PostgreSQL  
psql -U username -d intrunex_db -f backup_data.sql
```

#### 3.6 Database-Specific Extensions

Install the appropriate PHP extension based on your database choice:

- **SQLite**: `php-sqlite3` (already included)
- **MySQL/MariaDB**: `php-mysql` or `php-mysqli`
- **PostgreSQL**: `php-pgsql`

For Ubuntu/Debian systems:
```bash
# MySQL
sudo apt install php-mysql

# PostgreSQL  
sudo apt install php-pgsql

# Verify extension installation
php -m | grep -E "(mysql|pgsql|sqlite)"
```

#### 3.7 Production Database Considerations

For production environments, consider these additional configurations:

1. **Connection Pooling**: Use connection pooling for better performance
2. **Database Tuning**: Optimize database settings for your workload
3. **Backup Strategy**: Implement regular automated backups
4. **Monitoring**: Set up database performance monitoring
5. **Security**: Use SSL connections and proper firewall rules

**Example production MySQL configuration:**
```env
DATABASE_URL="mysql://intrunex_user:secure_password@db-server:3306/intrunex_db?sslmode=require&serverVersion=8.0&charset=utf8mb4"
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
