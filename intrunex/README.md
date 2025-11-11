# IntruneX Project Setup

This guide provides instructions to set up and run the IntruneX project from scratch.

## Prerequisites

Before you begin, ensure you have the following installed:

*   Docker and Docker Compose
*   PHP (version 8.1 or higher)
*   Composer
*   Node.js and npm

## Setup Instructions

Follow these steps to get the project running:

### 1. Environment Configuration

Create a `.env.local` file in the `intrunex/` directory by copying `.env.dev` or `.env.test` and adjust the variables as needed.
For example:
```bash
cp .env.dev .env.local
```
Ensure the `DATABASE_URL` in your `.env.local` file is correctly configured for the PostgreSQL service defined in `compose.yaml`.

### 2. Start Docker Containers

Navigate to the `intrunex/` directory and start the Docker containers:

```bash
cd intrunex/
docker-compose up -d
```

This will start the PostgreSQL database service.

### 3. Install PHP Dependencies

While still in the `intrunex/` directory, install the PHP dependencies using Composer:

```bash
composer install
```

### 4. Install Node.js Dependencies

Install the Node.js dependencies using npm:

```bash
npm install
```

### 5. Build Frontend Assets

Build the frontend assets using Webpack:

```bash
npm run build
```

### 6. Database Migrations

Run the database migrations to set up the database schema:

```bash
php bin/console doctrine:migrations:migrate
```

If you want to load initial data, you can run the fixtures (optional):

```bash
php bin/console doctrine:fixtures:load
```

### 7. Start the Symfony Application

The Symfony application can be served using the Symfony local web server or by configuring a web server like Nginx or Apache.

To use the Symfony local web server:

```bash
symfony server:start
```

Alternatively, you can access the application via the web server configured in your Docker setup (if applicable) or by pointing your local web server to the `public/` directory.

The application should now be accessible in your web browser.