#!/bin/sh
set -e

DEPLOYMENT_LOCK="/var/www/api/.deployment.lock"

# Bootstrap .env from the example file when needed.
if [ ! -f /var/www/api/.env ]; then
    if [ -f /var/www/api/.env.example ]; then
        echo "Creating .env from .env.example..."
        cp /var/www/api/.env.example /var/www/api/.env
    else
        max_attempts=30
        attempt=1
        while [ ! -f /var/www/api/.env ] && [ $attempt -le $max_attempts ]; do
            echo "Waiting for .env file... (attempt $attempt of $max_attempts)"
            sleep 2
            attempt=$((attempt + 1))
        done
    fi
fi

if [ ! -f /var/www/api/.env ]; then
    echo "Error: .env file not found after bootstrap"
    exit 1
fi

# Load variables from .env file
set -a
. /var/www/api/.env
set +a

# === DEPLOYMENT PHASE (only on first run) ===
if [ ! -f "$DEPLOYMENT_LOCK" ]; then
    echo "=== Running initial deployment setup ==="

    # Configure Git to trust the repository directory
    echo "Configuring Git safe directory..."
    export HOME=/tmp
    mkdir -p "$HOME"
    git config --global --add safe.directory /var/www/api

    # Restore writable Laravel directories for the runtime user
    echo "Setting permissions for storage and cache directories..."
    mkdir -p /var/www/api/storage /var/www/api/bootstrap/cache
    chmod -R 775 /var/www/api/storage /var/www/api/bootstrap/cache
    chown -R www-data:www-data /var/www/api/storage /var/www/api/bootstrap/cache

    # Install Composer dependencies
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader || {
        echo "Error while installing Composer dependencies"
        exit 1
    }
    
    # Generate application key if not set
    if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:CHANGEME" ]; then
        echo "Generating application key..."
        php artisan key:generate --force || {
            echo "Error while generating application key"
            exit 1
        }
        echo "Clearing cached config after key generation..."
        rm -f /var/www/api/bootstrap/cache/config.php || {
            echo "Error while clearing cached config"
            exit 1
        }
    fi

    # Create deployment lock file
    echo "Creating deployment lock file..."
    date > "$DEPLOYMENT_LOCK"
    echo "=== Deployment setup completed ==="
else
    echo "=== Deployment lock found, skipping initial setup ==="
    echo "Lock created at: $(cat $DEPLOYMENT_LOCK)"
fi

# Run PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm
