#!/usr/bin/env bash
set -euo pipefail

echo "Installing composer dependencies..."
composer install --quiet --no-interaction 2>&1 || echo "  WARNING: composer install failed"

echo "Installing chromedriver..."
./vendor/bin/dusk-updater detect --auto-update 2>&1 || echo "  WARNING: chromedriver install failed"

echo "Installing npm dependencies..."
npm install --silent 2>&1 || echo "  WARNING: npm install failed"
