#!/bin/bash
# ── LNC Backend Install Script ────────────────────────────────
# Run this ONCE on the server after cloning the repository.
# Usage: bash backend/install.sh

set -e
cd "$(dirname "$0")"

echo "=== LNC Backend Setup ==="

# Check PHP
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")
echo "PHP version: $PHP_VERSION"

# Check Composer
if ! command -v composer &>/dev/null; then
  echo "Installing Composer..."
  curl -sS https://getcomposer.org/installer | php
  sudo mv composer.phar /usr/local/bin/composer
fi

# Install dependencies
echo ""
echo "[1/6] Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Configure environment
if [ ! -f .env ]; then
  echo "[2/6] Copying .env.example to .env..."
  cp .env.example .env
  echo "      ⚠ Edit .env with your real credentials before continuing!"
  echo "      Press Enter when ready..."
  read
else
  echo "[2/6] .env already exists, skipping copy."
fi

# Generate app key
echo "[3/6] Generating app key..."
php artisan key:generate

# Run migrations
echo "[4/6] Running database migrations..."
php artisan migrate --force

# Seed database
echo "[5/6] Seeding database with initial content..."
php artisan db:seed --force

# Optimize
echo "[6/6] Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

echo ""
echo "=== Setup complete! ==="
echo "Admin panel: https://lomboknatureculture.com/admin"
echo "Create admin: php artisan make:filament-user"
