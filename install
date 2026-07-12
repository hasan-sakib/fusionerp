#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# FusionERP — Initial Installation Script
# Run this ONCE on your host machine to scaffold the Laravel application.
# Requires: PHP 8.4+, Composer, Node 22+, npm
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "════════════════════════════════════════════════════════════"
echo " FusionERP — Laravel 12 Installation"
echo "════════════════════════════════════════════════════════════"

# 1. Install Laravel 12
echo "→ Installing Laravel 12..."
composer create-project laravel/laravel:^12.0 laravel_tmp --no-interaction

# Move Laravel files into project root (skip docker files we already have)
shopt -s dotglob
for item in laravel_tmp/*; do
    name=$(basename "$item")
    # Skip files we've already created
    if [[ "$name" != "Dockerfile" && "$name" != "docker-compose.yml" && "$name" != "docker" && "$name" != "install.sh" && "$name" != ".git" ]]; then
        mv "$item" .
    fi
done
rm -rf laravel_tmp
shopt -u dotglob

# 2. Install PHP packages
echo "→ Installing PHP dependencies..."
composer require \
    spatie/laravel-permission:^6.0 \
    intervention/image:^3.0 \
    maatwebsite/excel:^3.1 \
    barryvdh/laravel-dompdf:^3.0 \
    --no-interaction

composer require --dev \
    laravel/telescope \
    --no-interaction

# 3. Install Breeze (Blade + Alpine)
echo "→ Installing Laravel Breeze..."
composer require laravel/breeze:^2.0 --dev --no-interaction
php artisan breeze:install blade --no-interaction

# 4. Install Node dependencies and build
echo "→ Installing Node dependencies..."
npm install
npm run build

# 5. Environment file
echo "→ Setting up .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

echo ""
echo "════════════════════════════════════════════════════════════"
echo " Installation complete!"
echo " Next steps:"
echo "   1. Edit .env with your DB credentials"
echo "   2. docker-compose up -d"
echo "   3. docker-compose exec app php artisan migrate --seed"
echo "════════════════════════════════════════════════════════════"
