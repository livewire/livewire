#!/usr/bin/env bash
set -euo pipefail

# ── Install deps ─────────────────────────────────────────────────────────────
echo "Installing composer dependencies..."
(cd "$DEST" && composer install --quiet --no-interaction 2>&1) || {
    echo "  WARNING: composer install failed"
}

echo "Installing chromedriver..."
(cd "$DEST" && ./vendor/bin/dusk-updater detect --auto-update 2>&1) || {
    echo "  WARNING: chromedriver install failed"
}

echo "Installing npm dependencies..."
(cd "$DEST" && npm install --silent 2>&1) || {
    echo "  WARNING: npm install failed"
}
